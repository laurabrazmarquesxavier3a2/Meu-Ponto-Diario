<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ponto</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>

<!-- CONTEÚDO -->
<div class="content">

    <h1 class="fw-bold">Horários de Ponto</h1>
    <h5 class="text-muted mb-4">Controle de entrada e saída dos funcionários</h5>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">
                <h5>Funcionários Presentes</h5>
                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    2 <i class="bi bi-clock"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">
                <h5>Atrasos Hoje</h5>
                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    1 <i class="bi bi-clock"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">
                <h5>Ausências</h5>
                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    5 <i class="bi bi-calendar"></i>
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

                    <tr>
                        <td><i class="bi bi-person me-2"></i>Mara Silva</td>
                        <td>08/03/2026</td>
                        <td>08:45</td>
                        <td>18:10</td>
                        <td>8h 25m</td>
                        <td><span class="badge bg-success">Completo</span></td>
                    </tr>

                    <tr>
                        <td><i class="bi bi-person me-2"></i>João Santos</td>
                        <td>08/03/2026</td>
                        <td>09:15</td>
                        <td>18:00</td>
                        <td>7h 45m</td>
                        <td><span class="badge bg-danger">Atraso</span></td>
                    </tr>

                    <tr>
                        <td><i class="bi bi-person me-2"></i>Ana Silva</td>
                        <td>08/03/2026</td>
                        <td>08:50</td>
                        <td>-</td>
                        <td>Em andamento</td>
                        <td><span class="badge bg-warning">Em andamento</span></td>
                    </tr>

                </tbody>

            </table>
        </div>
    </div>

</div>

</body>
</html>