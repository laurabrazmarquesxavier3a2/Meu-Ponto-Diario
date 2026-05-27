 <?php
require_once 'auth.php';
require_once 'config/database.php';

/*
========================================
ESTATÍSTICAS
========================================
*/

$totalComunicados = $con->query("
    SELECT COUNT(*) AS total
    FROM comunicados
")->fetch_assoc()['total'];

$totalFixados = $con->query("
    SELECT COUNT(*) AS total
    FROM comunicados
    WHERE fixado = 1
")->fetch_assoc()['total'];

$mesComunicados = $con->query("
    SELECT COUNT(*) AS total
    FROM comunicados
    WHERE MONTH(data_publicacao) = MONTH(CURRENT_DATE())
    AND YEAR(data_publicacao) = YEAR(CURRENT_DATE())
")->fetch_assoc()['total'];

/*
========================================
COMUNICADOS
========================================
*/

$sql = "
SELECT *
FROM comunicados
ORDER BY fixado DESC,
data_publicacao DESC
";

$resultado = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="UTF-8">
<title>Comunicados</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>

.card-dashboard{
    border-radius:14px;
    border:none;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

.card-fixado{
    background:#fef9e7;
    border-left:5px solid #f59e0b;
}

.badge-soft{
    padding:6px 10px;
    border-radius:20px;
    font-size:12px;
}

</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="fw-bold">
                Comunicados
            </h1>

            <h5 class="text-muted mb-4">
                Avisos importantes para os funcionários
            </h5>

        </div>

        <button
            class="btn btn-primary px-4 py-2"
            data-bs-toggle="modal"
            data-bs-target="#modalComunicado"
        >

            <i class="bi bi-bell me-2"></i>
            Novo Comunicado

        </button>

    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="card card-dashboard p-3">

                <small>Total de Comunicados</small>

                <h2 class="fw-bold d-flex justify-content-between">

                    <?= $totalComunicados ?>

                    <i class="bi bi-bell"></i>

                </h2>

            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-dashboard p-3">

                <small>Fixados</small>

                <h2 class="fw-bold d-flex justify-content-between">

                    <?= $totalFixados ?>

                    <i class="bi bi-pin"></i>

                </h2>

            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-dashboard p-3">

                <small>Este mês</small>

                <h2 class="fw-bold d-flex justify-content-between">

                    <?= $mesComunicados ?>

                    <i class="bi bi-calendar"></i>

                </h2>

            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-dashboard p-3">

                <small>Alcance</small>

                <h2 class="fw-bold d-flex justify-content-between">

                    Todos

                    <i class="bi bi-people"></i>

                </h2>

            </div>
        </div>

    </div>

    <!-- LISTA -->
    <div class="row g-4">

        <?php if($resultado->num_rows > 0): ?>

            <?php while($row = $resultado->fetch_assoc()): ?>

                <div class="col-12">

                    <div class="card card-dashboard p-4 <?= $row['fixado'] ? 'card-fixado' : '' ?>">

                        <div class="d-flex justify-content-between align-items-center mb-2">

                            <span class="badge bg-primary">

                                <?= $row['categoria'] ?>

                            </span>

                            <small class="text-muted">

                                <?= date('d/m/Y H:i', strtotime($row['data_publicacao'])) ?>

                            </small>

                        </div>

                        <h4 class="fw-bold">

                            <?= htmlspecialchars($row['titulo']) ?>

                            <?php if($row['fixado']){ ?>
                                <i class="bi bi-pin-angle-fill text-warning"></i>
                            <?php } ?>

                        </h4>

                        <p class="text-muted">

                            <?= nl2br(htmlspecialchars($row['conteudo'])) ?>

                        </p>

                        <small class="text-secondary">

                            Por <?= htmlspecialchars($row['autor']) ?>

                        </small>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="card card-dashboard p-5 text-center">

                    <i class="bi bi-bell-slash fs-1 text-secondary"></i>

                    <h4 class="mt-3">
                        Nenhum comunicado encontrado
                    </h4>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

<!-- MODAL -->
<div class="modal fade" id="modalComunicado" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <form action="salvar_comunicados.php" method="POST">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Novo Comunicado
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label>Título</label>

                        <input
                            type="text"
                            name="titulo"
                            class="form-control"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label>Conteúdo</label>

                        <textarea
                            name="conteudo"
                            class="form-control"
                            rows="5"
                            required
                        ></textarea>

                    </div>

                    <div class="mb-3">

                        <label>Categoria</label>

                        <select
                            name="categoria"
                            class="form-select"
                        >

                            <option>Política</option>
                            <option>Evento</option>
                            <option>Comemoração</option>
                            <option>Urgente</option>

                        </select>

                    </div>

                    <div class="form-check">

                        <input
                            type="checkbox"
                            name="fixado"
                            class="form-check-input"
                        >

                        <label class="form-check-label">
                            Fixar comunicado
                        </label>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Publicar
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>