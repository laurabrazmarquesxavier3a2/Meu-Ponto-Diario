```php
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

/* =========================================================
   MESES
========================================================= */

$meses = [
    1  => 'Janeiro',
    2  => 'Fevereiro',
    3  => 'Março',
    4  => 'Abril',
    5  => 'Maio',
    6  => 'Junho',
    7  => 'Julho',
    8  => 'Agosto',
    9  => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

/* =========================================================
   FILTROS
========================================================= */

$mes = isset($_GET['mes'])
    ? (int) $_GET['mes']
    : 0;

$ano = isset($_GET['ano'])
    ? (int) $_GET['ano']
    : 0;

if (!array_key_exists($mes, $meses)) {
    $mes = 0;
}

if ($ano < 2000 || $ano > 2100) {
    $ano = 0;
}

/* =========================================================
   CONSULTA DOS HOLERITES
========================================================= */

/*
|--------------------------------------------------------------------------
| A competência está armazenada no campo periodo.
|--------------------------------------------------------------------------
|
| Exemplos:
|
| Janeiro/2026
| Fevereiro/2026
| Abril/2026
|
| data_envio é apenas a data em que o arquivo foi enviado.
|
*/

$sql = "
    SELECT
        h.id,
        h.funcionario_id,
        h.arquivo,
        h.periodo,
        h.data_envio,
        h.status,
        h.id_empresa,
        f.nome

    FROM holerites h

    INNER JOIN funcionarios f
        ON f.id_funcionario = h.funcionario_id

    WHERE h.id_empresa = ?
      AND f.id_empresa = ?
";

$params = [
    $id_empresa,
    $id_empresa
];

$types = 'ii';

/*
|--------------------------------------------------------------------------
| FILTRO POR MÊS DA COMPETÊNCIA
|--------------------------------------------------------------------------
*/

if ($mes > 0) {
    $nomeMesSelecionado = $meses[$mes];

    $sql .= "
        AND LOWER(
            TRIM(
                SUBSTRING_INDEX(h.periodo, '/', 1)
            )
        ) = LOWER(?)
    ";

    $params[] = $nomeMesSelecionado;
    $types .= 's';
}

/*
|--------------------------------------------------------------------------
| FILTRO POR ANO DA COMPETÊNCIA
|--------------------------------------------------------------------------
*/

if ($ano > 0) {
    $sql .= "
        AND CAST(
            TRIM(
                SUBSTRING_INDEX(h.periodo, '/', -1)
            ) AS UNSIGNED
        ) = ?
    ";

    $params[] = $ano;
    $types .= 'i';
}

/*
|--------------------------------------------------------------------------
| ORDENA PELA COMPETÊNCIA
|--------------------------------------------------------------------------
*/

$sql .= "
    ORDER BY
        CAST(
            TRIM(
                SUBSTRING_INDEX(h.periodo, '/', -1)
            ) AS UNSIGNED
        ) DESC,

        FIELD(
            LOWER(
                TRIM(
                    SUBSTRING_INDEX(h.periodo, '/', 1)
                )
            ),
            'dezembro',
            'novembro',
            'outubro',
            'setembro',
            'agosto',
            'julho',
            'junho',
            'maio',
            'abril',
            'março',
            'fevereiro',
            'janeiro'
        ) ASC,

        h.data_envio DESC
";

$stmt = $con->prepare($sql);

if (!$stmt) {
    die(
        'Erro SQL holerites: ' .
        htmlspecialchars($con->error)
    );
}

$stmt->bind_param(
    $types,
    ...$params
);

if (!$stmt->execute()) {
    die(
        'Erro ao executar consulta de holerites: ' .
        htmlspecialchars($stmt->error)
    );
}

$resultado = $stmt->get_result();

$totalRegistros = $resultado->num_rows;

/* =========================================================
   INDICADOR: FUNCIONÁRIOS ATIVOS
========================================================= */

$stmtTotal = $con->prepare("
    SELECT COUNT(*) AS total
    FROM funcionarios
    WHERE id_empresa = ?
      AND ativo = 1
");

if (!$stmtTotal) {
    die(
        'Erro ao consultar funcionários ativos: ' .
        htmlspecialchars($con->error)
    );
}

$stmtTotal->bind_param(
    'i',
    $id_empresa
);

$stmtTotal->execute();

$resultadoTotal = $stmtTotal
    ->get_result()
    ->fetch_assoc();

$totalFuncionarios = (int) (
    $resultadoTotal['total'] ?? 0
);

$stmtTotal->close();

/* =========================================================
   INDICADOR: FUNCIONÁRIOS COM HOLERITE ENVIADO
========================================================= */

$stmtEnviados = $con->prepare("
    SELECT COUNT(DISTINCT funcionario_id) AS total

    FROM holerites

    WHERE id_empresa = ?
      AND status = 'enviado'
");

if (!$stmtEnviados) {
    die(
        'Erro ao consultar holerites enviados: ' .
        htmlspecialchars($con->error)
    );
}

$stmtEnviados->bind_param(
    'i',
    $id_empresa
);

$stmtEnviados->execute();

$resultadoEnviados = $stmtEnviados
    ->get_result()
    ->fetch_assoc();

$enviados = (int) (
    $resultadoEnviados['total'] ?? 0
);

$stmtEnviados->close();

/* =========================================================
   INDICADOR: PENDENTES
========================================================= */

$pendentes = max(
    0,
    $totalFuncionarios - $enviados
);

/* =========================================================
   PERCENTUAL
========================================================= */

$percentualEnvio = $totalFuncionarios > 0
    ? (int) round(
        ($enviados / $totalFuncionarios) * 100
    )
    : 0;

$percentualEnvio = min(
    100,
    max(0, $percentualEnvio)
);

/* =========================================================
   FUNCIONÁRIOS PARA O MODAL
========================================================= */

$stmtFuncs = $con->prepare("
    SELECT
        id_funcionario,
        nome

    FROM funcionarios

    WHERE id_empresa = ?
      AND ativo = 1

    ORDER BY nome ASC
");

if (!$stmtFuncs) {
    die(
        'Erro ao consultar lista de funcionários: ' .
        htmlspecialchars($con->error)
    );
}

$stmtFuncs->bind_param(
    'i',
    $id_empresa
);

$stmtFuncs->execute();

$resultadoFuncionarios = $stmtFuncs->get_result();

$funcionarios = [];

while (
    $funcionario = $resultadoFuncionarios->fetch_assoc()
) {
    $funcionarios[] = $funcionario;
}

$stmtFuncs->close();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Holerite</title>

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
            --yellow: #d97706;
            --red: #dc2626;
            --shadow:
                0 12px 30px rgba(15, 23, 42, .07);
        }

        body {
            background: var(--bg) !important;
            color: var(--text);
        }

        .content {
            min-height: 100vh;
            padding: 32px;
        }

        .page-header,
        .kpi-card,
        .filter-card,
        .table-card {
            background: var(--card);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .page-header {
            border-radius: 24px;
            padding: 28px;
            margin-bottom: 24px;
        }

        .page-title {
            margin-bottom: 6px;
            color: var(--text);
            font-size: 32px;
            font-weight: 850;
        }

        .page-subtitle {
            margin: 0;
            color: var(--muted);
        }

        .date-pill {
            padding: 10px 16px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            color: var(--blue);
            font-size: 14px;
            font-weight: 800;
            white-space: nowrap;
        }

        .kpi-card {
            height: 100%;
            padding: 22px;
            border-radius: 22px;
        }

        .kpi-label {
            margin-bottom: 8px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
        }

        .kpi-value {
            margin-bottom: 2px;
            color: var(--text);
            font-size: 32px;
            font-weight: 850;
        }

        .kpi-small {
            color: var(--muted);
            font-size: 13px;
        }

        .kpi-icon {
            width: 46px;
            height: 46px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #eff6ff;
            border-radius: 16px;

            color: var(--blue);
            font-size: 23px;
        }

        .filter-card,
        .table-card {
            overflow: hidden;
            border-radius: 22px;
        }

        .card-section-header {
            padding: 20px 22px;
            background: var(--card-soft);
            border-bottom: 1px solid var(--border);
        }

        .card-section-header h5 {
            margin-bottom: 4px;
            color: var(--text);
            font-weight: 850;
        }

        .card-section-header p {
            margin-bottom: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .card-section-body {
            padding: 22px;
        }

        .form-control,
        .form-select {
            min-height: 44px;
            background: var(--card);
            border-color: var(--border);
            border-radius: 14px;
            color: var(--text);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #93c5fd;

            box-shadow:
                0 0 0 .22rem rgba(37, 99, 235, .12);
        }

        .btn {
            border-radius: 14px;
            font-weight: 700;
        }

        .table {
            margin-bottom: 0;
            color: var(--text);
        }

        .table thead th {
            padding: 15px 20px;

            background: var(--card-soft);
            border-bottom: 1px solid var(--border);

            color: var(--muted);
            font-size: 12px;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 17px 20px;

            background: var(--card);
            border-bottom: 1px solid var(--border);

            color: var(--text);
            vertical-align: middle;
        }

        .table tbody tr:hover td {
            background: var(--card-soft);
        }

        .employee-avatar {
            width: 40px;
            height: 40px;
            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #eff6ff;
            border-radius: 50%;

            color: var(--blue);
            font-weight: 850;
        }

        .employee-name {
            color: var(--text);
            font-weight: 800;
        }

        .badge-status {
            display: inline-block;

            padding: 7px 12px;

            border-radius: 999px;

            font-size: 12px;
            font-weight: 800;
        }

        .badge-enviado {
            background: #dcfce7;
            color: #166534;
        }

        .badge-pendente {
            background: #fef3c7;
            color: #92400e;
        }

        .empty-state {
            padding: 42px 20px;
            color: var(--muted);
            text-align: center;
        }

        .empty-state i {
            display: block;
            margin-bottom: 10px;
            color: var(--muted);
            font-size: 36px;
        }

        .progress-clean {
            height: 9px;
            overflow: hidden;

            margin-top: 12px;

            background: var(--border);
            border-radius: 999px;
        }

        .progress-clean span {
            display: block;

            width: <?= $percentualEnvio ?>%;
            height: 100%;

            background: var(--blue);
            border-radius: 999px;
        }

        .floating-action {
            position: fixed;
            right: 32px;
            bottom: 28px;
            z-index: 1000;

            box-shadow:
                0 16px 32px rgba(37, 99, 235, .28);
        }

        .modal {
            z-index: 99999 !important;
        }

        .modal-backdrop {
            z-index: 99998 !important;
        }

        .modal-content {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 22px;
            color: var(--text);
        }

        .modal-header,
        .modal-footer {
            border-color: var(--border);
        }

        body.dark,
        body.dark-mode {
            --bg: #0f172a;
            --card: #111c2f;
            --card-soft: #17233a;
            --text: #f8fafc;
            --muted: #cbd5e1;
            --border: #2b3a55;
            --shadow:
                0 12px 30px rgba(0, 0, 0, .25);
        }

        body.dark .date-pill,
        body.dark-mode .date-pill,
        body.dark .kpi-icon,
        body.dark-mode .kpi-icon,
        body.dark .employee-avatar,
        body.dark-mode .employee-avatar {
            background: rgba(37, 99, 235, .16);
            border-color: rgba(147, 197, 253, .35);
            color: #93c5fd;
        }

        body.dark .form-control,
        body.dark-mode .form-control,
        body.dark .form-select,
        body.dark-mode .form-select {
            background: #0b1220;
            border-color: #334155;
            color: #f8fafc;
        }

        body.dark .form-control::placeholder,
        body.dark-mode .form-control::placeholder {
            color: #94a3b8;
        }

        body.dark .form-select option,
        body.dark-mode .form-select option {
            background: #0b1220;
            color: #f8fafc;
        }

        body.dark .badge-enviado,
        body.dark-mode .badge-enviado {
            background: rgba(22, 163, 74, .20);
            color: #86efac;
        }

        body.dark .badge-pendente,
        body.dark-mode .badge-pendente {
            background: rgba(217, 119, 6, .22);
            color: #fcd34d;
        }

        body.dark .text-muted,
        body.dark-mode .text-muted {
            color: #cbd5e1 !important;
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }

            .page-header {
                padding: 22px;
            }

            .page-title {
                font-size: 27px;
            }

            .floating-action {
                position: static;

                width: 100%;

                margin-top: 20px;
            }
        }
    </style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid">

        <?php if (isset($_GET['sucesso'])): ?>

            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm">

                <i class="bi bi-check-circle-fill me-2"></i>

                Holerite enviado com sucesso.

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fechar"
                ></button>

            </div>

        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>

            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm">

                <i class="bi bi-exclamation-triangle-fill me-2"></i>

                <?= htmlspecialchars(
                    $_GET['erro'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Fechar"
                ></button>

            </div>

        <?php endif; ?>

        <div class="page-header">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                <div>

                    <h1 class="page-title">
                        Envio de Holerite
                    </h1>

                    <p class="page-subtitle">
                        Gerencie holerites enviados, pendências e arquivos dos funcionários.
                    </p>

                </div>

                <span class="date-pill">

                    <i class="bi bi-calendar3 me-1"></i>

                    <?= date('d/m/Y') ?>

                </span>

            </div>

        </div>

        <div class="row g-3 mb-4">

            <div class="col-12 col-md-4">

                <div class="kpi-card">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="kpi-label">
                                Envio pendente
                            </div>

                            <h2 class="kpi-value">
                                <?= $pendentes ?>
                            </h2>

                            <div class="kpi-small">
                                Funcionários sem holerite enviado
                            </div>

                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-hourglass-split"></i>
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-12 col-md-4">

                <div class="kpi-card">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="kpi-label">
                                Enviados
                            </div>

                            <h2 class="kpi-value">
                                <?= $enviados ?>
                            </h2>

                            <div class="kpi-small">
                                Funcionários com envio registrado
                            </div>

                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-send-check"></i>
                        </div>

                    </div>

                    <div class="progress-clean">
                        <span></span>
                    </div>

                </div>

            </div>

            <div class="col-12 col-md-4">

                <div class="kpi-card">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="kpi-label">
                                Funcionários ativos
                            </div>

                            <h2 class="kpi-value">
                                <?= $totalFuncionarios ?>
                            </h2>

                            <div class="kpi-small">

                                <?= $percentualEnvio ?>%
                                com envio concluído

                            </div>

                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-people"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="filter-card mb-4">

            <div class="card-section-header">

                <h5>Filtros</h5>

                <p>
                    Busque holerites pelo mês e ano da competência.
                </p>

            </div>

            <div class="card-section-body">

                <form
                    method="GET"
                    action="holerite.php"
                    class="row g-3"
                    id="formFiltro"
                >

                    <div class="col-md-3">

                        <select
                            name="mes"
                            class="form-select"
                            aria-label="Filtrar por mês"
                        >

                            <option value="0">
                                Todos os meses
                            </option>

                            <?php foreach (
                                $meses as $numeroMes => $nomeMes
                            ): ?>

                                <option
                                    value="<?= $numeroMes ?>"
                                    <?= $mes === $numeroMes
                                        ? 'selected'
                                        : ''
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $nomeMes,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-3">

                        <input
                            type="number"
                            name="ano"
                            class="form-control"
                            placeholder="Ano"
                            min="2000"
                            max="2100"
                            value="<?= $ano > 0 ? $ano : '' ?>"
                        >

                    </div>

                    <div class="col-md-3">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i class="bi bi-search me-2"></i>

                            Filtrar

                        </button>

                    </div>

                    <div class="col-md-3">

                        <a
                            href="holerite.php"
                            class="btn btn-outline-secondary w-100"
                        >
                            Limpar
                        </a>

                    </div>

                </form>

            </div>

        </div>

        <div class="table-card">

            <div class="card-section-header">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div>

                        <h5>Holerites enviados</h5>

                        <p>
                            Arquivos disponíveis para download e conferência.
                        </p>

                    </div>

                    <span class="badge bg-primary rounded-pill px-3 py-2">

                        <?= $totalRegistros ?> registros

                    </span>

                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>
                            <th>Funcionário</th>
                            <th>Período</th>
                            <th>Data de envio</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($totalRegistros > 0): ?>

                        <?php while (
                            $row = $resultado->fetch_assoc()
                        ): ?>

                            <?php

                            $inicial = mb_strtoupper(
                                mb_substr(
                                    $row['nome'],
                                    0,
                                    1,
                                    'UTF-8'
                                ),
                                'UTF-8'
                            );

                            $dataEnvio = '-';

                            if (!empty($row['data_envio'])) {
                                $timestampEnvio = strtotime(
                                    $row['data_envio']
                                );

                                if ($timestampEnvio !== false) {
                                    $dataEnvio = date(
                                        'd/m/Y',
                                        $timestampEnvio
                                    );
                                }
                            }

                            ?>

                            <tr>

                                <td>

                                    <div class="d-flex align-items-center gap-2">

                                        <div class="employee-avatar">

                                            <?= htmlspecialchars(
                                                $inicial,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </div>

                                        <span class="employee-name">

                                            <?= htmlspecialchars(
                                                $row['nome'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>

                                        </span>

                                    </div>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $row['periodo'] ?? '-',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $dataEnvio,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>

                                <td>

                                    <?php if (
                                        ($row['status'] ?? '') === 'pendente'
                                    ): ?>

                                        <span class="badge-status badge-pendente">
                                            Pendente
                                        </span>

                                    <?php else: ?>

                                        <span class="badge-status badge-enviado">
                                            Enviado
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php if (
                                        !empty($row['arquivo'])
                                    ): ?>

                                        <a
                                            href="<?= htmlspecialchars(
                                                $row['arquivo'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="btn btn-outline-primary btn-sm"
                                        >

                                            <i class="bi bi-download me-1"></i>

                                            Download

                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Sem arquivo
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="5">

                                <div class="empty-state">

                                    <i class="bi bi-inbox"></i>

                                    Nenhum holerite encontrado para essa competência.

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <button
            type="button"
            class="btn btn-primary btn-lg floating-action px-4"
            data-bs-toggle="modal"
            data-bs-target="#modalHolerite"
        >

            <i class="bi bi-send-fill me-2"></i>

            Enviar Holerite

        </button>

    </div>

</div>

<div
    class="modal fade"
    id="modalHolerite"
    tabindex="-1"
    aria-labelledby="tituloModalHolerite"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content shadow">

            <form
                action="enviar_holerite.php"
                method="POST"
                enctype="multipart/form-data"
                id="formEnviarHolerite"
            >

                <div class="modal-header">

                    <h5
                        class="modal-title fw-bold"
                        id="tituloModalHolerite"
                    >

                        <i class="bi bi-send-fill me-2 text-primary"></i>

                        Enviar Holerite

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"
                    ></button>

                </div>

                <div class="modal-body">

                    <label class="form-label fw-semibold">
                        Funcionário
                    </label>

                    <select
                        name="funcionario_id"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Selecione
                        </option>

                        <?php foreach (
                            $funcionarios as $func
                        ): ?>

                            <option
                                value="<?= (int) $func['id_funcionario'] ?>"
                            >

                                <?= htmlspecialchars(
                                    $func['nome'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                    <label class="form-label fw-semibold mt-3">
                        Competência
                    </label>

                    <div class="row g-2">

                        <div class="col-md-6">

                            <select
                                name="mes"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Mês
                                </option>

                                <?php foreach (
                                    $meses as $nomeMes
                                ): ?>

                                    <option
                                        value="<?= htmlspecialchars(
                                            $nomeMes,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $nomeMes,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <select
                                name="ano"
                                class="form-select"
                                required
                            >

                                <?php

                                $anoAtual = (int) date('Y');

                                for (
                                    $i = $anoAtual + 1;
                                    $i >= $anoAtual - 5;
                                    $i--
                                ):

                                ?>

                                    <option
                                        value="<?= $i ?>"
                                        <?= $i === $anoAtual
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        <?= $i ?>
                                    </option>

                                <?php endfor; ?>

                            </select>

                        </div>

                    </div>

                    <label class="form-label fw-semibold mt-3">
                        PDF do Holerite
                    </label>

                    <input
                        type="file"
                        name="arquivo"
                        class="form-control"
                        accept="application/pdf,.pdf"
                        required
                    >

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnEnviarHolerite"
                    >

                        <i class="bi bi-send-fill me-2"></i>

                        Enviar

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const formularioEnvio = document.getElementById(
        'formEnviarHolerite'
    );

    const botaoEnvio = document.getElementById(
        'btnEnviarHolerite'
    );

    if (formularioEnvio && botaoEnvio) {

        formularioEnvio.addEventListener(
            'submit',
            function () {

                botaoEnvio.disabled = true;

                botaoEnvio.innerHTML = `
                    <span
                        class="spinner-border spinner-border-sm me-2"
                        aria-hidden="true"
                    ></span>
                    Enviando...
                `;

            }
        );

    }

});
</script>

<script src="js/theme.js"></script>
<script src="js/translate.js"></script>

</body>
</html>
```
