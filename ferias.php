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

/* TABELA DOS MESES DISPONÍVEIS */
$con->query("
    CREATE TABLE IF NOT EXISTS ferias_meses_disponiveis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_empresa INT NOT NULL,
        mes TINYINT NOT NULL,
        disponivel TINYINT NOT NULL DEFAULT 1,
        atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY empresa_mes (id_empresa, mes)
    )
");

/* GARANTE OS 12 MESES DA EMPRESA */
foreach ($mesesFerias as $numMes => $nomeMes) {
    $stmtMes = $con->prepare("
        INSERT IGNORE INTO ferias_meses_disponiveis 
        (id_empresa, mes, disponivel)
        VALUES (?, ?, 1)
    ");
    $stmtMes->bind_param("ii", $id_empresa, $numMes);
    $stmtMes->execute();
}

/* AÇÕES */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* SALVAR MESES DISPONÍVEIS */
    if (isset($_POST['salvar_meses'])) {

        $mesesSelecionados = $_POST['meses_disponiveis'] ?? [];

        $stmtReset = $con->prepare("
            UPDATE ferias_meses_disponiveis
            SET disponivel = 0
            WHERE id_empresa = ?
        ");
        $stmtReset->bind_param("i", $id_empresa);

        if ($stmtReset->execute()) {

            foreach ($mesesSelecionados as $mes) {
                $mes = intval($mes);

                if ($mes >= 1 && $mes <= 12) {
                    $stmtUpdate = $con->prepare("
                        UPDATE ferias_meses_disponiveis
                        SET disponivel = 1
                        WHERE id_empresa = ?
                        AND mes = ?
                    ");
                    $stmtUpdate->bind_param("ii", $id_empresa, $mes);
                    $stmtUpdate->execute();
                }
            }

            $mensagem = "Meses disponíveis atualizados com sucesso.";

            if (function_exists('registrarAtividade')) {
                registrarAtividade($con, "Atualizou os meses disponíveis para férias", "primary");
            }

        } else {
            $erro = "Erro ao atualizar meses disponíveis.";
        }
    }

    /* APROVAR / REJEITAR */
    if (!isset($_POST['salvar_meses'])) {

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

            $stmt->bind_param("sii", $msg, $id_ferias, $id_empresa);

            if ($stmt->execute()) {
                $mensagem = "Solicitação aprovada com sucesso.";

                if (function_exists('registrarAtividade')) {
                    registrarAtividade($con, "Aprovou uma solicitação de férias", "success");
                }

            } else {
                $erro = "Erro ao aprovar solicitação.";
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

            $stmt->bind_param("ssii", $msg, $motivo, $id_ferias, $id_empresa);

            if ($stmt->execute()) {
                $mensagem = "Solicitação rejeitada.";

                if (function_exists('registrarAtividade')) {
                    registrarAtividade($con, "Rejeitou uma solicitação de férias", "danger");
                }

            } else {
                $erro = "Erro ao rejeitar solicitação.";
            }
        }
    }
}

/* MESES DISPONÍVEIS */
$mesesDisponiveis = [];

$stmtMeses = $con->prepare("
    SELECT mes, disponivel
    FROM ferias_meses_disponiveis
    WHERE id_empresa = ?
");
$stmtMeses->bind_param("i", $id_empresa);
$stmtMeses->execute();
$resMeses = $stmtMeses->get_result();

while ($rowMes = $resMeses->fetch_assoc()) {
    $mesesDisponiveis[(int)$rowMes['mes']] = (int)$rowMes['disponivel'];
}

$totalMesesDisponiveis = array_sum($mesesDisponiveis);

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
    --green: #16a34a;
    --red: #dc2626;
    --yellow: #d97706;
    --cyan: #0891b2;
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

.month-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
}

.month-check input {
    display: none;
}

.month-check label {
    width: 100%;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--text);
    border-radius: 14px;
    padding: 12px 10px;
    font-weight: 750;
    font-size: 14px;
    text-align: center;
    cursor: pointer;
    transition: .18s ease;
}

.month-check input:checked + label {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: var(--blue);
}

.month-check label:hover {
    border-color: #93c5fd;
}

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

body.dark .month-check label,
body.dark-mode .month-check label,
body.dark .search-input,
body.dark-mode .search-input,
body.dark .form-control,
body.dark-mode .form-control {
    background: #0b1220;
    color: #f8fafc;
    border-color: #334155;
}

body.dark .month-check input:checked + label,
body.dark-mode .month-check input:checked + label {
    background: rgba(37, 99, 235, .20);
    border-color: rgba(147, 197, 253, .45);
    color: #93c5fd;
}

body.dark .badge-pendente,
body.dark-mode .badge-pendente {
    background: rgba(217, 119, 6, .22);
    color: #fcd34d;
}

body.dark .badge-visto,
body.dark-mode .badge-visto {
    background: rgba(8, 145, 178, .22);
    color: #67e8f9;
}

body.dark .badge-aprovado,
body.dark-mode .badge-aprovado {
    background: rgba(22, 163, 74, .20);
    color: #86efac;
}

body.dark .badge-rejeitado,
body.dark-mode .badge-rejeitado {
    background: rgba(220, 38, 38, .20);
    color: #fca5a5;
}

body.dark .badge-indefinido,
body.dark-mode .badge-indefinido {
    background: rgba(148, 163, 184, .18);
    color: #cbd5e1;
}

@media (max-width: 992px) {
    .month-grid {
        grid-template-columns: repeat(3, 1fr);
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
                    Gerencie solicitações, aprovações e meses disponíveis para os colaboradores.
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
                        <div class="kpi-label">Meses disponíveis</div>
                        <h2 class="kpi-value"><?= (int)$totalMesesDisponiveis ?></h2>
                        <div class="kpi-small">Liberados para solicitação</div>
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
            <h5>Meses disponíveis para férias</h5>
            <p>Os meses marcados aparecerão como disponíveis na tela dos colaboradores.</p>
        </div>

        <form method="POST" class="panel-body-custom">

            <div class="month-grid mb-3">
                <?php foreach ($mesesFerias as $numMes => $nomeMes): ?>
                    <div class="month-check">
                        <input
                            type="checkbox"
                            id="mes<?= $numMes ?>"
                            name="meses_disponiveis[]"
                            value="<?= $numMes ?>"
                            <?= !empty($mesesDisponiveis[$numMes]) ? 'checked' : '' ?>
                        >
                        <label for="mes<?= $numMes ?>">
                            <?= htmlspecialchars($nomeMes) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="salvar_meses" class="btn btn-primary btn-action">
                <i class="bi bi-save me-1"></i>
                Salvar meses disponíveis
            </button>

        </form>
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

                            <td>
                                <?= (int)$ferias['dias'] ?> dias
                            </td>

                            <td>
                                <?= date('d/m/Y', strtotime($ferias['data_solicitacao'])) ?>
                            </td>

                            <td>
                                <?= badgeStatus($ferias['status']) ?>
                            </td>

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