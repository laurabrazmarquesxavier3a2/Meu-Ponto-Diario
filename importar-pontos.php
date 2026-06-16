<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

$idEmpresa = (int) ($_SESSION['id_empresa'] ?? 0);

if ($idEmpresa <= 0) {
    die('Empresa não identificada.');
}

$mensagem = '';
$erros = [];

$importados = 0;
$atualizados = 0;
$ignorados = 0;

$dominioAntigo = 'empresa.com';
$dominioPrincipal = 'technova.com.br';

/**
 * Ajusta o domínio somente para localizar o funcionário.
 */
function prepararEmailParaBusca(
    $email,
    $dominioAntigo,
    $dominioPrincipal
): string {
    $email = trim(mb_strtolower((string) $email, 'UTF-8'));

    if ($email === '') {
        return '';
    }

    if (strpos($email, '@') === false) {
        return $email . '@' . $dominioPrincipal;
    }

    [$usuario, $dominio] = array_pad(
        explode('@', $email, 2),
        2,
        ''
    );

    $usuario = trim($usuario);
    $dominio = trim($dominio);

    if ($usuario === '') {
        return '';
    }

    if ($dominio === $dominioAntigo) {
        return $usuario . '@' . $dominioPrincipal;
    }

    return $email;
}

/**
 * Normaliza um horário recebido pela API.
 */
function normalizarHora($hora): ?string
{
    $hora = trim((string) $hora);

    if (
        $hora === '' ||
        $hora === '00:00:00' ||
        $hora === '00:00'
    ) {
        return null;
    }

    $formatos = [
        'H:i:s',
        'H:i'
    ];

    foreach ($formatos as $formato) {
        $dataHora = DateTime::createFromFormat(
            $formato,
            $hora
        );

        if (
            $dataHora instanceof DateTime &&
            $dataHora->format($formato) === $hora
        ) {
            return $dataHora->format('H:i:s');
        }
    }

    $timestamp = strtotime($hora);

    if ($timestamp === false) {
        return null;
    }

    return date('H:i:s', $timestamp);
}

/**
 * Converte horário para quantidade de segundos desde 00:00.
 */
function horarioParaSegundos(?string $hora): ?int
{
    if (empty($hora)) {
        return null;
    }

    $partes = explode(':', $hora);

    if (count($partes) < 2) {
        return null;
    }

    $horas = (int) ($partes[0] ?? 0);
    $minutos = (int) ($partes[1] ?? 0);
    $segundos = (int) ($partes[2] ?? 0);

    return
        ($horas * 3600) +
        ($minutos * 60) +
        $segundos;
}

/**
 * Calcula a diferença entre dois horários.
 * Também aceita uma jornada que termine depois da meia-noite.
 */
function diferencaEntreHorarios(
    ?string $inicio,
    ?string $fim
): int {
    $inicioSegundos = horarioParaSegundos($inicio);
    $fimSegundos = horarioParaSegundos($fim);

    if (
        $inicioSegundos === null ||
        $fimSegundos === null
    ) {
        return 0;
    }

    if ($fimSegundos < $inicioSegundos) {
        $fimSegundos += 86400;
    }

    return max(
        0,
        $fimSegundos - $inicioSegundos
    );
}

/**
 * Calcula exatamente os minutos trabalhados.
 */
function calcularMinutosTrabalhados(
    ?string $entrada,
    ?string $saida,
    ?string $saidaIntervalo = null,
    ?string $retornoIntervalo = null
): ?int {
    if (empty($entrada) || empty($saida)) {
        return null;
    }

    $segundosTrabalhados = diferencaEntreHorarios(
        $entrada,
        $saida
    );

    if (
        !empty($saidaIntervalo) &&
        !empty($retornoIntervalo)
    ) {
        $segundosIntervalo = diferencaEntreHorarios(
            $saidaIntervalo,
            $retornoIntervalo
        );

        $segundosTrabalhados -= $segundosIntervalo;
    }

    if ($segundosTrabalhados < 0) {
        return null;
    }

    return (int) round(
        $segundosTrabalhados / 60
    );
}

/**
 * Converte minutos para o formato decimal mantido na coluna total_horas.
 *
 * Exemplo:
 * 489 minutos = 8.15 horas decimais.
 */
function minutosParaHorasDecimais(?int $minutos): ?float
{
    if ($minutos === null) {
        return null;
    }

    return round($minutos / 60, 4);
}

/**
 * Define o status do registro.
 */
function definirStatusImportacao(
    ?string $entrada,
    ?string $saida,
    ?int $totalMinutos
): string {
    if (empty($entrada)) {
        return 'ausente';
    }

    if (empty($saida)) {
        return 'em andamento';
    }

    if ($totalMinutos === null) {
        return 'em andamento';
    }

    if ($totalMinutos > 480) {
        return 'hora_extra';
    }

    if ($totalMinutos === 480) {
        return 'completo';
    }

    return 'atraso';
}

/**
 * Importa ou atualiza um registro de ponto.
 */
function importarRegistroPonto(
    mysqli $con,
    int $idEmpresa,
    $emailApi,
    $data,
    $entrada,
    $saidaIntervalo,
    $retornoIntervalo,
    $saida
): void {
    global $importados;
    global $atualizados;
    global $ignorados;
    global $erros;
    global $dominioAntigo;
    global $dominioPrincipal;

    $emailOriginalApi = trim(
        mb_strtolower((string) $emailApi, 'UTF-8')
    );

    $emailBusca = prepararEmailParaBusca(
        $emailOriginalApi,
        $dominioAntigo,
        $dominioPrincipal
    );

    $data = trim((string) $data);

    $entrada = normalizarHora($entrada);
    $saidaIntervalo = normalizarHora($saidaIntervalo);
    $retornoIntervalo = normalizarHora($retornoIntervalo);
    $saida = normalizarHora($saida);

    if ($emailBusca === '' || $data === '') {
        $ignorados++;

        $erros[] =
            'Registro ignorado: e-mail ou data vazios.';

        return;
    }

    $dataValida = DateTime::createFromFormat(
        'Y-m-d',
        $data
    );

    if (
        !$dataValida ||
        $dataValida->format('Y-m-d') !== $data
    ) {
        $ignorados++;

        $erros[] =
            "Data inválida recebida para {$emailOriginalApi}: {$data}";

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | LOCALIZA O USUÁRIO
    |--------------------------------------------------------------------------
    */

    $stmtUser = $con->prepare("
        SELECT
            id_funcionario,
            id_empresa,
            email

        FROM usuarios

        WHERE email = ?
          AND id_empresa = ?

        LIMIT 1
    ");

    if (!$stmtUser) {
        die('Erro prepare usuário: ' . $con->error);
    }

    $stmtUser->bind_param(
        'si',
        $emailBusca,
        $idEmpresa
    );

    $stmtUser->execute();

    $usuario = $stmtUser
        ->get_result()
        ->fetch_assoc();

    $stmtUser->close();

    /*
    |--------------------------------------------------------------------------
    | SEGUNDA BUSCA PELO NOME ANTES DO @
    |--------------------------------------------------------------------------
    */

    if (
        !$usuario ||
        empty($usuario['id_funcionario'])
    ) {
        $usuarioEmail = explode(
            '@',
            $emailBusca,
            2
        )[0];

        $stmtUserAlt = $con->prepare("
            SELECT
                id_funcionario,
                id_empresa,
                email

            FROM usuarios

            WHERE SUBSTRING_INDEX(email, '@', 1) = ?
              AND id_empresa = ?

            LIMIT 1
        ");

        if (!$stmtUserAlt) {
            die(
                'Erro prepare usuário alternativo: ' .
                $con->error
            );
        }

        $stmtUserAlt->bind_param(
            'si',
            $usuarioEmail,
            $idEmpresa
        );

        $stmtUserAlt->execute();

        $usuario = $stmtUserAlt
            ->get_result()
            ->fetch_assoc();

        $stmtUserAlt->close();
    }

    if (
        !$usuario ||
        empty($usuario['id_funcionario'])
    ) {
        $ignorados++;

        $erros[] =
            "Funcionário não encontrado. API enviou: " .
            "{$emailOriginalApi} | busca usada: {$emailBusca}";

        return;
    }

    $idFuncionario = (int) $usuario['id_funcionario'];
    $idEmpresaFuncionario = (int) $usuario['id_empresa'];
    $emailEncontradoBanco = $usuario['email'];

    /*
    |--------------------------------------------------------------------------
    | CONFERE O FUNCIONÁRIO
    |--------------------------------------------------------------------------
    */

    $stmtFunc = $con->prepare("
        SELECT id_funcionario

        FROM funcionarios

        WHERE id_funcionario = ?
          AND id_empresa = ?

        LIMIT 1
    ");

    if (!$stmtFunc) {
        die('Erro prepare funcionário: ' . $con->error);
    }

    $stmtFunc->bind_param(
        'ii',
        $idFuncionario,
        $idEmpresaFuncionario
    );

    $stmtFunc->execute();

    $funcionario = $stmtFunc
        ->get_result()
        ->fetch_assoc();

    $stmtFunc->close();

    if (!$funcionario) {
        $ignorados++;

        $erros[] =
            "O usuário {$emailEncontradoBanco} existe, mas o " .
            'funcionário vinculado não foi encontrado.';

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULA MINUTOS E HORAS DECIMAIS
    |--------------------------------------------------------------------------
    */

    $totalMinutos = calcularMinutosTrabalhados(
        $entrada,
        $saida,
        $saidaIntervalo,
        $retornoIntervalo
    );

    $totalHoras = minutosParaHorasDecimais(
        $totalMinutos
    );

    $status = definirStatusImportacao(
        $entrada,
        $saida,
        $totalMinutos
    );

    /*
    |--------------------------------------------------------------------------
    | VERIFICA REGISTRO EXISTENTE
    |--------------------------------------------------------------------------
    */

    $stmtExiste = $con->prepare("
        SELECT id_ponto

        FROM pontos

        WHERE id_funcionario = ?
          AND id_empresa = ?
          AND data = ?

        LIMIT 1
    ");

    if (!$stmtExiste) {
        die('Erro prepare existe: ' . $con->error);
    }

    $stmtExiste->bind_param(
        'iis',
        $idFuncionario,
        $idEmpresaFuncionario,
        $data
    );

    $stmtExiste->execute();

    $registroExistente = $stmtExiste
        ->get_result()
        ->fetch_assoc();

    $stmtExiste->close();

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    if ($registroExistente) {
        $idPonto = (int) $registroExistente['id_ponto'];

        $stmtUpdate = $con->prepare("
            UPDATE pontos

            SET
                hora_entrada = ?,
                hora_saida = ?,
                total_horas = ?,
                status = ?,
                saida_intervalo = ?,
                retorno_intervalo = ?

            WHERE id_ponto = ?
              AND id_empresa = ?
        ");

        if (!$stmtUpdate) {
            die('Erro prepare update: ' . $con->error);
        }

        $stmtUpdate->bind_param(
            'ssdsssii',
            $entrada,
            $saida,
            $totalHoras,
            $status,
            $saidaIntervalo,
            $retornoIntervalo,
            $idPonto,
            $idEmpresaFuncionario
        );

        if ($stmtUpdate->execute()) {
            $atualizados++;
        } else {
            $erros[] =
                "Erro ao atualizar ponto de {$emailEncontradoBanco} " .
                "em {$data}: {$stmtUpdate->error}";
        }

        $stmtUpdate->close();

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    $stmtInsert = $con->prepare("
        INSERT INTO pontos (
            id_funcionario,
            data,
            hora_entrada,
            hora_saida,
            total_horas,
            status,
            id_empresa,
            saida_intervalo,
            retorno_intervalo
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmtInsert) {
        die('Erro prepare insert: ' . $con->error);
    }

    $stmtInsert->bind_param(
        'isssdsiss',
        $idFuncionario,
        $data,
        $entrada,
        $saida,
        $totalHoras,
        $status,
        $idEmpresaFuncionario,
        $saidaIntervalo,
        $retornoIntervalo
    );

    if ($stmtInsert->execute()) {
        $importados++;
    } else {
        $erros[] =
            "Erro ao importar ponto de {$emailEncontradoBanco} " .
            "em {$data}: {$stmtInsert->error}";
    }

    $stmtInsert->close();
}

/*
|--------------------------------------------------------------------------
| IMPORTAÇÃO DA API
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['api']) &&
    $_GET['api'] === '1'
) {
    $apiUrl =
        'http://localhost/Conexao-Api-Ponto/api/pontos.php';

    $contexto = stream_context_create([
        'http' => [
            'timeout' => 15,
            'ignore_errors' => true
        ]
    ]);

    $json = @file_get_contents(
        $apiUrl,
        false,
        $contexto
    );

    if ($json === false) {
        $erros[] =
            'Erro ao acessar a API. Verifique se o projeto ' .
            'Conexao-Api-Ponto está funcionando.';
    } else {
        $pontosApi = json_decode($json, true);

        if (
            json_last_error() !== JSON_ERROR_NONE
        ) {
            $erros[] =
                'A API retornou um JSON inválido: ' .
                json_last_error_msg();
        } elseif (
            isset($pontosApi['erro']) &&
            $pontosApi['erro'] === true
        ) {
            $erros[] =
                $pontosApi['mensagem'] ??
                'A API retornou um erro.';
        } else {
            /*
             * Compatibilidade com:
             *
             * [
             *   {...}
             * ]
             *
             * e:
             *
             * {
             *   "registros": [
             *      {...}
             *   ]
             * }
             */
            $registrosApi =
                isset($pontosApi['registros']) &&
                is_array($pontosApi['registros'])
                    ? $pontosApi['registros']
                    : $pontosApi;

            if (!is_array($registrosApi)) {
                $erros[] =
                    'A API não retornou uma lista de registros válida.';
            } else {
                foreach ($registrosApi as $registro) {
                    if (!is_array($registro)) {
                        $ignorados++;
                        continue;
                    }

                    $saidaIntervalo =
                        $registro['saida_intervalo']
                        ?? $registro['saida_almoco']
                        ?? null;

                    $retornoIntervalo =
                        $registro['retorno_intervalo']
                        ?? $registro['retorno_almoco']
                        ?? null;

                    importarRegistroPonto(
                        $con,
                        $idEmpresa,
                        $registro['email'] ?? '',
                        $registro['data'] ?? '',
                        $registro['entrada'] ?? null,
                        $saidaIntervalo,
                        $retornoIntervalo,
                        $registro['saida'] ?? null
                    );
                }

                $mensagem =
                    "Sincronização concluída. " .
                    "Importados: {$importados} | " .
                    "Atualizados: {$atualizados} | " .
                    "Ignorados: {$ignorados}";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Importar Pontos</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --page-bg: #f4f7fb;
            --card-bg: #ffffff;
            --card-soft: #eff6ff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #dbeafe;
            --code-bg: #f8fafc;
            --code-text: #1d4ed8;
            --shadow: 0 10px 35px rgba(15, 23, 42, .08);
        }

        body.dark,
        body.dark-mode,
        .dark-mode body {
            --page-bg: #0f172a;
            --card-bg: #111827;
            --card-soft: #162033;
            --text-main: #f8fafc;
            --text-muted: #cbd5e1;
            --border: #334155;
            --code-bg: #1e293b;
            --code-text: #93c5fd;
            --shadow: 0 10px 35px rgba(0, 0, 0, .35);
        }

        body {
            background: var(--page-bg);
            color: var(--text-main);
        }

        .content {
            color: var(--text-main);
        }

        .page-title {
            color: var(--text-main);
        }

        .page-subtitle {
            color: var(--text-muted);
        }

        .api-box,
        .status-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 26px;
            box-shadow: var(--shadow);
            color: var(--text-main);
        }

        .api-box h4,
        .api-box strong,
        .status-card h4,
        .status-card strong {
            color: var(--text-main);
        }

        .api-box p,
        .api-box small,
        .api-box .text-muted,
        .status-card p,
        .status-card small {
            color: var(--text-muted) !important;
        }

        .api-icon {
            width: 54px;
            height: 54px;
            background: #2563eb;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-box {
            background: var(--card-soft);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            color: var(--text-muted);
        }

        code {
            background: var(--code-bg);
            color: var(--code-text);
            padding: 8px 10px;
            border-radius: 10px;
            display: block;
            margin-top: 8px;
            word-break: break-all;
        }

        .alert-success,
        .alert-warning {
            border-radius: 14px;
        }

        body.dark .alert-success,
        body.dark-mode .alert-success {
            background: rgba(22, 163, 74, .18);
            color: #bbf7d0;
            border: 1px solid rgba(34, 197, 94, .35);
        }

        body.dark .alert-warning,
        body.dark-mode .alert-warning {
            background: rgba(245, 158, 11, .16);
            color: #fde68a;
            border: 1px solid rgba(245, 158, 11, .35);
        }

        .btn-outline-primary {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #2563eb;
            color: #ffffff;
        }
    </style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

            <div>

                <h1 class="fw-bold mb-1 page-title">
                    Importar Registros de Ponto
                </h1>

                <p class="mb-0 page-subtitle">

                    Sincronize automaticamente os registros da API externa
                    para o banco principal.

                </p>

            </div>

            <a
                href="ponto.php"
                class="btn btn-outline-primary"
            >

                <i class="bi bi-clock-history"></i>

                Ver registros

            </a>

        </div>

        <?php if ($mensagem !== ''): ?>

            <div class="alert alert-success">

                <?= htmlspecialchars($mensagem) ?>

            </div>

        <?php endif; ?>

        <?php if (count($erros) > 0): ?>

            <div class="alert alert-warning">

                <strong>Avisos:</strong>

                <ul class="mb-0 mt-2">

                    <?php foreach ($erros as $erro): ?>

                        <li>

                            <?= htmlspecialchars($erro) ?>

                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>

        <div class="row g-4">

            <div class="col-lg-7">

                <div class="api-box h-100">

                    <div class="d-flex align-items-center gap-3 mb-3">

                        <div class="api-icon">

                            <i class="bi bi-cloud-arrow-down fs-3"></i>

                        </div>

                        <div>

                            <h4 class="fw-bold mb-0">
                                Importar pela API
                            </h4>

                            <small>
                                Busca os pontos do sistema externo
                            </small>

                        </div>

                    </div>

                    <p>

                        Esse botão busca os registros do projeto
                        <strong>Conexao-Api-Ponto</strong>, que usa o banco
                        <strong>db_ponto</strong>, e salva ou atualiza os
                        dados na tabela <strong>pontos</strong> do banco
                        principal <strong>db_mpd</strong>.

                    </p>

                    <div class="info-box mb-3">

                        O total da jornada é calculado em minutos antes de
                        ser salvo. Isso evita resultados incorretos como
                        interpretar 8,15 horas decimais como 8 horas e
                        15 minutos.

                    </div>

                    <div class="info-box mb-3">

                        Se a API enviar e-mails antigos como
                        <strong>ana@empresa.com</strong>, o sistema procura
                        o usuário como
                        <strong>ana@technova.com.br</strong>, mas não altera
                        nenhum e-mail no banco.

                    </div>

                    <a
                        href="importar-pontos.php?api=1"
                        class="btn btn-primary w-100 py-2"
                    >

                        <i class="bi bi-arrow-repeat"></i>

                        Sincronizar pontos da API

                    </a>

                    <div class="mt-3">

                        <small>API usada:</small>

                        <code>http://localhost/Conexao-Api-Ponto/api/pontos.php</code>

                    </div>

                </div>

            </div>

            <div class="col-lg-5">

                <div class="status-card h-100">

                    <h4 class="fw-bold mb-3">

                        <i class="bi bi-info-circle"></i>

                        Como funciona

                    </h4>

                    <div class="info-box mb-3">

                        O sistema externo registra os pontos no banco
                        <strong>db_ponto</strong>.

                    </div>

                    <div class="info-box mb-3">

                        A API retorna os horários de entrada, intervalo
                        e saída.

                    </div>

                    <div class="info-box mb-3">

                        O importador calcula a jornada exata em minutos,
                        descontando o intervalo.

                    </div>

                    <div class="info-box">

                        Depois salva ou atualiza o registro na tabela
                        <strong>pontos</strong>.

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="js/theme.js"></script>
<script src="js/translate.js"></script>

</body>
</html>