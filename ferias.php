<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config/database.php';

if (file_exists('registrar-atividade.php')) {
    require_once 'registrar-atividade.php';
}

$id_empresa = $_SESSION['id_empresa'] ?? 0;

if (!$id_empresa) {
    die("Erro: empresa não identificada. Faça login novamente.");
}

$mensagem = '';
$erro = '';

/* AÇÕES */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_ferias = intval($_POST['id_ferias'] ?? 0);

    if (!$id_ferias) {
        $erro = "Pedido inválido.";
    }

    /* MARCAR COMO VISTO */
    if (!$erro && isset($_POST['marcar_visto'])) {

        $msg = "Sua solicitação de férias foi visualizada pelo RH.";

        $stmt = $con->prepare("
            UPDATE ferias
            SET 
                status = 'visto',
                data_visto = NOW(),
                mensagem_colaborador = ?
            WHERE id_ferias = ?
            AND id_empresa = ?
            AND status = 'pendente'
        ");

        $stmt->bind_param("sii", $msg, $id_ferias, $id_empresa);

        if ($stmt->execute()) {
            $mensagem = "Solicitação marcada como vista.";
        } else {
            $erro = "Erro ao marcar como visto.";
        }
    }

    /* APROVAR */
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

    /* REJEITAR */
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

    if ($status == 'pendente') {
        return '<span class="badge bg-warning text-dark">Pendente</span>';
    }

    if ($status == 'visto') {
        return '<span class="badge bg-info text-dark">Visto</span>';
    }

    if ($status == 'aprovado') {
        return '<span class="badge bg-success">Aprovado</span>';
    }

    if ($status == 'rejeitado') {
        return '<span class="badge bg-danger">Rejeitado</span>';
    }

    return '<span class="badge bg-secondary">Indefinido</span>';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Pedidos de férias</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
.card-dashboard{
    border-radius:16px;
    border:0;
    box-shadow:0 6px 18px rgba(0,0,0,.06);
}

.modal{
    z-index:99999 !important;
}

.modal-backdrop{
    z-index:99998 !important;
}
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid">

    <h1 class="fw-bold">Pedidos de férias</h1>
    <h5 class="text-muted mb-4">
        Gerencie solicitações de férias dos funcionários
    </h5>

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

    <div class="row g-4 mb-4">

        <div class="col-12 col-md-3">
            <div class="card card-dashboard p-3 text-start">
                <h6 class="text-muted">Pendentes</h6>
                <h1 class="fw-bold d-flex justify-content-between align-items-center">
                    <?= $pendentes ?>
                    <i class="bi bi-hourglass-split text-warning"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card card-dashboard p-3 text-start">
                <h6 class="text-muted">Vistos</h6>
                <h1 class="fw-bold d-flex justify-content-between align-items-center">
                    <?= $vistos ?>
                    <i class="bi bi-eye text-info"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card card-dashboard p-3 text-start">
                <h6 class="text-muted">Aprovados</h6>
                <h1 class="fw-bold d-flex justify-content-between align-items-center">
                    <?= $aprovados ?>
                    <i class="bi bi-check-circle text-success"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card card-dashboard p-3 text-start">
                <h6 class="text-muted">Rejeitados</h6>
                <h1 class="fw-bold d-flex justify-content-between align-items-center">
                    <?= $rejeitados ?>
                    <i class="bi bi-x-circle text-danger"></i>
                </h1>
            </div>
        </div>

    </div>

    <div class="card card-dashboard p-3">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <h5 class="mb-0">Solicitações</h5>

            <input
                type="text"
                class="form-control"
                id="buscarFerias"
                placeholder="Pesquisar por funcionário ou status..."
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
                        <th>Data Solicitação</th>
                        <th>Status</th>
                        <th>Mensagem</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>

                <tbody id="tabelaFerias">

                <?php if ($query->num_rows > 0): ?>

                    <?php while ($ferias = $query->fetch_assoc()): ?>

                        <tr>
                            <td>
                                <i class="bi bi-person me-2"></i>
                                <?= htmlspecialchars($ferias['nome']) ?>
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
                                <small>
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
                class="btn btn-success btn-sm"
                onclick="return confirm('Aprovar esta solicitação?')"
            >
                <i class="bi bi-check-lg"></i>
                Aprovar
            </button>
        </form>

        <button
            type="button"
            class="btn btn-danger btn-sm"
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
                        <td colspan="7" class="text-center text-muted py-4">
                            Nenhuma solicitação de férias encontrada.
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</div>

<!-- MODAL REJEITAR -->
<div class="modal fade" id="modalRejeitar" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <form method="POST" class="modal-content border-0 shadow">

            <div class="modal-header bg-danger text-white">

                <h5 class="modal-title">
                    <i class="bi bi-x-circle me-2"></i>
                    Rejeitar solicitação
                </h5>

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
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    name="rejeitar"
                    class="btn btn-danger"
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

buscar.addEventListener('keyup', function(){

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