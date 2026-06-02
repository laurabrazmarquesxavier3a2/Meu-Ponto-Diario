<?php
require_once 'auth.php';
require_once 'config/database.php';

$id_empresa = $_SESSION['id_empresa'] ?? 0;

if (!$id_empresa) {
    die("Erro: empresa não identificada. Faça login novamente.");
}

/* PRESENTES */
$sqlPresentes = "
SELECT COUNT(*) AS total
FROM pontos p
INNER JOIN funcionarios f
    ON f.id_funcionario = p.id_funcionario
WHERE p.status = 'completo'
AND p.id_empresa = ?
AND f.id_empresa = ?
";

$stmt = $con->prepare($sqlPresentes);
$stmt->bind_param("ii", $id_empresa, $id_empresa);
$stmt->execute();
$presentes = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

/* ATRASOS */
$sqlAtrasos = "
SELECT COUNT(*) AS total
FROM pontos p
INNER JOIN funcionarios f
    ON f.id_funcionario = p.id_funcionario
WHERE p.status = 'atraso'
AND p.id_empresa = ?
AND f.id_empresa = ?
";

$stmt = $con->prepare($sqlAtrasos);
$stmt->bind_param("ii", $id_empresa, $id_empresa);
$stmt->execute();
$atrasos = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

/* FUNCIONÁRIOS ATIVOS */
$sqlAtivos = "
SELECT COUNT(*) AS total
FROM funcionarios
WHERE ativo = 1
AND id_empresa = ?
";

$stmt = $con->prepare($sqlAtivos);
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$ativos = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

/* REGISTROS DE PONTO */
$sqlPontos = "
SELECT
    p.id_ponto,
    p.id_funcionario,
    p.data,
    p.hora_entrada,
    p.hora_saida,
    p.total_horas,
    p.status,
    f.nome
FROM pontos p
INNER JOIN funcionarios f
    ON f.id_funcionario = p.id_funcionario
WHERE p.id_empresa = ?
AND f.id_empresa = ?
ORDER BY p.data DESC, p.hora_entrada DESC
";

$stmtPontos = $con->prepare($sqlPontos);
$stmtPontos->bind_param("ii", $id_empresa, $id_empresa);
$stmtPontos->execute();
$query = $stmtPontos->get_result();

function formatarHora($hora) {
    if (!$hora || $hora == '00:00:00') {
        return '-';
    }

    return date('H:i', strtotime($hora));
}

function formatarTotalHoras($total) {
    if ($total === null || $total === '') {
        return 'Em andamento';
    }

    return number_format((float)$total, 2, ',', '.') . 'h';
}
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

    <h1 class="fw-bold">Histórico de Ponto</h1>

    <h5 class="text-muted mb-4">
        Controle de entrada e saída dos funcionários
    </h5>

    <div class="row g-4 mb-4">

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">

                <h5>Registros completos</h5>

                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $presentes ?>
                    <i class="bi bi-clock"></i>
                </h1>

            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">

                <h5>Atrasos registrados</h5>

                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $atrasos ?>
                    <i class="bi bi-clock-history"></i>
                </h1>

            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">

                <h5>Funcionários Ativos</h5>

                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $ativos ?>
                    <i class="bi bi-people"></i>
                </h1>

            </div>
        </div>

    </div>

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

                <?php if ($query->num_rows > 0): ?>

                    <?php while($ponto = $query->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <i class="bi bi-person me-2"></i>
                                <?= htmlspecialchars($ponto['nome']) ?>
                            </td>

                            <td>
                                <?= date('d/m/Y', strtotime($ponto['data'])) ?>
                            </td>

                            <td>
                                <?= formatarHora($ponto['hora_entrada']) ?>
                            </td>

                            <td>
                                <?= formatarHora($ponto['hora_saida']) ?>
                            </td>

                            <td>
                                <?= formatarTotalHoras($ponto['total_horas']) ?>
                            </td>

                            <td>
                                <?php
                                if($ponto['status'] == 'completo') {
                                    echo '<span class="badge bg-success">Completo</span>';
                                } elseif($ponto['status'] == 'atraso') {
                                    echo '<span class="badge bg-danger">Atraso</span>';
                                } elseif($ponto['status'] == 'em andamento') {
                                    echo '<span class="badge bg-warning text-dark">Em andamento</span>';
                                } else {
                                    echo '<span class="badge bg-secondary">Ausente</span>';
                                }
                                ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Nenhum registro de ponto encontrado para esta empresa.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script src="js/theme.js"></script>
</body>
</html>