<?php

require_once '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // usuário logado
    $id_usuario = $_SESSION['id_usuario'];

    // busca id_funcionario do usuário
    $sqlFuncionario = "
    SELECT id_funcionario
    FROM usuarios
    WHERE id_usuario = ?
    ";

    $stmtFuncionario = $con->prepare($sqlFuncionario);
    $stmtFuncionario->bind_param("i", $id_usuario);
    $stmtFuncionario->execute();

    $resultado = $stmtFuncionario->get_result();
    $usuario = $resultado->fetch_assoc();

    $id_funcionario = $usuario['id_funcionario'];

    // dados do formulário
    $motivo = $_POST['motivo'] ?? 'Licença Médica';
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $observacao = $_POST['observacao'] ?? '';

    // calcular dias
    $inicio = new DateTime($data_inicio);
    $fim = new DateTime($data_fim);

    $dias = $inicio->diff($fim)->days + 1;

    // arquivo enviado
    $arquivo = $_FILES['arquivo'];

    $nomeOriginal = $arquivo['name'];
    $tmp = $arquivo['tmp_name'];

    $extensao = strtolower(
        pathinfo($nomeOriginal, PATHINFO_EXTENSION)
    );

    // formatos permitidos
    $permitidos = [
        'pdf',
        'png',
        'jpg',
        'jpeg'
    ];

    if (!in_array($extensao, $permitidos)) {
        die("Formato inválido.");
    }

    // pastas
    $pastaFisica = "../uploads/licencas/";
    $pastaBanco = "uploads/licencas/";

    // cria pasta automaticamente
    if (!is_dir($pastaFisica)) {
        mkdir($pastaFisica, 0777, true);
    }

    // nome único do arquivo
    $novoNome = uniqid() . "." . $extensao;

    // caminho real do servidor
    $caminhoFisico = $pastaFisica . $novoNome;

    // caminho salvo no banco
    $caminhoBanco = $pastaBanco . $novoNome;

    // move arquivo
    move_uploaded_file(
        $tmp,
        $caminhoFisico
    );

    // inserir no banco
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

    $stmt->execute();

    header("Location: permissoes.php?sucesso=1");
    exit;
}
?>