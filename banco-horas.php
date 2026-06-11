<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

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

    ROUND(SUM(p.total_horas - 8), 2) AS saldo_total,

    ROUND(
        SUM(
            CASE
                WHEN MONTH(p.data) = MONTH(CURDATE())
                AND YEAR(p.data) = YEAR(CURDATE())
                THEN (p.total_horas - 8)
                ELSE 0
            END
        ),
        2
    ) AS saldo_mes,

    MAX(p.data) AS data_atualizacao

FROM pontos p

INNER JOIN funcionarios f
    ON f.id_funcionario = p.id_funcionario

WHERE p.id_empresa = ?
AND f.id_empresa = ?

GROUP BY f.id_funcionario

ORDER BY f.nome ASC
";

 $stmt = $con->prepare($sql);

$stmt->bind_param(
    "ii",
    $id_empresa,
    $id_empresa
);

$stmt->execute();

$resultado = $stmt->get_result();

while ($row = $resultado->fetch_assoc()) {

    $saldo_total = (float)$row['saldo_total'];

    if ($saldo_total > 0) {
        $status = 'positivo';
    } elseif ($saldo_total < 0) {
        $status = 'negativo';
    } else {
        $status = 'neutro';
    }

    $row['status'] = $status;

    $registros[] = $row;
}

$stmt->close();

/* DASHBOARD */
$totalFuncionarios = count($registros);
$maiorPositivo = null;
$maiorNegativo = null;
$alertasNegativos = [];

foreach ($registros as $registro) {
    if ($maiorPositivo === null || (float)$registro['saldo_total'] > (float)$maiorPositivo['saldo_total']) {
        $maiorPositivo = $registro;
    }

    if ($maiorNegativo === null || (float)$registro['saldo_total'] < (float)$maiorNegativo['saldo_total']) {
        $maiorNegativo = $registro;
    }

    if ((float)$registro['saldo_total'] < 0) {
        $alertasNegativos[] = $registro;
    }
}

usort($alertasNegativos, function($a, $b) {
    return (float)$a['saldo_total'] <=> (float)$b['saldo_total'];
});

$mediaExtras = $totalFuncionarios > 0 ? ((float)$totalExtras / $totalFuncionarios) : 0;
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
<link rel="stylesheet" href="css/banco-horas.css" >

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid">

        <div class="dashboard-header">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                    <h1 class="dashboard-title">Banco de Horas</h1>
                    <p class="dashboard-subtitle">
                        Acompanhe saldos, horas extras e pendências de compensação.
                    </p>
                </div>

                <div class="d-flex align-items-start">
                    <span class="month-pill">
                        <i class="bi bi-calendar3 me-1"></i>
                        <?= htmlspecialchars($mesNome) ?> / <?= date('Y') ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">

            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Saldos positivos</div>
                            <h2 class="kpi-value"><?= (int)$positivos ?></h2>
                            <div class="kpi-small">Funcionários acima do saldo</div>
                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-arrow-up-right"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Saldos negativos</div>
                            <h2 class="kpi-value"><?= (int)$negativos ?></h2>
                            <div class="kpi-small">Funcionários em atenção</div>
                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Horas extras</div>
                            <h2 class="kpi-value"><?= number_format((float)$totalExtras, 2, ',', '.') ?>h</h2>
                            <div class="kpi-small">Total acumulado no mês</div>
                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="kpi-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="kpi-label">Monitorados</div>
                            <h2 class="kpi-value"><?= (int)$totalFuncionarios ?></h2>
                            <div class="kpi-small">Com registro neste mês</div>
                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row g-3 mb-4">

            <div class="col-12 col-xl-7">
                <div class="panel-card">
                    <div class="panel-header">
                        <h5>Funcionários em alerta</h5>
                        <p>Maiores saldos negativos para acompanhamento do RH.</p>
                    </div>

                    <div class="panel-body-custom">

                        <?php if (count($alertasNegativos) > 0): ?>

                            <?php foreach (array_slice($alertasNegativos, 0, 5) as $alerta): ?>

                                <?php
                                $inicialAlerta = mb_strtoupper(mb_substr($alerta['nome'], 0, 1, 'UTF-8'), 'UTF-8');
                                ?>

                                <div class="alert-user">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="employee-avatar">
                                            <?= htmlspecialchars($inicialAlerta) ?>
                                        </div>

                                        <div>
                                            <div class="employee-name">
                                                <?= htmlspecialchars($alerta['nome']) ?>
                                            </div>
                                            <div class="text-muted small">
                                                Saldo total negativo
                                            </div>
                                        </div>
                                    </div>

                                    <span class="saldo-negativo">
                                        <?= formatarHoras($alerta['saldo_total']) ?>
                                    </span>
                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="empty-state">
                                <i class="bi bi-check-circle"></i>
                                Nenhum funcionário com saldo negativo.
                            </div>

                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="panel-card">
                    <div class="panel-header">
                        <h5>Resumo do mês</h5>
                        <p>Indicadores rápidos do período atual.</p>
                    </div>

                    <div class="panel-body-custom">

                        <div class="metric-line">
                            <span>Maior saldo positivo</span>
                            <strong class="saldo-positivo">
                                <?= $maiorPositivo ? htmlspecialchars($maiorPositivo['nome']) . ' ' . formatarHoras($maiorPositivo['saldo_total']) : '-' ?>
                            </strong>
                        </div>

                        <div class="metric-line">
                            <span>Maior saldo negativo</span>
                            <strong class="saldo-negativo">
                                <?= $maiorNegativo ? htmlspecialchars($maiorNegativo['nome']) . ' ' . formatarHoras($maiorNegativo['saldo_total']) : '-' ?>
                            </strong>
                        </div>

                        <div class="metric-line">
                            <span>Média de extras</span>
                            <strong>
                                <?= number_format((float)$mediaExtras, 2, ',', '.') ?>h
                            </strong>
                        </div>

                        <div class="metric-line">
                            <span>Total de registros</span>
                            <strong>
                                <?= (int)$totalFuncionarios ?>
                            </strong>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="table-card">

            <div class="table-card-header">
                <div class="row align-items-center g-3">
                    <div class="col-lg-6">
                        <h5>Saldos dos Funcionários</h5>
                        <p>Consulte o saldo total e mensal de cada funcionário.</p>
                    </div>

                    <div class="col-lg-3">
                        <input 
                            type="text" 
                            id="pesquisaTabela" 
                            class="form-control search-input" 
                            placeholder="Pesquisar funcionário"
                        >
                    </div>

                    <div class="col-lg-3">
                        <select id="filtroStatus" class="form-select status-filter">
                            <option value="todos">Todos</option>
                            <option value="positivo">Positivo</option>
                            <option value="negativo">Negativo</option>
                            <option value="neutro">Neutro</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Saldo Total</th>
                            <th>Este Mês</th>
                            <th>Última Atualização</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody id="corpoTabela">

                    <?php if (count($registros) > 0): ?>

                        <?php foreach ($registros as $row): ?>

                            <?php
                            $statusLinha = $row['status'] ?: 'neutro';

                            if ($statusLinha == 'positivo') {
                                $badge = '<span class="badge-status badge-positivo">Positivo</span>';
                            } elseif ($statusLinha == 'negativo') {
                                $badge = '<span class="badge-status badge-negativo">Negativo</span>';
                            } else {
                                $badge = '<span class="badge-status badge-neutro">Neutro</span>';
                                $statusLinha = 'neutro';
                            }

                            $dataAtualizacao = $row['data_atualizacao']
                                ? date('d/m/Y', strtotime($row['data_atualizacao']))
                                : 'Sem atualização';

                            $saldoTotalClass = 'saldo-neutro';
                            if ((float)$row['saldo_total'] > 0) {
                                $saldoTotalClass = 'saldo-positivo';
                            } elseif ((float)$row['saldo_total'] < 0) {
                                $saldoTotalClass = 'saldo-negativo';
                            }

                            $saldoMesClass = 'saldo-neutro';
                            if ((float)$row['saldo_mes'] > 0) {
                                $saldoMesClass = 'saldo-positivo';
                            } elseif ((float)$row['saldo_mes'] < 0) {
                                $saldoMesClass = 'saldo-negativo';
                            }

                            $inicial = mb_strtoupper(mb_substr($row['nome'], 0, 1, 'UTF-8'), 'UTF-8');
                            ?>

                            <tr 
                                data-nome="<?= htmlspecialchars(mb_strtolower($row['nome'], 'UTF-8')) ?>" 
                                data-status="<?= htmlspecialchars($statusLinha) ?>"
                            >
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="employee-avatar">
                                            <?= htmlspecialchars($inicial) ?>
                                        </div>

                                        <span class="employee-name">
                                            <?= htmlspecialchars($row['nome']) ?>
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="<?= $saldoTotalClass ?>">
                                        <?= formatarHoras($row['saldo_total']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="<?= $saldoMesClass ?>">
                                        <?= formatarHoras($row['saldo_mes']) ?>
                                    </span>
                                </td>

                                <td><?= htmlspecialchars($dataAtualizacao) ?></td>

                                <td><?= $badge ?></td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    Nenhum saldo encontrado para este mês.
                                </div>
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <div id="semResultado" class="empty-state d-none">
                <i class="bi bi-search"></i>
                Nenhum funcionário encontrado.
            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/theme.js"></script>

<script>
    const pesquisaTabela = document.getElementById('pesquisaTabela');
    const filtroStatus = document.getElementById('filtroStatus');
    const linhas = document.querySelectorAll('#corpoTabela tr[data-nome]');
    const semResultado = document.getElementById('semResultado');

    function removerAcentos(texto) {
        return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function filtrarTabela() {
        const pesquisa = removerAcentos(pesquisaTabela.value.toLowerCase().trim());
        const status = filtroStatus.value;

        let visiveis = 0;

        linhas.forEach(linha => {
            const nome = removerAcentos(linha.dataset.nome || '');
            const statusLinha = linha.dataset.status || 'neutro';

            const combinaNome = nome.includes(pesquisa);
            const combinaStatus = status === 'todos' || status === statusLinha;

            if (combinaNome && combinaStatus) {
                linha.style.display = '';
                visiveis++;
            } else {
                linha.style.display = 'none';
            }
        });

        if (linhas.length > 0) {
            semResultado.classList.toggle('d-none', visiveis > 0);
        }
    }

    pesquisaTabela.addEventListener('input', filtrarTabela);
    filtroStatus.addEventListener('change', filtrarTabela);
</script>
<script src="js/translate.js"></script>
</body>
</html>