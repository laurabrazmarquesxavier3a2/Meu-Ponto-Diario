<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once 'auth.php';
require_once 'config/database.php';

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
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid">

    <h1 class="fw-bold">Licenças Médicas</h1>

    <h5 class="text-muted mb-4">
        Gerencie envios de atestados e licenças médicas da sua empresa
    </h5>

    <div class="row g-4 mb-4">

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">
                <h5>Licenças Ativas</h5>

                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $licencasAtivas ?>
                    <i class="bi bi-heart-pulse"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">
                <h5>Total de Submissões</h5>

                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $totalSubmissoes ?>
                    <i class="bi bi-file-earmark-medical"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card card-dashboard p-3 text-start">
                <h5>Visualizadas</h5>

                <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                    <?= $licencasVistas ?>
                    <i class="bi bi-eye"></i>
                </h1>
            </div>
        </div>

    </div>

    <div class="card card-dashboard p-3">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <h5 class="mb-0">Atestados e Licenças</h5>

            <input
                type="text"
                class="form-control"
                id="pesquisarLicenca"
                placeholder="Pesquisar funcionário, motivo ou período..."
                style="max-width:320px;"
            >

        </div>

        <div class="table-responsive">

            <table class="table table-hover mt-3 align-middle">

                <thead class="table-light">
                    <tr>
                        <th>Funcionário</th>
                        <th>Período</th>
                        <th>Dias</th>
                        <th>Motivo</th>
                        <th>Data Envio</th>
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
                        ?>

                        <tr>

                            <td>
                                <i class="bi bi-person me-2"></i>
                                <?= htmlspecialchars($licenca['nome']) ?>
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
                                    <span class="badge bg-success">Visualizado</span>

                                    <?php if(!empty($licenca['data_visto'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($licenca['data_visto'])) ?>
                                        </small>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                <?php endif; ?>
                            </td>

                            <td>
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
                                        class="btn btn-success btn-sm ms-2"
                                        onclick="return confirm('Marcar licença como visualizada?')"
                                    >
                                        <i class="bi bi-check-lg"></i>
                                        Visto
                                    </a>

                                <?php endif; ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Nenhuma licença enviada para esta empresa
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

pesquisa.addEventListener('keyup', function(){

    const termo = this.value.toLowerCase();

    linhas.forEach(function(linha){

        const texto = linha.innerText.toLowerCase();

        linha.style.display = texto.includes(termo) ? '' : 'none';

    });

});
</script>

<script src="js/theme.js"></script>

</body>
</html>