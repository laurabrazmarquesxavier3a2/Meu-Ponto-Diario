
<?php

include('../config/database.php');

$tipo_reporte = $_POST['tipo_reporte'] ?? '';
$nome = $_POST['nome'] ?? 'Anônimo';
$categoria = $_POST['categoria'] ?? '';

$andar = $_POST['andar'] ?? '';
$sala = $_POST['sala'] ?? '';
$local_especifico = $_POST['local_especifico'] ?? '';

$descricao = $_POST['descricao'] ?? '';
$testemunhas = $_POST['testemunhas'] ?? '';

$evidencia = '';

if(isset($_FILES['evidencia']) && $_FILES['evidencia']['error'] == 0){

    $pasta = "../uploads/ocorrencias/";

    if(!is_dir($pasta)){
        mkdir($pasta, 0777, true);
    }

    $arquivo = time() . "_" . $_FILES['evidencia']['name'];

    move_uploaded_file(
        $_FILES['evidencia']['tmp_name'],
        $pasta . $arquivo
    );

    $evidencia = $arquivo;
}

if($tipo_reporte == 'Anônimo'){
    $nome = 'Anônimo';
}

$sql = "INSERT INTO ocorrencias (

    tipo_reporte,
    nome,
    categoria,
    andar,
    sala,
    local_especifico,
    descricao,
    testemunhas,
    evidencia

) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $con->prepare($sql);

$stmt->bind_param(

    "sssssssss",

    $tipo_reporte,
    $nome,
    $categoria,
    $andar,
    $sala,
    $local_especifico,
    $descricao,
    $testemunhas,
    $evidencia

);

$stmt->execute();

header("Location: seguranca.php?sucesso=1");
exit;
?>
