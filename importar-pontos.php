<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth.php';
require_once 'config/database.php';

$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idEmpresa) {
    die("Empresa não identificada.");
}

$mensagem = '';
$erros = [];
$importados = 0;

function calcularHoras($entrada, $saida) {
    if (!$entrada || !$saida) {
        return null;
    }

    $inicio = strtotime($entrada);
    $fim = strtotime($saida);

    if ($fim <= $inicio) {
        return null;
    }

    return round(($fim - $inicio) / 3600, 2);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['arquivo'])) {

    $arquivo = $_FILES['arquivo']['tmp_name'];
    $handle = fopen($arquivo, "r");

    if (!$handle) {
        die("Não foi possível abrir o arquivo.");
    }

    $cabecalho = fgets($handle);
    $separador = substr_count($cabecalho, ";") > substr_count($cabecalho, ",") ? ";" : ",";
    rewind($handle);

    $linha = 0;

    while (($dados = fgetcsv($handle, 3000, $separador)) !== false) {

        $linha++;

        if ($linha == 1) {
            continue;
        }

        if (count($dados) < 6) {
            $erros[] = "Linha $linha ignorada: dados incompletos.";
            continue;
        }

        $email = trim($dados[0]);
        $data = trim($dados[1]);
        $entrada = trim($dados[2]);
        $saida = trim($dados[3]);
        $totalHoras = trim($dados[4]);
        $status = trim($dados[5]);
        $justificativa = $dados[6] ?? null;

        if ($totalHoras === '') {
            $totalHoras = calcularHoras($entrada, $saida);
        } else {
            $totalHoras = floatval(str_replace(',', '.', $totalHoras));
        }

        if ($status == '') {
            $status = 'completo';
        }

        $statusPermitidos = ['completo', 'atraso', 'em andamento', 'ausente'];

        if (!in_array($status, $statusPermitidos)) {
            $status = 'completo';
        }

        $stmtUser = $con->prepare("
            SELECT id_funcionario
            FROM usuarios
            WHERE email = ?
            AND id_empresa = ?
            LIMIT 1
        ");

        $stmtUser->bind_param("si", $email, $idEmpresa);
        $stmtUser->execute();

        $usuario = $stmtUser->get_result()->fetch_assoc();

        if (!$usuario || empty($usuario['id_funcionario'])) {
            $erros[] = "Linha $linha ignorada: funcionário não encontrado ($email).";
            continue;
        }

        $idFuncionario = $usuario['id_funcionario'];

        $stmtExiste = $con->prepare("
            SELECT id_ponto
            FROM pontos
            WHERE id_funcionario = ?
            AND id_empresa = ?
            AND data = ?
            LIMIT 1
        ");

        $stmtExiste->bind_param("iis", $idFuncionario, $idEmpresa, $data);
        $stmtExiste->execute();

        if ($stmtExiste->get_result()->num_rows > 0) {
            $erros[] = "Linha $linha ignorada: ponto já existe para $email em $data.";
            continue;
        }

        $stmt = $con->prepare("
            INSERT INTO pontos (
                id_funcionario,
                data,
                hora_entrada,
                hora_saida,
                total_horas,
                status,
                justificativa,
                id_empresa
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isssdssi",
            $idFuncionario,
            $data,
            $entrada,
            $saida,
            $totalHoras,
            $status,
            $justificativa,
            $idEmpresa
        );

        if ($stmt->execute()) {
            $importados++;
        } else {
            $erros[] = "Linha $linha: erro ao importar ponto.";
        }
    }

    fclose($handle);

    $mensagem = "$importados registros de ponto importados.";
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
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
<div class="container-fluid">

<h1 class="fw-bold">Importar Registros de Ponto</h1>
<p class="text-muted">Importe o histórico real de ponto dos colaboradores.</p>

<?php if($mensagem): ?>
<div class="alert alert-success"><?= $mensagem ?></div>
<?php endif; ?>

<?php if($erros): ?>
<div class="alert alert-warning">
<ul class="mb-0">
<?php foreach($erros as $e): ?>
<li><?= htmlspecialchars($e) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<div class="card p-4 border-0 shadow-sm">
<form method="POST" enctype="multipart/form-data">

<label class="form-label fw-bold">Arquivo CSV</label>
<input type="file" name="arquivo" class="form-control mb-3" accept=".csv" required>

<button class="btn btn-primary">
<i class="bi bi-upload me-2"></i>
Importar
</button>

</form>

<hr>

<pre class="bg-light p-3 rounded">email,data,hora_entrada,hora_saida,total_horas,status,justificativa
bruno@empresa.com,2026-06-01,08:00:00,18:00:00,9.00,completo,
bruno@empresa.com,2026-06-02,08:10:00,17:00:00,7.83,completo,Atraso justificado</pre>

</div>

</div>
</div>

</body>
</html>