<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';

if (file_exists('registrar-atividade.php')) {
    require_once 'registrar-atividade.php';
}

$id_empresa = $_SESSION['id_empresa'] ?? 0;

if (!$id_empresa) {
    die("Erro: empresa não identificada. Faça login novamente.");
}

$mensagem = '';
$erro = '';

$mesesFerias = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

/* TABELA DOS MESES */
$con->query("
    CREATE TABLE IF NOT EXISTS ferias_meses_disponiveis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_empresa INT NOT NULL,
        mes TINYINT NOT NULL,
        disponivel TINYINT NOT NULL DEFAULT 1,
        limite_pedidos INT NULL DEFAULT NULL,
        atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY empresa_mes (id_empresa, mes)
    )
");

/* GARANTE COLUNA limite_pedidos */
$checkColuna = $con->query("SHOW COLUMNS FROM ferias_meses_disponiveis LIKE 'limite_pedidos'");

if ($checkColuna && $checkColuna->num_rows == 0) {
    $con->query("
        ALTER TABLE ferias_meses_disponiveis
        ADD COLUMN limite_pedidos INT NULL DEFAULT NULL AFTER disponivel
    ");
}

/* CORRIGE limite_pedidos PARA ACEITAR NULL */
$con->query("
    ALTER TABLE ferias_meses_disponiveis
    MODIFY limite_pedidos INT NULL DEFAULT NULL
");

/* GARANTE OS 12 MESES */
foreach ($mesesFerias as $numMes => $nomeMes) {
    $stmtMes = $con->prepare("
        INSERT IGNORE INTO ferias_meses_disponiveis 
        (id_empresa, mes, disponivel, limite_pedidos)
        VALUES (?, ?, 1, NULL)
    ");

    if ($stmtMes) {
        $stmtMes->bind_param("ii", $id_empresa, $numMes);
        $stmtMes->execute();
        $stmtMes->close();
    }
}

/* AÇÕES */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* SALVAR CONFIGURAÇÃO DO MÊS */
    if (isset($_POST['salvar_config_mes'])) {

        $mesConfig = intval($_POST['mes_config'] ?? 0);
        $tipoConfig = $_POST['tipo_config'] ?? 'liberado';
        $limitePedidos = $_POST['limite_pedidos'] ?? null;

        if ($mesConfig < 1 || $mesConfig > 12) {
            $erro = "Mês inválido.";
        } else {

            /* BLOQUEADO */
            if ($tipoConfig === 'bloqueado') {

                $stmtConfig = $con->prepare("
                    INSERT INTO ferias_meses_disponiveis
                        (id_empresa, mes, disponivel, limite_pedidos)
                    VALUES
                        (?, ?, 0, NULL)
                    ON DUPLICATE KEY UPDATE
                        disponivel = 0,
                        limite_pedidos = NULL,
                        atualizado_em = NOW()
                ");

                if (!$stmtConfig) {
                    $erro = "Erro ao preparar bloqueio: " . $con->error;
                } else {
                    $stmtConfig->bind_param("ii", $id_empresa, $mesConfig);

                    if ($stmtConfig->execute()) {
                        $mensagem = $mesesFerias[$mesConfig] . " bloqueado para solicitações.";

                        if (function_exists('registrarAtividade')) {
                            registrarAtividade(
                                $con,
                                "Bloqueou solicitações de férias para " . $mesesFerias[$mesConfig],
                                "warning"
                            );
                        }
                    } else {
                        $erro = "Erro ao bloquear mês: " . $stmtConfig->error;
                    }

                    $stmtConfig->close();
                }

            /* LIMITADO */
            } elseif ($tipoConfig === 'limitado') {

                $limitePedidosFinal = intval($limitePedidos);

                if ($limitePedidosFinal <= 0) {
                    $erro = "Informe uma quantidade válida de pedidos para o mês.";
                } else {

                    $stmtConfig = $con->prepare("
                        INSERT INTO ferias_meses_disponiveis
                            (id_empresa, mes, disponivel, limite_pedidos)
                        VALUES
                            (?, ?, 1, ?)
                        ON DUPLICATE KEY UPDATE
                            disponivel = 1,
                            limite_pedidos = VALUES(limite_pedidos),
                            atualizado_em = NOW()
                    ");

                    if (!$stmtConfig) {
                        $erro = "Erro ao preparar limite: " . $con->error;
                    } else {
                        $stmtConfig->bind_param(
                            "iii",
                            $id_empresa,
                            $mesConfig,
                            $limitePedidosFinal
                        );

                        if ($stmtConfig->execute()) {
                            $mensagem = $mesesFerias[$mesConfig] . " limitado a " . $limitePedidosFinal . " pedidos.";

                            if (function_exists('registrarAtividade')) {
                                registrarAtividade(
                                    $con,
                                    "Limitou solicitações de férias para " . $mesesFerias[$mesConfig],
                                    "primary"
                                );
                            }
                        } else {
                            $erro = "Erro ao limitar mês: " . $stmtConfig->error;
                        }

                        $stmtConfig->close();
                    }
                }

            /* LIBERADO SEM LIMITE */
            } else {

                $stmtConfig = $con->prepare("
                    INSERT INTO ferias_meses_disponiveis
                        (id_empresa, mes, disponivel, limite_pedidos)
                    VALUES
                        (?, ?, 1, NULL)
                    ON DUPLICATE KEY UPDATE
                        disponivel = 1,
                        limite_pedidos = NULL,
                        atualizado_em = NOW()
                ");

                if (!$stmtConfig) {
                    $erro = "Erro ao preparar liberação: " . $con->error;
                } else {
                    $stmtConfig->bind_param("ii", $id_empresa, $mesConfig);

                    if ($stmtConfig->execute()) {
                        $mensagem = $mesesFerias[$mesConfig] . " liberado sem limite.";

                        if (function_exists('registrarAtividade')) {
                            registrarAtividade(
                                $con,
                                "Liberou solicitações de férias para " . $mesesFerias[$mesConfig],
                                "success"
                            );
                        }
                    } else {
                        $erro = "Erro ao liberar mês: " . $stmtConfig->error;
                    }

                    $stmtConfig->close();
                }
            }
        }
    }

    /* APROVAR / REJEITAR */
    if (!isset($_POST['salvar_config_mes'])) {

        $id_ferias = intval($_POST['id_ferias'] ?? 0);

        if (!$id_ferias) {
            $erro = "Pedido inválido.";
        }

        if (!$erro && isset($_POST['aprovar'])) {

            $msg = "Sua solicitação de férias foi aprovada pelo RH.";

            $stmt = $con->prepare("
                UPDATE ferias
                SET 
                    status = 'aprovado',
                    data_visto = NOW(),
                    mensagem_colaborador = ?,
                    motivo_rejeicao = NULL
                WHERE id_ferias = ?
                AND id_empresa = ?
            ");

            if (!$stmt) {
                $erro = "Erro ao preparar aprovação: " . $con->error;
            } else {
                $stmt->bind_param("sii", $msg, $id_ferias, $id_empresa);

                if ($stmt->execute()) {
                    $mensagem = "Solicitação aprovada com sucesso.";

                    if (function_exists('registrarAtividade')) {
                        registrarAtividade($con, "Aprovou uma solicitação de férias", "success");
                    }
                } else {
                    $erro = "Erro ao aprovar solicitação.";
                }

                $stmt->close();
            }
        }

        if (!$erro && isset($_POST['rejeitar'])) {

            $motivo = trim($_POST['motivo_rejeicao'] ?? '');

            if ($motivo == '') {
                $motivo = "Solicitação rejeitada pelo RH.";
            }

            $msg = "Sua solicitação de férias foi rejeitada pelo RH.";

            $stmt = $con->prepare("
                UPDATE ferias
                SET 
                    status = 'rejeitado',
                    data_visto = NOW(),
                    mensagem_colaborador = ?,
                    motivo_rejeicao = ?
                WHERE id_ferias = ?
                AND id_empresa = ?
            ");

            if (!$stmt) {
                $erro = "Erro ao preparar rejeição: " . $con->error;
            } else {
                $stmt->bind_param("ssii", $msg, $motivo, $id_ferias, $id_empresa);

                if ($stmt->execute()) {
                    $mensagem = "Solicitação rejeitada.";

                    if (function_exists('registrarAtividade')) {
                        registrarAtividade($con, "Rejeitou uma solicitação de férias", "danger");
                    }
                } else {
                    $erro = "Erro ao rejeitar solicitação.";
                }

                $stmt->close();
            }
        }
    }
}

/* MESES CONFIGURADOS */
$mesesConfigurados = [];

$stmtMeses = $con->prepare("
    SELECT 
        mes, 
        disponivel,
        limite_pedidos
    FROM ferias_meses_disponiveis
    WHERE id_empresa = ?
    ORDER BY mes ASC
");

$stmtMeses->bind_param("i", $id_empresa);
$stmtMeses->execute();
$resMeses = $stmtMeses->get_result();

while ($rowMes = $resMeses->fetch_assoc()) {
    $mesesConfigurados[(int)$rowMes['mes']] = [
        'disponivel' => (int)$rowMes['disponivel'],
        'limite_pedidos' => $rowMes['limite_pedidos'] !== null ? (int)$rowMes['limite_pedidos'] : null,
        'usados' => 0
    ];
}

$stmtMeses->close();

/* PEDIDOS POR MÊS */
$stmtPedidosMes = $con->prepare("
    SELECT 
        MONTH(data_inicio) AS mes,
        COUNT(*) AS total
    FROM ferias
    WHERE id_empresa = ?
    GROUP BY MONTH(data_inicio)
");

$stmtPedidosMes->bind_param("i", $id_empresa);
$stmtPedidosMes->execute();
$resPedidosMes = $stmtPedidosMes->get_result();

while ($rowPedidoMes = $resPedidosMes->fetch_assoc()) {
    $mesPedido = (int)$rowPedidoMes['mes'];

    if (isset($mesesConfigurados[$mesPedido])) {
        $mesesConfigurados[$mesPedido]['usados'] = (int)$rowPedidoMes['total'];
    }
}

$stmtPedidosMes->close();

$totalMesesDisponiveis = 0;

foreach ($mesesConfigurados as $config) {
    if (!empty($config['disponivel'])) {
        $totalMesesDisponiveis++;
    }
}

/* CARDS */
$stmtCards = $con->prepare("
    SELECT
        SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) AS pendentes,
        SUM(CASE WHEN status = 'visto' THEN 1 ELSE 0 END) AS vistos,
        SUM(CASE WHEN status = 'aprovado' THEN 1 ELSE 0 END) AS aprovados,
        SUM(CASE WHEN status = 'rejeitado' THEN 1 ELSE 0 END) AS rejeitados,
        COUNT(*) AS total
    FROM ferias
    WHERE id_empresa = ?
");

$stmtCards->bind_param("i", $id_empresa);
$stmtCards->execute();
$cards = $stmtCards->get_result()->fetch_assoc();
$stmtCards->close();

$pendentes = $cards['pendentes'] ?? 0;
$vistos = $cards['vistos'] ?? 0;
$aprovados = $cards['aprovados'] ?? 0;
$rejeitados = $cards['rejeitados'] ?? 0;
$total = $cards['total'] ?? 0;

/* LISTAGEM */
$stmt = $con->prepare("
    SELECT
        fe.id_ferias,
        fe.data_inicio,
        fe.data_fim,
        fe.dias,
        fe.data_solicitacao,
        fe.status,
        fe.data_visto,
        fe.mensagem_colaborador,
        fe.motivo_rejeicao,
        f.nome
    FROM ferias fe
    INNER JOIN funcionarios f
        ON f.id_funcionario = fe.id_funcionario
        AND f.id_empresa = fe.id_empresa
    WHERE fe.id_empresa = ?
    ORDER BY fe.data_solicitacao DESC
");

$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$query = $stmt->get_result();

function badgeStatus($status) {
    if ($status == 'pendente') return '<span class="badge-status badge-pendente">Pendente</span>';
    if ($status == 'visto') return '<span class="badge-status badge-visto">Visto</span>';
    if ($status == 'aprovado') return '<span class="badge-status badge-aprovado">Aprovado</span>';
    if ($status == 'rejeitado') return '<span class="badge-status badge-rejeitado">Rejeitado</span>';
    return '<span class="badge-status badge-indefinido">Indefinido</span>';
}

function classeMes($config) {
    if (empty($config['disponivel'])) {
        return 'month-bloqueado';
    }

    if ($config['limite_pedidos'] !== null) {
        return 'month-limitado';
    }

    return 'month-liberado';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Pedidos de Férias</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
:root {
    --bg: #f5f7fb;
    --card: #ffffff;
    --card-soft: #f8fafc;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --blue: #2563eb;
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

.year-pill {
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

/* MESES */

.month-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
}

.month-card {
    width: 100%;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--text);
    border-radius: 16px;
    padding: 16px 10px;
    text-align: center;
    cursor: pointer;
    transition: .2s ease;
    font-weight: 850;
    font-size: 14px;
}

.month-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(15, 23, 42, .10);
    border-color: #93c5fd;
    color: var(--blue);
}

/* LIBERADO SEM LIMITE */
.month-liberado {
    background: #ffffff;
    border-color: var(--border);
    color: var(--text);
}

/* LIBERADO COM LIMITE - AZUL CLARO */
.month-limitado {
    background: #e0f2fe;
    border-color: #7dd3fc;
    color: #0c4a6e;
}

.month-limitado:hover {
    background: #bae6fd;
    border-color: #38bdf8;
    color: #075985;
}

/* BLOQUEADO */
.month-bloqueado {
    background: #fef2f2;
    border-color: #fecaca;
    color: #991b1b;
}

.month-bloqueado:hover {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #7f1d1d;
}

/* TABELA */

.table-card {
    border-radius: 20px;
    overflow: hidden;
}

.search-input {
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

.badge-status {
    border-radius: 999px;
    padding: 7px 11px;
    font-size: 12px;
    font-weight: 800;
    display: inline-block;
}

.badge-pendente {
    background: #fef3c7;
    color: #92400e;
}

.badge-visto {
    background: #cffafe;
    color: #155e75;
}

.badge-aprovado {
    background: #dcfce7;
    color: #166534;
}

.badge-rejeitado {
    background: #fee2e2;
    color: #991b1b;
}

.badge-indefinido {
    background: #e2e8f0;
    color: #334155;
}

.btn-action {
    border-radius: 12px;
    font-weight: 750;
}

.empty-state {
    text-align: center;
    padding: 36px 20px;
    color: var(--muted);
}

.alert {
    border-radius: 16px;
    border: 0;
}

/* MODAIS */

.modal {
    z-index: 99999 !important;
}

.modal-backdrop {
    z-index: 99998 !important;
}

.modal-content {
    background: var(--card);
    color: var(--text);
    border-radius: 20px;
}

.modal-header,
.modal-footer {
    border-color: var(--border);
}

.form-control,
.form-select {
    background: var(--card);
    color: var(--text);
    border-color: var(--border);
}

.form-control::placeholder {
    color: var(--muted);
}

/* RADIO CUSTOM */

.config-box {
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 14px;
    cursor: pointer;
    transition: .2s ease;
    display: flex;
    gap: 14px;
    align-items: flex-start;
    outline: none !important;
}

.config-box:hover {
    border-color: #93c5fd;
    background: #eff6ff;
}

.config-box input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.radio-fake {
    width: 19px;
    height: 19px;
    border-radius: 50%;
    border: 2px solid #94a3b8;
    background: transparent;
    flex-shrink: 0;
    margin-top: 3px;
    position: relative;
    transition: .2s ease;
}

.radio-fake::after {
    content: "";
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #2563eb;
    position: absolute;
    top: 3px;
    left: 3px;
    opacity: 0;
    transform: scale(.4);
    transition: .2s ease;
}

.config-box input[type="radio"]:checked + .radio-fake {
    border-color: #2563eb;
}

.config-box input[type="radio"]:checked + .radio-fake::after {
    opacity: 1;
    transform: scale(1);
}

.config-box:has(input[type="radio"]:checked) {
    border-color: rgba(37, 99, 235, .45);
    background: #eff6ff;
}

/* DARK MODE */

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

body.dark .year-pill,
body.dark-mode .year-pill,
body.dark .kpi-icon,
body.dark-mode .kpi-icon,
body.dark .employee-avatar,
body.dark-mode .employee-avatar {
    background: rgba(37, 99, 235, .16);
    color: #93c5fd;
}

body.dark .search-input,
body.dark-mode .search-input,
body.dark .form-control,
body.dark-mode .form-control,
body.dark .form-select,
body.dark-mode .form-select {
    background: #0b1220;
    color: #f8fafc;
    border-color: #334155;
}

body.dark .month-card,
body.dark-mode .month-card {
    background: #0b1220;
    color: #f8fafc;
    border-color: #334155;
}

body.dark .month-card:hover,
body.dark-mode .month-card:hover {
    background: rgba(37, 99, 235, .14);
    color: #93c5fd;
    border-color: rgba(147, 197, 253, .45);
}

/* COM LIMITE NO DARK MODE - AZUL CLARO */
body.dark .month-limitado,
body.dark-mode .month-limitado {
    background: rgba(14, 165, 233, .18);
    border-color: rgba(125, 211, 252, .45);
    color: #bae6fd;
}

body.dark .month-limitado:hover,
body.dark-mode .month-limitado:hover {
    background: rgba(14, 165, 233, .28);
    border-color: rgba(125, 211, 252, .70);
    color: #e0f2fe;
}

/* BLOQUEADO NO DARK MODE */
body.dark .month-bloqueado,
body.dark-mode .month-bloqueado {
    background: rgba(220, 38, 38, .14);
    color: #fca5a5;
    border-color: rgba(248, 113, 113, .35);
}

body.dark .month-bloqueado:hover,
body.dark-mode .month-bloqueado:hover {
    background: rgba(220, 38, 38, .22);
    color: #fecaca;
    border-color: rgba(248, 113, 113, .55);
}

body.dark .config-box,
body.dark-mode .config-box {
    border-color: #334155;
}

body.dark .config-box:hover,
body.dark-mode .config-box:hover,
body.dark .config-box:has(input[type="radio"]:checked),
body.dark-mode .config-box:has(input[type="radio"]:checked) {
    background: rgba(37, 99, 235, .14);
    color: #bfdbfe;
    border-color: rgba(147, 197, 253, .35);
}

body.dark .radio-fake,
body.dark-mode .radio-fake {
    border-color: #64748b;
}

body.dark .radio-fake::after,
body.dark-mode .radio-fake::after {
    background: #60a5fa;
}

body.dark .config-box input[type="radio"]:checked + .radio-fake,
body.dark-mode .config-box input[type="radio"]:checked + .radio-fake {
    border-color: #60a5fa;
}

@media (max-width: 1100px) {
    .month-grid {
        grid-template-columns: repeat(4, 1fr);
    }
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

    .month-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 520px) {
    .month-grid {
        grid-template-columns: repeat(2, 1fr);
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
                <h1 class="dashboard-title">Pedidos de Férias</h1>
                <p class="dashboard-subtitle">
                    Gerencie solicitações, aprovações e regras mensais para os colaboradores.
                </p>
            </div>

            <div class="d-flex align-items-start">
                <span class="year-pill">
                    <i class="bi bi-calendar-check me-1"></i>
                    <?= date('Y') ?>
                </span>
            </div>
        </div>
    </div>

    <?php if($mensagem): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($erro): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($erro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">

        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Pendentes</div>
                        <h2 class="kpi-value"><?= (int)$pendentes ?></h2>
                        <div class="kpi-small">Aguardando análise</div>
                    </div>
                    <div class="kpi-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Vistos</div>
                        <h2 class="kpi-value"><?= (int)$vistos ?></h2>
                        <div class="kpi-small">Visualizados pelo RH</div>
                    </div>
                    <div class="kpi-icon">
                        <i class="bi bi-eye"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Aprovados</div>
                        <h2 class="kpi-value"><?= (int)$aprovados ?></h2>
                        <div class="kpi-small">Solicitações aceitas</div>
                    </div>
                    <div class="kpi-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Meses liberados</div>
                        <h2 class="kpi-value"><?= (int)$totalMesesDisponiveis ?></h2>
                        <div class="kpi-small">Disponíveis para solicitação</div>
                    </div>
                    <div class="kpi-icon">
                        <i class="bi bi-calendar2-week"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="panel-card mb-4">
        <div class="panel-header">
            <h5>Configuração mensal de férias</h5>
            <p>Clique em um mês para liberar, bloquear ou limitar solicitações.</p>
        </div>

        <div class="panel-body-custom">
            <div class="month-grid">
                <?php foreach ($mesesFerias as $numMes => $nomeMes): ?>
                    <?php
                    $config = $mesesConfigurados[$numMes] ?? [
                        'disponivel' => 1,
                        'limite_pedidos' => null,
                        'usados' => 0
                    ];

                    $classe = classeMes($config);
                    ?>

                    <button
                        type="button"
                        class="month-card <?= htmlspecialchars($classe) ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#modalConfigMes"
                        data-mes="<?= (int)$numMes ?>"
                        data-nome="<?= htmlspecialchars($nomeMes) ?>"
                        data-disponivel="<?= (int)$config['disponivel'] ?>"
                        data-limite="<?= $config['limite_pedidos'] !== null ? (int)$config['limite_pedidos'] : '' ?>"
                        onclick="abrirConfigMes(this)"
                    >
                        <?= htmlspecialchars($nomeMes) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <h5>Solicitações</h5>
                    <p>Controle os pedidos enviados pelos funcionários.</p>
                </div>

                <div class="col-lg-5">
                    <input
                        type="text"
                        class="form-control search-input"
                        id="buscarFerias"
                        placeholder="Pesquisar por funcionário, status ou período..."
                    >
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>Período</th>
                        <th>Dias</th>
                        <th>Solicitação</th>
                        <th>Status</th>
                        <th>Mensagem</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>

                <tbody id="tabelaFerias">
                <?php if ($query->num_rows > 0): ?>
                    <?php while ($ferias = $query->fetch_assoc()): ?>
                        <?php
                        $inicial = mb_strtoupper(mb_substr($ferias['nome'], 0, 1, 'UTF-8'), 'UTF-8');
                        ?>

                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="employee-avatar">
                                        <?= htmlspecialchars($inicial) ?>
                                    </div>
                                    <span class="employee-name">
                                        <?= htmlspecialchars($ferias['nome']) ?>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <?= date('d/m/Y', strtotime($ferias['data_inicio'])) ?>
                                -
                                <?= date('d/m/Y', strtotime($ferias['data_fim'])) ?>
                            </td>

                            <td><?= (int)$ferias['dias'] ?> dias</td>

                            <td><?= date('d/m/Y', strtotime($ferias['data_solicitacao'])) ?></td>

                            <td><?= badgeStatus($ferias['status']) ?></td>

                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars($ferias['mensagem_colaborador'] ?? '-') ?>

                                    <?php if($ferias['status'] == 'rejeitado' && !empty($ferias['motivo_rejeicao'])): ?>
                                        <br>
                                        <strong>Motivo:</strong>
                                        <?= htmlspecialchars($ferias['motivo_rejeicao']) ?>
                                    <?php endif; ?>
                                </small>
                            </td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_ferias" value="<?= $ferias['id_ferias'] ?>">

                                        <button
                                            type="submit"
                                            name="aprovar"
                                            class="btn btn-success btn-sm btn-action"
                                            onclick="return confirm('Aprovar esta solicitação?')"
                                        >
                                            <i class="bi bi-check-lg"></i>
                                            Aprovar
                                        </button>
                                    </form>

                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm btn-action"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalRejeitar"
                                        data-id="<?= $ferias['id_ferias'] ?>"
                                        onclick="prepararRejeicao(this)"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                        Rejeitar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                Nenhuma solicitação de férias encontrada.
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</div>

<!-- MODAL CONFIGURAR MÊS -->
<div class="modal fade" id="modalConfigMes" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-gear me-2 text-primary"></i>
                    Configurar <span id="tituloMesConfig">mês</span>
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="mes_config" id="mesConfigInput">

                <div class="d-grid gap-3">

                    <label class="config-box">
                        <input
                            type="radio"
                            name="tipo_config"
                            value="liberado"
                            id="tipoLiberado"
                            onchange="toggleLimitePedidos()"
                        >
                        <span class="radio-fake"></span>

                        <div>
                            <strong>Liberado sem limite</strong>
                            <div class="text-muted small">
                                Funcionários poderão solicitar férias neste mês sem limite de quantidade.
                            </div>
                        </div>
                    </label>

                    <label class="config-box">
                        <input
                            type="radio"
                            name="tipo_config"
                            value="limitado"
                            id="tipoLimitado"
                            onchange="toggleLimitePedidos()"
                        >
                        <span class="radio-fake"></span>

                        <div class="w-100">
                            <strong>Liberado com limite</strong>
                            <div class="text-muted small mb-2">
                                Defina quantos pedidos poderão ser feitos neste mês.
                            </div>

                            <input
                                type="number"
                                name="limite_pedidos"
                                id="limitePedidosInput"
                                class="form-control"
                                min="1"
                                placeholder="Ex: 5"
                                disabled
                            >
                        </div>
                    </label>

                    <label class="config-box">
                        <input
                            type="radio"
                            name="tipo_config"
                            value="bloqueado"
                            id="tipoBloqueado"
                            onchange="toggleLimitePedidos()"
                        >
                        <span class="radio-fake"></span>

                        <div>
                            <strong>Bloqueado para solicitações</strong>
                            <div class="text-muted small">
                                Funcionários não poderão solicitar férias neste mês.
                            </div>
                        </div>
                    </label>

                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary btn-action"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    name="salvar_config_mes"
                    class="btn btn-primary btn-action"
                >
                    <i class="bi bi-save me-1"></i>
                    Salvar configuração
                </button>
            </div>

        </form>
    </div>
</div>

<!-- MODAL REJEITAR -->
<div class="modal fade" id="modalRejeitar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle me-2 text-danger"></i>
                    Rejeitar solicitação
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="id_ferias" id="idFeriasRejeitar">

                <label class="form-label fw-bold">
                    Motivo da rejeição
                </label>

                <textarea
                    name="motivo_rejeicao"
                    class="form-control"
                    rows="4"
                    placeholder="Ex: Período indisponível para o setor."
                    required
                ></textarea>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary btn-action"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    name="rejeitar"
                    class="btn btn-danger btn-action"
                >
                    Rejeitar pedido
                </button>
            </div>

        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function prepararRejeicao(botao){
    document.getElementById('idFeriasRejeitar').value = botao.dataset.id;
}

function abrirConfigMes(botao) {
    const mes = botao.dataset.mes;
    const nome = botao.dataset.nome;
    const disponivel = botao.dataset.disponivel;
    const limite = botao.dataset.limite;

    document.getElementById('tituloMesConfig').innerText = nome;
    document.getElementById('mesConfigInput').value = mes;

    const tipoLiberado = document.getElementById('tipoLiberado');
    const tipoLimitado = document.getElementById('tipoLimitado');
    const tipoBloqueado = document.getElementById('tipoBloqueado');
    const limiteInput = document.getElementById('limitePedidosInput');

    tipoLiberado.checked = false;
    tipoLimitado.checked = false;
    tipoBloqueado.checked = false;

    limiteInput.value = '';

    if (disponivel === '0') {
        tipoBloqueado.checked = true;
    } else if (limite !== '') {
        tipoLimitado.checked = true;
        limiteInput.value = limite;
    } else {
        tipoLiberado.checked = true;
    }

    toggleLimitePedidos();
}

function toggleLimitePedidos() {
    const tipoLimitado = document.getElementById('tipoLimitado');
    const limiteInput = document.getElementById('limitePedidosInput');

    if (tipoLimitado.checked) {
        limiteInput.disabled = false;
        limiteInput.required = true;
        setTimeout(() => limiteInput.focus(), 100);
    } else {
        limiteInput.disabled = true;
        limiteInput.required = false;
        limiteInput.value = '';
    }
}

const buscar = document.getElementById('buscarFerias');
const linhas = document.querySelectorAll('#tabelaFerias tr');

if (buscar) {
    buscar.addEventListener('keyup', function(){
        const termo = this.value.toLowerCase();

        linhas.forEach(function(linha){
            const texto = linha.innerText.toLowerCase();
            linha.style.display = texto.includes(termo) ? '' : 'none';
        });
    });
}
</script>

<script src="js/theme.js"></script>
<script src="js/translate.js"></script>
</body>
</html>