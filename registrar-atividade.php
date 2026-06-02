<?php

function registrarAtividade($con, $descricao, $tipo = 'primary') {

    if (!isset($_SESSION['id_usuario'])) {
        return;
    }

    $idUsuario = $_SESSION['id_usuario'];

    $stmt = $con->prepare("
        INSERT INTO atividades (
            id_usuario,
            descricao,
            tipo
        )
        VALUES (?, ?, ?)
    ");

    if ($stmt) {
        $stmt->bind_param(
            "iss",
            $idUsuario,
            $descricao,
            $tipo
        );

        $stmt->execute();
    }
}