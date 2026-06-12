<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idEmpresa) {
    die("Empresa não identificada.");
}

$mensagem = '';
$erros = [];
$importados = 0;
$atualizados = 0;
$ignorados = 0;

/*
|--------------------------------------------------------------------------
| CONFIGURAÇÃO DE DOMÍNIO
|--------------------------------------------------------------------------
| A API pode vir com @empresa.com.
| O banco principal pode estar com @technova.com.br.
| Este código NÃO altera o banco, apenas usa o e-mail convertido para localizar.
|--------------------------------------------------------------------------
*/

$dominioAntigo = 'empresa.com';
$dominioPrincipal = 'technova.com.br';

/*
|--------------------------------------------------------------------------
| AJUSTA EMAIL APENAS PARA BUSCA
|--------------------------------------------------------------------------
*/

function prepararEmailParaBusca($email, $dominioAntigo, $dominioPrincipal) {
    $email = trim(strtolower($email));

    if ($email === '') {
        return '';
    }

    if (strpos($email, '@') === false) {
        return $email . '@' . $dominioPrincipal;
    }

    $partes = explode('@', $email);
    $usuario = trim($partes[0]);
    $dominio = trim($partes[1] ?? '');

    if ($usuario === '') {
        return '';
    }

    if ($dominio === $dominioAntigo) {
        return $usuario . '@' . $dominioPrincipal;
    }

    return $email;
}

/*
|--------------------------------------------------------------------------
| NORMALIZA HORÁRIO
|--------------------------------------------------------------------------
*/

function normalizarHora($hora) {
    $hora = trim((string)$hora);

    if ($hora === '') {
        return null;
    }

    return $hora;
}

/*
|--------------------------------------------------------------------------
| CALCULA TOTAL DE HORAS
|--------------------------------------------------------------------------
*/

function calcularHoras($entrada, $saida, $saidaIntervalo = null, $retornoIntervalo = null) {
    if (!$entrada || !$saida) {
        return null;
    }

    $inicio = strtotime($entrada);
    $fim = strtotime($saida);

    if ($fim <= $inicio) {
        return null;
    }

    $segundos = $fim - $inicio;

    if (!empty($saidaIntervalo) && !empty($retornoIntervalo)) {
        $inicioIntervalo = strtotime($saidaIntervalo);
        $fimIntervalo = strtotime($retornoIntervalo);

        if ($fimIntervalo > $inicioIntervalo) {
            $segundos -= ($fimIntervalo - $inicioIntervalo);
        }
    }

    return round($segundos / 3600, 2);
}

/*
|--------------------------------------------------------------------------
| IMPORTA UM REGISTRO DE PONTO
|--------------------------------------------------------------------------
*/

function importarRegistroPonto(
    $con,
    $idEmpresa,
    $emailApi,
    $data,
    $entrada,
    $saidaIntervalo,
    $retornoIntervalo,
    $saida
) {
    global $importados, $atualizados, $ignorados, $erros;
    global $dominioAntigo, $dominioPrincipal;

    $emailOriginalApi = trim(strtolower($emailApi));
    $emailBusca = prepararEmailParaBusca($emailOriginalApi, $dominioAntigo, $dominioPrincipal);

    $data = trim($data);

    $entrada = normalizarHora($entrada);
    $saidaIntervalo = normalizarHora($saidaIntervalo);
    $retornoIntervalo = normalizarHora($retornoIntervalo);
    $saida = normalizarHora($saida);

    if ($emailBusca === '' || $data === '') {
        $ignorados++;
        $erros[] = "Registro ignorado: e-mail ou data vazios.";
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | PROCURA O USUÁRIO PELO EMAIL CONVERTIDO APENAS NA BUSCA
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
        die("Erro prepare usuário: " . $con->error);
    }

    $stmtUser->bind_param("si", $emailBusca, $idEmpresa);
    $stmtUser->execute();

    $usuario = $stmtUser->get_result()->fetch_assoc();

    /*
    |--------------------------------------------------------------------------
    | SEGUNDA TENTATIVA: PROCURA SÓ PELO NOME ANTES DO @
    |--------------------------------------------------------------------------
    | Exemplo:
    | API: ana@empresa.com
    | Banco: ana@technova.com.br
    |--------------------------------------------------------------------------
    */

    if (!$usuario || empty($usuario['id_funcionario'])) {

        $usuarioEmail = explode('@', $emailBusca)[0];

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
            die("Erro prepare usuário alternativo: " . $con->error);
        }

        $stmtUserAlt->bind_param("si", $usuarioEmail, $idEmpresa);
        $stmtUserAlt->execute();

        $usuario = $stmtUserAlt->get_result()->fetch_assoc();
    }

    if (!$usuario || empty($usuario['id_funcionario'])) {
        $ignorados++;
        $erros[] = "Funcionário não encontrado. API enviou: $emailOriginalApi | busca usada: $emailBusca";
        return;
    }

    $idFuncionario = (int)$usuario['id_funcionario'];
    $idEmpresaFuncionario = (int)$usuario['id_empresa'];
    $emailEncontradoBanco = $usuario['email'];

    /*
    |--------------------------------------------------------------------------
    | CONFERE SE O FUNCIONÁRIO EXISTE
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
        die("Erro prepare funcionário: " . $con->error);
    }

    $stmtFunc->bind_param("ii", $idFuncionario, $idEmpresaFuncionario);
    $stmtFunc->execute();

    $funcionario = $stmtFunc->get_result()->fetch_assoc();

    if (!$funcionario) {
        $ignorados++;
        $erros[] = "O usuário $emailEncontradoBanco existe, mas o funcionário vinculado não foi encontrado.";
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | TOTAL DE HORAS E STATUS
    |--------------------------------------------------------------------------
    */

    $totalHoras = calcularHoras(
        $entrada,
        $saida,
        $saidaIntervalo,
        $retornoIntervalo
    );

    if (!empty($entrada) && !empty($saida)) {
        $status = 'completo';
    } elseif (!empty($entrada) && empty($saida)) {
        $status = 'em andamento';
    } else {
        $status = 'ausente';
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICA SE JÁ EXISTE PONTO NESSA DATA
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
        die("Erro prepare existe: " . $con->error);
    }

    $stmtExiste->bind_param("iis", $idFuncionario, $idEmpresaFuncionario, $data);
    $stmtExiste->execute();

    $resultadoExiste = $stmtExiste->get_result();

    /*
    |--------------------------------------------------------------------------
    | UPDATE SE JÁ EXISTE
    |--------------------------------------------------------------------------
    */

    if ($resultadoExiste->num_rows > 0) {

        $ponto = $resultadoExiste->fetch_assoc();
        $idPonto = (int)$ponto['id_ponto'];

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
            die("Erro prepare update: " . $con->error);
        }

        $stmtUpdate->bind_param(
            "ssdsssii",
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
            $erros[] = "Erro ao atualizar ponto de $emailEncontradoBanco em $data: " . $stmtUpdate->error;
        }

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT SE NÃO EXISTE
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
        die("Erro prepare insert: " . $con->error);
    }

    $stmtInsert->bind_param(
        "isssdsiss",
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
        $erros[] = "Erro ao importar ponto de $emailEncontradoBanco em $data: " . $stmtInsert->error;
    }
}

/*
|--------------------------------------------------------------------------
| IMPORTAÇÃO PELA API
|--------------------------------------------------------------------------
*/

if (isset($_GET['api']) && $_GET['api'] == '1') {

    $apiUrl = "http://localhost/Conexao-Api-Ponto/api/pontos.php";

    $json = @file_get_contents($apiUrl);

    if ($json === false) {

        $erros[] = "Erro ao acessar a API. Verifique se o projeto Conexao-Api-Ponto está funcionando.";

    } else {

        $pontosApi = json_decode($json, true);

        if (!is_array($pontosApi)) {

            $erros[] = "A API não retornou dados válidos.";

        } else {

            foreach ($pontosApi as $registro) {

                $saidaIntervalo = $registro['saida_intervalo']
                    ?? $registro['saida_almoco']
                    ?? null;

                $retornoIntervalo = $registro['retorno_intervalo']
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

            $mensagem = "Sincronização concluída. Importados: $importados | Atualizados: $atualizados | Ignorados: $ignorados";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Importar Pontos</title>

<link rel="stylesheet" href="css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

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

.api-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 26px;
    box-shadow: var(--shadow);
    color: var(--text-main);
}

.api-box h4,
.api-box strong {
    color: var(--text-main);
}

.api-box p,
.api-box small,
.api-box .text-muted {
    color: var(--text-muted) !important;
}

.api-icon {
    width: 54px;
    height: 54px;
    background: #2563eb;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 24px;
    box-shadow: var(--shadow);
    color: var(--text-main);
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
body.dark-mode .alert-success,
.dark-mode body .alert-success {
    background: rgba(22, 163, 74, .18);
    color: #bbf7d0;
    border: 1px solid rgba(34, 197, 94, .35);
}

body.dark .alert-warning,
body.dark-mode .alert-warning,
.dark-mode body .alert-warning {
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
    color: #fff;
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-1 page-title">Importar Registros de Ponto</h1>
            <p class="mb-0 page-subtitle">
                Sincronize automaticamente os registros da API externa para o banco principal.
            </p>
        </div>

        <a href="ponto.php" class="btn btn-outline-primary">
            <i class="bi bi-clock-history"></i>
            Ver registros
        </a>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <?php if ($erros): ?>
        <div class="alert alert-warning">
            <strong>Avisos:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($erros as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
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
                        <h4 class="fw-bold mb-0">Importar pela API</h4>
                        <small>Busca os pontos do sistema externo</small>
                    </div>
                </div>

                <p>
                    Esse botão busca os registros do projeto
                    <strong>Conexao-Api-Ponto</strong>, que usa o banco
                    <strong>db_ponto</strong>, e salva ou atualiza os dados na tabela
                    <strong>pontos</strong> do banco principal <strong>db_mpd</strong>.
                </p>

                <div class="info-box mb-3">
                    Se a API enviar e-mails antigos como
                    <strong>ana@empresa.com</strong>, o sistema procura o usuário como
                    <strong>ana@technova.com.br</strong>, mas não altera nenhum e-mail no banco.
                </div>

                <a href="importar-pontos.php?api=1" class="btn btn-primary w-100 py-2">
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
                    A API retorna os registros em formato JSON.
                </div>

                <div class="info-box mb-3">
                    Este importador procura o funcionário correspondente no banco
                    <strong>db_mpd</strong>.
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