
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

$idEmpresa = isset($_SESSION['id_empresa'])
    ? (int) $_SESSION['id_empresa']
    : 0;

if (!$idEmpresa) {
    die(
        'Empresa não identificada. ' .
        'Faça login novamente.'
    );
}

$mensagem = '';
$erro = '';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['atualizar_status'])
) {

    $idOcorrencia = isset($_POST['id_ocorrencia'])
        ? (int) $_POST['id_ocorrencia']
        : 0;

    $status = isset($_POST['status'])
        ? $_POST['status']
        : '';

    if (!$idOcorrencia) {
        die('ID da ocorrência inválido.');
    }

    if (
        !in_array(
            $status,
            array(
                'aberta',
                'em_analise',
                'resolvida'
            )
        )
    ) {
        die(
            'Status inválido: ' .
            htmlspecialchars($status)
        );
    }

    $stmtBuscaOcorrencia = $con->prepare("
        SELECT
            id_usuario,
            categoria
        FROM ocorrencias
        WHERE id_ocorrencia = ?
          AND id_empresa = ?
        LIMIT 1
    ");

    if (!$stmtBuscaOcorrencia) {
        die(
            'Erro ao buscar ocorrência: ' .
            $con->error
        );
    }

    $stmtBuscaOcorrencia->bind_param(
        'ii',
        $idOcorrencia,
        $idEmpresa
    );

    $stmtBuscaOcorrencia->execute();

    $dadosOcorrencia =
        $stmtBuscaOcorrencia
            ->get_result()
            ->fetch_assoc();

    if (!$dadosOcorrencia) {
        die('Ocorrência não encontrada.');
    }

    $idUsuarioReporter = isset(
        $dadosOcorrencia['id_usuario']
    )
        ? (int) $dadosOcorrencia['id_usuario']
        : 0;

    $categoriaOcorrencia = isset(
        $dadosOcorrencia['categoria']
    )
        ? $dadosOcorrencia['categoria']
        : 'Ocorrência';

    $stmt = $con->prepare("
        UPDATE ocorrencias
        SET status = ?
        WHERE id_ocorrencia = ?
          AND id_empresa = ?
    ");

    if (!$stmt) {
        die(
            'Erro ao atualizar ocorrência: ' .
            $con->error
        );
    }

    $stmt->bind_param(
        'sii',
        $status,
        $idOcorrencia,
        $idEmpresa
    );

    $stmt->execute();

    if ($status === 'aberta') {
        $statusTexto = 'Aberta';
    } elseif ($status === 'em_analise') {
        $statusTexto = 'Em análise';
    } elseif ($status === 'resolvida') {
        $statusTexto = 'Resolvida';
    } else {
        $statusTexto = 'Atualizada';
    }

    if ($idUsuarioReporter > 0) {

        criarNotificacao(
            $con,
            $idEmpresa,
            $idUsuarioReporter,
            'emergencia',
            'Status da sua ocorrência atualizado',
            'Sua ocorrência de categoria "' .
            $categoriaOcorrencia .
            '" foi atualizada para: ' .
            $statusTexto .
            '.',
            'solic.php'
        );
    }

    header(
        'Location: emergencias.php?status_ok=1'
    );

    exit;
}

/* =========================================================
   EXCLUIR OCORRÊNCIA
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['excluir_ocorrencia'])
) {

    $idOcorrencia = isset($_POST['id_ocorrencia'])
        ? (int) $_POST['id_ocorrencia']
        : 0;

    if (!$idOcorrencia) {
        die('ID da ocorrência inválido.');
    }

    $stmt = $con->prepare("
        DELETE FROM ocorrencias
        WHERE id_ocorrencia = ?
          AND id_empresa = ?
    ");

    if (!$stmt) {
        die(
            'Erro ao excluir ocorrência: ' .
            $con->error
        );
    }

    $stmt->bind_param(
        'ii',
        $idOcorrencia,
        $idEmpresa
    );

    $stmt->execute();

    header(
        'Location: emergencias.php?excluido=1'
    );

    exit;
}

/* =========================================================
   MENSAGENS
========================================================= */

if (isset($_GET['status_ok'])) {
    $mensagem =
        'Status atualizado com sucesso. ' .
        'O colaborador foi notificado.';
}

if (isset($_GET['excluido'])) {
    $mensagem =
        'Ocorrência excluída com sucesso.';
}

/* =========================================================
   BUSCAR OCORRÊNCIAS
========================================================= */

$stmt = $con->prepare("
    SELECT *
    FROM ocorrencias
    WHERE id_empresa = ?
    ORDER BY data_ocorrencia DESC
");

if (!$stmt) {
    die(
        'Erro ao buscar ocorrências: ' .
        $con->error
    );
}

$stmt->bind_param(
    'i',
    $idEmpresa
);

$stmt->execute();

$result = $stmt->get_result();

$ocorrencias = array();

$total = 0;
$abertas = 0;
$analise = 0;
$resolvidas = 0;

while ($row = $result->fetch_assoc()) {

    $ocorrencias[] = $row;

    $total++;

    if ($row['status'] === 'aberta') {
        $abertas++;
    }

    if ($row['status'] === 'em_analise') {
        $analise++;
    }

    if ($row['status'] === 'resolvida') {
        $resolvidas++;
    }
}

/* =========================================================
   FUNÇÕES VISUAIS
========================================================= */

function textoStatus($status)
{
    if ($status === 'aberta') {
        return 'Aberta';
    }

    if ($status === 'em_analise') {
        return 'Em análise';
    }

    if ($status === 'resolvida') {
        return 'Resolvida';
    }

    return 'Indefinido';
}

function classeStatus($status)
{
    if ($status === 'resolvida') {
        return 'status-resolvida';
    }

    if ($status === 'em_analise') {
        return 'status-analise';
    }

    return 'status-aberta';
}

function iconeStatus($status)
{
    if ($status === 'resolvida') {
        return 'bi-check-circle-fill';
    }

    if ($status === 'em_analise') {
        return 'bi-hourglass-split';
    }

    return 'bi-exclamation-circle-fill';
}

function linkEvidenciaOcorrencia($evidencia)
{
    if (empty($evidencia)) {
        return '';
    }

    $evidencia = str_replace(
        '\\',
        '/',
        $evidencia
    );

    $evidencia = ltrim(
        $evidencia,
        '/'
    );

    if (strpos($evidencia, '../') === 0) {
        $evidencia = substr(
            $evidencia,
            3
        );
    }

    if (
        file_exists(
            __DIR__ .
            '/' .
            $evidencia
        )
    ) {
        return $evidencia;
    }

    if (
        file_exists(
            __DIR__ .
            '/../' .
            $evidencia
        )
    ) {
        return '../' . $evidencia;
    }

    return $evidencia;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Central de Emergências</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

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
            --page-bg: #f3f6f9;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --primary: #1769ff;
            --primary-soft: #eef5ff;
            --shadow:
                0 10px 30px
                rgba(15, 23, 42, .07);
        }

        body {
            background: var(--page-bg);
            color: var(--text-main);
        }

        body.dark,
        body.dark-mode {
            --page-bg: #0f172a;
            --surface: #111827;
            --surface-soft: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #cbd5e1;
            --border: #334155;
            --primary-soft: #172554;
        }

        .content {
            min-height: 100vh;
            color: var(--text-main);
        }

        .page-wrap {
            padding: 28px;
        }

        /* CABEÇALHO */

        .page-header {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow);

            padding: 28px 30px;
            margin-bottom: 22px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .page-header h1 {
            color: var(--text-main);
            font-size: 30px;
            font-weight: 800;
            margin: 0 0 5px;
        }

        .page-header p {
            color: var(--text-muted);
            margin: 0;
        }

        .total-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            padding: 10px 15px;
            border-radius: 999px;

            background: var(--primary-soft);
            color: var(--primary);

            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* CARDS */

        .summary-card {
            height: 100%;
            min-height: 112px;

            padding: 20px;

            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow);

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .summary-label {
            display: block;

            color: var(--text-muted);

            font-size: 13px;
            font-weight: 600;

            margin-bottom: 5px;
        }

        .summary-value {
            display: block;

            color: var(--text-main);

            font-size: 29px;
            font-weight: 800;
            line-height: 1;
        }

        .summary-description {
            color: var(--text-muted);
            font-size: 12px;
            margin: 7px 0 0;
        }

        .summary-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;

            border-radius: 14px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 21px;
        }

        .icon-open {
            color: #2563eb;
            background: #eff6ff;
        }

        .icon-analysis {
            color: #d97706;
            background: #fffbeb;
        }

        .icon-resolved {
            color: #15803d;
            background: #f0fdf4;
        }

        .icon-total {
            color: #7c3aed;
            background: #f5f3ff;
        }

        /* PAINEL */

        .main-panel {
            overflow: hidden;

            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .panel-header {
            padding: 21px 25px;
            border-bottom: 1px solid var(--border);

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .panel-header h2 {
            color: var(--text-main);
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 4px;
        }

        .panel-header p {
            color: var(--text-muted);
            font-size: 13px;
            margin: 0;
        }

        .search-wrapper {
            width: min(430px, 100%);
        }

        .search-wrapper .input-group-text,
        .search-wrapper .form-control {
            background: var(--surface);
            color: var(--text-main);
            border-color: var(--border);
        }

        /* OCORRÊNCIAS */

        .occurrences-list {
            padding: 0 24px;
        }

        .occurrence {
            padding: 22px 0;
            border-bottom: 1px solid var(--border);
        }

        .occurrence:last-child {
            border-bottom: 0;
        }

        .occurrence-heading {
            display: flex;
            align-items: flex-start;
            gap: 12px;

            margin-bottom: 16px;
        }

        .occurrence-icon {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;

            border-radius: 14px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: var(--primary);
            background: var(--primary-soft);

            font-size: 20px;
        }

        .occurrence-category {
            color: var(--text-main);
            font-size: 17px;
            font-weight: 800;
            margin: 0;
        }

        .occurrence-date {
            color: var(--text-muted);
            font-size: 12px;
            margin-top: 4px;
        }

        .badges-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 7px;

            margin-top: 7px;
        }

        .status-chip,
        .type-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;

            padding: 5px 9px;
            border-radius: 999px;

            font-size: 11px;
            font-weight: 700;
        }

        .status-aberta {
            color: #1d4ed8;
            background: #dbeafe;
        }

        .status-analise {
            color: #92400e;
            background: #fef3c7;
        }

        .status-resolvida {
            color: #166534;
            background: #dcfce7;
        }

        .type-chip {
            color: var(--text-main);
            background: var(--surface-soft);
            border: 1px solid var(--border);
        }

        .description-box {
            padding: 15px 16px;
            margin-bottom: 16px;

            background: var(--surface-soft);
            border: 1px solid var(--border);
            border-radius: 14px;

            color: var(--text-main);
            line-height: 1.6;
        }

        .details-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 10px;
            margin-bottom: 18px;
        }

        .detail-box {
            min-width: 0;

            padding: 13px;

            background: var(--surface-soft);
            border: 1px solid var(--border);
            border-radius: 13px;
        }

        .detail-label {
            display: block;

            color: var(--text-muted);

            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;

            margin-bottom: 4px;
        }

        .detail-value {
            display: block;

            color: var(--text-main);

            font-size: 13px;
            font-weight: 700;

            overflow-wrap: anywhere;
        }

        .occurrence-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .status-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-form .form-select {
            min-width: 160px;
        }

        .form-select,
        .form-control {
            background-color: var(--surface);
            color: var(--text-main);
            border-color: var(--border);
        }

        .form-select:focus,
        .form-control:focus {
            background-color: var(--surface);
            color: var(--text-main);
        }

        /* VAZIO */

        .empty-state {
            min-height: 280px;

            padding: 50px 25px;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;

            text-align: center;
        }

        .empty-icon {
            width: 68px;
            height: 68px;

            border-radius: 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            color: var(--primary);
            background: var(--primary-soft);

            font-size: 30px;

            margin-bottom: 16px;
        }

        .empty-state h3 {
            color: var(--text-main);
            font-size: 21px;
            font-weight: 800;
            margin: 0 0 6px;
        }

        .empty-state p {
            color: var(--text-muted);
            margin: 0;
        }

        @media (max-width: 1100px) {

            .details-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {

            .page-wrap {
                padding: 18px;
            }

            .page-header,
            .panel-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-header {
                padding: 22px;
            }

            .search-wrapper {
                width: 100%;
            }

            .occurrence-actions,
            .status-form {
                align-items: stretch;
                flex-direction: column;
            }

            .status-form .form-select {
                width: 100%;
            }
        }

        @media (max-width: 520px) {

            .details-grid {
                grid-template-columns: 1fr;
            }

            .occurrences-list {
                padding: 0 16px;
            }

            .page-header h1 {
                font-size: 25px;
            }
        }

    </style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid page-wrap">

        <?php if ($mensagem !== ''): ?>

            <div
                class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4"
            >

                <i class="bi bi-check-circle-fill me-2"></i>

                <?= htmlspecialchars($mensagem) ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>

        <?php if ($erro !== ''): ?>

            <div
                class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm mb-4"
            >

                <i class="bi bi-exclamation-triangle-fill me-2"></i>

                <?= htmlspecialchars($erro) ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>

        <!-- CABEÇALHO -->

        <section class="page-header">

            <div>

                <h1>
                    Central de Emergências
                </h1>

                <p>
                    Gerencie ocorrências reportadas pelos colaboradores.
                </p>

            </div>

            <div class="total-badge">

                <i class="bi bi-shield-check"></i>

                <?= $total ?>

                <?= $total === 1
                    ? 'ocorrência'
                    : 'ocorrências' ?>

            </div>

        </section>

        <!-- CARDS -->

        <div class="row g-3 mb-4">

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="summary-card">

                    <div>

                        <span class="summary-label">
                            Abertas
                        </span>

                        <strong class="summary-value">
                            <?= $abertas ?>
                        </strong>

                        <p class="summary-description">
                            Aguardando atendimento
                        </p>

                    </div>

                    <div class="summary-icon icon-open">

                        <i class="bi bi-exclamation-circle"></i>

                    </div>

                </div>

            </div>

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="summary-card">

                    <div>

                        <span class="summary-label">
                            Em análise
                        </span>

                        <strong class="summary-value">
                            <?= $analise ?>
                        </strong>

                        <p class="summary-description">
                            Ocorrências em avaliação
                        </p>

                    </div>

                    <div class="summary-icon icon-analysis">

                        <i class="bi bi-hourglass-split"></i>

                    </div>

                </div>

            </div>

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="summary-card">

                    <div>

                        <span class="summary-label">
                            Resolvidas
                        </span>

                        <strong class="summary-value">
                            <?= $resolvidas ?>
                        </strong>

                        <p class="summary-description">
                            Atendimentos concluídos
                        </p>

                    </div>

                    <div class="summary-icon icon-resolved">

                        <i class="bi bi-check-circle"></i>

                    </div>

                </div>

            </div>

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="summary-card">

                    <div>

                        <span class="summary-label">
                            Total
                        </span>

                        <strong class="summary-value">
                            <?= $total ?>
                        </strong>

                        <p class="summary-description">
                            Ocorrências registradas
                        </p>

                    </div>

                    <div class="summary-icon icon-total">

                        <i class="bi bi-clipboard2-data"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- LISTA -->

        <section class="main-panel">

            <header class="panel-header">

                <div>

                    <h2>
                        Ocorrências registradas
                    </h2>

                    <p>
                        Acompanhe os reportes e atualize cada atendimento.
                    </p>

                </div>

                <?php if (!empty($ocorrencias)): ?>

                    <div class="search-wrapper">

                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-search"></i>

                            </span>

                            <input
                                type="text"
                                class="form-control"
                                id="pesquisarOcorrencia"
                                placeholder="Pesquisar categoria, local ou descrição"
                            >

                        </div>

                    </div>

                <?php endif; ?>

            </header>

            <?php if (!empty($ocorrencias)): ?>

                <div class="occurrences-list">

                    <?php foreach ($ocorrencias as $e): ?>

                        <?php

                        $linkEvidencia =
                            linkEvidenciaOcorrencia(
                                isset($e['evidencia'])
                                    ? $e['evidencia']
                                    : ''
                            );

                        $textoPesquisa =
                            strtolower(
                                (isset($e['categoria'])
                                    ? $e['categoria']
                                    : '') .
                                ' ' .
                                (isset($e['descricao'])
                                    ? $e['descricao']
                                    : '') .
                                ' ' .
                                (isset($e['nome'])
                                    ? $e['nome']
                                    : '') .
                                ' ' .
                                (isset($e['andar'])
                                    ? $e['andar']
                                    : '') .
                                ' ' .
                                (isset($e['sala'])
                                    ? $e['sala']
                                    : '') .
                                ' ' .
                                (isset($e['local_especifico'])
                                    ? $e['local_especifico']
                                    : '')
                            );

                        ?>

                        <article
                            class="occurrence"
                            data-search="<?= htmlspecialchars(
                                $textoPesquisa
                            ) ?>"
                        >

                            <div class="occurrence-heading">

                                <div class="occurrence-icon">

                                    <i class="bi bi-shield-exclamation"></i>

                                </div>

                                <div>

                                    <h3 class="occurrence-category">

                                        <?= htmlspecialchars(
                                            isset($e['categoria'])
                                                ? $e['categoria']
                                                : 'Ocorrência'
                                        ) ?>

                                    </h3>

                                    <div class="badges-row">

                                        <span
                                            class="status-chip <?= classeStatus(
                                                isset($e['status'])
                                                    ? $e['status']
                                                    : ''
                                            ) ?>"
                                        >

                                            <i
                                                class="bi <?= iconeStatus(
                                                    isset($e['status'])
                                                        ? $e['status']
                                                        : ''
                                                ) ?>"
                                            ></i>

                                            <?= textoStatus(
                                                isset($e['status'])
                                                    ? $e['status']
                                                    : ''
                                            ) ?>

                                        </span>

                                        <span class="type-chip">

                                            <?= htmlspecialchars(
                                                isset($e['tipo_reporte'])
                                                    ? $e['tipo_reporte']
                                                    : 'Reporte'
                                            ) ?>

                                        </span>

                                    </div>

                                    <div class="occurrence-date">

                                        <i class="bi bi-calendar3 me-1"></i>

                                        <?php if (
                                            !empty(
                                                $e['data_ocorrencia']
                                            )
                                        ): ?>

                                            <?= date(
                                                'd/m/Y H:i',
                                                strtotime(
                                                    $e['data_ocorrencia']
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            Data não informada

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                            <div class="description-box">

                                <?= nl2br(
                                    htmlspecialchars(
                                        isset($e['descricao'])
                                            ? $e['descricao']
                                            : ''
                                    )
                                ) ?>

                            </div>

                            <div class="details-grid">

                                <div class="detail-box">

                                    <span class="detail-label">
                                        Reportado por
                                    </span>

                                    <strong class="detail-value">

                                        <?= htmlspecialchars(
                                            !empty($e['nome'])
                                                ? $e['nome']
                                                : 'Anônimo'
                                        ) ?>

                                    </strong>

                                </div>

                                <div class="detail-box">

                                    <span class="detail-label">
                                        Andar e sala
                                    </span>

                                    <strong class="detail-value">

                                        <?= htmlspecialchars(
                                            !empty($e['andar'])
                                                ? $e['andar']
                                                : '-'
                                        ) ?>

                                        <?php if (
                                            !empty($e['sala'])
                                        ): ?>

                                            ·

                                            <?= htmlspecialchars(
                                                $e['sala']
                                            ) ?>

                                        <?php endif; ?>

                                    </strong>

                                </div>

                                <div class="detail-box">

                                    <span class="detail-label">
                                        Local específico
                                    </span>

                                    <strong class="detail-value">

                                        <?= htmlspecialchars(
                                            !empty(
                                                $e['local_especifico']
                                            )
                                                ? $e['local_especifico']
                                                : '-'
                                        ) ?>

                                    </strong>

                                </div>

                                <div class="detail-box">

                                    <span class="detail-label">
                                        Evidência
                                    </span>

                                    <?php if (
                                        $linkEvidencia !== ''
                                    ): ?>

                                        <a
                                            href="<?= htmlspecialchars(
                                                $linkEvidencia
                                            ) ?>"
                                            target="_blank"
                                            class="btn btn-outline-primary btn-sm"
                                        >

                                            <i class="bi bi-eye me-1"></i>

                                            Visualizar

                                        </a>

                                    <?php else: ?>

                                        <strong class="detail-value">
                                            Não enviada
                                        </strong>

                                    <?php endif; ?>

                                </div>

                            </div>

                            <div class="occurrence-actions">

                                <form
                                    method="POST"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="id_ocorrencia"
                                        value="<?= (int) $e[
                                            'id_ocorrencia'
                                        ] ?>"
                                    >

                                    <select
                                        name="status"
                                        class="form-select form-select-sm"
                                    >

                                        <option
                                            value="aberta"
                                            <?= (
                                                $e['status'] ===
                                                'aberta'
                                            )
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            Aberta
                                        </option>

                                        <option
                                            value="em_analise"
                                            <?= (
                                                $e['status'] ===
                                                'em_analise'
                                            )
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            Em análise
                                        </option>

                                        <option
                                            value="resolvida"
                                            <?= (
                                                $e['status'] ===
                                                'resolvida'
                                            )
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            Resolvida
                                        </option>

                                    </select>

                                    <button
                                        type="submit"
                                        name="atualizar_status"
                                        class="btn btn-primary btn-sm px-3"
                                    >

                                        <i class="bi bi-check-lg me-1"></i>

                                        Atualizar

                                    </button>

                                </form>

                                <form
                                    method="POST"
                                    onsubmit="
                                        return confirm(
                                            'Deseja excluir esta ocorrência?'
                                        );
                                    "
                                >

                                    <input
                                        type="hidden"
                                        name="id_ocorrencia"
                                        value="<?= (int) $e[
                                            'id_ocorrencia'
                                        ] ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="excluir_ocorrencia"
                                        class="btn btn-outline-danger btn-sm px-3"
                                    >

                                        <i class="bi bi-trash me-1"></i>

                                        Excluir

                                    </button>

                                </form>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <div class="empty-icon">

                        <i class="bi bi-shield-check"></i>

                    </div>

                    <h3>
                        Nenhuma ocorrência encontrada
                    </h3>

                    <p>
                        As ocorrências reportadas pelos colaboradores aparecerão aqui.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<script>

var pesquisarOcorrencia =
    document.getElementById(
        'pesquisarOcorrencia'
    );

if (pesquisarOcorrencia) {

    pesquisarOcorrencia.addEventListener(
        'input',
        function () {

            var termo =
                this.value
                    .toLowerCase()
                    .trim();

            var itens =
                document.querySelectorAll(
                    '.occurrence'
                );

            for (
                var indice = 0;
                indice < itens.length;
                indice++
            ) {

                var conteudo =
                    itens[indice]
                        .getAttribute(
                            'data-search'
                        ) || '';

                if (
                    conteudo.indexOf(termo) !== -1
                ) {
                    itens[indice].style.display = '';
                } else {
                    itens[indice].style.display = 'none';
                }
            }
        }
    );
}

</script>
<script src="js/theme.js"></script>
</body>
</html>