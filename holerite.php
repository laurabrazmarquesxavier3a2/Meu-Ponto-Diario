<?php
require_once 'auth.php';
require_once 'config/database.php';

/* 
>>>>>>> 9dc2a108882bb3ebf48c6c0cc8d7c62cda4f3645
   FILTRO MÊS / ANO
*/
$mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$ano = isset($_GET['ano']) ? $_GET['ano'] : '';

/* 
   HOLERITES (COM FILTRO)
*/
$sql = "
SELECT
    h.*,
    f.nome
FROM holerites h
INNER JOIN funcionarios f
ON h.funcionario_id = f.id_funcionario
WHERE 1=1
";

if (!empty($mes) && !empty($ano)) {
    $sql .= " AND MONTH(h.data_envio) = $mes AND YEAR(h.data_envio) = $ano ";
} elseif (!empty($mes)) {
    $sql .= " AND MONTH(h.data_envio) = $mes ";
} elseif (!empty($ano)) {
    $sql .= " AND YEAR(h.data_envio) = $ano ";
}

$sql .= " ORDER BY h.data_envio DESC";

$resultado = $con->query($sql);


$sqlPendentes = "
SELECT
(
    (SELECT COUNT(*)
    FROM funcionarios)

    -

    (SELECT COUNT(DISTINCT funcionario_id)
    FROM holerites
    WHERE status = 'enviado')
) AS total
";

$pendentes = $con
->query($sqlPendentes)
->fetch_assoc()['total'];

$enviados = $con->query("
SELECT COUNT(DISTINCT funcionario_id) AS total
FROM holerites
WHERE status = 'enviado'
")->fetch_assoc()['total'];

$totalFuncionarios = $con->query("
SELECT COUNT(*) as total
FROM funcionarios
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Holerite</title>

<link rel="stylesheet" href="css/style.css">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
rel="stylesheet">

</head>

<body class="bg-light">

<!-- LOADING -->
<div
id="loadingScreen"
class="position-fixed top-0 start-0 w-100 h-100 bg-white d-flex justify-content-center align-items-center"
style="z-index:9999;">

    <div
    class="spinner-border text-primary"
    style="width:4rem;height:4rem;">
    </div>

</div>

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>

            <h1 class="fw-bold">

                <i class=" text-primary me-2"></i>

                Envio de Holerite

            </h1>

            <p class="text-muted mb-0">
                Gerencie os holerites enviados aos funcionários
            </p>

        </div>
        
<span class="badge bg-primary fs-6 rounded-pill px-4 py-3">

    <?php
    date_default_timezone_set('America/Sao_Paulo');
    echo date('d/m/Y');
    ?>

</span>

    </div>

    <!-- ALERTA -->
    <div class="alert alert-primary d-flex align-items-center rounded-4 shadow-sm mb-4">

        <i class="bi bi-info-circle-fill fs-4 me-3"></i>

        <div>

            Utilize os filtros abaixo para localizar holerites rapidamente.

        </div>

    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <!-- PENDENTES -->
        <div class="col-12 col-md-4">

            <div class="card shadow-sm border-0 rounded-4 h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h6 class="text-muted">
                                Envio Pendente
                            </h6>

                            <h1 class="fw-bold">
                                <?= $pendentes ?>
                            </h1>

                        </div>

                        <i class="bi bi-clock-history fs-1 text-warning"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- ENVIADOS -->
        <div class="col-12 col-md-4">

            <div class="card shadow-sm border-0 rounded-4 h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h6 class="text-muted">
                                Enviados
                            </h6>

                            <h1 class="fw-bold">
                                <?= $enviados ?>
                            </h1>

                        </div>

                        <i class="bi bi-check-circle-fill fs-1 text-success"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- FUNCIONÁRIOS -->
        <div class="col-12 col-md-4">

            <div class="card shadow-sm border-0 rounded-4 h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <h6 class="text-muted">
                                Funcionários
                            </h6>

                            <h1 class="fw-bold">
                                <?= $totalFuncionarios ?>
                            </h1>

                        </div>

                        <i class="bi bi-people-fill fs-1 text-primary"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- FILTRO -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">

        <div class="card-body">

            <form method="GET" class="row g-3">

                <!-- MÊS -->
                <div class="col-md-3">

                    <select name="mes" class="form-select rounded-4">

                        <option value="">
                            Mês
                        </option>

                        <option value="1"  <?= $mes==1?'selected':'' ?>>Janeiro</option>
                        <option value="2"  <?= $mes==2?'selected':'' ?>>Fevereiro</option>
                        <option value="3"  <?= $mes==3?'selected':'' ?>>Março</option>
                        <option value="4"  <?= $mes==4?'selected':'' ?>>Abril</option>
                        <option value="5"  <?= $mes==5?'selected':'' ?>>Maio</option>
                        <option value="6"  <?= $mes==6?'selected':'' ?>>Junho</option>
                        <option value="7"  <?= $mes==7?'selected':'' ?>>Julho</option>
                        <option value="8"  <?= $mes==8?'selected':'' ?>>Agosto</option>
                        <option value="9"  <?= $mes==9?'selected':'' ?>>Setembro</option>
                        <option value="10" <?= $mes==10?'selected':'' ?>>Outubro</option>
                        <option value="11" <?= $mes==11?'selected':'' ?>>Novembro</option>
                        <option value="12" <?= $mes==12?'selected':'' ?>>Dezembro</option>

                    </select>

                </div>

                <!-- ANO -->
                <div class="col-md-3">

                    <input
                    type="number"
                    name="ano"
                    class="form-control rounded-4"
                    placeholder="Ano"
                    value="<?= $ano ?>">

                </div>

                <!-- FILTRAR -->
                <div class="col-md-3">

                    <button class="btn btn-primary w-100 rounded-pill">

                        <i class="bi bi-search me-2"></i>

                        Filtrar

                    </button>

                </div>

                <!-- LIMPAR -->
                <div class="col-md-3">

                    <a
                    href="holerite.php"
                    class="btn btn-secondary w-100 rounded-pill">

                        Limpar

                    </a>

                </div>

            </form>

        </div>

    </div>

    <!-- TABELA -->
    <div class="card shadow-sm border-0 rounded-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-table me-2"></i>

                    Solicitações

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

                    <?php while($row = $resultado->fetch_assoc()) { ?>

                        <tr>

                            <td>

                                <i class="bi bi-person-circle text-primary me-2"></i>

                                <?= $row['nome'] ?>

                            </td>

                            <td>

                                <?= $row['periodo'] ?>

                            </td>

                            <td>

                                <?= date('d/m/Y', strtotime($row['data_envio'])) ?>

                            </td>

                            <td>

                                <?php if($row['status'] == 'pendente'){ ?>

                                    <span class="badge rounded-pill bg-warning text-dark px-3 py-2">

                                        <i class="bi bi-clock-fill me-1"></i>

                                        Pendente

                                    </span>

                                <?php } else { ?>

                                    <span class="badge rounded-pill bg-success px-3 py-2">

                                        <i class="bi bi-check-circle-fill me-1"></i>

                                        Enviado

                                    </span>

                                <?php } ?>

                            </td>

                            <td>

                                <a
                                href="<?= $row['arquivo'] ?>"
                                target="_blank"
                                class="btn btn-outline-primary btn-sm rounded-pill"
                                data-bs-toggle="tooltip"
                                title="Baixar arquivo">

                                    <i class="bi bi-download me-1"></i>

                                    Download

                                </a>

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- BOTÃO FINAL -->
    <div class="d-flex justify-content-end mt-4">

        <button
        class="btn btn-primary btn-lg rounded-pill shadow-sm px-4"
        data-bs-toggle="modal"
        data-bs-target="#modalHolerite">

            <i class="bi bi-send-fill me-2"></i>

            Enviar Holerite

        </button>

    </div>

</div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalHolerite" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content rounded-4 border-0 shadow">

            <form
            action="enviar_holerite.php"
            method="POST"
            enctype="multipart/form-data">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">

                        <i class="bi bi-send-fill text-primary me-2"></i>

                        Enviar Holerite

                    </h5>

                    <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <!-- FUNCIONÁRIO -->
                    <label class="form-label fw-semibold">

                        Funcionário

                    </label>

                    <select
                    name="funcionario_id"
                    class="form-select rounded-4"
                    required>

                        <option value="">
                            Selecione
                        </option>

                        <?php
                        $funcionarios = $con->query("
                        SELECT * FROM funcionarios ORDER BY nome
                        ");

                        while($func = $funcionarios->fetch_assoc()){
                        ?>

                            <option value="<?= $func['id_funcionario'] ?>">

                                <?= $func['nome'] ?>

                            </option>

                        <?php } ?>

                    </select>

                    <!-- COMPETÊNCIA -->
                    <label class="form-label fw-semibold mt-3">

                        Competência

                    </label>

                    <div class="row g-2">

                        <!-- MÊS -->
                        <div class="col-md-6">

                            <select
                            name="mes"
                            class="form-select rounded-4"
                            required>

                                <option value="">
                                    Selecione o mês
                                </option>

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

                        <!-- ANO -->
                        <div class="col-md-6">

                            <select
                            name="ano"
                            class="form-select rounded-4"
                            required>

                                <?php
                                $anoAtual = date('Y');

                                for ($i = $anoAtual + 1; $i >= $anoAtual - 5; $i--) {
                                ?>

                                    <option value="<?= $i ?>">

                                        <?= $i ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                    </div>

                    <!-- PDF -->
                    <label class="form-label fw-semibold mt-3">

                        PDF do Holerite

                    </label>

                    <input
                    type="file"
                    name="arquivo"
                    class="form-control rounded-4"
                    accept=".pdf"
                    required>

                </div>

                <div class="modal-footer">

                    <button
                    type="button"
                    class="btn btn-secondary rounded-pill"
                    data-bs-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                    type="submit"
                    class="btn btn-primary rounded-pill">

                        <i class="bi bi-send-fill me-2"></i>

                        Enviar

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- TOAST -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">

    <div
    id="toastSistema"
    class="toast align-items-center border-0 text-bg-success"
    role="alert">

        <div class="d-flex">

            <div class="toast-body">

                <i class="bi bi-check-circle-fill me-2"></i>

                Painel carregado com sucesso!

            </div>

            <button
            type="button"
            class="btn-close btn-close-white me-2 m-auto"
            data-bs-dismiss="toast">
            </button>

        </div>

    </div>

</div>

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script>

/* LOADING */
window.addEventListener('load', () => {

    document.getElementById('loadingScreen')
    .classList.add('d-none');

});

/* TOAST */
const toastElement =
document.getElementById('toastSistema');

const toast =
new bootstrap.Toast(toastElement);

toast.show();

/* TOOLTIP */
const tooltipTriggerList =
document.querySelectorAll('[data-bs-toggle="tooltip"]');

[...tooltipTriggerList].map(tooltipTriggerEl =>
    new bootstrap.Tooltip(tooltipTriggerEl)
);

/* BOTÃO ENVIAR */
const form =
document.querySelector('form');

form.addEventListener('submit', () => {

    const botao =
    form.querySelector('button[type="submit"]');

    botao.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Enviando...
    `;

    botao.disabled = true;

});

</script>

</body>
</html>