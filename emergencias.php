<?php
require_once 'auth.php';
require_once 'config/database.php';

$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

$mensagem = '';
$erro = '';

/* ATUALIZAR STATUS */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['atualizar_status'])) {

    $idOcorrencia = intval($_POST['id_ocorrencia']);
    $status = $_POST['status_ocorrencia'];

    $stmt = $con->prepare("
        UPDATE ocorrencias
        SET status_ocorrencia = ?
        WHERE id_ocorrencia = ?
        AND id_empresa = ?
    ");

    $stmt->bind_param("sii", $status, $idOcorrencia, $idEmpresa);

    if ($stmt->execute()) {
        $mensagem = "Status da ocorrência atualizado.";
    } else {
        $erro = "Erro ao atualizar ocorrência.";
    }
}

/* EXCLUIR */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['excluir_ocorrencia'])) {

    $idOcorrencia = intval($_POST['id_ocorrencia']);

    $stmt = $con->prepare("
        DELETE FROM ocorrencias
        WHERE id_ocorrencia = ?
        AND id_empresa = ?
    ");

    $stmt->bind_param("ii", $idOcorrencia, $idEmpresa);

    if ($stmt->execute()) {
        $mensagem = "Ocorrência excluída com sucesso.";
    } else {
        $erro = "Erro ao excluir ocorrência.";
    }
}

/* LISTAGEM */
$stmt = $con->prepare("
    SELECT *
    FROM ocorrencias
    WHERE id_empresa = ?
    ORDER BY data_ocorrencia DESC
");

$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$result = $stmt->get_result();

$ocorrencias = [];
$total = 0;
$abertas = 0;
$andamento = 0;
$resolvidas = 0;
$criticas = 0;

while ($row = $result->fetch_assoc()) {
    $ocorrencias[] = $row;
    $total++;

    if ($row['status_ocorrencia'] == 'Aberto') {
        $abertas++;
    }

    if ($row['status_ocorrencia'] == 'Em andamento') {
        $andamento++;
    }

    if ($row['status_ocorrencia'] == 'Resolvido') {
        $resolvidas++;
    }

    if ($row['nivel_risco'] == 'Crítico') {
        $criticas++;
    }
}

function badgeNivel($nivel) {
    if ($nivel == 'Crítico') {
        return 'bg-danger';
    }

    if ($nivel == 'Alto') {
        return 'bg-warning text-dark';
    }

    if ($nivel == 'Médio') {
        return 'bg-info text-dark';
    }

    return 'bg-secondary';
}

function badgeStatus($status) {
    if ($status == 'Resolvido') {
        return 'bg-success';
    }

    if ($status == 'Em andamento') {
        return 'bg-warning text-dark';
    }

    return 'bg-primary';
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Emergências</title>

<link rel="stylesheet" href="css/style.css">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
rel="stylesheet">

<style>
.card-emergencia{
    border-radius:18px;
    transition:.25s;
}

.card-emergencia:hover{
    transform:translateY(-3px);
}

.icon-card{
    width:52px;
    height:52px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.ocorrencia-critica{
    border-left:6px solid #dc3545 !important;
}

.ocorrencia-alta{
    border-left:6px solid #ffc107 !important;
}

.ocorrencia-media{
    border-left:6px solid #0dcaf0 !important;
}
</style>

</head>

<body class="bg-light">

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid py-4">

    <?php if($mensagem): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($erro): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $erro ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- HEADER -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">

        <div>
            <h1 class="fw-bold text-primary mb-1">
                <i class="bi bi-shield-fill-check me-2"></i>
                Central de Emergências
            </h1>

            <p class="text-muted mb-0">
                Gerenciamento rápido de ocorrências reportadas
            </p>
        </div>

        <span class="badge bg-primary rounded-pill px-4 py-3 fs-6 mt-3 mt-lg-0">
            <?= $total ?> ocorrências
        </span>

    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-primary text-white fs-4">
                        <i class="bi bi-list-check"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Total</p>
                        <h3 class="fw-bold mb-0"><?= $total ?></h3>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-danger text-white fs-4">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Críticas</p>
                        <h3 class="fw-bold mb-0"><?= $criticas ?></h3>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-warning text-dark fs-4">
                        <i class="bi bi-hourglass-split"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Em andamento</p>
                        <h3 class="fw-bold mb-0"><?= $andamento ?></h3>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-success text-white fs-4">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Resolvidas</p>
                        <h3 class="fw-bold mb-0"><?= $resolvidas ?></h3>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-4">

                    <div class="input-group">

                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="text"
                            class="form-control border-start-0"
                            placeholder="Pesquisar ocorrência..."
                            id="pesquisa">

                    </div>

                </div>

                <div class="col-md-4">

                    <select class="form-select" id="filtroStatus">
                        <option value="todos">Todos os status</option>
                        <option value="Aberto">Aberto</option>
                        <option value="Em andamento">Em andamento</option>
                        <option value="Resolvido">Resolvido</option>
                    </select>

                </div>

                <div class="col-md-4">

                    <select class="form-select" id="filtroNivel">
                        <option value="todos">Todos os níveis</option>
                        <option value="Crítico">Crítico</option>
                        <option value="Alto">Alto</option>
                        <option value="Médio">Médio</option>
                    </select>

                </div>

            </div>

        </div>

    </div>

    <!-- LISTA -->
    <div id="listaOcorrencias">

    <?php if(count($ocorrencias) > 0): ?>

        <?php foreach($ocorrencias as $e): ?>

            <?php
            $classeRisco = '';

            if ($e['nivel_risco'] == 'Crítico') {
                $classeRisco = 'ocorrencia-critica';
            } elseif ($e['nivel_risco'] == 'Alto') {
                $classeRisco = 'ocorrencia-alta';
            } elseif ($e['nivel_risco'] == 'Médio') {
                $classeRisco = 'ocorrencia-media';
            }
            ?>

            <div
                class="card border-0 shadow-sm rounded-4 mb-4 ocorrencia card-emergencia <?= $classeRisco ?>"
                data-status="<?= htmlspecialchars($e['status_ocorrencia']) ?>"
                data-nivel="<?= htmlspecialchars($e['nivel_risco']) ?>"
                data-texto="<?= strtolower(htmlspecialchars(
                    $e['categoria'] . ' ' .
                    $e['descricao'] . ' ' .
                    $e['nome'] . ' ' .
                    $e['andar'] . ' ' .
                    $e['sala'] . ' ' .
                    $e['local_especifico']
                )) ?>"
            >

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">

                        <div class="d-flex gap-2 flex-wrap">

                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                <?= htmlspecialchars($e['categoria']) ?>
                            </span>

                            <span class="badge <?= badgeNivel($e['nivel_risco']) ?> rounded-pill px-3 py-2">
                                <?= htmlspecialchars($e['nivel_risco']) ?>
                            </span>

                            <span class="badge <?= badgeStatus($e['status_ocorrencia']) ?> rounded-pill px-3 py-2">
                                <?= htmlspecialchars($e['status_ocorrencia']) ?>
                            </span>

                        </div>

                        <small class="text-muted">
                            <i class="bi bi-clock me-1"></i>
                            <?= date('d/m/Y H:i', strtotime($e['data_ocorrencia'])) ?>
                        </small>

                    </div>

                    <p class="text-muted mb-4">
                        <?= nl2br(htmlspecialchars($e['descricao'])) ?>
                    </p>

                    <div class="row g-3 mb-4">

                        <div class="col-md-3">

                            <div class="bg-light rounded-4 p-3 h-100">

                                <small class="text-muted d-block mb-1">
                                    Reportado por
                                </small>

                                <strong>
                                    <?php if($e['tipo_reporte'] == 'Anônimo'): ?>
                                        <i class="bi bi-incognito me-1"></i>
                                    <?php endif; ?>

                                    <?= htmlspecialchars($e['nome']) ?>
                                </strong>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="bg-light rounded-4 p-3 h-100">

                                <small class="text-muted d-block mb-1">
                                    Andar / Sala
                                </small>

                                <strong>
                                    <?= htmlspecialchars($e['andar']) ?>
                                </strong>

                                <br>

                                <small>
                                    <?= htmlspecialchars($e['sala']) ?>
                                </small>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="bg-light rounded-4 p-3 h-100">

                                <small class="text-muted d-block mb-1">
                                    Local
                                </small>

                                <strong>
                                    <?= htmlspecialchars($e['local_especifico']) ?>
                                </strong>

                            </div>

                        </div>

                        <div class="col-md-3">

                            <div class="bg-light rounded-4 p-3 h-100">

                                <small class="text-muted d-block mb-1">
                                    Evidência
                                </small>

                                <?php if(!empty($e['evidencia'])): ?>

                                    <a
                                        href="uploads/ocorrencias/<?= htmlspecialchars($e['evidencia']) ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-primary"
                                    >
                                        <i class="bi bi-paperclip me-1"></i>
                                        Ver arquivo
                                    </a>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        Nenhuma
                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                    <div class="accordion mb-4" id="accordion<?= $e['id_ocorrencia'] ?>">

                        <div class="accordion-item border rounded-4">

                            <h2 class="accordion-header">

                                <button
                                    class="accordion-button collapsed rounded-4"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#detalhes<?= $e['id_ocorrencia'] ?>"
                                >
                                    Mais detalhes
                                </button>

                            </h2>

                            <div
                                id="detalhes<?= $e['id_ocorrencia'] ?>"
                                class="accordion-collapse collapse"
                            >

                                <div class="accordion-body">

                                    <div class="row g-3">

                                        <div class="col-md-6">

                                            <strong>Testemunhas</strong>

                                            <p class="text-muted mb-0 mt-1">
                                                <?= htmlspecialchars($e['testemunhas'] ?? 'Nenhuma') ?>
                                            </p>

                                        </div>

                                        <div class="col-md-6">

                                            <strong>Status atual</strong>

                                            <p class="text-muted mb-0 mt-1">
                                                <?= htmlspecialchars($e['status_ocorrencia']) ?>
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                        <form method="POST" class="d-flex gap-2">

                            <input
                                type="hidden"
                                name="id_ocorrencia"
                                value="<?= $e['id_ocorrencia'] ?>"
                            >

                            <select
                                name="status_ocorrencia"
                                class="form-select form-select-sm"
                            >
                                <option value="Aberto" <?= $e['status_ocorrencia'] == 'Aberto' ? 'selected' : '' ?>>
                                    Aberto
                                </option>

                                <option value="Em andamento" <?= $e['status_ocorrencia'] == 'Em andamento' ? 'selected' : '' ?>>
                                    Em andamento
                                </option>

                                <option value="Resolvido" <?= $e['status_ocorrencia'] == 'Resolvido' ? 'selected' : '' ?>>
                                    Resolvido
                                </option>
                            </select>

                            <button
                                type="submit"
                                name="atualizar_status"
                                class="btn btn-primary btn-sm"
                            >
                                <i class="bi bi-check-lg"></i>
                            </button>

                        </form>

                        <form
                            method="POST"
                            onsubmit="return confirm('Deseja excluir esta ocorrência?')"
                        >

                            <input
                                type="hidden"
                                name="id_ocorrencia"
                                value="<?= $e['id_ocorrencia'] ?>"
                            >

                            <button
                                type="submit"
                                name="excluir_ocorrencia"
                                class="btn btn-outline-danger btn-sm"
                            >
                                <i class="bi bi-trash me-1"></i>
                                Excluir
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">

            <i class="bi bi-shield-slash display-1 text-secondary"></i>

            <h4 class="fw-bold mt-3">
                Nenhuma ocorrência encontrada
            </h4>

            <p class="text-muted">
                As ocorrências reportadas aparecerão aqui.
            </p>

        </div>

    <?php endif; ?>

    </div>

</div>

</div>

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script>
const filtroStatus = document.getElementById('filtroStatus');
const filtroNivel = document.getElementById('filtroNivel');
const pesquisa = document.getElementById('pesquisa');
const ocorrencias = document.querySelectorAll('.ocorrencia');

function filtrarOcorrencias(){

    const status = filtroStatus.value;
    const nivel = filtroNivel.value;
    const texto = pesquisa.value.toLowerCase();

    ocorrencias.forEach(card => {

        const cardStatus = card.dataset.status;
        const cardNivel = card.dataset.nivel;
        const cardTexto = card.dataset.texto;

        let mostrar = true;

        if(status !== 'todos' && cardStatus !== status){
            mostrar = false;
        }

        if(nivel !== 'todos' && cardNivel !== nivel){
            mostrar = false;
        }

        if(!cardTexto.includes(texto)){
            mostrar = false;
        }

        card.style.display = mostrar ? 'block' : 'none';

    });

}

filtroStatus.addEventListener('change', filtrarOcorrencias);
filtroNivel.addEventListener('change', filtrarOcorrencias);
pesquisa.addEventListener('keyup', filtrarOcorrencias);
</script>
<script src="js/theme.js"></script>
</body>
</html>