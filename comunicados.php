```php
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

$idEmpresa = $_SESSION['id_empresa'] ?? null;
$nomeUsuario = $_SESSION['nome'] ?? 'Administrador';

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

$mensagem = '';
$erro = '';

/* =========================================================
   AÇÕES
========================================================= */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    /* SALVAR COMUNICADO */

    if (isset($_POST['salvar_comunicado'])) {

        $titulo = trim($_POST['titulo']);
        $conteudo = trim($_POST['conteudo']);
        $categoria = trim($_POST['categoria']);
        $publico = trim($_POST['publico']);

        $fixado = isset($_POST['fixado'])
            ? 1
            : 0;

        if ($titulo == '' || $conteudo == '') {

            $erro = "Preencha título e conteúdo.";

        } else {

            $stmt = $con->prepare("
                INSERT INTO comunicados
                (
                    titulo,
                    conteudo,
                    categoria,
                    fixado,
                    autor,
                    publico,
                    id_empresa
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sssissi",
                $titulo,
                $conteudo,
                $categoria,
                $fixado,
                $nomeUsuario,
                $publico,
                $idEmpresa
            );

            if ($stmt->execute()) {

                $mensagem =
                    "Comunicado publicado com sucesso.";

            } else {

                $erro =
                    "Erro ao publicar comunicado: " .
                    $stmt->error;
            }
        }
    }

    /* EXCLUIR COMUNICADO */

    if (isset($_POST['excluir_comunicado'])) {

        $id = intval($_POST['id']);

        $stmt = $con->prepare("
            DELETE FROM comunicados
            WHERE id = ?
            AND id_empresa = ?
        ");

        $stmt->bind_param(
            "ii",
            $id,
            $idEmpresa
        );

        if ($stmt->execute()) {

            $mensagem =
                "Comunicado excluído com sucesso.";

        } else {

            $erro =
                "Erro ao excluir comunicado.";
        }
    }

    /* FIXAR OU DESFIXAR */

    if (isset($_POST['alternar_fixado'])) {

        $id = intval($_POST['id']);
        $fixadoAtual = intval($_POST['fixado_atual']);
        $novoFixado = $fixadoAtual ? 0 : 1;

        $stmt = $con->prepare("
            UPDATE comunicados
            SET fixado = ?
            WHERE id = ?
            AND id_empresa = ?
        ");

        $stmt->bind_param(
            "iii",
            $novoFixado,
            $id,
            $idEmpresa
        );

        if ($stmt->execute()) {

            $mensagem = $novoFixado
                ? "Comunicado fixado."
                : "Comunicado desfixado.";

        } else {

            $erro =
                "Erro ao alterar fixação.";
        }
    }
}

/* =========================================================
   CONTADORES
========================================================= */

$totalComunicados = 0;
$totalFixados = 0;
$mesComunicados = 0;

$stmtTotal = $con->prepare("
    SELECT COUNT(*) AS total
    FROM comunicados
    WHERE id_empresa = ?
");

$stmtTotal->bind_param(
    "i",
    $idEmpresa
);

$stmtTotal->execute();

$totalComunicados = $stmtTotal
    ->get_result()
    ->fetch_assoc()['total'];

$stmtFixados = $con->prepare("
    SELECT COUNT(*) AS total
    FROM comunicados
    WHERE id_empresa = ?
    AND fixado = 1
");

$stmtFixados->bind_param(
    "i",
    $idEmpresa
);

$stmtFixados->execute();

$totalFixados = $stmtFixados
    ->get_result()
    ->fetch_assoc()['total'];

$stmtMes = $con->prepare("
    SELECT COUNT(*) AS total
    FROM comunicados
    WHERE id_empresa = ?
    AND MONTH(data_publicacao) = MONTH(CURRENT_DATE())
    AND YEAR(data_publicacao) = YEAR(CURRENT_DATE())
");

$stmtMes->bind_param(
    "i",
    $idEmpresa
);

$stmtMes->execute();

$mesComunicados = $stmtMes
    ->get_result()
    ->fetch_assoc()['total'];

/* =========================================================
   LISTAGEM
========================================================= */

$stmtComunicados = $con->prepare("
    SELECT *
    FROM comunicados
    WHERE id_empresa = ?
    ORDER BY fixado DESC, data_publicacao DESC
");

$stmtComunicados->bind_param(
    "i",
    $idEmpresa
);

$stmtComunicados->execute();

$resultado = $stmtComunicados->get_result();

/* =========================================================
   CATEGORIA
========================================================= */

function badgeCategoria($categoria)
{
    if ($categoria == 'Urgente') {
        return 'bg-danger';
    }

    if ($categoria == 'Evento') {
        return 'bg-success';
    }

    if ($categoria == 'Comemoração') {
        return 'bg-warning text-dark';
    }

    if ($categoria == 'Aviso') {
        return 'bg-info text-dark';
    }

    return 'bg-primary';
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

    <title>Comunicados</title>

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

        body.dark,
        body.dark-mode {
            --page-bg: #0f172a;
            --surface: #111827;
            --surface-soft: #1e293b;

            --text-main: #f8fafc;
            --text-muted: #cbd5e1;

            --border: #334155;

            --primary-soft: #172554;

            --shadow:
                0 10px 30px
                rgba(0, 0, 0, .28);
        }

        body {
            background: var(--page-bg);
            color: var(--text-main);
        }

        .content {
            min-height: 100vh;
            color: var(--text-main);
        }

        .page-container {
            padding: 28px;
        }

        /* =================================================
           CABEÇALHO
        ================================================= */

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

            font-size: 15px;
            line-height: 1.5;

            margin: 0;

            max-width: 720px;
        }

        .btn-new {
            border-radius: 10px;

            font-weight: 600;

            padding: 10px 18px;

            white-space: nowrap;
        }

        /* =================================================
           ALERTAS
        ================================================= */

        .custom-alert {
            border: 0;
            border-radius: 16px;
            box-shadow: var(--shadow);
        }

        /* =================================================
           RESUMO
        ================================================= */

        .summary-card {
            height: 100%;
            min-height: 112px;

            background: var(--surface);

            border: 1px solid var(--border);
            border-radius: 18px;

            box-shadow: var(--shadow);

            padding: 20px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .summary-card small {
            display: block;

            color: var(--text-muted);

            font-size: 13px;
            font-weight: 600;

            margin-bottom: 5px;
        }

        .summary-card h2 {
            color: var(--text-main);

            font-size: 29px;
            font-weight: 800;
            line-height: 1;

            margin: 0;
        }

        .summary-card p {
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

        .icon-total {
            color: #2563eb;
            background: #eff6ff;
        }

        .icon-fixed {
            color: #d97706;
            background: #fffbeb;
        }

        .icon-month {
            color: #15803d;
            background: #f0fdf4;
        }

        .icon-public {
            color: #7c3aed;
            background: #f5f3ff;
        }

        /* =================================================
           PAINEL
        ================================================= */

        .main-panel {
            background: var(--surface);

            border: 1px solid var(--border);
            border-radius: 20px;

            box-shadow: var(--shadow);

            overflow: hidden;
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

        .search-box {
            width: min(430px, 100%);
        }

        .search-box .input-group-text,
        .search-box .form-control {
            background: var(--surface);
            color: var(--text-main);
            border-color: var(--border);
        }

        .search-box .form-control::placeholder {
            color: var(--text-muted);
        }

        /* =================================================
           CARDS DOS COMUNICADOS
        ================================================= */

        .communications-list {
            display: flex;
            flex-direction: column;

            gap: 22px;

            padding: 24px;

            background: var(--surface-soft);
        }

        .communication-item {
            position: relative;

            margin: 0;
            padding: 24px;

            background: var(--surface);

            border: 1px solid var(--border);
            border-radius: 18px;

            box-shadow:
                0 5px 18px
                rgba(15, 23, 42, .06);
        }

        .communication-item.fixed-item {
            border-left:
                5px solid #f59e0b;
        }

        .communication-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            gap: 18px;

            margin-bottom: 20px;
        }

        .communication-title-area {
            display: flex;
            align-items: flex-start;

            gap: 14px;

            min-width: 0;
        }

        .communication-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;

            border-radius: 14px;

            background: var(--primary-soft);
            color: var(--primary);

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 20px;
        }

        .communication-title {
            color: var(--text-main);

            font-size: 18px;
            font-weight: 800;

            margin: 0;
        }

        .communication-date {
            color: var(--text-muted);

            font-size: 12px;

            white-space: nowrap;
        }

        .badges-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;

            gap: 7px;
        }

        .badges-row .badge {
            border-radius: 999px;

            font-size: 11px;
            font-weight: 700;

            padding: 6px 10px;
        }

        .public-badge {
            background: var(--surface-soft) !important;

            border: 1px solid var(--border);

            color: var(--text-main) !important;
        }

        .communication-text {
            background: var(--surface-soft);

            border: 1px solid var(--border);
            border-radius: 14px;

            color: var(--text-main);

            line-height: 1.7;

            padding: 18px 20px;
            margin: 0 0 22px;
        }

        .communication-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 18px;

            padding-top: 4px;
        }

        .communication-author {
            color: var(--text-muted);

            font-size: 12px;
        }

        .communication-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;

            gap: 8px;
        }

        .communication-actions form {
            margin: 0;
        }

        .communication-actions .btn {
            border-radius: 8px;
        }

        /* =================================================
           ESTADO VAZIO
        ================================================= */

        .empty-state {
            min-height: 300px;

            padding: 50px 25px;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;

            text-align: center;
        }

        .empty-state-icon {
            width: 70px;
            height: 70px;

            border-radius: 20px;

            background: var(--primary-soft);
            color: var(--primary);

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 30px;

            margin-bottom: 17px;
        }

        .empty-state h3 {
            color: var(--text-main);

            font-size: 21px;
            font-weight: 800;

            margin: 0 0 6px;
        }

        .empty-state p {
            color: var(--text-muted);

            margin: 0 0 18px;
        }

        /* =================================================
           MODAL
        ================================================= */

        .modal {
            z-index: 99999 !important;
        }

        .modal-backdrop {
            z-index: 99998 !important;
        }

        .modal-content {
            background: var(--surface);
            color: var(--text-main);

            border: 0;
            border-radius: 20px;

            overflow: hidden;
        }

        .modal-header {
            min-height: 70px;

            padding: 20px 24px;

            background: var(--primary);
            color: #ffffff;

            border: 0;
        }

        .modal-title {
            color: #ffffff;

            font-weight: 800;
        }

        .modal-body {
            background: var(--surface);

            padding: 25px !important;
        }

        .modal-footer {
            background: var(--surface);

            border-top: 1px solid var(--border);

            padding: 16px 25px;
        }

        .modal .form-label,
        .modal .form-check-label {
            color: var(--text-main);
        }

        .modal .form-control,
        .modal .form-select {
            background: var(--surface);
            color: var(--text-main);

            border-color: var(--border);
            border-radius: 10px;

            min-height: 44px;
        }

        .modal textarea.form-control {
            min-height: 130px;
        }

        .modal .form-control:focus,
        .modal .form-select:focus {
            background: var(--surface);
            color: var(--text-main);

            border-color: #86b7fe;

            box-shadow:
                0 0 0 .2rem
                rgba(13, 110, 253, .15);
        }

        /* =================================================
           DARK MODE
        ================================================= */

        body.dark .public-badge,
        body.dark-mode .public-badge {
            background: #1e293b !important;

            color: #f8fafc !important;

            border-color: #334155;
        }

        body.dark .communication-text,
        body.dark-mode .communication-text {
            background: #182235;
        }

        body.dark .communication-item,
        body.dark-mode .communication-item {
            box-shadow:
                0 5px 18px
                rgba(0, 0, 0, .22);
        }

        body.dark .icon-total,
        body.dark-mode .icon-total {
            background:
                rgba(37, 99, 235, .18);
        }

        body.dark .icon-fixed,
        body.dark-mode .icon-fixed {
            background:
                rgba(245, 158, 11, .16);
        }

        body.dark .icon-month,
        body.dark-mode .icon-month {
            background:
                rgba(22, 163, 74, .16);
        }

        body.dark .icon-public,
        body.dark-mode .icon-public {
            background:
                rgba(124, 58, 237, .16);
        }

        /* =================================================
           RESPONSIVO
        ================================================= */

        @media (max-width: 768px) {

            .page-container {
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

            .btn-new,
            .search-box {
                width: 100%;
            }

            .communications-list {
                gap: 16px;

                padding: 16px;
            }

            .communication-item {
                padding: 20px;
            }

            .communication-top,
            .communication-footer {
                align-items: flex-start;
                flex-direction: column;
            }

            .communication-date {
                white-space: normal;
            }
        }

        @media (max-width: 520px) {

            .page-header h1 {
                font-size: 25px;
            }

            .communication-title-area {
                flex-direction: column;
            }

            .communication-actions,
            .communication-actions form,
            .communication-actions .btn {
                width: 100%;
            }
        }

    </style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid page-container">

        <!-- MENSAGENS -->

        <?php if ($mensagem): ?>

            <div
                class="alert alert-success alert-dismissible fade show custom-alert mb-4"
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

        <?php if ($erro): ?>

            <div
                class="alert alert-danger alert-dismissible fade show custom-alert mb-4"
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
                    Comunicados
                </h1>

                <p>
                    Publique avisos, eventos e informações importantes para os funcionários da empresa.
                </p>

            </div>

            <button
                type="button"
                class="btn btn-primary btn-new"
                data-bs-toggle="modal"
                data-bs-target="#modalComunicado"
            >

                <i class="bi bi-plus-lg me-2"></i>

                Novo comunicado

            </button>

        </section>

        <!-- CARDS DE RESUMO -->

        <div class="row g-3 mb-4">

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="summary-card">

                    <div>

                        <small>Total</small>

                        <h2>
                            <?= $totalComunicados ?>
                        </h2>

                        <p>
                            Comunicados publicados
                        </p>

                    </div>

                    <div class="summary-icon icon-total">

                        <i class="bi bi-megaphone-fill"></i>

                    </div>

                </div>

            </div>

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="summary-card">

                    <div>

                        <small>Fixados</small>

                        <h2>
                            <?= $totalFixados ?>
                        </h2>

                        <p>
                            Destacados no topo
                        </p>

                    </div>

                    <div class="summary-icon icon-fixed">

                        <i class="bi bi-pin-angle-fill"></i>

                    </div>

                </div>

            </div>

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="summary-card">

                    <div>

                        <small>Este mês</small>

                        <h2>
                            <?= $mesComunicados ?>
                        </h2>

                        <p>
                            Publicados no período
                        </p>

                    </div>

                    <div class="summary-icon icon-month">

                        <i class="bi bi-calendar-check-fill"></i>

                    </div>

                </div>

            </div>

            <div class="col-12 col-sm-6 col-xl-3">

                <div class="summary-card">

                    <div>

                        <small>Responsável</small>

                        <h2>
                            RH
                        </h2>

                        <p>
                            Administração dos avisos
                        </p>

                    </div>

                    <div class="summary-icon icon-public">

                        <i class="bi bi-people-fill"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- PAINEL -->

        <section class="main-panel">

            <header class="panel-header">

                <div>

                    <h2>
                        Comunicados publicados
                    </h2>

                    <p>
                        Consulte, fixe ou exclua os avisos enviados.
                    </p>

                </div>

                <div class="search-box">

                    <div class="input-group">

                        <span class="input-group-text">

                            <i class="bi bi-search"></i>

                        </span>

                        <input
                            type="text"
                            id="buscarComunicado"
                            class="form-control"
                            placeholder="Pesquisar comunicado"
                        >

                    </div>

                </div>

            </header>

            <?php if ($resultado->num_rows > 0): ?>

                <div
                    class="communications-list"
                    id="listaComunicados"
                >

                    <?php while ($row = $resultado->fetch_assoc()): ?>

                        <article
                            class="communication-item comunicado-item <?= $row['fixado'] ? 'fixed-item' : '' ?>"
                            data-texto="<?= strtolower(
                                htmlspecialchars(
                                    $row['titulo'] .
                                    ' ' .
                                    $row['conteudo'] .
                                    ' ' .
                                    $row['categoria'] .
                                    ' ' .
                                    $row['autor']
                                )
                            ) ?>"
                        >

                            <div class="communication-top">

                                <div class="communication-title-area">

                                    <div class="communication-icon">

                                        <i class="bi bi-megaphone-fill"></i>

                                    </div>

                                    <div>

                                        <div class="badges-row mb-2">

                                            <span
                                                class="badge <?= badgeCategoria(
                                                    $row['categoria']
                                                ) ?>"
                                            >

                                                <?= htmlspecialchars(
                                                    $row['categoria']
                                                ) ?>

                                            </span>

                                            <?php if ($row['fixado']): ?>

                                                <span class="badge bg-warning text-dark">

                                                    <i class="bi bi-pin-angle-fill me-1"></i>

                                                    Fixado

                                                </span>

                                            <?php endif; ?>

                                            <span class="badge public-badge">

                                                <i class="bi bi-people me-1"></i>

                                                <?= htmlspecialchars(
                                                    $row['publico'] ?? 'Todos'
                                                ) ?>

                                            </span>

                                        </div>

                                        <h3 class="communication-title">

                                            <?= htmlspecialchars(
                                                $row['titulo']
                                            ) ?>

                                        </h3>

                                    </div>

                                </div>

                                <div class="communication-date">

                                    <i class="bi bi-clock me-1"></i>

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $row['data_publicacao']
                                        )
                                    ) ?>

                                </div>

                            </div>

                            <div class="communication-text">

                                <?= nl2br(
                                    htmlspecialchars(
                                        $row['conteudo']
                                    )
                                ) ?>

                            </div>

                            <div class="communication-footer">

                                <div class="communication-author">

                                    <i class="bi bi-person-circle me-1"></i>

                                    Publicado por

                                    <strong>
                                        <?= htmlspecialchars(
                                            $row['autor']
                                        ) ?>
                                    </strong>

                                </div>

                                <div class="communication-actions">

                                    <form method="POST">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $row['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="fixado_atual"
                                            value="<?= $row['fixado'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="alternar_fixado"
                                            class="btn btn-outline-warning btn-sm px-3"
                                        >

                                            <i class="bi bi-pin-angle me-1"></i>

                                            <?= $row['fixado']
                                                ? 'Desfixar'
                                                : 'Fixar' ?>

                                        </button>

                                    </form>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Deseja excluir este comunicado?')"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $row['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="excluir_comunicado"
                                            class="btn btn-outline-danger btn-sm px-3"
                                        >

                                            <i class="bi bi-trash me-1"></i>

                                            Excluir

                                        </button>

                                    </form>

                                </div>

                            </div>

                        </article>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <div class="empty-state-icon">

                        <i class="bi bi-megaphone"></i>

                    </div>

                    <h3>
                        Nenhum comunicado encontrado
                    </h3>

                    <p>
                        Publique o primeiro comunicado para sua equipe.
                    </p>

                    <button
                        type="button"
                        class="btn btn-primary px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#modalComunicado"
                    >

                        <i class="bi bi-plus-lg me-2"></i>

                        Novo comunicado

                    </button>

                </div>

            <?php endif; ?>

        </section>

    </div>

</div>

<!-- MODAL -->

<div
    class="modal fade"
    id="modalComunicado"
    tabindex="-1"
    aria-labelledby="tituloModalComunicado"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <form
            method="POST"
            class="modal-content shadow"
        >

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="tituloModalComunicado"
                >

                    <i class="bi bi-megaphone-fill me-2"></i>

                    Novo comunicado

                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>

            </div>

            <div class="modal-body">

                <div class="mb-3">

                    <label class="form-label fw-bold">
                        Título
                    </label>

                    <input
                        type="text"
                        name="titulo"
                        class="form-control"
                        placeholder="Digite o título do comunicado"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label fw-bold">
                        Conteúdo
                    </label>

                    <textarea
                        name="conteudo"
                        class="form-control"
                        rows="5"
                        placeholder="Escreva o comunicado"
                        required
                    ></textarea>

                </div>

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Categoria
                        </label>

                        <select
                            name="categoria"
                            class="form-select"
                        >

                            <option>Aviso</option>
                            <option>Política</option>
                            <option>Evento</option>
                            <option>Comemoração</option>
                            <option>Urgente</option>

                        </select>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Público
                        </label>

                        <select
                            name="publico"
                            class="form-select"
                        >

                            <option>Todos</option>
                            <option>RH</option>
                            <option>Funcionários</option>
                            <option>Gestores</option>

                        </select>

                    </div>

                </div>

                <div class="form-check mt-4">

                    <input
                        type="checkbox"
                        name="fixado"
                        class="form-check-input"
                        id="fixado"
                    >

                    <label
                        class="form-check-label fw-semibold"
                        for="fixado"
                    >

                        Fixar comunicado no topo

                    </label>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light border"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    name="salvar_comunicado"
                    class="btn btn-primary px-4"
                >

                    <i class="bi bi-send-fill me-2"></i>

                    Publicar

                </button>

            </div>

        </form>

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<script>

var buscar =
    document.getElementById(
        'buscarComunicado'
    );

var comunicados =
    document.querySelectorAll(
        '.comunicado-item'
    );

if (buscar) {

    buscar.addEventListener(
        'keyup',
        function () {

            var termo =
                this.value
                    .toLowerCase()
                    .trim();

            for (
                var indice = 0;
                indice < comunicados.length;
                indice++
            ) {

                var texto =
                    comunicados[indice]
                        .getAttribute(
                            'data-texto'
                        ) || '';

                if (
                    texto.indexOf(termo) !== -1
                ) {

                    comunicados[indice]
                        .style.display = '';

                } else {

                    comunicados[indice]
                        .style.display = 'none';
                }
            }
        }
    );
}

</script>

<script src="js/theme.js"></script>
<script src="js/translate.js"></script>

</body>

</html>
```
