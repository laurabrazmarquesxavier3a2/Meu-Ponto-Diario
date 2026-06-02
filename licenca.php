 <?php
require_once 'auth.php';
require_once 'config/database.php';

// BUSCAR LICENÇAS
$sql = "
SELECT
    lm.*,
    f.nome
FROM licencas_medicas lm
INNER JOIN funcionarios f
ON lm.id_funcionario = f.id_funcionario
ORDER BY lm.data_envio DESC
";

$result = $con->query($sql);

// CARDS
$totalSubmissoes = $con->query(
    "SELECT COUNT(*) as total FROM licencas_medicas"
)->fetch_assoc()['total'];

$licencasAtivas = $con->query(
    "SELECT COUNT(*) as total
     FROM licencas_medicas
     WHERE CURDATE()
     BETWEEN data_inicio AND data_fim"
)->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Licença Médica</title>

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

    <div class="container-fluid">

        <h1 class="fw-bold">Licenças Médicas</h1>
        <h5 class="text-muted mb-4">
            Gerencie envios de atestados e licenças médicas
        </h5>

        <!-- CARDS -->
        <div class="row g-4 mb-4">

            <div class="col-12 col-md-6">
                <div class="card card-dashboard p-3 text-start">
                    <h5>Licenças Ativas</h5>

                    <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                        <?= $licencasAtivas ?>
                        <i class="bi bi-heart-pulse"></i>
                    </h1>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card card-dashboard p-3 text-start">
                    <h5>Total de Submissões</h5>

                    <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                        <?= $totalSubmissoes ?>
                        <i class="bi bi-file-earmark-medical"></i>
                    </h1>
                </div>
            </div>

        </div>

        <!-- TABELA -->
        <div class="card card-dashboard p-3">

            <h5>Atestados e Licenças</h5>

            <div class="table-responsive">

                <table class="table table-hover mt-3">

                    <thead class="table-light">
                        <tr>
                            <th>Funcionário</th>
                            <th>Período</th>
                            <th>Dias</th>
                            <th>Motivo</th>
                            <th>Data Envio</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if($result->num_rows > 0){ ?>

                        <?php while($licenca = $result->fetch_assoc()){ ?>

                            <tr>

                                <td>
                                    <i class="bi bi-person me-2"></i>
                                    <?= htmlspecialchars($licenca['nome']) ?>
                                </td>

                                <td>
                                    <?= date(
                                        'd/m/Y',
                                        strtotime($licenca['data_inicio'])
                                    ) ?>
                                    -
                                    <?= date(
                                        'd/m/Y',
                                        strtotime($licenca['data_fim'])
                                    ) ?>
                                </td>

                                <td>
                                    <?= $licenca['dias'] ?> dias
                                </td>

                                <td>
                                    <?= htmlspecialchars($licenca['motivo']) ?>
                                </td>

                                <td>
                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime($licenca['data_envio'])
                                    ) ?>
                                </td>

                                <td>
                                    <a
                                        href="<?= $licenca['arquivo_atestado'] ?>"
                                        target="_blank"
                                        class="d-flex align-items-center gap-2 text-primary text-decoration-none"
                                    >
                                        <i class="bi bi-eye"></i>
                                        Ver atestado
                                    </a>
                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Nenhuma licença enviada
                            </td>
                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>
        </div>

    </div>

</div>
<script src="js/theme.js"></script>
</body>
</html>