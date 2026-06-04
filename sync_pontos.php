<?php

require_once 'config/database.php';

/*
|--------------------------------------------------------------------------
| URL DA API
|--------------------------------------------------------------------------
*/

$apiUrl = "http://localhost/Conexao-Api-Ponto/api/pontos.php";

$json = file_get_contents($apiUrl);

if (!$json) {
    die("Erro ao acessar a API.");
}

$pontosApi = json_decode($json, true);

if (!$pontosApi) {
    die("Nenhum dado recebido.");
}

/*
|--------------------------------------------------------------------------
| PROCESSA CADA REGISTRO
|--------------------------------------------------------------------------
*/

foreach ($pontosApi as $registro) {

    $email = $registro['email'];
    $data = $registro['data'];

    $entrada = $registro['entrada'];
    $saida = $registro['saida'];

    /*
    |--------------------------------------------------------------------------
    | PROCURA FUNCIONÁRIO PELO EMAIL
    |--------------------------------------------------------------------------
    */

    $stmt = $con->prepare("
        SELECT id_funcionario, id_empresa
        FROM usuarios
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 0) {
        continue;
    }

    $usuario = $resultado->fetch_assoc();

    $idFuncionario = $usuario['id_funcionario'];
    $idEmpresa = $usuario['id_empresa'];

    /*
    |--------------------------------------------------------------------------
    | CALCULA TOTAL DE HORAS
    |--------------------------------------------------------------------------
    */

    $totalHoras = null;

    if (!empty($entrada) && !empty($saida)) {

        $inicio = strtotime($entrada);
        $fim = strtotime($saida);

        $totalHoras = round(
            ($fim - $inicio) / 3600,
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DEFINE STATUS
    |--------------------------------------------------------------------------
    */

    $status = "completo";

    if (empty($saida)) {
        $status = "em andamento";
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICA SE JÁ EXISTE
    |--------------------------------------------------------------------------
    */

    $check = $con->prepare("
        SELECT id_ponto
        FROM pontos
        WHERE id_funcionario = ?
        AND data = ?
    ");

    $check->bind_param(
        "is",
        $idFuncionario,
        $data
    );

    $check->execute();

    $existe = $check->get_result();


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    if ($existe->num_rows > 0) {

        $linha = $existe->fetch_assoc();

        $update = $con->prepare("
            UPDATE pontos
            SET
                hora_entrada = ?,
                hora_saida = ?,
                total_horas = ?,
                status = ?
            WHERE id_ponto = ?
        ");

        $update->bind_param(
            "ssdsi",
            $entrada,
            $saida,
            $totalHoras,
            $status,
            $linha['id_ponto']
        );

         $update->execute();

    } else {


        $insert = $con->prepare("
            INSERT INTO pontos
            (
                id_funcionario,
                data,
                hora_entrada,
                hora_saida,
                total_horas,
                status,
                id_empresa
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?)
        ");

        $insert->bind_param(
            "isssdsi",
            $idFuncionario,
            $data,
            $entrada,
            $saida,
            $totalHoras,
            $status,
            $idEmpresa
        );
         
        $insert->execute();

}

}

echo "Sincronização concluída.";