<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'auth.php';
require_once 'config/database.php';

$idEmpresa = $_SESSION['id_empresa'] ?? 0;

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_status'])) {

    $idOcorrencia = (int)($_POST['id_ocorrencia'] ?? 0);
    $status = $_POST['status'] ?? '';

    if (!$idOcorrencia) {
        die("ID da ocorrência inválido.");
    }

    if (!in_array($status, ['aberta', 'em_analise', 'resolvida'])) {
        die("Status inválido: " . htmlspecialchars($status));
    }

    $stmt = $con->prepare("
        UPDATE ocorrencias
        SET status = ?
        WHERE id_ocorrencia = ?
        AND id_empresa = ?
    ");

    $stmt->bind_param("sii", $status, $idOcorrencia, $idEmpresa);
    $stmt->execute();

    header("Location: emergencias.php?status_ok=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_ocorrencia'])) {

    $idOcorrencia = (int)($_POST['id_ocorrencia'] ?? 0);

    $stmt = $con->prepare("
        DELETE FROM ocorrencias
        WHERE id_ocorrencia = ?
        AND id_empresa = ?
    ");

    $stmt->bind_param("ii", $idOcorrencia, $idEmpresa);
    $stmt->execute();

    header("Location: emergencias.php?excluido=1");
    exit;
}

if (isset($_GET['status_ok'])) {
    $mensagem = "Status atualizado com sucesso.";
}

if (isset($_GET['excluido'])) {
    $mensagem = "Ocorrência excluída com sucesso.";
}

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

function textoStatus($status) {
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

function badgeStatus($status) {
    if ($status === 'resolvida') {
        return 'bg-success';
    }

    if ($status === 'em_analise') {
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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body class="bg-light">

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid py-4">

    <?php if($mensagem): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <?= htmlspecialchars($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($erro): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <?= htmlspecialchars($erro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="fw-bold text-primary mb-1">
                <i class="bi bi-shield-fill-check me-2"></i>
                Central de Emergências
            </h1>

            <p class="text-muted mb-0">
                Gerenciamento de ocorrências reportadas pelos colaboradores
            </p>
        </div>

        <span class="badge bg-primary rounded-pill px-4 py-3 fs-6">
            <?= $total ?> ocorrências
        </span>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <p class="text-muted mb-0">Abertas</p>
                    <h3 class="fw-bold"><?= $abertas ?></h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <p class="text-muted mb-0">Em análise</p>
                    <h3 class="fw-bold"><?= $analise ?></h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <p class="text-muted mb-0">Resolvidas</p>
                    <h3 class="fw-bold"><?= $resolvidas ?></h3>
                </div>
            </div>
        </div>

    </div>

    <?php if(count($ocorrencias) > 0): ?>

        <?php foreach($ocorrencias as $e): ?>

            <div class="card border-0 shadow-sm rounded-4 mb-4">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">

                        <div class="d-flex gap-2 flex-wrap">

                            <span class="badge bg-secondary rounded-pill px-3 py-2">
                                <?= htmlspecialchars($e['categoria']) ?>
                            </span>

                            <span class="badge <?= badgeStatus($e['status']) ?> rounded-pill px-3 py-2">
                                <?= textoStatus($e['status']) ?>
                            </span>

                        </div>

                        <small class="text-muted">
                            <?= date('d/m/Y H:i', strtotime($e['data_ocorrencia'])) ?>
                        </small>

                    </div>

                    <p class="text-muted">
                        <?= nl2br(htmlspecialchars($e['descricao'])) ?>
                    </p>

                    <div class="row g-3 mb-4">

                        <div class="col-md-3">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <small class="text-muted d-block">Reportado por</small>
                                <strong><?= htmlspecialchars($e['nome']) ?></strong>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <small class="text-muted d-block">Andar / Sala</small>
                                <strong><?= htmlspecialchars($e['andar']) ?></strong><br>
                                <small><?= htmlspecialchars($e['sala']) ?></small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <small class="text-muted d-block">Local</small>
                                <strong><?= htmlspecialchars($e['local_especifico']) ?></strong>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-light rounded-4 p-3 h-100">
                                <small class="text-muted d-block">Evidência</small>

                                <?php if(!empty($e['evidencia'])): ?>
                                    <a href="../<?= htmlspecialchars($e['evidencia']) ?>" target="_blank" class="btn btn-sm btn-primary">
                                        Ver arquivo
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nenhuma</span>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">

                        <form method="POST" class="d-flex gap-2">

                            <input type="hidden" name="id_ocorrencia" value="<?= $e['id_ocorrencia'] ?>">

                            <select name="status" class="form-select form-select-sm">
                                <option value="aberta" <?= $e['status'] === 'aberta' ? 'selected' : '' ?>>
                                    Aberta
                                </option>

                                <option value="em_analise" <?= $e['status'] === 'em_analise' ? 'selected' : '' ?>>
                                    Em análise
                                </option>

                                <option value="resolvida" <?= $e['status'] === 'resolvida' ? 'selected' : '' ?>>
                                    Resolvida
                                </option>
                            </select>

                            <button type="submit" name="atualizar_status" class="btn btn-primary btn-sm">
                                Salvar
                            </button>

                        </form>

                        <form method="POST" onsubmit="return confirm('Deseja excluir esta ocorrência?')">

                            <input type="hidden" name="id_ocorrencia" value="<?= $e['id_ocorrencia'] ?>">

                            <button type="submit" name="excluir_ocorrencia" class="btn btn-outline-danger btn-sm">
                                Excluir
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <h4 class="fw-bold">
                Nenhuma ocorrência encontrada
            </h4>

            <p class="text-muted">
                As ocorrências reportadas aparecerão aqui.
            </p>
        </div>

    <?php endif; ?>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/theme.js"></script>

</body>
</html>