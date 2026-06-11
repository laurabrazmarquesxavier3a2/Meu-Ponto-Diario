<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

/* MARCAR COMO VISTO */
if (isset($_GET['visto'])) {

    $idLicenca = (int) $_GET['visto'];

    $mensagem = "Sua licença médica foi visualizada pelo RH.";

    $stmtVisto = $con->prepare("
        UPDATE licencas_medicas
        SET
            status = 'visto',
            data_visto = NOW(),
            mensagem_colaborador = ?
        WHERE id = ?
        AND id_empresa = ?
    ");

    if (!$stmtVisto) {
        die("Erro SQL visto: " . $con->error);
    }

    $stmtVisto->bind_param("sii", $mensagem, $idLicenca, $idEmpresa);
    $stmtVisto->execute();

    header("Location: licenca.php");
    exit;
}

/* BUSCAR LICENÇAS DA EMPRESA */
$sql = "
SELECT
    lm.*,
    f.nome
FROM licencas_medicas lm
INNER JOIN funcionarios f
    ON lm.id_funcionario = f.id_funcionario
    AND lm.id_empresa = f.id_empresa
WHERE lm.id_empresa = ?
ORDER BY lm.data_envio DESC
";

$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Erro SQL licenças: " . $con->error);
}

$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();

/* TOTAL */
$stmtTotal = $con->prepare("
    SELECT COUNT(*) AS total
    FROM licencas_medicas
    WHERE id_empresa = ?
");

$stmtTotal->bind_param("i", $idEmpresa);
$stmtTotal->execute();
$totalSubmissoes = $stmtTotal->get_result()->fetch_assoc()['total'] ?? 0;

/* ATIVAS */
$stmtAtivas = $con->prepare("
    SELECT COUNT(*) AS total
    FROM licencas_medicas
    WHERE id_empresa = ?
    AND CURDATE() BETWEEN data_inicio AND data_fim
");

$stmtAtivas->bind_param("i", $idEmpresa);
$stmtAtivas->execute();
$licencasAtivas = $stmtAtivas->get_result()->fetch_assoc()['total'] ?? 0;

/* VISTAS */
$stmtVistas = $con->prepare("
    SELECT COUNT(*) AS total
    FROM licencas_medicas
    WHERE id_empresa = ?
    AND status = 'visto'
");

$stmtVistas->bind_param("i", $idEmpresa);
$stmtVistas->execute();
$licencasVistas = $stmtVistas->get_result()->fetch_assoc()['total'] ?? 0;

$pendentes = max(0, $totalSubmissoes - $licencasVistas);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Licença Médica</title>

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
    --shadow: 0 12px 30px rgba(15, 23, 42, .07);
}

body {
    background: var(--bg);
    color: var(--text);
}

.content {
    min-height: 100vh;
    padding: 32px;
}

.page-header,
.kpi-card,
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

.table-card {
    border-radius: 22px;
    overflow: hidden;
}

.table-card-header {
    padding: 22px;
    background: var(--card-soft);
    border-bottom: 1px solid var(--border);
}

.table-card-header h5 {
    color: var(--text);
    font-weight: 850;
    margin-bottom: 4px;
}

.table-card-header p {
    color: var(--muted);
    margin-bottom: 0;
    font-size: 14px;
}

.search-input {
    border-radius: 14px;
    min-height: 44px;
    border-color: var(--border);
    background: var(--card);
    color: var(--text);
}

.search-input::placeholder {
    color: var(--muted);
}

.search-input:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 .22rem rgba(37, 99, 235, .12);
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

.badge-visto {
    background: #dcfce7;
    color: #166534;
}

.badge-pendente {
    background: #fef3c7;
    color: #92400e;
}

.btn {
    border-radius: 14px;
    font-weight: 700;
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

body.dark .search-input,
body.dark-mode .search-input {
    background: #0b1220;
    color: #f8fafc;
    border-color: #334155;
}

body.dark .search-input::placeholder,
body.dark-mode .search-input::placeholder {
    color: #94a3b8;
}

body.dark .badge-visto,
body.dark-mode .badge-visto {
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
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid">

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="page-title">Licenças Médicas</h1>
                <p class="page-subtitle">
                    Gerencie atestados, períodos de afastamento e visualizações pelo RH.
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
                        <div class="kpi-label">Licenças ativas</div>
                        <h2 class="kpi-value"><?= (int)$licencasAtivas ?></h2>
                        <div class="kpi-small">Afastamentos em andamento</div>
                    </div>

                    <div class="kpi-icon">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Total de submissões</div>
                        <h2 class="kpi-value"><?= (int)$totalSubmissoes ?></h2>
                        <div class="kpi-small">Atestados enviados</div>
                    </div>

                    <div class="kpi-icon">
                        <i class="bi bi-file-earmark-medical"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Pendentes</div>
                        <h2 class="kpi-value"><?= (int)$pendentes ?></h2>
                        <div class="kpi-small">Aguardando visualização</div>
                    </div>

                    <div class="kpi-icon">
                        <i class="bi bi-eye"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="table-card">

        <div class="table-card-header">
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <h5>Atestados e Licenças</h5>
                    <p>Consulte os envios feitos pelos colaboradores.</p>
                </div>

                <div class="col-lg-5">
                    <input
                        type="text"
                        class="form-control search-input"
                        id="pesquisarLicenca"
                        placeholder="Pesquisar funcionário, motivo ou período..."
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
                        <th>Motivo</th>
                        <th>Envio</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody id="tabelaLicencas">

                <?php if($result->num_rows > 0): ?>

                    <?php while($licenca = $result->fetch_assoc()): ?>

                        <?php
                        $arquivo = $licenca['arquivo_atestado'] ?? '';

                        if (!empty($arquivo)) {
                            $arquivoLink = str_replace('../', '', $arquivo);
                        } else {
                            $arquivoLink = '';
                        }

                        $status = $licenca['status'] ?? 'pendente';
                        $inicial = mb_strtoupper(mb_substr($licenca['nome'], 0, 1, 'UTF-8'), 'UTF-8');
                        ?>

                        <tr>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="employee-avatar">
                                        <?= htmlspecialchars($inicial) ?>
                                    </div>

                                    <span class="employee-name">
                                        <?= htmlspecialchars($licenca['nome']) ?>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <?= date('d/m/Y', strtotime($licenca['data_inicio'])) ?>
                                -
                                <?= date('d/m/Y', strtotime($licenca['data_fim'])) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($licenca['dias']) ?> dias
                            </td>

                            <td>
                                <?= htmlspecialchars($licenca['motivo']) ?>
                            </td>

                            <td>
                                <?= date('d/m/Y H:i', strtotime($licenca['data_envio'])) ?>
                            </td>

                            <td>
                                <?php if($status == 'visto'): ?>
                                    <span class="badge-status badge-visto">
                                        Visualizado
                                    </span>

                                    <?php if(!empty($licenca['data_visto'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($licenca['data_visto'])) ?>
                                        </small>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <span class="badge-status badge-pendente">
                                        Pendente
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex gap-2 flex-wrap">

                                    <?php if(!empty($arquivoLink)): ?>

                                        <a
                                            href="<?= htmlspecialchars($arquivoLink) ?>"
                                            target="_blank"
                                            class="btn btn-outline-primary btn-sm"
                                        >
                                            <i class="bi bi-eye me-1"></i>
                                            Ver atestado
                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">Sem arquivo</span>

                                    <?php endif; ?>

                                    <?php if($status != 'visto'): ?>

                                        <a
                                            href="licenca.php?visto=<?= $licenca['id'] ?>"
                                            class="btn btn-success btn-sm"
                                            onclick="return confirm('Marcar licença como visualizada?')"
                                        >
                                            <i class="bi bi-check-lg"></i>
                                            Visto
                                        </a>

                                    <?php endif; ?>

                                </div>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                Nenhuma licença enviada para esta empresa.
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

<script>
const pesquisa = document.getElementById('pesquisarLicenca');
const linhas = document.querySelectorAll('#tabelaLicencas tr');

if (pesquisa) {
    pesquisa.addEventListener('keyup', function(){

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