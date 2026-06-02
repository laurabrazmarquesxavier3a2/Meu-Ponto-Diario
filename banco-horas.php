<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config/database.php';

$id_empresa = $_SESSION['id_empresa'] ?? 0;

if (!$id_empresa) {
    die("Erro: empresa não identificada. Faça login novamente.");
}

$mesAtual = date('Y-m');

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

$mesNome = $meses[date('m')];

function formatarHoras($valor) {
    $valor = (float)$valor;

    if ($valor > 0) {
        return '+' . number_format($valor, 2, ',', '.') . 'h';
    }

    if ($valor < 0) {
        return number_format($valor, 2, ',', '.') . 'h';
    }

    return '0h';
}

/* CARDS */
$positivos = 0;
$negativos = 0;
$totalExtras = 0;

$sqlCards = "
SELECT
    SUM(CASE WHEN status = 'positivo' THEN 1 ELSE 0 END) AS positivos,
    SUM(CASE WHEN status = 'negativo' THEN 1 ELSE 0 END) AS negativos,
    COALESCE(SUM(horas_extras_mes), 0) AS total_extras
FROM banco_horas
WHERE id_empresa = ?
AND mes = ?
";

$stmtCards = $con->prepare($sqlCards);

if (!$stmtCards) {
    die("Erro no prepare cards: " . $con->error);
}

$stmtCards->bind_param("is", $id_empresa, $mesAtual);
$stmtCards->execute();
$stmtCards->bind_result($positivos, $negativos, $totalExtras);
$stmtCards->fetch();
$stmtCards->close();

$positivos = $positivos ?? 0;
$negativos = $negativos ?? 0;
$totalExtras = $totalExtras ?? 0;

/* TABELA */
$registros = [];

$sql = "
SELECT
    f.nome,
    bh.saldo_total,
    bh.saldo_mes,
    bh.data_atualizacao,
    bh.status
FROM banco_horas bh
INNER JOIN funcionarios f
    ON f.id_funcionario = bh.id_funcionario
WHERE bh.id_empresa = ?
AND f.id_empresa = ?
AND bh.mes = ?
ORDER BY f.nome ASC
";

$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Erro no prepare tabela: " . $con->error);
}

$stmt->bind_param("iis", $id_empresa, $id_empresa, $mesAtual);
$stmt->execute();

$stmt->bind_result(
    $nome,
    $saldo_total,
    $saldo_mes,
    $data_atualizacao,
    $status
);

while ($stmt->fetch()) {
    $registros[] = [
        'nome' => $nome,
        'saldo_total' => $saldo_total,
        'saldo_mes' => $saldo_mes,
        'data_atualizacao' => $data_atualizacao,
        'status' => $status
    ];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banco de Horas</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <h1 class="fw-bold">Banco de Horas</h1>
    <h5 class="text-muted mb-4">Controle de saldo de horas dos funcionários</h5>

    <div class="container-fluid">

        <div class="row g-4 mb-4">

            <div class="col-6 col-md-3">
                <div class="card card-dashboard p-3">
                    <h5>Saldo positivo</h5>
                    <h1 class="fw-bold d-flex justify-content-between">
                        <?= $positivos ?>
                        <i class="bi bi-graph-up-arrow"></i>
                    </h1>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card card-dashboard p-3">
                    <h5>Saldo negativo</h5>
                    <h1 class="fw-bold d-flex justify-content-between">
                        <?= $negativos ?>
                        <i class="bi bi-graph-down-arrow"></i>
                    </h1>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card card-dashboard p-3">
                    <h5>Total de horas extras</h5>
                    <h1 class="fw-bold d-flex justify-content-between">
                        <?= number_format((float)$totalExtras, 2, ',', '.') ?>h
                        <i class="bi bi-clock"></i>
                    </h1>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card card-dashboard p-3">
                    <h5>Este mês</h5>
                    <h1 class="fw-bold d-flex justify-content-between">
                        <?= $mesNome ?>
                        <i class="bi bi-calendar"></i>
                    </h1>
                </div>
            </div>

        </div>

        <div class="card card-dashboard p-3">
            <h5>Saldos de Banco de Horas</h5>

            <div class="table-responsive">
                <table class="table table-hover mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>Funcionário</th>
                            <th>Saldo Total</th>
                            <th>Este Mês</th>
                            <th>Última Atualização</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($registros) > 0): ?>

                        <?php foreach ($registros as $row): ?>

                            <?php
                            if ($row['status'] == 'positivo') {
                                $badge = '<span class="badge bg-success">Positivo</span>';
                            } elseif ($row['status'] == 'negativo') {
                                $badge = '<span class="badge bg-danger">Negativo</span>';
                            } else {
                                $badge = '<span class="badge bg-secondary">Neutro</span>';
                            }

                            $dataAtualizacao = $row['data_atualizacao']
                                ? date('d/m/Y', strtotime($row['data_atualizacao']))
                                : 'Sem atualização';
                            ?>

                            <tr>
                                <td>
                                    <i class="bi bi-person me-2"></i>
                                    <?= htmlspecialchars($row['nome']) ?>
                                </td>

                                <td><?= formatarHoras($row['saldo_total']) ?></td>

                                <td><?= formatarHoras($row['saldo_mes']) ?></td>

                                <td><?= $dataAtualizacao ?></td>

                                <td><?= $badge ?></td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Nenhum saldo encontrado para este mês.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<script src="js/theme.js"></script>
</body>
</html>