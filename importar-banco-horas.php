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
        $mes = trim($dados[1]);
        $saldoTotal = floatval(str_replace(',', '.', $dados[2]));
        $saldoMes = floatval(str_replace(',', '.', $dados[3]));
        $extras = floatval(str_replace(',', '.', $dados[4]));
        $debitos = floatval(str_replace(',', '.', $dados[5]));

        $status = 'neutro';

        if ($saldoTotal > 0) {
            $status = 'positivo';
        } elseif ($saldoTotal < 0) {
            $status = 'negativo';
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
            SELECT id_banco
            FROM banco_horas
            WHERE id_funcionario = ?
            AND id_empresa = ?
            AND mes = ?
            LIMIT 1
        ");

        $stmtExiste->bind_param("iis", $idFuncionario, $idEmpresa, $mes);
        $stmtExiste->execute();

        $existe = $stmtExiste->get_result()->fetch_assoc();

        if ($existe) {

            $stmt = $con->prepare("
                UPDATE banco_horas
                SET
                    saldo_total = ?,
                    saldo_mes = ?,
                    horas_extras_mes = ?,
                    horas_debito_mes = ?,
                    data_atualizacao = CURDATE(),
                    status = ?
                WHERE id_banco = ?
            ");

            $stmt->bind_param(
                "ddddsi",
                $saldoTotal,
                $saldoMes,
                $extras,
                $debitos,
                $status,
                $existe['id_banco']
            );

        } else {

            $stmt = $con->prepare("
                INSERT INTO banco_horas (
                    id_funcionario,
                    mes,
                    saldo_total,
                    saldo_mes,
                    horas_extras_mes,
                    horas_debito_mes,
                    data_atualizacao,
                    status,
                    id_empresa
                )
                VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, ?)
            ");

            $stmt->bind_param(
                "isddddsi",
                $idFuncionario,
                $mes,
                $saldoTotal,
                $saldoMes,
                $extras,
                $debitos,
                $status,
                $idEmpresa
            );
        }

        if ($stmt->execute()) {
            $importados++;
        } else {
            $erros[] = "Linha $linha: erro ao salvar banco de horas.";
        }
    }

    fclose($handle);

    $mensagem = "$importados saldos de banco de horas importados.";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Importar Banco de Horas</title>

<link rel="stylesheet" href="css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
<div class="container-fluid">

<h1 class="fw-bold">Importar Banco de Horas</h1>
<p class="text-muted">Importe saldos reais enviados pela empresa.</p>

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

<pre class="bg-light p-3 rounded">email,mes,saldo_total,saldo_mes,horas_extras_mes,horas_debito_mes
bruno@empresa.com,2026-06,12.50,4.00,6.00,2.00</pre>
</div>
</div>
</div>
<script src="js/theme.js"></script>
</body>
</html>