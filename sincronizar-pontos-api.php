<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';

/*
|--------------------------------------------------------------------------
| URL DA API DO SISTEMA EXTERNO
|--------------------------------------------------------------------------
*/

$apiUrl = "http://localhost/Conexao-Api-Ponto/api/pontos.php";

$json = @file_get_contents($apiUrl);

if ($json === false) {
    die("Erro ao acessar a API. Verifique se o projeto Conexao-Api-Ponto está funcionando.");
}

$pontosApi = json_decode($json, true);

if (!is_array($pontosApi)) {
    die("A API não retornou dados válidos.");
}

$importados = 0;
$atualizados = 0;
$ignorados = 0;
$avisos = [];

/*
|--------------------------------------------------------------------------
| PROCESSA CADA REGISTRO RECEBIDO DA API
|--------------------------------------------------------------------------
*/

foreach ($pontosApi as $registro) {

    $email = trim($registro['email'] ?? '');
    $data = $registro['data'] ?? null;

    $entrada = !empty($registro['entrada']) ? $registro['entrada'] : null;
    $saidaAlmoco = !empty($registro['saida_almoco']) ? $registro['saida_almoco'] : null;
    $retornoAlmoco = !empty($registro['retorno_almoco']) ? $registro['retorno_almoco'] : null;
    $saida = !empty($registro['saida']) ? $registro['saida'] : null;

    if ($email === '' || empty($data)) {
        $ignorados++;
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | PROCURA FUNCIONÁRIO PELO EMAIL NO DB_MPD
    |--------------------------------------------------------------------------
    */

    $stmt = $con->prepare("
        SELECT 
            id_funcionario,
            id_empresa
        FROM funcionarios
        WHERE email = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("Erro prepare funcionário: " . $con->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 0) {
        $ignorados++;
        $avisos[] = "Funcionário não encontrado no db_mpd: " . $email;
        continue;
    }

    $funcionario = $resultado->fetch_assoc();

    $idFuncionario = (int)$funcionario['id_funcionario'];
    $idEmpresa = (int)$funcionario['id_empresa'];

    /*
    |--------------------------------------------------------------------------
    | CALCULA TOTAL DE HORAS
    |--------------------------------------------------------------------------
    */

    $totalHoras = null;

    if (!empty($entrada) && !empty($saida)) {

        $inicio = strtotime($entrada);
        $fim = strtotime($saida);

        $segundosTrabalhados = $fim - $inicio;

        if (!empty($saidaAlmoco) && !empty($retornoAlmoco)) {

            $inicioAlmoco = strtotime($saidaAlmoco);
            $fimAlmoco = strtotime($retornoAlmoco);

            $segundosAlmoco = $fimAlmoco - $inicioAlmoco;

            if ($segundosAlmoco > 0) {
                $segundosTrabalhados -= $segundosAlmoco;
            }
        }

        if ($segundosTrabalhados > 0) {
            $totalHoras = round($segundosTrabalhados / 3600, 2);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DEFINE STATUS
    |--------------------------------------------------------------------------
    */

    if (!empty($entrada) && !empty($saida)) {
        $status = "completo";
    } else {
        $status = "em andamento";
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICA SE JÁ EXISTE ESSE PONTO NO DB_MPD
    |--------------------------------------------------------------------------
    */

    $check = $con->prepare("
        SELECT id_ponto
        FROM pontos
        WHERE id_funcionario = ?
        AND id_empresa = ?
        AND data = ?
        LIMIT 1
    ");

    if (!$check) {
        die("Erro prepare check: " . $con->error);
    }

    $check->bind_param(
        "iis",
        $idFuncionario,
        $idEmpresa,
        $data
    );

    $check->execute();

    $existe = $check->get_result();

    /*
    |--------------------------------------------------------------------------
    | UPDATE SE JÁ EXISTE
    |--------------------------------------------------------------------------
    */

    if ($existe->num_rows > 0) {

        $linha = $existe->fetch_assoc();
        $idPonto = (int)$linha['id_ponto'];

        $update = $con->prepare("
            UPDATE pontos
            SET
                hora_entrada = ?,
                saida_almoco = ?,
                retorno_almoco = ?,
                hora_saida = ?,
                total_horas = ?,
                status = ?
            WHERE id_ponto = ?
            AND id_empresa = ?
        ");

        if (!$update) {
            die("Erro prepare update: " . $con->error);
        }

        $update->bind_param(
            "ssssdsii",
            $entrada,
            $saidaAlmoco,
            $retornoAlmoco,
            $saida,
            $totalHoras,
            $status,
            $idPonto,
            $idEmpresa
        );

        if ($update->execute()) {
            $atualizados++;
        } else {
            $avisos[] = "Erro ao atualizar ponto de $email: " . $update->error;
        }

    } else {

        /*
        |--------------------------------------------------------------------------
        | INSERT SE NÃO EXISTE
        |--------------------------------------------------------------------------
        */

        $insert = $con->prepare("
            INSERT INTO pontos (
                id_funcionario,
                data,
                hora_entrada,
                saida_almoco,
                retorno_almoco,
                hora_saida,
                total_horas,
                status,
                id_empresa
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$insert) {
            die("Erro prepare insert: " . $con->error);
        }

        $insert->bind_param(
            "isssssdsi",
            $idFuncionario,
            $data,
            $entrada,
            $saidaAlmoco,
            $retornoAlmoco,
            $saida,
            $totalHoras,
            $status,
            $idEmpresa
        );

        if ($insert->execute()) {
            $importados++;
        } else {
            $avisos[] = "Erro ao importar ponto de $email: " . $insert->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Sincronização de Pontos</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f7fb;
    padding: 40px;
}

.card {
    background: white;
    max-width: 760px;
    margin: auto;
    padding: 32px;
    border-radius: 18px;
    box-shadow: 0 15px 40px rgba(0,0,0,.08);
}

h1 {
    color: #1e40af;
    margin-top: 0;
}

.success {
    background: #dcfce7;
    color: #166534;
    padding: 18px;
    border-radius: 14px;
    margin-bottom: 18px;
}

.box {
    background: #eff6ff;
    padding: 18px;
    border-radius: 14px;
    color: #1e3a8a;
    margin-bottom: 18px;
}

.warning {
    background: #fef3c7;
    color: #92400e;
    padding: 18px;
    border-radius: 14px;
    margin-top: 18px;
}

a {
    color: #2563eb;
    font-weight: bold;
    text-decoration: none;
}

ul {
    margin-bottom: 0;
}
</style>
</head>

<body>

<div class="card">

    <h1>Sincronização concluída</h1>

    <div class="success">
        Os pontos da API foram importados para o banco principal <strong>db_mpd</strong>.
    </div>

    <div class="box">
        <strong>Importados:</strong> <?= (int)$importados ?><br>
        <strong>Atualizados:</strong> <?= (int)$atualizados ?><br>
        <strong>Ignorados:</strong> <?= (int)$ignorados ?>
    </div>

    <?php if (!empty($avisos)): ?>
        <div class="warning">
            <strong>Avisos:</strong>
            <ul>
                <?php foreach ($avisos as $aviso): ?>
                    <li><?= htmlspecialchars($aviso) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <p style="margin-top:20px;">
        <a href="ponto.php">Voltar para a página de Ponto</a>
    </p>

</div>

</body>
</html>