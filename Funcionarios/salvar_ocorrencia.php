<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../config/database.php';

$id_empresa = $_SESSION['id_empresa'] ?? 0;
$id_usuario = $_SESSION['id_usuario'] ?? 0;

if (!$id_empresa || !$id_usuario) {
    die("Sessão inválida. Faça login novamente.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: seguranca.php");
    exit;
}

$tipo_reporte = $_POST['tipo_reporte'] ?? '';
$nome = trim($_POST['nome'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$andar = trim($_POST['andar'] ?? '');
$sala = trim($_POST['sala'] ?? '');
$local_especifico = trim($_POST['local_especifico'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$testemunhas = trim($_POST['testemunhas'] ?? '');

if ($tipo_reporte === 'Anônimo') {
    $nome = 'Anônimo';
}

if ($nome === '') {
    $nome = 'Anônimo';
}

$evidencia = null;

if (isset($_FILES['evidencia']) && $_FILES['evidencia']['error'] === UPLOAD_ERR_OK) {

    $pasta = "../uploads/ocorrencias/";

    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    $nomeOriginal = basename($_FILES['evidencia']['name']);
    $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

    $permitidos = ['jpg', 'jpeg', 'png', 'pdf', 'mp4', 'mov', 'webp'];

    if (!in_array($extensao, $permitidos)) {
        die("Tipo de arquivo não permitido.");
    }

    $arquivo = uniqid('ocorrencia_', true) . "." . $extensao;
    $destino = $pasta . $arquivo;

    if (!move_uploaded_file($_FILES['evidencia']['tmp_name'], $destino)) {
        die("Erro ao salvar evidência.");
    }

    $evidencia = "uploads/ocorrencias/" . $arquivo;
}

$sql = "
INSERT INTO ocorrencias (
    id_empresa,
    id_usuario,
    tipo_reporte,
    nome,
    categoria,
    andar,
    sala,
    local_especifico,
    descricao,
    testemunhas,
    evidencia,
    status,
    data_ocorrencia
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aberta', NOW())
";

$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Erro no prepare: " . $con->error);
}

$stmt->bind_param(
    "iisssssssss",
    $id_empresa,
    $id_usuario,
    $tipo_reporte,
    $nome,
    $categoria,
    $andar,
    $sala,
    $local_especifico,
    $descricao,
    $testemunhas,
    $evidencia
);

if (!$stmt->execute()) {
    die("Erro ao salvar ocorrência: " . $stmt->error);
}

header("Location: seguranca.php?sucesso=1");
exit;