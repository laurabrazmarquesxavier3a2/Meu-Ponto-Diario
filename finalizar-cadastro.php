<?php

session_start();
require_once 'config/database.php';
require_once 'lang.php';

if (!isset($_SESSION['cadastro_empresa'])) {
    die("Sessão de cadastro não encontrada.");
}

$empresa = $_SESSION['cadastro_empresa'];

mysqli_begin_transaction($con);

try {

    $logoBanco = null;
    $statusEmpresa = 'ativa';

    /*
    ==========================
    CADASTRA EMPRESA
    ==========================
    */

    $sqlEmpresa = "
    INSERT INTO empresas (
        razao_social,
        nome_fantasia,
        cnpj,
        segmento,
        email,
        telefone,
        responsavel,
        cargo_responsavel,
        endereco,
        cidade,
        estado,
        cep,
        logo,
        plano,
        status
    )
    VALUES (
        ?,?,?,?,?,?,
        ?,?,?,?,?,?,
        ?,?,?
    )
    ";

    $stmtEmpresa = $con->prepare($sqlEmpresa);

    if (!$stmtEmpresa) {
        throw new Exception(
            "Erro prepare empresa: " . $con->error
        );
    }

    $stmtEmpresa->bind_param(
        "sssssssssssssss",

        $empresa['razao_social'],
        $empresa['nome_fantasia'],
        $empresa['cnpj'],
        $empresa['segmento'],
        $empresa['email'],
        $empresa['telefone'],

        $empresa['responsavel'],
        $empresa['cargo'],

        $empresa['endereco'],
        $empresa['cidade'],
        $empresa['estado'],
        $empresa['cep'],

        $logoBanco,
        $empresa['plano'],
        $statusEmpresa
    );

    if (!$stmtEmpresa->execute()) {
        throw new Exception(
            "Erro empresa: " .
            $stmtEmpresa->error
        );
    }

    $idEmpresa = $con->insert_id;

    if (!$idEmpresa) {
        throw new Exception(
            "ID da empresa não gerado."
        );
    }

    /*
    ==========================
    CADASTRA USUÁRIO RH
    ==========================
    */

    $senhaHash = password_hash(
        $empresa['senha'],
        PASSWORD_DEFAULT
    );

    $stmtUsuario = $con->prepare("
        INSERT INTO usuarios (
            id_empresa,
            nome,
            email,
            senha,
            telefone,
            cidade,
            cargo,
            tipo,
            status
        )
        VALUES (
            ?,?,?,?,?,?,?,
            'rh',
            'ativo'
        )
    ");

    if (!$stmtUsuario) {
        throw new Exception(
            "Erro prepare usuário: " .
            $con->error
        );
    }

    $stmtUsuario->bind_param(
        "issssss",

        $idEmpresa,
        $empresa['responsavel'],
        $empresa['email'],
        $senhaHash,
        $empresa['telefone'],
        $empresa['cidade'],
        $empresa['cargo']
    );

    if (!$stmtUsuario->execute()) {
        throw new Exception(
            "Erro usuário: " .
            $stmtUsuario->error
        );
    }

    mysqli_commit($con);

    unset($_SESSION['cadastro_empresa']);

    header("Location: login.php?cadastro=ok");
    exit;

} catch (Exception $e) {

    mysqli_rollback($con);

    echo "<h2>ERRO</h2>";
    echo "<pre>";
    echo $e->getMessage();
    echo "</pre>";
}