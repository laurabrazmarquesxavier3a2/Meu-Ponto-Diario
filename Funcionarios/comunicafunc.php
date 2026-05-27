<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

/*
========================================
BUSCAR COMUNICADOS
========================================
*/

$sql = "
SELECT *
FROM comunicados
ORDER BY fixado DESC, data_publicacao DESC
";

$result = $con->query($sql);

$totalComunicados = $result->num_rows;

/*
========================================
COMUNICADOS FIXADOS
========================================
*/

$sqlFixados = "
SELECT *
FROM comunicados
WHERE fixado = 1
ORDER BY data_publicacao DESC
";

$fixados = $con->query($sqlFixados);
$totalFixados = $fixados->num_rows;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Comunicados</title>

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

.card-comunicado{
    border: none;
    border-radius: 20px;
    transition: .3s;
}

.card-comunicado:hover{
    transform: translateY(-5px);
}

.card-fixado{
    border-left: 6px solid #f59e0b;
    background: #fffaf0;
}

.badge-categoria{
    background: #0d6efd;
    color: white;
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 12px;
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
                Comunicados
            </h2>

            <p class="text-secondary mb-0">
                Total encontrado:
                <b><?= $totalComunicados ?></b>
            </p>

        </div>

    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-md-6">

            <div class="card shadow-sm border-0 rounded-4 p-3">

                <small>Total de Comunicados</small>

                <h2 class="fw-bold d-flex justify-content-between">

                    <?= $totalComunicados ?>

                    <i class="fa-solid fa-bullhorn"></i>

                </h2>

            </div>

        </div>

        <div class="col-md-6">

            <div class="card shadow-sm border-0 rounded-4 p-3">

                <small>Fixados</small>

                <h2 class="fw-bold d-flex justify-content-between">

                    <?= $totalFixados ?>

                    <i class="fa-solid fa-thumbtack"></i>

                </h2>

            </div>

        </div>

    </div>

    <!-- FIXADOS -->
    <?php if($fixados->num_rows > 0): ?>

        <h4 class="fw-bold mb-3">
            📌 Comunicados Fixados
        </h4>

        <div class="row g-4 mb-5">

            <?php while($f = $fixados->fetch_assoc()): ?>

                <div class="col-12">

                    <div class="card card-comunicado card-fixado shadow-sm">

                        <div class="card-body p-4">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <span class="badge-categoria">
                                    <?= $f['categoria'] ?>
                                </span>

                                <small class="text-secondary">

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime($f['data_publicacao'])
                                    ) ?>

                                </small>

                            </div>

                            <h4 class="fw-bold">
                                <?= $f['titulo'] ?>
                            </h4>

                            <p class="text-secondary mb-0">
                                <?= nl2br($f['conteudo']) ?>
                            </p>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php endif; ?>

    <!-- TODOS -->
    <h4 class="fw-bold mb-3">
        Todos os Comunicados
    </h4>

    <div class="row g-4">

        <?php if($result->num_rows > 0): ?>

            <?php while($row = $result->fetch_assoc()): ?>

                <div class="col-12 col-md-6">

                    <div class="card card-comunicado shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <span class="badge-categoria">
                                    <?= $row['categoria'] ?>
                                </span>

                                <small class="text-secondary">

                                    <?= date(
                                        'd/m/Y',
                                        strtotime($row['data_publicacao'])
                                    ) ?>

                                </small>

                            </div>

                            <h5 class="fw-bold">
                                <?= $row['titulo'] ?>
                            </h5>

                            <p class="text-secondary mb-0">
                                <?= nl2br($row['conteudo']) ?>
                            </p>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="empty-box shadow-sm">

                    <i class="fa-solid fa-bell-slash fa-3x text-secondary mb-3"></i>

                    <h4>
                        Nenhum comunicado encontrado
                    </h4>

                    <p class="text-secondary mb-0">
                        Ainda não existem comunicados publicados.
                    </p>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>