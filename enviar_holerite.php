<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';

$id_empresa = $_SESSION['id_empresa'] ?? 0;

if (!$id_empresa) {
    header("Location: holerite.php?erro=" . urlencode("Empresa não identificada."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: holerite.php");
    exit;
}

$funcionario_id = isset($_POST['funcionario_id']) ? (int)$_POST['funcionario_id'] : 0;
$mes = trim($_POST['mes'] ?? '');
$ano = trim($_POST['ano'] ?? '');

if (!$funcionario_id || empty($mes) || empty($ano)) {
    header("Location: holerite.php?erro=" . urlencode("Preencha todos os campos."));
    exit;
}

$stmtFunc = $con->prepare("
SELECT id_funcionario
FROM funcionarios
WHERE id_funcionario = ?
AND id_empresa = ?
LIMIT 1
");

$stmtFunc->bind_param("ii", $funcionario_id, $id_empresa);
$stmtFunc->execute();
$funcExiste = $stmtFunc->get_result();

if ($funcExiste->num_rows === 0) {
    header("Location: holerite.php?erro=" . urlencode("Funcionário inválido para esta empresa."));
    exit;
}

if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
    header("Location: holerite.php?erro=" . urlencode("Erro ao enviar o arquivo PDF."));
    exit;
}

$arquivo = $_FILES['arquivo'];

$extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

if ($extensao !== 'pdf') {
    header("Location: holerite.php?erro=" . urlencode("Envie apenas arquivo PDF."));
    exit;
}

$pasta = 'uploads/holerites/';

if (!is_dir($pasta)) {
    mkdir($pasta, 0777, true);
}

$nomeArquivo = 'holerite_' . $id_empresa . '_' . $funcionario_id . '_' . uniqid() . '.pdf';
$caminhoArquivo = $pasta . $nomeArquivo;

if (!move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
    header("Location: holerite.php?erro=" . urlencode("Não foi possível salvar o arquivo."));
    exit;
}

$periodo = $mes . '/' . $ano;
$status = 'enviado';

$stmt = $con->prepare("
INSERT INTO holerites
(funcionario_id, arquivo, periodo, data_envio, status, id_empresa)
VALUES
(?, ?, ?, NOW(), ?, ?)
");

if (!$stmt) {
    header("Location: holerite.php?erro=" . urlencode("Erro no banco: " . $con->error));
    exit;
}

$stmt->bind_param("isssi", $funcionario_id, $caminhoArquivo, $periodo, $status, $id_empresa);

if (!$stmt->execute()) {
    header("Location: holerite.php?erro=" . urlencode("Erro ao salvar holerite: " . $stmt->error));
    exit;
}

header("Location: holerite.php?sucesso=1");
exit;