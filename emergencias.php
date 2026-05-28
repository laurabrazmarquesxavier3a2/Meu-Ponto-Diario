<?php require_once 'auth.php'; ?>

<?php

$emergencias = [

    [
        "id" => 1,
        "categoria" => "Vazamento",
        "descricao" => "Água escorrendo próximo ao corredor principal.",
        "responsavel" => "João Santos",
        "anonimo" => false,
        "andar" => "3º Andar",
        "sala" => "302",
        "local" => "Corredor principal",
        "testemunhas" => "Carlos Eduardo",
        "evidencia" => true,
        "nivel" => "Alto",
        "status" => "Em andamento",
        "data" => "27/05/2026 10:30"
    ],

    [
        "id" => 2,
        "categoria" => "Assédio",
        "descricao" => "Discussão agressiva entre colaboradores.",
        "responsavel" => "Anônimo",
        "anonimo" => true,
        "andar" => "2º Andar",
        "sala" => "Marketing",
        "local" => "Sala de descanso",
        "testemunhas" => "Não informado",
        "evidencia" => false,
        "nivel" => "Crítico",
        "status" => "Aberto",
        "data" => "27/05/2026 09:15"
    ],

    [
        "id" => 3,
        "categoria" => "Problema elétrico",
        "descricao" => "Tomada soltando faíscas próximo aos computadores.",
        "responsavel" => "Lucas Almeida",
        "anonimo" => false,
        "andar" => "1º Andar",
        "sala" => "Laboratório 04",
        "local" => "Parede lateral",
        "testemunhas" => "Fernanda Lima",
        "evidencia" => true,
        "nivel" => "Médio",
        "status" => "Resolvido",
        "data" => "26/05/2026 16:45"
    ]

];

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

                        <?= count($emergencias) ?> ocorrências

                    </span>

                </div>

            </div>

        </div>

    </div>

    <!-- DASHBOARD -->
    <div class="row g-4 mb-4">

        <div class="col-md-4">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <small class="text-muted">

                        Casos críticos

                    </small>

                    <div class="d-flex justify-content-between align-items-center">

                        <h1 class="fw-bold text-primary">

                            1

                        </h1>

                        <i class="bi bi-fire fs-2 text-primary"></i>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <small class="text-muted">

                        Em andamento

                    </small>

                    <div class="d-flex justify-content-between align-items-center">

                        <h1 class="fw-bold text-primary">

                            1

                        </h1>

                        <i class="bi bi-clock-history fs-2 text-primary"></i>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <small class="text-muted">

                        Resolvidos

                    </small>

                    <div class="d-flex justify-content-between align-items-center">

                        <h1 class="fw-bold text-primary">

                            1

                        </h1>

                        <i class="bi bi-check-circle fs-2 text-primary"></i>

                    </div>

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

    <?php foreach ($emergencias as $e): ?>

    <div
    class="card border-0 shadow-sm rounded-4 mb-4 ocorrencia"
    data-status="<?= $e['status'] ?>"
    data-nivel="<?= $e['nivel'] ?>"
    data-categoria="<?= strtolower($e['categoria']) ?>">

        <div class="card-body p-4">

            <!-- TOP -->
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">

                <div class="d-flex gap-2 flex-wrap">

                    <span class="badge bg-primary rounded-pill px-3 py-2">

                        <?= $e['categoria'] ?>

                    </span>

                    <?php if($e['nivel'] == 'Crítico'){ ?>

                        <span class="badge bg-danger rounded-pill px-3 py-2">

                            <?= $e['nivel'] ?>

                        </span>

                    <?php } elseif($e['nivel'] == 'Alto'){ ?>

                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">

                            <?= $e['nivel'] ?>

                        </span>

                    <?php } else { ?>

                        <span class="badge bg-info rounded-pill px-3 py-2">

                            <?= $e['nivel'] ?>

                        </span>

                    <?php } ?>

                    <span
                    class="badge rounded-pill px-3 py-2 badgeStatus
                    <?= $e['status'] == 'Resolvido'
                        ? 'bg-success'
                        : 'bg-primary' ?>">

                        <?= $e['status'] ?>

                    </span>

                </div>

                <small class="text-muted">

                    <i class="bi bi-clock me-1"></i>

                    <?= $e['data'] ?>

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

                            <?php if($e['anonimo']){ ?>

                                <i class="bi bi-incognito me-1"></i>

                            <?php } ?>

                            <?= $e['responsavel'] ?>

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

                            <?= $e['local'] ?>

                        </strong>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="bg-light rounded-4 p-3 h-100">

                        <small class="text-muted d-block mb-1">

                            Evidência

                        </small>

                        <?php if($e['evidencia']){ ?>

                            <span class="badge bg-primary">

                                Anexada

                            </span>

                        <?php } else { ?>

                            <span class="badge bg-secondary">

                                Nenhuma

                            </span>

                        <?php } ?>

                    </div>

                </div>

            </div>

            <!-- ACCORDION -->
            <div class="accordion" id="accordion<?= $e['id'] ?>">

                <div class="accordion-item border rounded-4">

                    <h2 class="accordion-header">

                        <button
                        class="accordion-button collapsed rounded-4"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#detalhes<?= $e['id'] ?>">

                            Mais detalhes

                        </button>

                    </h2>

                    <div
                    id="detalhes<?= $e['id'] ?>"
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

                                        <?= $e['status'] ?>

                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- AÇÕES -->
            <div class="d-flex gap-2 mt-4 flex-wrap">

                <button
                class="btn btn-primary rounded-pill btnResolver">

                    <i class="bi bi-check-lg me-1"></i>

                    Resolver

                </button>

                <button
                class="btn btn-outline-secondary rounded-pill btnDesfazer d-none">

                    <i class="bi bi-arrow-counterclockwise me-1"></i>

                    Desfazer

                </button>

                <button
                class="btn btn-outline-primary rounded-pill">

                    <i class="bi bi-eye me-1"></i>

                    Visualizar

                </button>

            </div>

        </div>

    </div>

    <?php endforeach; ?>

    </div>

</div>

</div>

<!-- TOAST -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">

    <div
    id="toastSistema"
    class="toast border-0 shadow">

        <div class="toast-header">

            <i class="bi bi-bell-fill text-primary me-2"></i>

            <strong class="me-auto">

                Sistema

            </strong>

            <button
            type="button"
            class="btn-close"
            data-bs-dismiss="toast">
            </button>

        </div>

        <div
        class="toast-body"
        id="toastTexto">

            Sistema atualizado

        </div>

    </div>

</div>

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script>

/*
========================================
TOAST
========================================
*/

const toast =
new bootstrap.Toast(
document.getElementById('toastSistema'));

const toastTexto =
document.getElementById('toastTexto');

/*
========================================
FILTROS
========================================
*/

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

/*
========================================
RESOLVER
========================================
*/

const botoesResolver =
document.querySelectorAll('.btnResolver');

botoesResolver.forEach(botao => {

    botao.addEventListener('click', () => {

        const card =
        botao.closest('.ocorrencia');

        const badgeStatus =
        card.querySelector('.badgeStatus');

        const btnDesfazer =
        card.querySelector('.btnDesfazer');

        botao.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2"></span>
            Salvando...
        `;

        botao.disabled = true;

        setTimeout(() => {

            badgeStatus.classList.remove(
                'bg-primary'
            );

            badgeStatus.classList.add(
                'bg-success'
            );

            badgeStatus.innerHTML =
            'Resolvido';

            card.dataset.status =
            'Resolvido';

            botao.classList.add('d-none');

            btnDesfazer.classList.remove('d-none');

            toastTexto.innerHTML =
            'Ocorrência marcada como resolvida';

            toast.show();

        }, 800);

    });

});

/*
========================================
DESFAZER
========================================
*/

const botoesDesfazer =
document.querySelectorAll('.btnDesfazer');

botoesDesfazer.forEach(botao => {

    botao.addEventListener('click', () => {

        const card =
        botao.closest('.ocorrencia');

        const badgeStatus =
        card.querySelector('.badgeStatus');

        const btnResolver =
        card.querySelector('.btnResolver');

        badgeStatus.classList.remove(
            'bg-success'
        );

        badgeStatus.classList.add(
            'bg-primary'
        );

        badgeStatus.innerHTML =
        'Em andamento';

        card.dataset.status =
        'Em andamento';

        btnResolver.innerHTML = `
            <i class="bi bi-check-lg me-1"></i>
            Resolver
        `;

        btnResolver.disabled = false;

        btnResolver.classList.remove('d-none');

        botao.classList.add('d-none');

        toastTexto.innerHTML =
        'Alteração desfeita';

        toast.show();

    });

});

</script>

</body>
</html>