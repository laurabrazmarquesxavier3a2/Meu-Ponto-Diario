
<?php require_once 'auth.php'; ?>

<?php

include('config/database.php');

$sql = "SELECT * FROM ocorrencias ORDER BY data_ocorrencia DESC";

$result = $con->query($sql);

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

</head>

<body class="bg-light">

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>

                    <h1 class="fw-bold text-primary mb-1">

                        <i class="bi bi-shield-fill-check me-2"></i>

                        Central de Emergências

                    </h1>

                    <p class="text-muted mb-0">

                        Gerenciamento rápido de ocorrências reportadas

                    </p>

                </div>

                <div class="d-flex gap-2">

                    <span class="badge bg-primary rounded-pill px-4 py-3 fs-6">

                        <?= $result->num_rows ?> ocorrências

                    </span>

                </div>

            </div>

        </div>

    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body">

            <div class="row g-3">

                <!-- PESQUISA -->
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

                <!-- STATUS -->
                <div class="col-md-4">

                    <select
                    class="form-select"
                    id="filtroStatus">

                        <option value="todos">

                            Todos os status

                        </option>

                        <option value="Aberto">

                            Aberto

                        </option>

                        <option value="Em andamento">

                            Em andamento

                        </option>

                        <option value="Resolvido">

                            Resolvido

                        </option>

                    </select>

                </div>

                <!-- NÍVEL -->
                <div class="col-md-4">

                    <select
                    class="form-select"
                    id="filtroNivel">

                        <option value="todos">

                            Todos os níveis

                        </option>

                        <option value="Crítico">

                            Crítico

                        </option>

                        <option value="Alto">

                            Alto

                        </option>

                        <option value="Médio">

                            Médio

                        </option>

                    </select>

                </div>

            </div>

        </div>

    </div>

    <!-- LISTA -->
    <div id="listaOcorrencias">

    <?php while($e = $result->fetch_assoc()) { ?>

    <div
    class="card border-0 shadow-sm rounded-4 mb-4 ocorrencia"

    data-status="<?= $e['status_ocorrencia'] ?>"

    data-nivel="<?= $e['nivel_risco'] ?>"

    data-categoria="<?= strtolower($e['categoria']) ?>">

        <div class="card-body p-4">

            <!-- TOP -->
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">

                <div class="d-flex gap-2 flex-wrap">

                    <span class="badge bg-primary rounded-pill px-3 py-2">

                        <?= $e['categoria'] ?>

                    </span>

                    <?php if($e['nivel_risco'] == 'Crítico'){ ?>

                        <span class="badge bg-danger rounded-pill px-3 py-2">

                            <?= $e['nivel_risco'] ?>

                        </span>

                    <?php } elseif($e['nivel_risco'] == 'Alto'){ ?>

                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">

                            <?= $e['nivel_risco'] ?>

                        </span>

                    <?php } else { ?>

                        <span class="badge bg-info rounded-pill px-3 py-2">

                            <?= $e['nivel_risco'] ?>

                        </span>

                    <?php } ?>

                    <span
                    class="badge rounded-pill px-3 py-2 badgeStatus
                    <?= $e['status_ocorrencia'] == 'Resolvido'
                        ? 'bg-success'
                        : 'bg-primary' ?>">

                        <?= $e['status_ocorrencia'] ?>

                    </span>

                </div>

                <small class="text-muted">

                    <i class="bi bi-clock me-1"></i>

                    <?= date('d/m/Y H:i', strtotime($e['data_ocorrencia'])) ?>

                </small>

            </div>

            <!-- DESCRIÇÃO -->
            <p class="text-muted mb-4">

                <?= $e['descricao'] ?>

            </p>

            <!-- INFO GRID -->
            <div class="row g-3 mb-4">

                <div class="col-md-3">

                    <div class="bg-light rounded-4 p-3 h-100">

                        <small class="text-muted d-block mb-1">

                            Reportado por

                        </small>

                        <strong>

                            <?php if($e['tipo_reporte'] == 'Anônimo'){ ?>

                                <i class="bi bi-incognito me-1"></i>

                            <?php } ?>

                            <?= $e['nome'] ?>

                        </strong>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="bg-light rounded-4 p-3 h-100">

                        <small class="text-muted d-block mb-1">

                            Andar / Sala

                        </small>

                        <strong>

                            <?= $e['andar'] ?>

                        </strong>

                        <br>

                        <small>

                            <?= $e['sala'] ?>

                        </small>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="bg-light rounded-4 p-3 h-100">

                        <small class="text-muted d-block mb-1">

                            Local

                        </small>

                        <strong>

                            <?= $e['local_especifico'] ?>

                        </strong>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="bg-light rounded-4 p-3 h-100">

                        <small class="text-muted d-block mb-1">

                            Evidência

                        </small>

                        <?php if(!empty($e['evidencia'])){ ?>

                            <a
                            href="uploads/ocorrencias/<?= $e['evidencia'] ?>"
                            target="_blank"
                            class="btn btn-sm btn-primary">

                                Ver arquivo

                            </a>

                        <?php } else { ?>

                            <span class="badge bg-secondary">

                                Nenhuma

                            </span>

                        <?php } ?>

                    </div>

                </div>

            </div>

            <!-- ACCORDION -->
            <div class="accordion" id="accordion<?= $e['id_ocorrencia'] ?>">

                <div class="accordion-item border rounded-4">

                    <h2 class="accordion-header">

                        <button
                        class="accordion-button collapsed rounded-4"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#detalhes<?= $e['id_ocorrencia'] ?>">

                            Mais detalhes

                        </button>

                    </h2>

                    <div
                    id="detalhes<?= $e['id_ocorrencia'] ?>"
                    class="accordion-collapse collapse">

                        <div class="accordion-body">

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <strong>

                                        Testemunhas

                                    </strong>

                                    <p class="text-muted mb-0 mt-1">

                                        <?= $e['testemunhas'] ?>

                                    </p>

                                </div>

                                <div class="col-md-6">

                                    <strong>

                                        Status atual

                                    </strong>

                                    <p class="text-muted mb-0 mt-1">

                                        <?= $e['status_ocorrencia'] ?>

                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <?php } ?>

    </div>

</div>

</div>

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script>

const filtroStatus =
document.getElementById('filtroStatus');

const filtroNivel =
document.getElementById('filtroNivel');

const pesquisa =
document.getElementById('pesquisa');

const ocorrencias =
document.querySelectorAll('.ocorrencia');

function filtrarOcorrencias(){

    const status =
    filtroStatus.value;

    const nivel =
    filtroNivel.value;

    const texto =
    pesquisa.value.toLowerCase();

    ocorrencias.forEach(card => {

        const cardStatus =
        card.dataset.status;

        const cardNivel =
        card.dataset.nivel;

        const cardCategoria =
        card.dataset.categoria;

        let mostrar = true;

        if(status !== 'todos' &&
           cardStatus !== status){

            mostrar = false;

        }

        if(nivel !== 'todos' &&
           cardNivel !== nivel){

            mostrar = false;

        }

        if(!cardCategoria.includes(texto)){

            mostrar = false;

        }

        card.style.display =
        mostrar ? 'block' : 'none';

    });

}

filtroStatus.addEventListener('change',
filtrarOcorrencias);

filtroNivel.addEventListener('change',
filtrarOcorrencias);

pesquisa.addEventListener('keyup',
filtrarOcorrencias);

</script>

</body>
</html>
```
