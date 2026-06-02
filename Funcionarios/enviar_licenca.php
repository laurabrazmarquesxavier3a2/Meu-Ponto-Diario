<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';

/* VERIFICA LOGIN */
if (
    !isset($_SESSION['id_usuario']) ||
    !isset($_SESSION['id_empresa'])
) {
    die("Usuário não autenticado.");
}

/* VERIFICA ENVIO */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido.");
}

$id_usuario = $_SESSION['id_usuario'];
$id_empresa = $_SESSION['id_empresa'];

/* BUSCA FUNCIONÁRIO REAL DA EMPRESA */
$sqlFuncionario = "
SELECT 
    id_funcionario,
    nome
FROM usuarios
WHERE id_usuario = ?
AND id_empresa = ?
LIMIT 1
";

$stmtFuncionario = $con->prepare($sqlFuncionario);

if (!$stmtFuncionario) {
    die("Erro SQL funcionário: " . $con->error);
}

$stmtFuncionario->bind_param(
    "ii",
    $id_usuario,
    $id_empresa
);

$stmtFuncionario->execute();

$resultado = $stmtFuncionario->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario || empty($usuario['id_funcionario'])) {
    die("Funcionário não encontrado nesta empresa.");
}

$id_funcionario = $usuario['id_funcionario'];

/* DADOS */
$motivo = $_POST['motivo'] ?? 'Licença Médica';
$data_inicio = $_POST['data_inicio'] ?? '';
$data_fim = $_POST['data_fim'] ?? '';
$observacao = $_POST['observacao'] ?? '';

if ($data_inicio == '' || $data_fim == '') {
    die("Informe a data de início e fim.");
}

/* CALCULA DIAS */
$inicio = new DateTime($data_inicio);
$fim = new DateTime($data_fim);

if ($fim < $inicio) {
    die("A data final não pode ser menor que a data inicial.");
}

$dias = $inicio->diff($fim)->days + 1;

/* ARQUIVO */
if (
    !isset($_FILES['arquivo']) ||
    $_FILES['arquivo']['error'] != 0
) {
    die("Arquivo não enviado.");
}

$arquivo = $_FILES['arquivo'];
$nomeOriginal = $arquivo['name'];
$tmp = $arquivo['tmp_name'];

/* EXTENSÃO */
$extensao = strtolower(
    pathinfo($nomeOriginal, PATHINFO_EXTENSION)
);

/* FORMATOS */
$permitidos = [
    'pdf',
    'png',
    'jpg',
    'jpeg'
];

if (!in_array($extensao, $permitidos)) {
    die("Formato inválido.");
}

/* PASTAS */
$pastaFisica = "../uploads/licencas/";
$pastaBanco = "uploads/licencas/";

if (!is_dir($pastaFisica)) {
    mkdir($pastaFisica, 0777, true);
}

/* NOVO NOME */
$novoNome = uniqid("licenca_") . "." . $extensao;

/* CAMINHOS */
$caminhoFisico = $pastaFisica . $novoNome;
$caminhoBanco = $pastaBanco . $novoNome;

/* MOVE ARQUIVO */
if (!move_uploaded_file($tmp, $caminhoFisico)) {
    die("Erro ao mover arquivo.");
}

/* INSERT COM ID_EMPRESA */
$sql = "
INSERT INTO licencas_medicas
(
    id_funcionario,
    arquivo_atestado,
    tipo_arquivo,
    motivo,
    data_inicio,
    data_fim,
    dias,
    observacao,
    id_empresa
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Erro SQL licença: " . $con->error);
}

$stmt->bind_param(
    "isssssisi",
    $id_funcionario,
    $caminhoBanco,
    $extensao,
    $motivo,
    $data_inicio,
    $data_fim,
    $dias,
    $observacao,
    $id_empresa
);

if (!$stmt->execute()) {
    die("Erro ao salvar no banco: " . $stmt->error);
}

/* ATIVIDADE RECENTE */
$stmtAtv = $con->prepare("
    INSERT INTO atividades (
        id_usuario,
        descricao,
        tipo
    )
    VALUES (?, ?, 'warning')
");

if ($stmtAtv) {

    $descricao = "Enviou solicitação de licença médica";

    $stmtAtv->bind_param(
        "is",
        $id_usuario,
        $descricao
    );

    $stmtAtv->execute();
}

/* REDIRECIONA */
header("Location: pedidosf.php?sucesso=1");
exit;

?>