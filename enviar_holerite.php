<?php
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $funcionario_id = (int) $_POST['funcionario_id'];
    $mes = trim($_POST['mes']);
    $ano = trim($_POST['ano']);

    $periodo = $mes . '/' . $ano;

    /*
    ========================================
    VERIFICA ARQUIVO
    ========================================
    */

    if (
        isset($_FILES['arquivo']) &&
        $_FILES['arquivo']['error'] === 0
    ) {

        $arquivo = $_FILES['arquivo'];

        /*
        ========================================
        CRIAR PASTA SE NÃO EXISTIR
        ========================================
        */

        $pasta = "uploads/holerites/";

        

        /*
        ========================================
        GERAR NOME ÚNICO
        ========================================
        */

        $extensao = strtolower(
            pathinfo($arquivo['name'], PATHINFO_EXTENSION)
        );

        // aceita apenas PDF
        if ($extensao !== 'pdf') {
            die("Somente arquivos PDF são permitidos.");
        }

        $nomeArquivo =
            uniqid('holerite_', true)
            . '.pdf';

        $caminho = $pasta . $nomeArquivo;

        /*
        ========================================
        MOVE O ARQUIVO
        ========================================
        */

        if (move_uploaded_file(
            $arquivo['tmp_name'],
            $caminho
        )) {

            /*
            ========================================
            SALVAR NO BANCO
            ========================================
            */

            $sql = "
                INSERT INTO holerites
                (
                    funcionario_id,
                    arquivo,
                    periodo,
                    status
                )
                VALUES
                (?, ?, ?, 'enviado')
            ";

            $stmt = $con->prepare($sql);

            if (!$stmt) {
                die("Erro SQL: " . $con->error);
            }

            $stmt->bind_param(
                "iss",
                $funcionario_id,
                $caminho,
                $periodo
            );

            if ($stmt->execute()) {

                header("Location: holerite.php?sucesso=1");
                exit;

            } else {

                echo "Erro ao salvar no banco: "
                . $stmt->error;

            }

        } else {

            echo "Erro ao mover arquivo.";

        }

    } else {

        echo "Nenhum arquivo enviado.";

    }
}
?>