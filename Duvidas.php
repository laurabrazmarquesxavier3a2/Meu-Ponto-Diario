<?php

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "meu_ponto_diario";

$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    die("Erro na conexão");
}

$nome = $_POST['nome'];
$email = $_POST['email'];
$duvida = $_POST['duvida'];

$sql = "INSERT INTO duvidas(nome, email, duvida)
VALUES ('$nome', '$email', '$duvida')";

if(mysqli_query($conexao, $sql)) {

    echo "
    <script>
        alert('Dúvida enviada com sucesso!');
        window.location.href='ajuda.php';
    </script>
    ";

} else {

    echo "Erro ao enviar dúvida.";

}

?>