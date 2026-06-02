<?php
session_start();

require_once 'config/database.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    $sql = "SELECT * FROM usuarios
            WHERE email = ?
            AND status = 'ativo'
            LIMIT 1";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $usuario = $resultado->fetch_assoc();

        if (password_verify($senha, $usuario['senha'])) {

            session_regenerate_id(true);

            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['id_empresa'] = $usuario['id_empresa'];
            $_SESSION['id_funcionario'] = $usuario['id_funcionario'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['tipo'] = $usuario['tipo'];

            $update = "UPDATE usuarios
                       SET ultimo_login = NOW()
                       WHERE id_usuario = ?";

            $stmtUpdate = $con->prepare($update);
            $stmtUpdate->bind_param("i", $usuario['id_usuario']);
            $stmtUpdate->execute();

            if ($usuario['tipo'] == 'empresa' || $usuario['tipo'] == 'rh') {
                header("Location: ponto.php");
                exit;
            }

            if ($usuario['tipo'] == 'funcionario') {
                header("Location: Funcionarios/pontoF.php");
                exit;
            }

        } else {
            $erro = "Senha incorreta.";
        }

    } else {
        $erro = "Usuário não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/login.css">

</head>

<body>

<div class="bg-login">

    <div class="login-box">

        <img src="img/logo-branca.png" class="logo">

        <div class="login-title">
            Gestão de RH rápida <br> e segura.
        </div>

        <?php if(isset($_GET['cadastro'])): ?>
            <div class="alert alert-success">
                Conta criada com sucesso.
                Agora faça login para acessar o sistema.
            </div>
        <?php endif; ?>

        <?php if($erro): ?>
            <div class="alert alert-danger">
                <?= $erro ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="E-mail"
                required
            >

            <input
                type="password"
                name="senha"
                class="form-control"
                placeholder="Senha"
                required
            >

            <button type="submit" class="btn btn-login">
                Entrar
            </button>

            <a href="#" class="forgot">
                Esqueci minha senha.
            </a>

        </form>

    </div>

</div>

</body>
</html>