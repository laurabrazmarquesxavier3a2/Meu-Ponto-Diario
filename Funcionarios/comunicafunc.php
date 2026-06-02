<?php
require_once '../auth.php';
require_once '../config/database.php';

$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

$stmt = $con->prepare("
    SELECT *
    FROM comunicados
    WHERE id_empresa = ?
    ORDER BY fixado DESC, data_publicacao DESC
");

$stmt->bind_param("i", $idEmpresa);
$stmt->execute();

$comunicados = $stmt->get_result();

function badgeCategoria($categoria) {

    if ($categoria == 'Urgente') {
        return 'bg-danger';
    }

    if ($categoria == 'Evento') {
        return 'bg-success';
    }

    if ($categoria == 'Comemoração') {
        return 'bg-warning text-dark';
    }

    if ($categoria == 'Política') {
        return 'bg-primary';
    }

    if ($categoria == 'Aviso') {
        return 'bg-info text-dark';
    }

    return 'bg-secondary';
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Comunicados</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link
rel="stylesheet"
href="../css/global.css">

<style>
.comunicado-card{
    border:0;
    border-radius:18px;
    box-shadow:0 8px 24px rgba(0,0,0,.06);
    transition:.25s;
}

.comunicado-card:hover{
    transform:translateY(-3px);
}

.comunicado-fixado{
    border-left:6px solid #f59e0b;
    background:#fff9e6;
}

.icon-empty{
    width:90px;
    height:90px;
    border-radius:50%;
    background:#eaf2ff;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:auto;
}
</style>

</head>

<body>

<?php include 'sidebarfunc.php'; ?>

<div class="content">

    <div class="title mb-4">

        <h1>
            Comunicados
        </h1>

        <p>
            Acompanhe os avisos importantes da sua empresa
        </p>

    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body">

            <div class="input-group">

                <span class="input-group-text bg-white">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>

                <input
                    type="text"
                    id="buscarComunicado"
                    class="form-control"
                    placeholder="Pesquisar comunicados..."
                >

            </div>

        </div>

    </div>

    <div class="row g-4" id="listaComunicados">

        <?php if($comunicados->num_rows > 0): ?>

            <?php while($com = $comunicados->fetch_assoc()): ?>

                <div
                    class="col-12 comunicado-item"
                    data-texto="<?= strtolower(htmlspecialchars($com['titulo'] . ' ' . $com['conteudo'] . ' ' . $com['categoria'] . ' ' . $com['autor'])) ?>"
                >

                    <div class="card comunicado-card p-4 <?= $com['fixado'] ? 'comunicado-fixado' : '' ?>">

                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">

                            <div class="d-flex flex-wrap gap-2">

                                <span class="badge <?= badgeCategoria($com['categoria']) ?> px-3 py-2 rounded-pill">
                                    <?= htmlspecialchars($com['categoria'] ?? 'Aviso') ?>
                                </span>

                                <?php if($com['fixado']): ?>

                                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                        <i class="fa-solid fa-thumbtack me-1"></i>
                                        Fixado
                                    </span>

                                <?php endif; ?>

                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                    <?= htmlspecialchars($com['publico'] ?? 'Todos') ?>
                                </span>

                            </div>

                            <small class="text-muted">
                                <i class="fa-regular fa-clock me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($com['data_publicacao'])) ?>
                            </small>

                        </div>

                        <h4 class="fw-bold mb-3">
                            <?= htmlspecialchars($com['titulo']) ?>
                        </h4>

                        <p class="text-muted mb-4">
                            <?= nl2br(htmlspecialchars($com['conteudo'])) ?>
                        </p>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                            <small class="text-secondary">
                                <i class="fa-solid fa-user-tie me-1"></i>
                                Publicado por <?= htmlspecialchars($com['autor'] ?? 'RH') ?>
                            </small>

                            <?php if($com['fixado']): ?>
                                <small class="text-warning fw-bold">
                                    <i class="fa-solid fa-star me-1"></i>
                                    Prioritário
                                </small>
                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">

                    <div class="icon-empty mb-3">

                        <i class="fa-regular fa-bell-slash text-primary display-5"></i>

                    </div>

                    <h4 class="fw-bold">
                        Nenhum comunicado disponível
                    </h4>

                    <p class="text-muted mb-0">
                        Quando o RH publicar avisos, eles aparecerão aqui.
                    </p>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

<script>
const buscar = document.getElementById('buscarComunicado');
const comunicados = document.querySelectorAll('.comunicado-item');

buscar.addEventListener('keyup', function(){

    const termo = this.value.toLowerCase();

    comunicados.forEach(function(item){

        const texto = item.dataset.texto;

        item.style.display =
        texto.includes(termo) ? '' : 'none';

    });

});
</script>

</body>
</html>