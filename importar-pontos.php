
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Sao_Paulo');

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

$idEmpresa = (int) ($_SESSION['id_empresa'] ?? 0);

if ($idEmpresa <= 0) {
    die('Empresa não identificada. Faça login novamente.');
}

$mensagem = '';
$erros = [];

$importados = 0;
$atualizados = 0;
$ignorados = 0;

$dominioAntigo = 'empresa.com';
$dominioPrincipal = 'technova.com.br';

/* =========================================================
   FUNÇÕES GERAIS
========================================================= */

function normalizarHora($hora): ?string
{
    $hora = trim((string) $hora);

    if (
        $hora === '' ||
        $hora === '00:00' ||
        $hora === '00:00:00'
    ) {
        return null;
    }

    foreach (['H:i', 'H:i:s'] as $formato) {

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

    return null;
}

function prepararEmailParaBusca(
    string $email,
    string $dominioAntigo,
    string $dominioPrincipal
): string {

    $email = trim(
        mb_strtolower($email, 'UTF-8')
    );

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

    if ($dominio === $dominioAntigo) {
        return $usuario . '@' . $dominioPrincipal;
    }

    return $email;
}

function horaParaSegundos(?string $hora): ?int
{
    if (empty($hora)) {
        return null;
    }

    $partes = explode(':', $hora);

    if (count($partes) < 2) {
        return null;
    }

    return
        ((int) $partes[0] * 3600) +
        ((int) $partes[1] * 60) +
        ((int) ($partes[2] ?? 0));
}

function diferencaHorarios(
    ?string $inicio,
    ?string $fim
): int {

    $inicioSegundos = horaParaSegundos($inicio);
    $fimSegundos = horaParaSegundos($fim);

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

function calcularTotalHoras(
    ?string $entrada,
    ?string $saidaIntervalo,
    ?string $retornoIntervalo,
    ?string $saida
): ?float {

    if (empty($entrada) || empty($saida)) {
        return null;
    }

    $segundos = diferencaHorarios(
        $entrada,
        $saida
    );

    if (
        !empty($saidaIntervalo) &&
        !empty($retornoIntervalo)
    ) {
        $segundos -= diferencaHorarios(
            $saidaIntervalo,
            $retornoIntervalo
        );
    }

    if ($segundos < 0) {
        return null;
    }

    return round(
        $segundos / 3600,
        4
    );
}

function definirStatusPonto(
    ?string $entrada,
    ?string $saida,
    ?float $totalHoras
): string {

    if (empty($entrada)) {
        return 'ausente';
    }

    if (empty($saida)) {
        return 'em andamento';
    }

    if ($totalHoras === null) {
        return 'em andamento';
    }

    if ($totalHoras > 8) {
        return 'hora_extra';
    }

    if ($totalHoras >= 7.92) {
        return 'completo';
    }

    return 'atraso';
}

function conectarDbPonto(): ?mysqli
{
    $tentativas = [
        ['localhost', 'root', 'usbw', 'db_ponto'],
        ['127.0.0.1', 'root', 'usbw', 'db_ponto'],
        ['localhost', 'root', '', 'db_ponto'],
        ['127.0.0.1', 'root', '', 'db_ponto']
    ];

    mysqli_report(MYSQLI_REPORT_OFF);

    foreach ($tentativas as $tentativa) {

        [$host, $usuario, $senha, $banco] =
            $tentativa;

        $conexao = @new mysqli(
            $host,
            $usuario,
            $senha,
            $banco
        );

        if (!$conexao->connect_error) {

            $conexao->set_charset('utf8mb4');

            mysqli_report(
                MYSQLI_REPORT_ERROR |
                MYSQLI_REPORT_STRICT
            );

            return $conexao;
        }
    }

    mysqli_report(
        MYSQLI_REPORT_ERROR |
        MYSQLI_REPORT_STRICT
    );

    return null;
}

/* =========================================================
   BUSCAR FUNCIONÁRIOS
========================================================= */

$stmtFuncionarios = $con->prepare("
    SELECT DISTINCT
        f.id_funcionario,
        f.nome,
        f.cargo,
        u.email

    FROM funcionarios AS f

    LEFT JOIN usuarios AS u
        ON u.id_funcionario = f.id_funcionario
       AND u.id_empresa = f.id_empresa

    WHERE f.id_empresa = ?

    ORDER BY f.nome ASC
");

if (!$stmtFuncionarios) {
    die(
        'Erro ao buscar funcionários: ' .
        $con->error
    );
}

$stmtFuncionarios->bind_param(
    'i',
    $idEmpresa
);

$stmtFuncionarios->execute();

$resultadoFuncionarios =
    $stmtFuncionarios->get_result();

$funcionarios = [];

while (
    $funcionario =
    $resultadoFuncionarios->fetch_assoc()
) {
    $funcionarios[] = $funcionario;
}

$stmtFuncionarios->close();

/* =========================================================
   EDITAR PONTO PELO MODAL
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['editar_ponto'])
) {

    $idFuncionario = (int) (
        $_POST['id_funcionario'] ?? 0
    );

    $dataPonto = trim(
        $_POST['data_ponto'] ?? ''
    );

    $tipoMarcacao = trim(
        $_POST['tipo_marcacao'] ?? ''
    );

    $novaHora = normalizarHora(
        $_POST['nova_hora'] ?? ''
    );

    $tiposPermitidos = [
        'hora_entrada' =>
            'entrada',

        'saida_intervalo' =>
            'saída para o intervalo',

        'retorno_intervalo' =>
            'retorno do intervalo',

        'hora_saida' =>
            'saída definitiva'
    ];

    $dataValida = DateTime::createFromFormat(
        'Y-m-d',
        $dataPonto
    );

    if ($idFuncionario <= 0) {

        $erros[] =
            'Selecione um funcionário válido.';

    } elseif (
        !$dataValida ||
        $dataValida->format('Y-m-d') !== $dataPonto
    ) {

        $erros[] =
            'Informe uma data válida.';

    } elseif (
        !isset($tiposPermitidos[$tipoMarcacao])
    ) {

        $erros[] =
            'Selecione o tipo de marcação.';

    } elseif ($novaHora === null) {

        $erros[] =
            'Informe um horário válido.';

    } else {

        mysqli_begin_transaction($con);

        try {

            /* =============================================
               LOCALIZA FUNCIONÁRIO
            ============================================== */

            $stmtFuncionario = $con->prepare("
                SELECT
                    f.nome,
                    u.email

                FROM funcionarios AS f

                LEFT JOIN usuarios AS u
                    ON u.id_funcionario =
                       f.id_funcionario
                   AND u.id_empresa =
                       f.id_empresa

                WHERE f.id_funcionario = ?
                  AND f.id_empresa = ?

                LIMIT 1
            ");

            if (!$stmtFuncionario) {
                throw new Exception(
                    'Erro ao localizar funcionário: ' .
                    $con->error
                );
            }

            $stmtFuncionario->bind_param(
                'ii',
                $idFuncionario,
                $idEmpresa
            );

            $stmtFuncionario->execute();

            $funcionario = $stmtFuncionario
                ->get_result()
                ->fetch_assoc();

            $stmtFuncionario->close();

            if (!$funcionario) {
                throw new Exception(
                    'Funcionário não encontrado.'
                );
            }

            /* =============================================
               VERIFICA REGISTRO EXISTENTE
            ============================================== */

            $stmtExiste = $con->prepare("
                SELECT
                    id_ponto,
                    hora_entrada,
                    saida_intervalo,
                    retorno_intervalo,
                    hora_saida

                FROM pontos

                WHERE id_funcionario = ?
                  AND id_empresa = ?
                  AND data = ?

                LIMIT 1
            ");

            if (!$stmtExiste) {
                throw new Exception(
                    'Erro ao consultar ponto: ' .
                    $con->error
                );
            }

            $stmtExiste->bind_param(
                'iis',
                $idFuncionario,
                $idEmpresa,
                $dataPonto
            );

            $stmtExiste->execute();

            $ponto = $stmtExiste
                ->get_result()
                ->fetch_assoc();

            $stmtExiste->close();

            /* =============================================
               CRIA O REGISTRO CASO NÃO EXISTA
            ============================================== */

            if (!$ponto) {

                $stmtCriar = $con->prepare("
                    INSERT INTO pontos (
                        id_funcionario,
                        id_empresa,
                        data,
                        status,
                        justificativa
                    )
                    VALUES (
                        ?, ?, ?,
                        'em andamento',
                        'Registro criado manualmente pelo RH.'
                    )
                ");

                if (!$stmtCriar) {
                    throw new Exception(
                        'Erro ao criar ponto: ' .
                        $con->error
                    );
                }

                $stmtCriar->bind_param(
                    'iis',
                    $idFuncionario,
                    $idEmpresa,
                    $dataPonto
                );

                if (!$stmtCriar->execute()) {
                    throw new Exception(
                        'Erro ao criar ponto: ' .
                        $stmtCriar->error
                    );
                }

                $ponto = [
                    'id_ponto' =>
                        (int) $stmtCriar->insert_id,

                    'hora_entrada' => null,
                    'saida_intervalo' => null,
                    'retorno_intervalo' => null,
                    'hora_saida' => null
                ];

                $stmtCriar->close();
            }

            $idPonto =
                (int) $ponto['id_ponto'];

            $horarios = [
                'hora_entrada' =>
                    $ponto['hora_entrada'] ?? null,

                'saida_intervalo' =>
                    $ponto['saida_intervalo'] ?? null,

                'retorno_intervalo' =>
                    $ponto['retorno_intervalo'] ?? null,

                'hora_saida' =>
                    $ponto['hora_saida'] ?? null
            ];

            $horarios[$tipoMarcacao] =
                $novaHora;

            $totalHoras = calcularTotalHoras(
                $horarios['hora_entrada'],
                $horarios['saida_intervalo'],
                $horarios['retorno_intervalo'],
                $horarios['hora_saida']
            );

            $statusPonto = definirStatusPonto(
                $horarios['hora_entrada'],
                $horarios['hora_saida'],
                $totalHoras
            );

            $justificativa =
                'Ponto editado manualmente pelo RH. ' .
                ucfirst(
                    $tiposPermitidos[$tipoMarcacao]
                ) .
                ' alterada para ' .
                substr($novaHora, 0, 5) .
                '.';

            /* =============================================
               ATUALIZA db_mpd
            ============================================== */

            $stmtAtualizar = $con->prepare("
                UPDATE pontos

                SET
                    hora_entrada = ?,
                    saida_intervalo = ?,
                    retorno_intervalo = ?,
                    hora_saida = ?,
                    total_horas = ?,
                    status = ?,
                    justificativa = ?

                WHERE id_ponto = ?
                  AND id_empresa = ?
            ");

            if (!$stmtAtualizar) {
                throw new Exception(
                    'Erro ao preparar atualização: ' .
                    $con->error
                );
            }

            $stmtAtualizar->bind_param(
                'ssssdssii',
                $horarios['hora_entrada'],
                $horarios['saida_intervalo'],
                $horarios['retorno_intervalo'],
                $horarios['hora_saida'],
                $totalHoras,
                $statusPonto,
                $justificativa,
                $idPonto,
                $idEmpresa
            );

            if (!$stmtAtualizar->execute()) {
                throw new Exception(
                    'Erro ao atualizar ponto: ' .
                    $stmtAtualizar->error
                );
            }

            $stmtAtualizar->close();

            /* =============================================
               ATUALIZA TAMBÉM db_ponto
            ============================================== */

            $emailFuncionario = trim(
                $funcionario['email'] ?? ''
            );

            $conPonto = conectarDbPonto();

            if (
                $conPonto &&
                $emailFuncionario !== ''
            ) {

                $stmtExterno = $conPonto->prepare("
                    SELECT id
                    FROM registros_ponto
                    WHERE LOWER(TRIM(email)) =
                          LOWER(TRIM(?))
                      AND data = ?
                    ORDER BY id DESC
                    LIMIT 1
                ");

                if ($stmtExterno) {

                    $stmtExterno->bind_param(
                        'ss',
                        $emailFuncionario,
                        $dataPonto
                    );

                    $stmtExterno->execute();

                    $pontoExterno = $stmtExterno
                        ->get_result()
                        ->fetch_assoc();

                    $stmtExterno->close();

                    if ($pontoExterno) {

                        $idExterno =
                            (int) $pontoExterno['id'];

                        $stmtAtualizarExterno =
                            $conPonto->prepare("
                                UPDATE registros_ponto
                                SET
                                    entrada = ?,
                                    saida_intervalo = ?,
                                    retorno_intervalo = ?,
                                    saida = ?
                                WHERE id = ?
                            ");

                        if ($stmtAtualizarExterno) {

                            $stmtAtualizarExterno
                                ->bind_param(
                                    'ssssi',
                                    $horarios[
                                        'hora_entrada'
                                    ],
                                    $horarios[
                                        'saida_intervalo'
                                    ],
                                    $horarios[
                                        'retorno_intervalo'
                                    ],
                                    $horarios[
                                        'hora_saida'
                                    ],
                                    $idExterno
                                );

                            $stmtAtualizarExterno
                                ->execute();

                            $stmtAtualizarExterno
                                ->close();
                        }

                    } else {

                        $stmtInserirExterno =
                            $conPonto->prepare("
                                INSERT INTO registros_ponto (
                                    email,
                                    data,
                                    entrada,
                                    saida_intervalo,
                                    retorno_intervalo,
                                    saida
                                )
                                VALUES (?, ?, ?, ?, ?, ?)
                            ");

                        if ($stmtInserirExterno) {

                            $stmtInserirExterno
                                ->bind_param(
                                    'ssssss',
                                    $emailFuncionario,
                                    $dataPonto,
                                    $horarios[
                                        'hora_entrada'
                                    ],
                                    $horarios[
                                        'saida_intervalo'
                                    ],
                                    $horarios[
                                        'retorno_intervalo'
                                    ],
                                    $horarios[
                                        'hora_saida'
                                    ]
                                );

                            $stmtInserirExterno
                                ->execute();

                            $stmtInserirExterno
                                ->close();
                        }
                    }
                }

                $conPonto->close();
            }

            mysqli_commit($con);

            $mensagem =
                'Ponto de ' .
                $funcionario['nome'] .
                ' atualizado com sucesso em ' .
                date(
                    'd/m/Y',
                    strtotime($dataPonto)
                ) .
                '.';

        } catch (Throwable $e) {

            mysqli_rollback($con);

            $erros[] =
                $e->getMessage();
        }
    }
}

/* =========================================================
   IMPORTAR REGISTRO DA API
========================================================= */

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

    $emailOriginal = trim(
        mb_strtolower(
            (string) $emailApi,
            'UTF-8'
        )
    );

    $emailBusca = prepararEmailParaBusca(
        $emailOriginal,
        $dominioAntigo,
        $dominioPrincipal
    );

    $data = trim((string) $data);

    $entrada = normalizarHora($entrada);
    $saidaIntervalo =
        normalizarHora($saidaIntervalo);

    $retornoIntervalo =
        normalizarHora($retornoIntervalo);

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
            "Data inválida para {$emailOriginal}: {$data}";

        return;
    }

    /* LOCALIZA USUÁRIO */

    $stmtUsuario = $con->prepare("
        SELECT
            id_funcionario,
            id_empresa,
            email

        FROM usuarios

        WHERE LOWER(TRIM(email)) =
              LOWER(TRIM(?))
          AND id_empresa = ?

        LIMIT 1
    ");

    if (!$stmtUsuario) {
        throw new Exception(
            'Erro ao localizar usuário: ' .
            $con->error
        );
    }

    $stmtUsuario->bind_param(
        'si',
        $emailBusca,
        $idEmpresa
    );

    $stmtUsuario->execute();

    $usuario = $stmtUsuario
        ->get_result()
        ->fetch_assoc();

    $stmtUsuario->close();

    if (
        !$usuario ||
        empty($usuario['id_funcionario'])
    ) {

        $usuarioAntesArroba = explode(
            '@',
            $emailBusca,
            2
        )[0];

        $stmtAlternativo = $con->prepare("
            SELECT
                id_funcionario,
                id_empresa,
                email

            FROM usuarios

            WHERE SUBSTRING_INDEX(email, '@', 1) = ?
              AND id_empresa = ?

            LIMIT 1
        ");

        if ($stmtAlternativo) {

            $stmtAlternativo->bind_param(
                'si',
                $usuarioAntesArroba,
                $idEmpresa
            );

            $stmtAlternativo->execute();

            $usuario = $stmtAlternativo
                ->get_result()
                ->fetch_assoc();

            $stmtAlternativo->close();
        }
    }

    if (
        !$usuario ||
        empty($usuario['id_funcionario'])
    ) {

        $ignorados++;

        $erros[] =
            "Funcionário não encontrado: {$emailOriginal}.";

        return;
    }

    $idFuncionario =
        (int) $usuario['id_funcionario'];

    $idEmpresaFuncionario =
        (int) $usuario['id_empresa'];

    /* EVITA IMPORTAR DURANTE FÉRIAS */

    $stmtFerias = $con->prepare("
        SELECT id_ferias
        FROM ferias
        WHERE id_funcionario = ?
          AND id_empresa = ?
          AND status = 'aprovado'
          AND ? BETWEEN data_inicio AND data_fim
        LIMIT 1
    ");

    if ($stmtFerias) {

        $stmtFerias->bind_param(
            'iis',
            $idFuncionario,
            $idEmpresaFuncionario,
            $data
        );

        $stmtFerias->execute();

        $emFerias =
            $stmtFerias
                ->get_result()
                ->num_rows > 0;

        $stmtFerias->close();

        if ($emFerias) {
            $ignorados++;
            return;
        }
    }

    /* EVITA IMPORTAR DURANTE LICENÇA */

    $stmtLicenca = $con->prepare("
        SELECT id
        FROM licencas_medicas
        WHERE id_funcionario = ?
          AND id_empresa = ?
          AND status IN ('visto', 'aprovado')
          AND ? BETWEEN data_inicio AND data_fim
        LIMIT 1
    ");

    if ($stmtLicenca) {

        $stmtLicenca->bind_param(
            'iis',
            $idFuncionario,
            $idEmpresaFuncionario,
            $data
        );

        $stmtLicenca->execute();

        $emLicenca =
            $stmtLicenca
                ->get_result()
                ->num_rows > 0;

        $stmtLicenca->close();

        if ($emLicenca) {
            $ignorados++;
            return;
        }
    }

    /* EVITA IMPORTAR DURANTE AFASTAMENTO */

    $resultadoTabela = $con->query("
        SHOW TABLES LIKE 'afastamentos'
    ");

    if (
        $resultadoTabela &&
        $resultadoTabela->num_rows > 0
    ) {

        $stmtAfastamento = $con->prepare("
            SELECT id_afastamento
            FROM afastamentos
            WHERE id_funcionario = ?
              AND id_empresa = ?
              AND status = 'aprovado'
              AND ? BETWEEN data_inicio AND data_fim
            LIMIT 1
        ");

        if ($stmtAfastamento) {

            $stmtAfastamento->bind_param(
                'iis',
                $idFuncionario,
                $idEmpresaFuncionario,
                $data
            );

            $stmtAfastamento->execute();

            $afastado =
                $stmtAfastamento
                    ->get_result()
                    ->num_rows > 0;

            $stmtAfastamento->close();

            if ($afastado) {
                $ignorados++;
                return;
            }
        }
    }

    $totalHoras = calcularTotalHoras(
        $entrada,
        $saidaIntervalo,
        $retornoIntervalo,
        $saida
    );

    $statusPonto = definirStatusPonto(
        $entrada,
        $saida,
        $totalHoras
    );

    $stmtExiste = $con->prepare("
        SELECT id_ponto
        FROM pontos
        WHERE id_funcionario = ?
          AND id_empresa = ?
          AND data = ?
        LIMIT 1
    ");

    if (!$stmtExiste) {
        throw new Exception(
            'Erro ao verificar registro: ' .
            $con->error
        );
    }

    $stmtExiste->bind_param(
        'iis',
        $idFuncionario,
        $idEmpresaFuncionario,
        $data
    );

    $stmtExiste->execute();

    $registroExistente =
        $stmtExiste
            ->get_result()
            ->fetch_assoc();

    $stmtExiste->close();

    if ($registroExistente) {

        $idPonto =
            (int) $registroExistente['id_ponto'];

        $stmtAtualizar = $con->prepare("
            UPDATE pontos
            SET
                hora_entrada = ?,
                saida_intervalo = ?,
                retorno_intervalo = ?,
                hora_saida = ?,
                total_horas = ?,
                status = ?
            WHERE id_ponto = ?
              AND id_empresa = ?
        ");

        if (!$stmtAtualizar) {
            throw new Exception(
                'Erro ao atualizar ponto: ' .
                $con->error
            );
        }

        $stmtAtualizar->bind_param(
            'ssssdsii',
            $entrada,
            $saidaIntervalo,
            $retornoIntervalo,
            $saida,
            $totalHoras,
            $statusPonto,
            $idPonto,
            $idEmpresaFuncionario
        );

        if ($stmtAtualizar->execute()) {
            $atualizados++;
        }

        $stmtAtualizar->close();

    } else {

        $stmtInserir = $con->prepare("
            INSERT INTO pontos (
                id_funcionario,
                id_empresa,
                data,
                hora_entrada,
                saida_intervalo,
                retorno_intervalo,
                hora_saida,
                total_horas,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmtInserir) {
            throw new Exception(
                'Erro ao inserir ponto: ' .
                $con->error
            );
        }

        $stmtInserir->bind_param(
            'iisssssds',
            $idFuncionario,
            $idEmpresaFuncionario,
            $data,
            $entrada,
            $saidaIntervalo,
            $retornoIntervalo,
            $saida,
            $totalHoras,
            $statusPonto
        );

        if ($stmtInserir->execute()) {
            $importados++;
        }

        $stmtInserir->close();
    }
}

/* =========================================================
   IMPORTAÇÃO PELA API
========================================================= */

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
            'Não foi possível acessar a API externa.';

    } else {

        $resposta = json_decode(
            $json,
            true
        );

        if (
            json_last_error() !==
            JSON_ERROR_NONE
        ) {

            $erros[] =
                'A API retornou JSON inválido: ' .
                json_last_error_msg();

        } else {

            $registros =
                isset($resposta['registros']) &&
                is_array($resposta['registros'])
                    ? $resposta['registros']
                    : $resposta;

            if (!is_array($registros)) {

                $erros[] =
                    'A API não retornou uma lista válida.';

            } else {

                mysqli_begin_transaction($con);

                try {

                    foreach ($registros as $registro) {

                        if (!is_array($registro)) {
                            $ignorados++;
                            continue;
                        }

                        importarRegistroPonto(
                            $con,
                            $idEmpresa,
                            $registro['email'] ?? '',
                            $registro['data'] ?? '',
                            $registro['entrada'] ?? null,
                            $registro['saida_intervalo']
                                ?? $registro['saida_almoco']
                                ?? null,
                            $registro['retorno_intervalo']
                                ?? $registro['retorno_almoco']
                                ?? null,
                            $registro['saida'] ?? null
                        );
                    }

                    mysqli_commit($con);

                    $mensagem =
                        "Sincronização concluída. " .
                        "Importados: {$importados} | " .
                        "Atualizados: {$atualizados} | " .
                        "Ignorados: {$ignorados}.";

                } catch (Throwable $e) {

                    mysqli_rollback($con);

                    $erros[] =
                        'Importação cancelada: ' .
                        $e->getMessage();
                }
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

    <link
        rel="stylesheet"
        href="css/style.css"
    >

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
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #dbeafe;
            --soft: #f8fafc;
            --shadow:
                0 10px 35px
                rgba(15, 23, 42, .08);
        }

        body.dark,
        body.dark-mode,
        .dark-mode body {
            --page-bg: #0f172a;
            --card-bg: #111827;
            --text-main: #f8fafc;
            --text-muted: #cbd5e1;
            --border: #334155;
            --soft: #1e293b;
            --shadow:
                0 10px 35px
                rgba(0, 0, 0, .35);
        }

        body {
            background: var(--page-bg);
            color: var(--text-main);
        }

        .page-title {
            color: var(--text-main);
        }

        .page-subtitle {
            color: var(--text-muted);
        }

        .api-box {
            background: var(--card-bg);
            color: var(--text-main);

            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 26px;

            box-shadow: var(--shadow);
        }

        .api-box p,
        .api-box small {
            color: var(--text-muted);
        }

        .api-icon {
            width: 54px;
            height: 54px;
            flex: 0 0 54px;

            border-radius: 50%;

            background: #2563eb;
            color: #ffffff;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--card-bg);
            color: var(--text-main);
            border: 0;
            border-radius: 20px;
        }

        .modal-body {
            background: var(--card-bg);
        }

        .form-control,
        .form-select {
            background-color: var(--card-bg);
            color: var(--text-main);
            border-color: var(--border);
        }

        .form-control:focus,
        .form-select:focus {
            background-color: var(--card-bg);
            color: var(--text-main);
        }

        .employee-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;

            z-index: 1060;

            max-height: 210px;
            overflow-y: auto;

            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 0 0 12px 12px;

            box-shadow: var(--shadow);
        }

        .employee-result {
            width: 100%;
            border: 0;
            border-bottom: 1px solid var(--border);
            background: transparent;
            color: var(--text-main);

            padding: 10px 12px;
            text-align: left;
        }

        .employee-result:hover {
            background: var(--soft);
        }

        .employee-result:last-child {
            border-bottom: 0;
        }

        .modal {
            z-index: 99999 !important;
        }

        .modal-backdrop {
            z-index: 99998 !important;
        }

    </style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid">

        <div
            class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4"
        >

            <div>

                <h1 class="fw-bold mb-1 page-title">
                    Importar Histórico de Ponto
                </h1>

                <p class="mb-0 page-subtitle">
                    Sincronize e corrija os pontos dos funcionários.
                </p>

            </div>

            <a
                href="ponto.php"
                class="btn btn-outline-primary"
            >
                <i class="bi bi-clock-history me-1"></i>
                Ver registros
            </a>

        </div>

        <?php if ($mensagem !== ''): ?>

            <div class="alert alert-success rounded-4">

                <i class="bi bi-check-circle-fill me-2"></i>

                <?= htmlspecialchars($mensagem) ?>

            </div>

        <?php endif; ?>

        <?php if (!empty($erros)): ?>

            <div class="alert alert-warning rounded-4">

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

            <!-- IMPORTAR -->

            <div class="col-12">

                <div class="api-box">

                    <div class="d-flex align-items-center gap-3 mb-3">

                        <div class="api-icon">
                            <i class="bi bi-cloud-arrow-down fs-3"></i>
                        </div>

                        <div>

                            <h4 class="fw-bold mb-0">
                                Importar pontos
                            </h4>

                            <small>
                                Sincronize os registros externos
                            </small>

                        </div>

                    </div>

                    <p class="mb-4">

                        Busca os registros do sistema externo e
                        importa ou atualiza os pontos dos funcionários.

                    </p>

                    <a
                        href="importar-pontos.php?api=1"
                        class="btn btn-primary w-100 py-2"
                    >
                        <i class="bi bi-arrow-repeat me-1"></i>
                        Sincronizar pontos dos funcionários
                    </a>

                </div>

            </div>

            <!-- EDITAR -->

            <div class="col-12">

                <div class="api-box">

                    <div class="d-flex align-items-center gap-3 mb-3">

                        <div class="api-icon">
                            <i class="bi bi-pencil-square fs-3"></i>
                        </div>

                        <div>

                            <h4 class="fw-bold mb-0">
                                Editar ponto
                            </h4>

                            <small>
                                Corrija uma marcação manualmente
                            </small>

                        </div>

                    </div>

                    <p class="mb-4">

                        Procure o funcionário pelo nome, informe a data
                        e escolha qual horário deseja corrigir.

                    </p>

                    <button
                        type="button"
                        class="btn btn-outline-primary w-100 py-2"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditarPonto"
                    >
                        <i class="bi bi-pencil-square me-1"></i>
                        Editar ponto
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- =====================================================
     MODAL EDITAR PONTO
====================================================== -->

<div
    class="modal fade"
    id="modalEditarPonto"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content shadow">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title fw-bold">

                    <i class="bi bi-pencil-square me-2"></i>

                    Editar ponto do funcionário

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>

            </div>

            <form method="POST">

                <div class="modal-body p-4">

                    <input
                        type="hidden"
                        name="id_funcionario"
                        id="idFuncionarioPonto"
                    >

                    <div class="row g-3">

                        <div class="col-12">

                            <label class="form-label fw-bold">
                                Funcionário
                            </label>

                            <div class="position-relative">

                                <input
                                    type="text"
                                    id="buscaFuncionarioPonto"
                                    class="form-control"
                                    placeholder="Digite o nome do funcionário"
                                    autocomplete="off"
                                    required
                                >

                                <div
                                    class="employee-results d-none"
                                    id="resultadosFuncionarios"
                                ></div>

                            </div>

                            <small
                                class="text-muted"
                                id="funcionarioSelecionadoTexto"
                            >
                                Digite e selecione um funcionário.
                            </small>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Data
                            </label>

                            <input
                                type="date"
                                name="data_ponto"
                                class="form-control"
                                value="<?= date('Y-m-d') ?>"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Tipo de marcação
                            </label>

                            <select
                                name="tipo_marcacao"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Selecione
                                </option>

                                <option value="hora_entrada">
                                    Entrada
                                </option>

                                <option value="saida_intervalo">
                                    Ida ao intervalo
                                </option>

                                <option value="retorno_intervalo">
                                    Retorno do intervalo
                                </option>

                                <option value="hora_saida">
                                    Saída definitiva
                                </option>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Novo horário
                            </label>

                            <input
                                type="time"
                                name="nova_hora"
                                class="form-control"
                                step="60"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                Resultado
                            </label>

                            <div class="form-control bg-light">

                                O total e o status serão recalculados
                                automaticamente.

                            </div>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        name="editar_ponto"
                        class="btn btn-primary"
                    >

                        <i class="bi bi-check-lg me-1"></i>

                        Salvar alteração

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<script>

const funcionariosPonto = <?= json_encode(
    $funcionarios,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
) ?>;

const buscaFuncionario =
    document.getElementById(
        'buscaFuncionarioPonto'
    );

const resultadosFuncionarios =
    document.getElementById(
        'resultadosFuncionarios'
    );

const idFuncionarioPonto =
    document.getElementById(
        'idFuncionarioPonto'
    );

const funcionarioSelecionadoTexto =
    document.getElementById(
        'funcionarioSelecionadoTexto'
    );

function escaparHtml(texto) {

    const elemento =
        document.createElement('div');

    elemento.textContent =
        texto === null ||
        texto === undefined
            ? ''
            : String(texto);

    return elemento.innerHTML;
}

function mostrarFuncionarios(termo) {

    termo = termo.trim().toLowerCase();

    resultadosFuncionarios.innerHTML = '';

    idFuncionarioPonto.value = '';

    funcionarioSelecionadoTexto.textContent =
        'Digite e selecione um funcionário.';

    if (termo.length < 1) {

        resultadosFuncionarios.classList.add(
            'd-none'
        );

        return;
    }

    const encontrados =
        funcionariosPonto
            .filter(function (funcionario) {

                return String(
                    funcionario.nome || ''
                )
                    .toLowerCase()
                    .includes(termo);
            })
            .slice(0, 10);

    if (encontrados.length === 0) {

        resultadosFuncionarios.innerHTML = `
            <div class="p-3 text-muted">
                Nenhum funcionário encontrado.
            </div>
        `;

        resultadosFuncionarios.classList.remove(
            'd-none'
        );

        return;
    }

    encontrados.forEach(function (funcionario) {

        const botao =
            document.createElement('button');

        botao.type = 'button';

        botao.className =
            'employee-result';

        botao.innerHTML = `
            <strong>
                ${escaparHtml(funcionario.nome)}
            </strong>

            <small class="d-block text-muted">
                ${escaparHtml(
                    funcionario.cargo || 'Sem cargo'
                )}
            </small>
        `;

        botao.addEventListener(
            'click',
            function () {

                idFuncionarioPonto.value =
                    funcionario.id_funcionario;

                buscaFuncionario.value =
                    funcionario.nome;

                funcionarioSelecionadoTexto.textContent =
                    'Selecionado: ' +
                    funcionario.nome;

                resultadosFuncionarios.classList.add(
                    'd-none'
                );
            }
        );

        resultadosFuncionarios.appendChild(
            botao
        );
    });

    resultadosFuncionarios.classList.remove(
        'd-none'
    );
}

buscaFuncionario.addEventListener(
    'input',
    function () {

        mostrarFuncionarios(
            this.value
        );
    }
);

document.addEventListener(
    'click',
    function (evento) {

        if (
            !buscaFuncionario.contains(evento.target) &&
            !resultadosFuncionarios.contains(
                evento.target
            )
        ) {
            resultadosFuncionarios.classList.add(
                'd-none'
            );
        }
    }
);

const formularioEditarPonto =
    document.querySelector(
        '#modalEditarPonto form'
    );

formularioEditarPonto.addEventListener(
    'submit',
    function (evento) {

        if (
            !idFuncionarioPonto.value ||
            parseInt(
                idFuncionarioPonto.value,
                10
            ) <= 0
        ) {

            evento.preventDefault();

            alert(
                'Digite o nome e selecione um funcionário da lista.'
            );

            buscaFuncionario.focus();
        }
    }
);

/*
 * Se uma alteração tiver sido enviada com erro,
 * mantém o modal aberto para o usuário corrigir.
 */
<?php if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['editar_ponto']) &&
    !empty($erros)
): ?>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const modal =
            bootstrap.Modal.getOrCreateInstance(
                document.getElementById(
                    'modalEditarPonto'
                )
            );

        modal.show();
    }
);

<?php endif; ?>

</script>

<script src="js/theme.js"></script>
</body>
</html>