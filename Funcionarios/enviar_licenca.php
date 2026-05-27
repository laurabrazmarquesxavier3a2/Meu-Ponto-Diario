<?php

session_start();

require_once '../config/database.php';

/* VERIFICA LOGIN */

if (!isset($_SESSION['id_usuario'])) {

    die("Usuário não autenticado.");

}

/* VERIFICA ENVIO */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    die("Método inválido.");

}

/* ID USUÁRIO */

$id_usuario = $_SESSION['id_usuario'];

/* BUSCA FUNCIONÁRIO */

$sqlFuncionario = "
SELECT id_funcionario
FROM usuarios
WHERE id_usuario = ?
";

$stmtFuncionario = $con->prepare($sqlFuncionario);

if (!$stmtFuncionario) {

    die("Erro SQL funcionário.");

}

$stmtFuncionario->bind_param("i", $id_usuario);

$stmtFuncionario->execute();

$resultado = $stmtFuncionario->get_result();

$usuario = $resultado->fetch_assoc();

/* VERIFICA FUNCIONÁRIO */

if (!$usuario) {

    die("Funcionário não encontrado.");

}

$id_funcionario = $usuario['id_funcionario'];

/* DADOS */

$motivo = $_POST['motivo'] ?? 'Licença Médica';

$data_inicio = $_POST['data_inicio'];

$data_fim = $_POST['data_fim'];

$observacao = $_POST['observacao'] ?? '';

/* CALCULA DIAS */

$inicio = new DateTime($data_inicio);

$fim = new DateTime($data_fim);

$dias = $inicio->diff($fim)->days + 1;

/* ARQUIVO */

if (!isset($_FILES['arquivo'])) {

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

/* CRIA PASTA */

if (!is_dir($pastaFisica)) {

    mkdir($pastaFisica, 0777, true);

}

/* NOVO NOME */

$novoNome = uniqid() . "." . $extensao;

/* CAMINHOS */

$caminhoFisico = $pastaFisica . $novoNome;

$caminhoBanco = $pastaBanco . $novoNome;

/* MOVE ARQUIVO */

if (!move_uploaded_file($tmp, $caminhoFisico)) {

    die("Erro ao mover arquivo.");

}

/* INSERT */

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
    observacao
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
";

$stmt = $con->prepare($sql);

if (!$stmt) {

    die("Erro SQL licença.");

}

$stmt->bind_param(
    "isssssis",
    $id_funcionario,
    $caminhoBanco,
    $extensao,
    $motivo,
    $data_inicio,
    $data_fim,
    $dias,
    $observacao
);

if (!$stmt->execute()) {

    die("Erro ao salvar no banco.");

}

/* REDIRECIONA */

header("Location: pedidosf.php?sucesso=1");

exit;
?>