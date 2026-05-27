<?php

require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    /*
    ========================================
    RECEBER DADOS
    ========================================
    */

    $titulo = trim($_POST['titulo']);
    $conteudo = trim($_POST['conteudo']);
    $categoria = $_POST['categoria'];

    $fixado = isset($_POST['fixado'])
        ? 1
        : 0;

    $autor = 'Administrador';

    /*
    ========================================
    VALIDAR
    ========================================
    */

    if (empty($titulo) || empty($conteudo)) {

        die("Preencha todos os campos.");

    }

    /*
    ========================================
    SALVAR
    ========================================
    */

    $sql = "
    INSERT INTO comunicados
    (
        titulo,
        conteudo,
        categoria,
        fixado,
        autor
    )
    VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = $con->prepare($sql);

    if (!$stmt) {
        die("Erro no prepare: " . $con->error);
    }

    $stmt->bind_param(
        "sssis",
        $titulo,
        $conteudo,
        $categoria,
        $fixado,
        $autor
    );

    if ($stmt->execute()) {

        header("Location: comunicados.php");
        exit;

    } else {

        echo "Erro ao salvar: " . $stmt->error;
    }
}
?>