<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

$id_empresa = (int) ($_SESSION['id_empresa'] ?? 0);

if ($id_empresa <= 0) {
    die('Erro: empresa não identificada. Faça login novamente.');
}

function horasDecimaisParaMinutos($totalHoras): ?int
{
    if ($totalHoras === null || $totalHoras === '') {
        return null;
    }

    return (int) round((float) $totalHoras * 60);
}

/**
 * Formata o horário TIME do banco.
 */
function formatarHora($hora): string
{
    if (
        empty($hora) ||
        $hora === '00:00:00' ||
        $hora === '00:00'
    ) {
        return '-';
    }

    $timestamp = strtotime($hora);

    if ($timestamp === false) {
        return '-';
    }

    return date('H:i', $timestamp);
}

function formatarDuracaoMinutos(?int $totalMinutos): string
{
    if ($totalMinutos === null) {
        return 'Em andamento';
    }

    $totalMinutos = max(0, $totalMinutos);

    if ($totalMinutos === 0) {
        return '0h';
    }

    $horas = intdiv($totalMinutos, 60);
    $minutos = $totalMinutos % 60;

    if ($horas === 0) {
        return $minutos . 'min';
    }

    if ($minutos === 0) {
        return $horas . 'h';
    }

    return $horas .
        'h' .
        str_pad(
            (string) $minutos,
            2,
            '0',
            STR_PAD_LEFT
        ) .
        'min';
}

/**
 * Recebe o valor decimal do banco e devolve a duração formatada.
 */
function formatarTotalHoras($totalHoras): string
{
    return formatarDuracaoMinutos(
        horasDecimaisParaMinutos($totalHoras)
    );
}

/**
 * Determina o status utilizando minutos inteiros.
 */
function definirStatusPonto(array $ponto): array
{
    $entrada = $ponto['hora_entrada'] ?? null;
    $saida = $ponto['hora_saida'] ?? null;

    if (
        empty($entrada) ||
        $entrada === '00:00:00'
    ) {
        return [
            'codigo' => 'ausente',
            'texto' => 'Ausente',
            'classe' => 'badge-ausente'
        ];
    }

    if (
        empty($saida) ||
        $saida === '00:00:00'
    ) {
        return [
            'codigo' => 'em_andamento',
            'texto' => 'Em andamento',
            'classe' => 'badge-andamento'
        ];
    }

    $totalMinutos = horasDecimaisParaMinutos(
        $ponto['total_horas'] ?? null
    );

    if ($totalMinutos === null) {
        return [
            'codigo' => 'em_andamento',
            'texto' => 'Em andamento',
            'classe' => 'badge-andamento'
        ];
    }

    if ($totalMinutos > 480) {
        return [
            'codigo' => 'hora_extra',
            'texto' => 'Hora Extra',
            'classe' => 'badge-completo'
        ];
    }

    if ($totalMinutos === 480) {
        return [
            'codigo' => 'completo',
            'texto' => 'Completo',
            'classe' => 'badge-completo'
        ];
    }

    return [
        'codigo' => 'atraso',
        'texto' => 'Horas Pendentes',
        'classe' => 'badge-atraso'
    ];
}

/* =========================================================
   CARDS DE HOJE
========================================================= */

$sqlHoje = "
    SELECT
        COUNT(
            CASE
                WHEN hora_entrada IS NOT NULL
                 AND hora_entrada <> '00:00:00'
                THEN 1
            END
        ) AS entradas_hoje,

        COUNT(
            CASE
                WHEN hora_saida IS NOT NULL
                 AND hora_saida <> '00:00:00'
                THEN 1
            END
        ) AS saidas_hoje,

        COUNT(
            CASE
                WHEN hora_entrada IS NOT NULL
                 AND hora_entrada <> '00:00:00'
                 AND (
                        hora_saida IS NULL
                        OR hora_saida = '00:00:00'
                     )
                THEN 1
            END
        ) AS em_andamento,

        COUNT(
            CASE
                WHEN total_horas IS NOT NULL
                 AND ROUND(total_horas * 60) < 480
                THEN 1
            END
        ) AS atrasos_hoje

    FROM pontos

    WHERE id_empresa = ?
      AND data = CURDATE()
";

$stmtHoje = $con->prepare($sqlHoje);

if (!$stmtHoje) {
    die('Erro no prepare cards hoje: ' . $con->error);
}

$stmtHoje->bind_param('i', $id_empresa);
$stmtHoje->execute();

$resHoje = $stmtHoje->get_result()->fetch_assoc();

$entradasHoje = (int) ($resHoje['entradas_hoje'] ?? 0);
$saidasHoje = (int) ($resHoje['saidas_hoje'] ?? 0);
$emAndamento = (int) ($resHoje['em_andamento'] ?? 0);
$atrasosHoje = (int) ($resHoje['atrasos_hoje'] ?? 0);

$stmtHoje->close();

/* =========================================================
   REGISTROS COMPLETOS DE HOJE
========================================================= */

$sqlPresentes = "
    SELECT COUNT(*) AS total

    FROM pontos p

    INNER JOIN funcionarios f
        ON f.id_funcionario = p.id_funcionario
       AND f.id_empresa = p.id_empresa

    WHERE p.id_empresa = ?
      AND f.id_empresa = ?
      AND p.data = CURDATE()
      AND p.hora_entrada IS NOT NULL
      AND p.hora_entrada <> '00:00:00'
      AND p.hora_saida IS NOT NULL
      AND p.hora_saida <> '00:00:00'
";

$stmt = $con->prepare($sqlPresentes);

if (!$stmt) {
    die('Erro no prepare presentes: ' . $con->error);
}

$stmt->bind_param('ii', $id_empresa, $id_empresa);
$stmt->execute();

$presentes = (int) (
    $stmt->get_result()->fetch_assoc()['total'] ?? 0
);

$stmt->close();

/* =========================================================
   ATRASOS DE HOJE
========================================================= */

$sqlAtrasos = "
    SELECT COUNT(*) AS total

    FROM pontos p

    INNER JOIN funcionarios f
        ON f.id_funcionario = p.id_funcionario
       AND f.id_empresa = p.id_empresa

    WHERE p.id_empresa = ?
      AND f.id_empresa = ?
      AND p.data = CURDATE()
      AND p.total_horas IS NOT NULL
      AND ROUND(p.total_horas * 60) < 480
";

$stmt = $con->prepare($sqlAtrasos);

if (!$stmt) {
    die('Erro no prepare atrasos: ' . $con->error);
}

$stmt->bind_param('ii', $id_empresa, $id_empresa);
$stmt->execute();

$atrasos = (int) (
    $stmt->get_result()->fetch_assoc()['total'] ?? 0
);

$stmt->close();

/* =========================================================
   FUNCIONÁRIOS ATIVOS
========================================================= */

$sqlAtivos = "
    SELECT COUNT(*) AS total

    FROM funcionarios

    WHERE ativo = 1
      AND id_empresa = ?
";

$stmt = $con->prepare($sqlAtivos);

if (!$stmt) {
    die('Erro no prepare ativos: ' . $con->error);
}

$stmt->bind_param('i', $id_empresa);
$stmt->execute();

$ativos = (int) (
    $stmt->get_result()->fetch_assoc()['total'] ?? 0
);

$stmt->close();

/* =========================================================
   REGISTROS DE PONTO
========================================================= */

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
       AND f.id_empresa = p.id_empresa

    WHERE p.id_empresa = ?
      AND f.id_empresa = ?

    ORDER BY
        p.data DESC,
        p.hora_entrada DESC
";

$stmtPontos = $con->prepare($sqlPontos);

if (!$stmtPontos) {
    die('Erro no prepare pontos: ' . $con->error);
}

$stmtPontos->bind_param(
    'ii',
    $id_empresa,
    $id_empresa
);

$stmtPontos->execute();

$query = $stmtPontos->get_result();

$registros = [];
$ultimosRegistros = [];

while ($ponto = $query->fetch_assoc()) {
    $ponto['total_minutos'] =
        horasDecimaisParaMinutos($ponto['total_horas']);

    $registros[] = $ponto;

    if (count($ultimosRegistros) < 5) {
        $ultimosRegistros[] = $ponto;
    }
}

$stmtPontos->close();

$percentualPresentes = $ativos > 0
    ? (int) round(($presentes / $ativos) * 100)
    : 0;

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Ponto</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --card-soft: #f8fafc;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --blue: #2563eb;
            --green: #16a34a;
            --red: #dc2626;
            --yellow: #d97706;
            --shadow: 0 10px 26px rgba(15, 23, 42, .06);
        }

        body {
            background: var(--bg);
            color: var(--text);
        }

        .content {
            min-height: 100vh;
            padding: 32px;
        }

        .dashboard-header,
        .kpi-card,
        .panel-card,
        .table-card {
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .dashboard-header {
            border-radius: 22px;
            padding: 26px;
            margin-bottom: 22px;
        }

        .dashboard-title {
            font-size: 31px;
            font-weight: 850;
            margin: 0 0 6px;
            color: var(--text);
        }

        .dashboard-subtitle {
            color: var(--muted);
            margin: 0;
        }

        .date-pill {
            background: #eff6ff;
            color: var(--blue);
            border-radius: 999px;
            padding: 9px 15px;
            font-size: 14px;
            font-weight: 800;
            white-space: nowrap;
        }

        .kpi-card {
            border-radius: 20px;
            padding: 20px;
            height: 100%;
        }

        .kpi-label {
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .kpi-value {
            color: var(--text);
            font-size: 30px;
            font-weight: 850;
            margin-bottom: 3px;
        }

        .kpi-small {
            color: var(--muted);
            font-size: 13px;
        }

        .kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: #eff6ff;
            color: var(--blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .panel-card {
            border-radius: 20px;
            height: 100%;
            overflow: hidden;
        }

        .panel-header,
        .table-card-header {
            padding: 20px 22px;
            border-bottom: 1px solid var(--border);
            background: var(--card-soft);
        }

        .panel-header h5,
        .table-card-header h5 {
            color: var(--text);
            font-weight: 850;
            margin-bottom: 4px;
        }

        .panel-header p,
        .table-card-header p {
            color: var(--muted);
            margin-bottom: 0;
            font-size: 14px;
        }

        .panel-body-custom {
            padding: 20px 22px;
        }

        .progress-clean {
            height: 10px;
            border-radius: 999px;
            background: var(--border);
            overflow: hidden;
        }

        .progress-clean div {
            height: 100%;
            width: <?= $percentualPresentes ?>%;
            background: var(--blue);
            border-radius: 999px;
        }

        .last-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 0;
            border-bottom: 1px solid var(--border);
        }

        .last-item:last-child {
            border-bottom: none;
        }

        .employee-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #eff6ff;
            color: var(--blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 850;
            flex-shrink: 0;
        }

        .employee-name {
            color: var(--text);
            font-weight: 800;
        }

        .table-card {
            border-radius: 20px;
            overflow: hidden;
        }

        .search-input,
        .status-filter {
            border-radius: 12px;
            border: 1px solid var(--border);
            height: 42px;
            color: var(--text);
            background: var(--card);
        }

        .search-input::placeholder {
            color: var(--muted);
        }

        .table {
            margin-bottom: 0;
            color: var(--text);
        }

        .table thead th {
            background: var(--card-soft);
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 15px 20px;
            white-space: nowrap;
            border-bottom: 1px solid var(--border);
        }

        .table tbody td {
            background: var(--card);
            color: var(--text);
            padding: 16px 20px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border);
        }

        .table tbody tr:hover td {
            background: var(--card-soft);
        }

        .hour-text {
            color: var(--text);
            font-weight: 750;
        }

        .total-text {
            color: var(--blue);
            font-weight: 850;
            white-space: nowrap;
        }

        .badge-status {
            border-radius: 999px;
            padding: 7px 11px;
            font-size: 12px;
            font-weight: 800;
            display: inline-block;
            white-space: nowrap;
        }

        .badge-completo {
            background: #dcfce7;
            color: #166534;
        }

        .badge-atraso {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-andamento {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-ausente {
            background: #e2e8f0;
            color: #334155;
        }

        .empty-state {
            text-align: center;
            padding: 36px 20px;
            color: var(--muted);
        }

        body.dark,
        body.dark-mode {
            --bg: #0f172a;
            --card: #111c2f;
            --card-soft: #17233a;
            --text: #f8fafc;
            --muted: #cbd5e1;
            --border: #2b3a55;
            --shadow: 0 10px 28px rgba(0, 0, 0, .22);
        }

        body.dark .date-pill,
        body.dark-mode .date-pill,
        body.dark .kpi-icon,
        body.dark-mode .kpi-icon,
        body.dark .employee-avatar,
        body.dark-mode .employee-avatar {
            background: rgba(37, 99, 235, .16);
            color: #93c5fd;
        }

        body.dark .search-input,
        body.dark-mode .search-input,
        body.dark .status-filter,
        body.dark-mode .status-filter {
            background: #0b1220;
            color: #f8fafc;
            border-color: #334155;
        }

        body.dark .search-input::placeholder,
        body.dark-mode .search-input::placeholder {
            color: #94a3b8;
        }

        body.dark .status-filter option,
        body.dark-mode .status-filter option {
            background: #0b1220;
            color: #f8fafc;
        }

        body.dark .badge-completo,
        body.dark-mode .badge-completo {
            background: rgba(22, 163, 74, .20);
            color: #86efac;
        }

        body.dark .badge-atraso,
        body.dark-mode .badge-atraso {
            background: rgba(220, 38, 38, .20);
            color: #fca5a5;
        }

        body.dark .badge-andamento,
        body.dark-mode .badge-andamento {
            background: rgba(217, 119, 6, .22);
            color: #fcd34d;
        }

        body.dark .badge-ausente,
        body.dark-mode .badge-ausente {
            background: rgba(148, 163, 184, .18);
            color: #cbd5e1;
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }

            .dashboard-header {
                padding: 22px;
            }

            .dashboard-title {
                font-size: 26px;
            }

            .kpi-value {
                font-size: 26px;
            }
        }
    </style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid">

        <div class="dashboard-header">

            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">

                <div>

                    <h1 class="dashboard-title">
                        Dashboard de Ponto
                    </h1>

                    <p class="dashboard-subtitle">
                        Acompanhe entradas, saídas, atrasos e registros em andamento.
                    </p>

                </div>

                <div class="d-flex align-items-start">

                    <span class="date-pill">

                        <i class="bi bi-calendar3 me-1"></i>

                        <?= date('d/m/Y') ?>

                    </span>

                </div>

            </div>

        </div>

        <div class="row g-3 mb-4">

            <div class="col-12 col-md-6 col-xl-3">

                <div class="kpi-card">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="kpi-label">
                                Entradas hoje
                            </div>

                            <h2 class="kpi-value">
                                <?= $entradasHoje ?>
                            </h2>

                            <div class="kpi-small">
                                Funcionários que iniciaram jornada
                            </div>

                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-12 col-md-6 col-xl-3">

                <div class="kpi-card">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="kpi-label">
                                Saídas hoje
                            </div>

                            <h2 class="kpi-value">
                                <?= $saidasHoje ?>
                            </h2>

                            <div class="kpi-small">
                                Jornadas finalizadas hoje
                            </div>

                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-box-arrow-right"></i>
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-12 col-md-6 col-xl-3">

                <div class="kpi-card">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="kpi-label">
                                Atrasos hoje
                            </div>

                            <h2 class="kpi-value">
                                <?= $atrasosHoje ?>
                            </h2>

                            <div class="kpi-small">
                                Registros com menos de 8h
                            </div>

                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-12 col-md-6 col-xl-3">

                <div class="kpi-card">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="kpi-label">
                                Em andamento
                            </div>

                            <h2 class="kpi-value">
                                <?= $emAndamento ?>
                            </h2>

                            <div class="kpi-small">
                                Pontos ainda abertos
                            </div>

                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-hourglass-split"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="row g-3 mb-4">

            <div class="col-12 col-xl-5">

                <div class="panel-card">

                    <div class="panel-header">

                        <h5>Resumo operacional</h5>

                        <p>
                            Visão rápida da situação geral de hoje.
                        </p>

                    </div>

                    <div class="panel-body-custom">

                        <div class="d-flex justify-content-between mb-2">

                            <span class="text-muted">
                                Registros completos hoje
                            </span>

                            <strong class="employee-name">
                                <?= $presentes ?>
                            </strong>

                        </div>

                        <div class="d-flex justify-content-between mb-2">

                            <span class="text-muted">
                                Funcionários ativos
                            </span>

                            <strong class="employee-name">
                                <?= $ativos ?>
                            </strong>

                        </div>

                        <div class="d-flex justify-content-between mb-3">

                            <span class="text-muted">
                                Atrasos hoje
                            </span>

                            <strong class="employee-name">
                                <?= $atrasos ?>
                            </strong>

                        </div>

                        <div class="progress-clean mb-2">
                            <div></div>
                        </div>

                        <small class="text-muted">

                            <?= $percentualPresentes ?>% de registros completos
                            hoje em relação aos funcionários ativos.

                        </small>

                    </div>

                </div>

            </div>

            <div class="col-12 col-xl-7">

                <div class="panel-card">

                    <div class="panel-header">

                        <h5>Últimos registros</h5>

                        <p>
                            Movimentações mais recentes de ponto.
                        </p>

                    </div>

                    <div class="panel-body-custom">

                        <?php if (count($ultimosRegistros) > 0): ?>

                            <?php foreach ($ultimosRegistros as $item): ?>

                                <?php

                                $inicial = mb_strtoupper(
                                    mb_substr(
                                        $item['nome'],
                                        0,
                                        1,
                                        'UTF-8'
                                    ),
                                    'UTF-8'
                                );

                                $statusItem = definirStatusPonto($item);

                                ?>

                                <div class="last-item">

                                    <div class="d-flex align-items-center gap-2">

                                        <div class="employee-avatar">

                                            <?= htmlspecialchars($inicial) ?>

                                        </div>

                                        <div>

                                            <div class="employee-name">

                                                <?= htmlspecialchars(
                                                    $item['nome']
                                                ) ?>

                                            </div>

                                            <div class="text-muted small">

                                                <?= date(
                                                    'd/m/Y',
                                                    strtotime($item['data'])
                                                ) ?>

                                                · Entrada

                                                <?= formatarHora(
                                                    $item['hora_entrada']
                                                ) ?>

                                                <?php if (
                                                    $item['total_minutos'] !== null
                                                ): ?>

                                                    · Total

                                                    <?= formatarDuracaoMinutos(
                                                        $item['total_minutos']
                                                    ) ?>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </div>

                                    <span class="badge-status <?= htmlspecialchars(
                                        $statusItem['classe']
                                    ) ?>">

                                        <?= htmlspecialchars(
                                            $statusItem['texto']
                                        ) ?>

                                    </span>

                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="empty-state">
                                Nenhum registro recente encontrado.
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

        <div class="table-card">

            <div class="table-card-header">

                <div class="row align-items-center g-3">

                    <div class="col-lg-6">

                        <h5>Registros de Ponto</h5>

                        <p>
                            Consulte entradas, saídas e status dos funcionários.
                        </p>

                    </div>

                    <div class="col-lg-3">

                        <input
                            type="text"
                            id="pesquisaTabela"
                            class="form-control search-input"
                            placeholder="Pesquisar funcionário"
                            autocomplete="off"
                        >

                    </div>

                    <div class="col-lg-3">

                        <select
                            id="filtroStatus"
                            class="form-select status-filter"
                        >

                            <option value="todos">
                                Todos
                            </option>

                            <option value="completo">
                                Completo
                            </option>

                            <option value="hora_extra">
                                Hora Extra
                            </option>

                            <option value="atraso">
                                Horas Pendentes
                            </option>

                            <option value="em_andamento">
                                Em andamento
                            </option>

                            <option value="ausente">
                                Ausente
                            </option>

                        </select>

                    </div>

                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>Funcionário</th>

                            <th>Data</th>

                            <th>Entrada</th>

                            <th>Saída</th>

                            <th>Total</th>

                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody id="corpoTabela">

                    <?php if (count($registros) > 0): ?>

                        <?php foreach ($registros as $ponto): ?>

                            <?php

                            $statusLinha = definirStatusPonto($ponto);

                            $inicial = mb_strtoupper(
                                mb_substr(
                                    $ponto['nome'],
                                    0,
                                    1,
                                    'UTF-8'
                                ),
                                'UTF-8'
                            );

                            ?>

                            <tr
                                data-nome="<?= htmlspecialchars(
                                    mb_strtolower(
                                        $ponto['nome'],
                                        'UTF-8'
                                    )
                                ) ?>"
                                data-status="<?= htmlspecialchars(
                                    $statusLinha['codigo']
                                ) ?>"
                            >

                                <td>

                                    <div class="d-flex align-items-center gap-2">

                                        <div class="employee-avatar">

                                            <?= htmlspecialchars($inicial) ?>

                                        </div>

                                        <span class="employee-name">

                                            <?= htmlspecialchars(
                                                $ponto['nome']
                                            ) ?>

                                        </span>

                                    </div>

                                </td>

                                <td>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime($ponto['data'])
                                    ) ?>

                                </td>

                                <td>

                                    <span class="hour-text">

                                        <?= formatarHora(
                                            $ponto['hora_entrada']
                                        ) ?>

                                    </span>

                                </td>

                                <td>

                                    <span class="hour-text">

                                        <?= formatarHora(
                                            $ponto['hora_saida']
                                        ) ?>

                                    </span>

                                </td>

                                <td>

                                    <span class="total-text">

                                        <?= formatarDuracaoMinutos(
                                            $ponto['total_minutos']
                                        ) ?>

                                    </span>

                                </td>

                                <td>

                                    <span class="badge-status <?= htmlspecialchars(
                                        $statusLinha['classe']
                                    ) ?>">

                                        <?= htmlspecialchars(
                                            $statusLinha['texto']
                                        ) ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="6">

                                <div class="empty-state">

                                    Nenhum registro de ponto encontrado para
                                    esta empresa.

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div
                id="semResultado"
                class="empty-state d-none"
            >

                Nenhum funcionário encontrado.

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/theme.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const pesquisaTabela = document.getElementById(
        'pesquisaTabela'
    );

    const filtroStatus = document.getElementById(
        'filtroStatus'
    );

    const linhas = document.querySelectorAll(
        '#corpoTabela tr[data-nome]'
    );

    const semResultado = document.getElementById(
        'semResultado'
    );

    function removerAcentos(texto) {
        return String(texto || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function filtrarTabela() {
        const pesquisa = removerAcentos(
            pesquisaTabela.value
                .toLowerCase()
                .trim()
        );

        const statusSelecionado = filtroStatus.value;

        let visiveis = 0;

        linhas.forEach(function (linha) {
            const nome = removerAcentos(
                linha.dataset.nome || ''
            );

            const statusLinha =
                linha.dataset.status || 'ausente';

            const combinaNome =
                nome.includes(pesquisa);

            const combinaStatus =
                statusSelecionado === 'todos' ||
                statusSelecionado === statusLinha;

            const mostrar =
                combinaNome && combinaStatus;

            linha.style.display =
                mostrar ? '' : 'none';

            if (mostrar) {
                visiveis++;
            }
        });

        if (linhas.length > 0) {
            semResultado.classList.toggle(
                'd-none',
                visiveis > 0
            );
        }
    }

    if (pesquisaTabela) {
        pesquisaTabela.addEventListener(
            'input',
            filtrarTabela
        );
    }

    if (filtroStatus) {
        filtroStatus.addEventListener(
            'change',
            filtrarTabela
        );
    }

});
</script>

<script src="js/translate.js"></script>

</body>
</html>