<?php
include('conexao.php'); // troque se sua conexão tiver outro nome

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $funcionario_id = $_POST['funcionario_id'];
    $periodo = $_POST['periodo'];

    // verifica se arquivo foi enviado
    if(isset($_FILES['holerite']) && $_FILES['holerite']['error'] == 0){

        $arquivo = $_FILES['holerite'];

        // nome único pro PDF
        $nomeArquivo = time() . "_" . basename($arquivo['name']);

        // pasta onde vai salvar
        $caminho = "uploads/" . $nomeArquivo;

        // move arquivo para pasta uploads
        if(move_uploaded_file($arquivo['tmp_name'], $caminho)){

            // salva no banco
            $sql = "INSERT INTO holerites
            (funcionario_id, arquivo, periodo, status)
            VALUES (?, ?, ?, 'enviado')";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "iss",
                $funcionario_id,
                $caminho,
                $periodo
            );

            if($stmt->execute()){
                header("Location: holerite.php");
                exit;
            } else {
                echo "Erro ao salvar no banco.";
            }

        } else {
            echo "Erro ao mover arquivo.";
        }

    } else {
        echo "Nenhum arquivo enviado.";
    }
}
?>