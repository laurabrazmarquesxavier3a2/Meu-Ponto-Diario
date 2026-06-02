<?php
require_once 'auth.php';
require_once 'config/database.php';


$presentes = mysqli_num_rows(
    mysqli_query($con, "
        SELECT * FROM pontos
        WHERE status = 'completo'
        AND data = CURDATE()
    ")
);

$atrasos = mysqli_num_rows(
    mysqli_query($con, "
        SELECT * FROM pontos
        WHERE status = 'atraso'
        AND data = CURDATE()
    ")
);

$ausencias = mysqli_num_rows(
    mysqli_query($con, "
        SELECT * FROM funcionarios
        WHERE ativo = 1
    ")
);

$query = mysqli_query($con, "

SELECT
    pontos.*,
    funcionarios.nome

FROM pontos

INNER JOIN funcionarios
ON funcionarios.id_funcionario = pontos.id_funcionario

ORDER BY pontos.data DESC

");

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Ponto</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <h1 class="fw-bold">Horários de Ponto</h1>

    <h5 class="text-muted mb-4">
        Controle de entrada e saída dos funcionários
    </h5>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">

                <h5>Funcionários Presentes</h5>

                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $presentes ?>
                    <i class="bi bi-clock"></i>
                </h1>

            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">

                <h5>Atrasos Hoje</h5>

                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $atrasos ?>
                    <i class="bi bi-clock"></i>
                </h1>

            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">

                <h5>Funcionários Ativos</h5>

                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $ausencias ?>
                    <i class="bi bi-people"></i>
                </h1>

            </div>
        </div>

    </div>

    <!-- TABELA -->
    <div class="card card-dashboard p-3">

        <h5>Registros de Ponto</h5>

        <div class="table-responsive">

            <table class="table table-hover mt-3">

                <thead class="table-light">

                    <tr>
                        <th>Funcionário</th>
                        <th>Data</th>
                        <th>Entrada</th>
                        <th>Saída</th>
                        <th>Total de Horas</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody>

                <?php while($ponto = mysqli_fetch_assoc($query)): ?>

                    <tr>

                        <td>
                            <i class="bi bi-person me-2"></i>
                            <?= $ponto['nome'] ?>
                        </td>

                        <td>
                            <?= date('d/m/Y', strtotime($ponto['data'])) ?>
                        </td>

                        <td>
                            <?= $ponto['hora_entrada'] ?>
                        </td>

                        <td>
                            <?= $ponto['hora_saida'] ?? '-' ?>
                        </td>

                        <td>

                            <?php
                            if($ponto['total_horas']) {
                                echo $ponto['total_horas'] . 'h';
                            } else {
                                echo 'Em andamento';
                            }
                            ?>

                        </td>

                        <td>

                            <?php
                            
                            if($ponto['status'] == 'completo') {
                                echo '<span class="badge bg-success">Completo</span>';
                            }

                            elseif($ponto['status'] == 'atraso') {
                                echo '<span class="badge bg-danger">Atraso</span>';
                            }

                            elseif($ponto['status'] == 'em andamento') {
                                echo '<span class="badge bg-warning">Em andamento</span>';
                            }

                            else {
                                echo '<span class="badge bg-secondary">Ausente</span>';
                            }

                            ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>
<script src="js/theme.js"></script>
</body>
</html>