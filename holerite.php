 <?php
require_once 'auth.php';
require_once 'config/database.php';

/* =========================
   FILTRO MÊS / ANO
========================= */
$mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$ano = isset($_GET['ano']) ? $_GET['ano'] : '';

/* =========================
   HOLERITES (COM FILTRO)
========================= */
$sql = "
SELECT
    h.*,
    f.nome
FROM holerites h
INNER JOIN funcionarios f
ON h.funcionario_id = f.id_funcionario
WHERE 1=1
";

if (!empty($mes) && !empty($ano)) {
    $sql .= " AND MONTH(h.data_envio) = $mes AND YEAR(h.data_envio) = $ano ";
} elseif (!empty($mes)) {
    $sql .= " AND MONTH(h.data_envio) = $mes ";
} elseif (!empty($ano)) {
    $sql .= " AND YEAR(h.data_envio) = $ano ";
}

$sql .= " ORDER BY h.data_envio DESC";

$resultado = $con->query($sql);


 /*
========================================
PENDENTES
========================================
*/

$sqlPendentes = "
SELECT
(
    (SELECT COUNT(*)
    FROM funcionarios)

    -

    (SELECT COUNT(DISTINCT funcionario_id)
    FROM holerites
    WHERE status = 'enviado')
) AS total
";

$pendentes = $con
->query($sqlPendentes)
->fetch_assoc()['total'];

$enviados = $con->query("
SELECT COUNT(DISTINCT funcionario_id) AS total
FROM holerites
WHERE status = 'enviado'
")->fetch_assoc()['total'];

$totalFuncionarios = $con->query("
SELECT COUNT(*) as total
FROM funcionarios
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Holerite</title>

    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
<div class="container-fluid">

    <h1 class="fw-bold">Envio de holerite</h1>
    <h5 class="text-muted mb-4">
        Gerencie o envio de holerites dos funcionários
    </h5>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">
                <h5>Envio Pendente</h5>
                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $pendentes ?>
                    <i class="bi bi-calendar"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">
                <h5>Enviados</h5>
                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $enviados ?>
                    <i class="bi bi-check-lg"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">
                <h5>Total de funcionários</h5>
                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $totalFuncionarios ?>
                </h1>
            </div>
        </div>

    </div>

    <!-- BOTÃO ENVIAR -->
    <button
        class="btn btn-primary mb-4"
        data-bs-toggle="modal"
        data-bs-target="#modalHolerite"
    >
        <i class="bi bi-send"></i>
        Enviar Holerite
    </button>

    <!-- 🔥 FILTRO MÊS / ANO -->
    <form method="GET" class="row g-2 mb-4">

        <div class="col-md-3">
            <select name="mes" class="form-control">
                <option value="">Mês</option>
                <option value="1"  <?= $mes==1?'selected':'' ?>>Janeiro</option>
                <option value="2"  <?= $mes==2?'selected':'' ?>>Fevereiro</option>
                <option value="3"  <?= $mes==3?'selected':'' ?>>Março</option>
                <option value="4"  <?= $mes==4?'selected':'' ?>>Abril</option>
                <option value="5"  <?= $mes==5?'selected':'' ?>>Maio</option>
                <option value="6"  <?= $mes==6?'selected':'' ?>>Junho</option>
                <option value="7"  <?= $mes==7?'selected':'' ?>>Julho</option>
                <option value="8"  <?= $mes==8?'selected':'' ?>>Agosto</option>
                <option value="9"  <?= $mes==9?'selected':'' ?>>Setembro</option>
                <option value="10" <?= $mes==10?'selected':'' ?>>Outubro</option>
                <option value="11" <?= $mes==11?'selected':'' ?>>Novembro</option>
                <option value="12" <?= $mes==12?'selected':'' ?>>Dezembro</option>
            </select>
        </div>

        <div class="col-md-3">
            <input
                type="number"
                name="ano"
                class="form-control"
                placeholder="Ano (ex: 2026)"
                value="<?= $ano ?>"
            >
        </div>

        <div class="col-md-3">
            <button class="btn btn-primary w-100">
                Filtrar
            </button>
        </div>

        <div class="col-md-3">
            <a href="holerite.php" class="btn btn-secondary w-100">
                Limpar
            </a>
        </div>

    </form>

    <!-- TABELA -->
    <div class="card card-dashboard p-3">

        <h5>Solicitações</h5>

        <div class="table-responsive">

            <table class="table table-hover mt-3">

                <thead class="table-light">
                    <tr>
                        <th>Funcionário</th>
                        <th>Período</th>
                        <th>Data de Envio</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php while($row = $resultado->fetch_assoc()) { ?>

                    <tr>

                        <td>
                            <i class="bi bi-person me-2"></i>
                            <?= $row['nome'] ?>
                        </td>

                        <td><?= $row['periodo'] ?></td>

                        <td>
                            <?= date('d/m/Y', strtotime($row['data_envio'])) ?>
                        </td>

                        <td>
                            <?php if($row['status'] == 'pendente'){ ?>
                                <span class="badge bg-warning">Pendente</span>
                            <?php } else { ?>
                                <span class="badge bg-primary">Enviado</span>
                            <?php } ?>
                        </td>

                        <td>
                            <a href="<?= $row['arquivo'] ?>" target="_blank"
                               class="d-flex align-items-center gap-2 text-primary text-decoration-none">
                                <i class="bi bi-download"></i>
                                Download
                            </a>
                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>
    </div>

</div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalHolerite" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="enviar_holerite.php" method="POST" enctype="multipart/form-data">

                <div class="modal-header">
                    <h5 class="modal-title">Enviar Holerite</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="form-label">Funcionário</label>
                    <select name="funcionario_id" class="form-control" required>
                        <option value="">Selecione</option>

                        <?php
                        $funcionarios = $con->query("
                        SELECT * FROM funcionarios ORDER BY nome
                        ");

                        while($func = $funcionarios->fetch_assoc()){
                        ?>
                            <option value="<?= $func['id_funcionario'] ?>">
                                <?= $func['nome'] ?>
                            </option>
                        <?php } ?>

                    </select>

                    <label class="form-label mt-3">
    Competência
</label>

<div class="row g-2">

    <!-- MÊS -->
    <div class="col-md-6">

        <select
            name="mes"
            class="form-control"
            required
        >
            <option value="">
                Selecione o mês
            </option>

            <option value="Janeiro">Janeiro</option>
            <option value="Fevereiro">Fevereiro</option>
            <option value="Março">Março</option>
            <option value="Abril">Abril</option>
            <option value="Maio">Maio</option>
            <option value="Junho">Junho</option>
            <option value="Julho">Julho</option>
            <option value="Agosto">Agosto</option>
            <option value="Setembro">Setembro</option>
            <option value="Outubro">Outubro</option>
            <option value="Novembro">Novembro</option>
            <option value="Dezembro">Dezembro</option>
        </select>

    </div>

    <!-- ANO -->
    <div class="col-md-6">

        <select
            name="ano"
            class="form-control"
            required
        >

            <?php
            $anoAtual = date('Y');

            for ($i = $anoAtual + 1; $i >= $anoAtual - 5; $i--) {
            ?>

                <option value="<?= $i ?>">
                    <?= $i ?>
                </option>

            <?php } ?>

        </select>

    </div>

</div>

                    <label class="form-label mt-3">PDF do Holerite</label>
                    <input type="file" name="arquivo" class="form-control"
                           accept=".pdf" required>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>