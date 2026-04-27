<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Banco de Horas</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

<!-- CHAMANDO SIDEBAR -->
<?php include 'sidebar.php'; ?>

<!-- CONTEÚDO -->
<div class="content">

    <h1 class="fw-bold">Banco de Horas</h1>
    <h5 class="text-muted mb-4">Controle de saldo de horas dos funcionários</h5>

    <div class="container-fluid">

        <!-- CARDS -->
        <div class="row g-4 mb-4">

            <div class="col-6 col-md-3">
                <div class="card card-dashboard p-3">
                    <h5>Saldo positivo</h5>
                    <h1 class="fw-bold d-flex justify-content-between">
                        4 <i class="bi bi-graph-up-arrow"></i>
                    </h1>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card card-dashboard p-3">
                    <h5>Saldo negativo</h5>
                    <h1 class="fw-bold d-flex justify-content-between">
                        2 <i class="bi bi-graph-down-arrow"></i>
                    </h1>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card card-dashboard p-3">
                    <h5>Total de horas extras</h5>
                    <h1 class="fw-bold d-flex justify-content-between">
                        52h <i class="bi bi-clock"></i>
                    </h1>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card card-dashboard p-3">
                    <h5>Este mês</h5>
                    <h1 class="fw-bold d-flex justify-content-between">
                        Março <i class="bi bi-calendar"></i>
                    </h1>
                </div>
            </div>

        </div>

        <!-- TABELA -->
        <div class="card card-dashboard p-3">
            <h5>Saldos de Banco de Horas</h5>

            <div class="table-responsive">
                <table class="table table-hover mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>Funcionários</th>
                            <th>Saldo Total</th>
                            <th>Este Mês</th>
                            <th>Última Atualização</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td><i class="bi bi-person me-2"></i>João Silva</td>
                            <td>+10h</td>
                            <td>+2h</td>
                            <td>10/03/2026</td>
                            <td><span class="badge bg-success">Positivo</span></td>
                        </tr>

                        <tr>
                            <td><i class="bi bi-person me-2"></i>Maria Souza</td>
                            <td>-5h</td>
                            <td>-1h</td>
                            <td>09/03/2026</td>
                            <td><span class="badge bg-danger">Negativo</span></td>
                        </tr>
                    </tbody>

                </table>
            </div>
        </div>

    </div>

</div>

</body>
</html>