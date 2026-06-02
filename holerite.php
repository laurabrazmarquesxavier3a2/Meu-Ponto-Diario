<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config/database.php';

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
</head>

<body class="bg-light">

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid py-4">

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success rounded-4">
            Holerite enviado com sucesso.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger rounded-4">
            <?= htmlspecialchars($_GET['erro']) ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="fw-bold">Envio de Holerite</h1>
            <p class="text-muted mb-0">Gerencie os holerites enviados aos funcionários</p>
        </div>

        <span class="badge bg-primary fs-6 rounded-pill px-4 py-3">
            <?= date('d/m/Y') ?>
        </span>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <h6 class="text-muted">Envio Pendente</h6>
                    <h1 class="fw-bold"><?= max(0, $pendentes) ?></h1>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <h6 class="text-muted">Enviados</h6>
                    <h1 class="fw-bold"><?= $enviados ?></h1>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <h6 class="text-muted">Funcionários</h6>
                    <h1 class="fw-bold"><?= $totalFuncionarios ?></h1>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">

            <form method="GET" class="row g-3">

                <div class="col-md-3">
                    <select name="mes" class="form-select rounded-4">
                        <option value="">Mês</option>
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
                    <input type="number" name="ano" class="form-control rounded-4" placeholder="Ano" value="<?= htmlspecialchars($ano) ?>">
                </div>

                <div class="col-md-3">
                    <button class="btn btn-primary w-100 rounded-pill">
                        <i class="bi bi-search me-2"></i>Filtrar
                    </button>
                </div>

                <div class="col-md-3">
                    <a href="holerite.php" class="btn btn-secondary w-100 rounded-pill">Limpar</a>
                </div>

            </form>

        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-table me-2"></i>Holerites
                </h5>

                <span class="badge bg-primary rounded-pill">
                    <?= $resultado->num_rows ?> registros
                </span>
            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
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

                            <tr>
                                <td>
                                    <i class="bi bi-person-circle text-primary me-2"></i>
                                    <?= htmlspecialchars($row['nome']) ?>
                                </td>

                                <td><?= htmlspecialchars($row['periodo']) ?></td>

                                <td><?= date('d/m/Y', strtotime($row['data_envio'])) ?></td>

                                <td>
                                    <?php if($row['status'] == 'pendente'): ?>
                                        <span class="badge rounded-pill bg-warning text-dark px-3 py-2">
                                            Pendente
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-success px-3 py-2">
                                            Enviado
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if (!empty($row['arquivo'])): ?>
                                        <a href="<?= htmlspecialchars($row['arquivo']) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                                            <i class="bi bi-download me-1"></i>Download
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Sem arquivo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Nenhum holerite encontrado.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button class="btn btn-primary btn-lg rounded-pill shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#modalHolerite">
            <i class="bi bi-send-fill me-2"></i>Enviar Holerite
        </button>
    </div>

</div>
</div>

<div class="modal fade" id="modalHolerite" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">

            <form action="enviar_holerite.php" method="POST" enctype="multipart/form-data">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Enviar Holerite</h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="form-label fw-semibold">Funcionário</label>

                    <select name="funcionario_id" class="form-select rounded-4" required>
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
                            <select name="mes" class="form-select rounded-4" required>
                                <option value="">Selecione o mês</option>
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
                            <select name="ano" class="form-select rounded-4" required>
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

                    <input type="file" name="arquivo" class="form-control rounded-4" accept="application/pdf,.pdf" required>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary rounded-pill">
                        <i class="bi bi-send-fill me-2"></i>Enviar
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
</body>
</html>