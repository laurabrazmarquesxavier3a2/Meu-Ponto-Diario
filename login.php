 <?php
session_start();

$usuario_correto = "admin";
$senha_correta = "123456";

$usuario = $_POST['usuario'] ?? '';
$senha = $_POST['senha'] ?? '';

if ($usuario === $usuario_correto && $senha === $senha_correta) {
    $_SESSION['usuario'] = $usuario;
    header("Location: ponto.php");
    exit;
} else {
    header("Location: login.html?erro=1");
    exit;
}