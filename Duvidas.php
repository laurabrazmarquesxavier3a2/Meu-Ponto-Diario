<?php
require_once 'lang.php';

// PARTE DO BANCO

$host = "localhost";
$usuario = "root";
$senha = "root";
$banco = "db_mpd";

$conexao = mysqli_connect($host, $usuario, $senha, $banco);

// Verifica conexão
if (!$conexao) {
    die("Erro na conexão: " . mysqli_connect_error());
}

// Define UTF-8
mysqli_set_charset($conexao, "utf8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Evita erros caso venha vazio
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $duvida = trim($_POST['duvida']);

    // Verifica campos vazios
    if (!empty($nome) && !empty($email) && !empty($duvida)) {

        $sql = "INSERT INTO duvidas (nome, email, duvida) VALUES (?, ?, ?)";

        $stmt = mysqli_prepare($conexao, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "sss", $nome, $email, $duvida);

            if (mysqli_stmt_execute($stmt)) {

              //  echo "Dúvida enviada com sucesso!";

            } else {

                echo "Erro ao enviar.";

            }

            mysqli_stmt_close($stmt);

        } else {

            echo "Erro na preparação da consulta.";

        }

    } else {

        echo "Preencha todos os campos.";

    }
}

// Fecha conexão
mysqli_close($conexao);

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Dúvida enviada</title>

    <link rel="stylesheet" href="css/Duvidas.css">

</head>

<body>

    <div class="sucesso-container">

        <!-- LOGO -->

        <img
            src="img/logo-azul.png"
            alt="Logo"
            class="logo">

        <!-- MENSAGEM -->

        <h1>

            Obrigado por compartilhar sua dúvida,
            responderemos assim que possível.

        </h1>

        <!-- BOTÃO VOLTAR -->

        <a href="ajuda.php">

            Voltar

        </a>

    </div>
</body>

</html>