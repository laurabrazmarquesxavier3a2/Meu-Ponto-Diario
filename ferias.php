<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedidos de férias</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

<!-- SIDEBAR (vem do PHP) -->
<?php include 'sidebar.php'; ?>

<!-- CONTEÚDO -->
<div class="content">

    <div class="container-fluid">

        <h1 class="fw-bold">Pedidos de férias</h1>
        <h5 class="text-muted mb-4">Gerencie solicitações de férias dos funcionários</h5>

        <!-- CARDS -->
        <div class="row g-4 mb-4">

            <div class="col-12 col-md-4">
                <div class="card card-dashboard p-3 text-start">
                    <h5>Aguardando Aprovação</h5>
                    <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                        2 <i class="bi bi-calendar"></i>
                    </h1>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card card-dashboard p-3 text-start">
                    <h5>Aprovados</h5>
                    <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                        2 <i class="bi bi-check-lg"></i>
                    </h1>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card card-dashboard p-3 text-start">
                    <h5>Total de solicitações</h5>
                    <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                        5
                    </h1>
                </div>
            </div>

        </div>

        <!-- TABELA -->
        <div class="card card-dashboard p-3">
            <h5>Solicitações</h5>

            <div class="table-responsive">
                <table class="table table-hover mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>Funcionário</th>
                            <th>Período</th>
                            <th>Dias</th>
                            <th>Data da solicitação</th>
                            <th>Status</th>
                            <th>Ações</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td><i class="bi bi-person me-2"></i>João Silva</td>
                            <td>14/04/2026 - 24/04/2026</td>
                            <td>10 dias</td>
                            <td>09/03/2026</td>
                            <td><span class="badge bg-success">Aprovado</span></td>
                            <td></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td><i class="bi bi-person me-2"></i>Lucas Ferreira</td>
                            <td>09/06/2026 - 23/06/2026</td>
                            <td>14 dias</td>
                            <td>06/03/2026</td>
                            <td><span class="badge bg-warning">Pendente</span></td>

                            <td>
                                <a href="#" class="d-flex align-items-center gap-2 text-success text-decoration-none">
                                    <i class="bi bi-check"></i>Aprovar
                                </a>
                            </td>

                            <td>
                                <a href="#" class="d-flex align-items-center gap-2 text-danger text-decoration-none">
                                    <i class="bi bi-x"></i>Rejeitar
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td><i class="bi bi-person me-2"></i>Maria Souza</td>
                            <td>30/04/2026 - 14/05/2026</td>
                            <td>14 dias</td>
                            <td>07/03/2026</td>
                            <td><span class="badge bg-danger">Rejeitado</span></td>
                            <td></td>
                            <td></td>
                        </tr>

                    </tbody>

                </table>
            </div>
        </div>

    </div>

</div>

</body>
</html>