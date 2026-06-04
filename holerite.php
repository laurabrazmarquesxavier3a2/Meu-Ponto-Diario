<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';

$id_empresa = $_SESSION['id_empresa'] ?? 0;

if (!$id_empresa) {
    die("Erro: empresa não identificada. Faça login novamente.");
}

$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : '';
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : '';

$sql = "
SELECT
    h.*,
    f.nome
FROM holerites h
INNER JOIN funcionarios f
    ON h.funcionario_id = f.id_funcionario
WHERE h.id_empresa = ?
AND f.id_empresa = ?
";

$params = [$id_empresa, $id_empresa];
$types = "ii";

if (!empty($mes)) {
    $sql .= " AND MONTH(h.data_envio) = ? ";
    $params[] = $mes;
    $types .= "i";
}

if (!empty($ano)) {
    $sql .= " AND YEAR(h.data_envio) = ? ";
    $params[] = $ano;
    $types .= "i";
}

$sql .= " ORDER BY h.data_envio DESC";

$stmt = $con->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();

$stmtPendentes = $con->prepare("
SELECT
(
    (SELECT COUNT(*) FROM funcionarios WHERE id_empresa = ? AND ativo = 1)
    -
    (SELECT COUNT(DISTINCT funcionario_id) FROM holerites WHERE status = 'enviado' AND id_empresa = ?)
) AS total
");
$stmtPendentes->bind_param("ii", $id_empresa, $id_empresa);
$stmtPendentes->execute();
$pendentes = $stmtPendentes->get_result()->fetch_assoc()['total'] ?? 0;

$stmtEnviados = $con->prepare("
SELECT COUNT(DISTINCT funcionario_id) AS total
FROM holerites
WHERE status = 'enviado'
AND id_empresa = ?
");
$stmtEnviados->bind_param("i", $id_empresa);
$stmtEnviados->execute();
$enviados = $stmtEnviados->get_result()->fetch_assoc()['total'] ?? 0;

$stmtTotal = $con->prepare("
SELECT COUNT(*) AS total
FROM funcionarios
WHERE id_empresa = ?
AND ativo = 1
");
$stmtTotal->bind_param("i", $id_empresa);
$stmtTotal->execute();
$totalFuncionarios = $stmtTotal->get_result()->fetch_assoc()['total'] ?? 0;

$stmtFuncs = $con->prepare("
SELECT id_funcionario, nome
FROM funcionarios
WHERE id_empresa = ?
AND ativo = 1
ORDER BY nome
");
$stmtFuncs->bind_param("i", $id_empresa);
$stmtFuncs->execute();
$funcionarios = $stmtFuncs->get_result();

$percentualEnvio = $totalFuncionarios > 0 ? round(($enviados / $totalFuncionarios) * 100) : 0;
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
    --shadow: 0 12px 30px rgba(15, 23, 42, .07);
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
    font-size: 32px;
    font-weight: 850;
    margin-bottom: 6px;
    color: var(--text);
}

.page-subtitle {
    color: var(--muted);
    margin: 0;
}

.date-pill {
    background: #eff6ff;
    color: var(--blue);
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 800;
}

.kpi-card {
    border-radius: 22px;
    padding: 22px;
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
    font-size: 32px;
    font-weight: 850;
    margin-bottom: 2px;
}

.kpi-small {
    color: var(--muted);
    font-size: 13px;
}

.kpi-icon {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    background: #eff6ff;
    color: var(--blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 23px;
}

.filter-card,
.table-card {
    border-radius: 22px;
    overflow: hidden;
}

.card-section-header {
    padding: 20px 22px;
    background: var(--card-soft);
    border-bottom: 1px solid var(--border);
}

.card-section-header h5 {
    color: var(--text);
    font-weight: 850;
    margin-bottom: 4px;
}

.card-section-header p {
    color: var(--muted);
    margin-bottom: 0;
    font-size: 14px;
}

.card-section-body {
    padding: 22px;
}

.form-control,
.form-select {
    border-radius: 14px;
    min-height: 44px;
    border-color: var(--border);
    background: var(--card);
    color: var(--text);
}

.form-control:focus,
.form-select:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 .22rem rgba(37, 99, 235, .12);
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
    background: var(--card-soft);
    color: var(--muted);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}

.table tbody td {
    background: var(--card);
    color: var(--text);
    padding: 17px 20px;
    vertical-align: middle;
    border-bottom: 1px solid var(--border);
}

.table tbody tr:hover td {
    background: var(--card-soft);
}

.employee-avatar {
    width: 40px;
    height: 40px;
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
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 800;
    display: inline-block;
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
    text-align: center;
    padding: 42px 20px;
    color: var(--muted);
}

.empty-state i {
    display: block;
    font-size: 36px;
    margin-bottom: 10px;
    color: var(--muted);
}

.progress-clean {
    height: 9px;
    border-radius: 999px;
    background: var(--border);
    overflow: hidden;
    margin-top: 12px;
}

.progress-clean span {
    display: block;
    height: 100%;
    width: <?= (int)$percentualEnvio ?>%;
    background: var(--blue);
    border-radius: 999px;
}

.floating-action {
    position: fixed;
    right: 32px;
    bottom: 28px;
    z-index: 1000;
    box-shadow: 0 16px 32px rgba(37, 99, 235, .28);
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
    border-radius: 22px;
    border: 1px solid var(--border);
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
    --shadow: 0 12px 30px rgba(0, 0, 0, .25);
}

body.dark .date-pill,
body.dark-mode .date-pill,
body.dark .kpi-icon,
body.dark-mode .kpi-icon,
body.dark .employee-avatar,
body.dark-mode .employee-avatar {
    background: rgba(37, 99, 235, .16);
    color: #93c5fd;
    border-color: rgba(147, 197, 253, .35);
}

body.dark .form-control,
body.dark-mode .form-control,
body.dark .form-select,
body.dark-mode .form-select {
    background: #0b1220;
    color: #f8fafc;
    border-color: #334155;
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

@media(max-width: 768px) {
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
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($_GET['erro']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="page-title">Envio de Holerite</h1>
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
                        <div class="kpi-label">Envio pendente</div>
                        <h2 class="kpi-value"><?= max(0, $pendentes) ?></h2>
                        <div class="kpi-small">Funcionários sem holerite enviado</div>
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
                        <div class="kpi-label">Enviados</div>
                        <h2 class="kpi-value"><?= (int)$enviados ?></h2>
                        <div class="kpi-small">Funcionários com envio registrado</div>
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
                        <div class="kpi-label">Funcionários ativos</div>
                        <h2 class="kpi-value"><?= (int)$totalFuncionarios ?></h2>
                        <div class="kpi-small"><?= (int)$percentualEnvio ?>% com envio concluído</div>
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
            <p>Busque holerites por mês e ano de envio.</p>
        </div>

        <div class="card-section-body">
            <form method="GET" class="row g-3">

                <div class="col-md-3">
                    <select name="mes" class="form-select">
                        <option value="">Todos os meses</option>
                        <option value="1" <?= $mes==1?'selected':'' ?>>Janeiro</option>
                        <option value="2" <?= $mes==2?'selected':'' ?>>Fevereiro</option>
                        <option value="3" <?= $mes==3?'selected':'' ?>>Março</option>
                        <option value="4" <?= $mes==4?'selected':'' ?>>Abril</option>
                        <option value="5" <?= $mes==5?'selected':'' ?>>Maio</option>
                        <option value="6" <?= $mes==6?'selected':'' ?>>Junho</option>
                        <option value="7" <?= $mes==7?'selected':'' ?>>Julho</option>
                        <option value="8" <?= $mes==8?'selected':'' ?>>Agosto</option>
                        <option value="9" <?= $mes==9?'selected':'' ?>>Setembro</option>
                        <option value="10" <?= $mes==10?'selected':'' ?>>Outubro</option>
                        <option value="11" <?= $mes==11?'selected':'' ?>>Novembro</option>
                        <option value="12" <?= $mes==12?'selected':'' ?>>Dezembro</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <input type="number" name="ano" class="form-control" placeholder="Ano" value="<?= htmlspecialchars($ano) ?>">
                </div>

                <div class="col-md-3">
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-search me-2"></i>
                        Filtrar
                    </button>
                </div>

                <div class="col-md-3">
                    <a href="holerite.php" class="btn btn-outline-secondary w-100">
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
                    <p>Arquivos disponíveis para download e conferência.</p>
                </div>

                <span class="badge bg-primary rounded-pill px-3 py-2">
                    <?= $resultado->num_rows ?> registros
                </span>
            </div>
        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>Período</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($resultado->num_rows > 0): ?>

                    <?php while($row = $resultado->fetch_assoc()): ?>

                        <?php
                        $inicial = mb_strtoupper(mb_substr($row['nome'], 0, 1, 'UTF-8'), 'UTF-8');
                        ?>

                        <tr>
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

                            <td><?= htmlspecialchars($row['periodo']) ?></td>

                            <td><?= date('d/m/Y', strtotime($row['data_envio'])) ?></td>

                            <td>
                                <?php if($row['status'] == 'pendente'): ?>
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
                                <?php if (!empty($row['arquivo'])): ?>
                                    <a href="<?= htmlspecialchars($row['arquivo']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-download me-1"></i>
                                        Download
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Sem arquivo</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                Nenhum holerite encontrado.
                            </div>
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>
    </div>

    <button class="btn btn-primary btn-lg floating-action px-4" data-bs-toggle="modal" data-bs-target="#modalHolerite">
        <i class="bi bi-send-fill me-2"></i>
        Enviar Holerite
    </button>

</div>
</div>

<div class="modal fade" id="modalHolerite" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow">

            <form action="enviar_holerite.php" method="POST" enctype="multipart/form-data">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-send-fill me-2 text-primary"></i>
                        Enviar Holerite
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="form-label fw-semibold">Funcionário</label>

                    <select name="funcionario_id" class="form-select" required>
                        <option value="">Selecione</option>

                        <?php while($func = $funcionarios->fetch_assoc()): ?>
                            <option value="<?= $func['id_funcionario'] ?>">
                                <?= htmlspecialchars($func['nome']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label class="form-label fw-semibold mt-3">Competência</label>

                    <div class="row g-2">

                        <div class="col-md-6">
                            <select name="mes" class="form-select" required>
                                <option value="">Mês</option>
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

                        <div class="col-md-6">
                            <select name="ano" class="form-select" required>
                                <?php
                                $anoAtual = date('Y');
                                for ($i = $anoAtual + 1; $i >= $anoAtual - 5; $i--):
                                ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                    </div>

                    <label class="form-label fw-semibold mt-3">PDF do Holerite</label>

                    <input type="file" name="arquivo" class="form-control" accept="application/pdf,.pdf" required>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
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
const forms = document.querySelectorAll('form');

forms.forEach(form => {
    form.addEventListener('submit', () => {
        const botao = form.querySelector('button[type="submit"]');

        if (botao) {
            botao.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
            botao.disabled = true;
        }
    });
});
</script>

<script src="js/theme.js"></script>
<script src="js/translate.js"></script>
</body>
</html>