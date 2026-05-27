<?php
session_start();
require_once '../config/database.php';

/*
========================================
VALIDAÇÃO LOGIN
========================================
*/

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

$id_usuario = (int) $_SESSION['id_usuario'];

/*
========================================
BUSCAR ID DO FUNCIONÁRIO
========================================
*/

$sqlUsuario = "
    SELECT id_funcionario
    FROM usuarios
    WHERE id_usuario = ?
";

$stmtUsuario = $con->prepare($sqlUsuario);

if (!$stmtUsuario) {
    die("Erro SQL: " . $con->error);
}

$stmtUsuario->bind_param("i", $id_usuario);
$stmtUsuario->execute();

$resultUsuario = $stmtUsuario->get_result();

if ($resultUsuario->num_rows == 0) {
    die("Funcionário não encontrado.");
}

$usuario = $resultUsuario->fetch_assoc();
$id_funcionario = (int)$usuario['id_funcionario'];

if (!$id_funcionario) {
    die("Funcionário não encontrado.");
}

/*
========================================
FILTROS
========================================
*/

$ano = $_GET['ano'] ?? date('Y');
$mes = $_GET['mes'] ?? '';

/*
========================================
MESES
========================================
*/

$meses = [
    '01' => 'Janeiro',
    '02' => 'Fevereiro',
    '03' => 'Março',
    '04' => 'Abril',
    '05' => 'Maio',
    '06' => 'Junho',
    '07' => 'Julho',
    '08' => 'Agosto',
    '09' => 'Setembro',
    '10' => 'Outubro',
    '11' => 'Novembro',
    '12' => 'Dezembro'
];

/*
========================================
BUSCAR ANOS
========================================
*/

$sqlAnos = "
    SELECT DISTINCT YEAR(data_envio) AS ano
    FROM holerites
    WHERE funcionario_id = ?
    ORDER BY ano DESC
";

$stmtAnos = $con->prepare($sqlAnos);

if (!$stmtAnos) {
    die("Erro SQL (anos): " . $con->error);
}

$stmtAnos->bind_param("i", $id_funcionario);
$stmtAnos->execute();

$anos = $stmtAnos->get_result();

/*
========================================
QUERY PRINCIPAL
========================================
*/

$sql = "
    SELECT *
    FROM holerites
    WHERE funcionario_id = ?
    AND YEAR(data_envio) = ?
";

if ($mes != '') {
    $sql .= " AND MONTH(data_envio) = ?";
}

$sql .= " ORDER BY data_envio DESC";

$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Erro SQL (holerites): " . $con->error);
}

if ($mes != '') {

    $mesInt = (int)$mes;

    $stmt->bind_param(
        "iii",
        $id_funcionario,
        $ano,
        $mesInt
    );

} else {

    $stmt->bind_param(
        "ii",
        $id_funcionario,
        $ano
    );
}

$stmt->execute();

$result = $stmt->get_result();

$total = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Seus Holerites</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="../css/global.css">

<style>

body{
    background: #f4f6f9;
}

.content{
    margin-left: 270px;
    padding: 30px;
}

.page-title{
    font-weight: 700;
    color: #1f2937;
}

.card-holerite{
    border: none;
    border-radius: 20px;
    transition: .3s;
}

.card-holerite:hover{
    transform: translateY(-5px);
}

.badge-mes{
    background: #0d6efd;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 13px;
}

.empty-box{
    background: white;
    padding: 40px;
    border-radius: 20px;
    text-align: center;
}

@media(max-width:991px){

    .content{
        margin-left: 0;
        padding: 20px;
    }

}

</style>

</head>

<body>

<?php include 'sidebarfunc.php'; ?>

<div class="content">

    <!-- TOPO -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>

            <h2 class="page-title">
                Seus Holerites
            </h2>

            <p class="text-secondary mb-0">
                Total encontrado:
                <b><?= $total ?></b>
            </p>

        </div>

        <!-- FILTROS -->
        <form method="GET" class="d-flex gap-2 flex-wrap">

            <!-- ANO -->
            <select
                name="ano"
                class="form-select"
                onchange="this.form.submit()"
            >

                <?php if($anos->num_rows > 0): ?>

                    <?php while($a = $anos->fetch_assoc()): ?>

                        <option
                            value="<?= $a['ano'] ?>"
                            <?= ($a['ano'] == $ano) ? 'selected' : '' ?>
                        >
                            <?= $a['ano'] ?>
                        </option>

                    <?php endwhile; ?>

                <?php else: ?>

                    <option value="<?= date('Y') ?>">
                        <?= date('Y') ?>
                    </option>

                <?php endif; ?>

            </select>

            <!-- MÊS -->
            <select
                name="mes"
                class="form-select"
                onchange="this.form.submit()"
            >

                <option value="">
                    Todos
                </option>

                <?php foreach($meses as $num => $nome): ?>

                    <option
                        value="<?= $num ?>"
                        <?= ($mes == $num) ? 'selected' : '' ?>
                    >
                        <?= $nome ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </form>

    </div>

    <!-- LISTA -->
    <div class="row g-4">

        <?php if($total > 0): ?>

            <?php while($row = $result->fetch_assoc()): ?>

                <?php

                 $periodo = explode('/', $row['periodo']);

                $nomeMes = $periodo[0] ?? 'Mês';
                
                 $a = $periodo[1] ?? date('Y');
                ?>

                <div class="col-12 col-md-6 col-lg-4">

                    <div class="card card-holerite shadow-sm h-100">

                        <div class="card-body p-4 d-flex flex-column">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <h5 class="mb-0 fw-bold">
                                    Holerite
                                </h5>

                                <span class="badge-mes text-white">
                                    <?= $nomeMes ?> / <?= $a ?>
                                </span>

                            </div>

                            <p class="text-secondary flex-grow-1">

                                Comprovante referente ao mês de
                                <b><?= $nomeMes ?></b>.

                            </p>

                            <div class="d-flex gap-2 mt-3">

                                <a
                                    href="../<?= $row['arquivo'] ?>"
                                    target="_blank"
                                    class="btn btn-primary w-50"
                                >
                                    <i class="fa-solid fa-eye me-1"></i>
                                    Ver
                                </a>

                                <a
                                    href="../<?= $row['arquivo'] ?>"
                                    download
                                    class="btn btn-outline-primary w-50"
                                >
                                    <i class="fa-solid fa-download me-1"></i>
                                    Baixar
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="empty-box shadow-sm">

                    <i class="fa-solid fa-file-circle-xmark fa-3x text-secondary mb-3"></i>

                    <h4>
                        Nenhum holerite encontrado
                    </h4>

                    <p class="text-secondary mb-0">
                        Não existem holerites para esse período.
                    </p>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>