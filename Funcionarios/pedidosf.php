<?php 
$meses = [
    "Janeiro",
    "Fevereiro",
    "Março",
    "Abril",
    "Maio",
    "Junho"
];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pedidos</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/sidebarfunc.css">

    <!-- CSS ESPECÍFICO -->
    <link rel="stylesheet" href="../css/pedidosf.css">

</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'sidebarfunc.php'; ?>

    <!-- MAIN -->
    <main class="pedidos-page">

        <div class="container-fluid">

            <!-- TÍTULO -->
            <div class="mb-4">

                <h2 class="fw-bold mb-1">
                    Pedidos
                </h2>

                <p class="text-muted mb-0">
                    Gerencie suas solicitações de ausências e benefícios
                </p>

            </div>

            <!-- CARDS -->
            <div class="row g-4">

                <!-- CARD FÉRIAS -->
                <div class="col-12 col-xl-6">

                    <div class="card border-0 shadow-sm rounded-4 h-100">

                        <div class="card-body p-4">

                            <!-- HEADER -->
                            <div class="d-flex align-items-center gap-3 mb-4">

                                <div class="icon-box bg-primary bg-opacity-10 text-primary">

                                    <i class="fa-solid fa-umbrella-beach"></i>

                                </div>

                                <div>

                                    <h4 class="fw-bold mb-1">
                                        Solicitar Férias
                                    </h4>

                                    <p class="text-muted mb-0">
                                        Escolha um mês disponível
                                    </p>

                                </div>

                            </div>

                            <!-- MESES -->
                            <div class="row g-3">

                                <?php foreach($meses as $mes){ ?>

                                    <div class="col-6">

                                        <button
                                            type="button"
                                            class="btn btn-light border w-100 py-3 fw-semibold mes-btn"
                                            onclick="selecionarMes(this)"
                                        >

                                            <?php echo $mes; ?>

                                        </button>

                                    </div>

                                <?php } ?>

                            </div>

                            <!-- BOX -->
                            <div class="alert alert-primary mt-4">

                                <div id="mesSelecionadoTexto">

                                    Nenhum mês selecionado

                                </div>

                            </div>

                            <!-- TENTATIVAS -->
                            <div class="mb-4">

                                <span class="fw-semibold">
                                    Alterações restantes:
                                </span>

                                <span id="tentativasRestantes">
                                    2
                                </span>

                            </div>

                            <!-- BOTÃO -->
                            <button
                                id="btnSolicitar"
                                class="btn btn-primary w-100 py-3 fw-semibold"
                                onclick="solicitarFerias()"
                            >

                                <i class="fa-solid fa-paper-plane me-2"></i>

                                Solicitar Férias

                            </button>

                        </div>

                    </div>

                </div>

                <!-- CARD LICENÇA -->
                <div class="col-12 col-xl-6">

                    <div class="card border-0 shadow-sm rounded-4 h-100">

                        <div class="card-body p-4">

                            <!-- HEADER -->
                            <div class="d-flex align-items-center gap-3 mb-4">

                                <div class="icon-box bg-danger bg-opacity-10 text-danger">

                                    <i class="fa-solid fa-file-medical"></i>

                                </div>

                                <div>

                                    <h4 class="fw-bold mb-1">
                                        Licença Médica
                                    </h4>

                                    <p class="text-muted mb-0">
                                        Envie seu atestado médico
                                    </p>

                                </div>

                            </div>

                            <!-- FORM -->
                            <form
                                action="enviar_licenca.php"
                                method="POST"
                                enctype="multipart/form-data"
                            >

                                <div class="mb-3">

                                    <label class="form-label fw-semibold">
                                        Motivo
                                    </label>

                                    <input
                                        type="text"
                                        name="motivo"
                                        class="form-control"
                                        required
                                    >

                                </div>

                                <div class="row">

                                    <div class="col-md-6 mb-3">

                                        <label class="form-label fw-semibold">
                                            Data início
                                        </label>

                                        <input
                                            type="date"
                                            name="data_inicio"
                                            class="form-control"
                                            required
                                        >

                                    </div>

                                    <div class="col-md-6 mb-3">

                                        <label class="form-label fw-semibold">
                                            Data fim
                                        </label>

                                        <input
                                            type="date"
                                            name="data_fim"
                                            class="form-control"
                                            required
                                        >

                                    </div>

                                </div>

                                <div class="mb-4">

                                    <label class="form-label fw-semibold">
                                        Atestado
                                    </label>

                                    <input
                                        type="file"
                                        name="arquivo"
                                        accept=".pdf,.png,.jpg,.jpeg"
                                        class="form-control"
                                        required
                                    >

                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-danger w-100 py-3 fw-semibold"
                                >

                                    <i class="fa-solid fa-upload me-2"></i>

                                    Enviar Licença

                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- ALERTA -->
    <div
        id="alerta"
        class="position-fixed bottom-0 end-0 m-4 alert alert-dark shadow d-none"
        style="z-index:9999;"
    ></div>

<script>

let mesSelecionado = null;

const limiteEdicoes = 2;

let edicoesRestantes = limiteEdicoes;

let sistemaBloqueado = false;

function selecionarMes(botao){

    if(sistemaBloqueado){

        mostrarAlerta(
            "⚠ Solicitação bloqueada"
        );

        return;

    }

    document.querySelectorAll(".mes-btn")
    .forEach(btn => {

        btn.classList.remove(
            "btn-primary",
            "text-white"
        );

        btn.classList.add(
            "btn-light"
        );

    });

    botao.classList.remove("btn-light");

    botao.classList.add(
        "btn-primary",
        "text-white"
    );

    mesSelecionado =
    botao.innerText;

    atualizarBox();

}

function atualizarBox(){

    const texto =
    document.getElementById(
        "mesSelecionadoTexto"
    );

    if(!mesSelecionado){

        texto.innerHTML =
        "Nenhum mês selecionado";

        return;

    }

    texto.innerHTML = `

        <strong>
            Mês selecionado:
        </strong>

        <br>

        ${mesSelecionado}

    `;

}

function solicitarFerias(){

    if(sistemaBloqueado){

        mostrarAlerta(
            "⚠ Solicitação bloqueada"
        );

        return;

    }

    if(!mesSelecionado){

        mostrarAlerta(
            "Selecione um mês"
        );

        return;

    }

    mostrarAlerta(
        "🎉 Solicitação enviada para " +
        mesSelecionado
    );

    edicoesRestantes--;

    document.getElementById(
        "tentativasRestantes"
    ).innerText =
    edicoesRestantes;

    if(edicoesRestantes <= 0){

        bloquearSistema();

    }

}

function bloquearSistema(){

    sistemaBloqueado = true;

    document.querySelectorAll(".mes-btn")
    .forEach(btn => {

        btn.disabled = true;

    });

    const btn =
    document.getElementById(
        "btnSolicitar"
    );

    btn.disabled = true;

    btn.classList.remove(
        "btn-primary"
    );

    btn.classList.add(
        "btn-secondary"
    );

    btn.innerHTML =
    "Limite atingido";

}

function mostrarAlerta(texto){

    const alerta =
    document.getElementById(
        "alerta"
    );

    alerta.innerHTML = texto;

    alerta.classList.remove(
        "d-none"
    );

    setTimeout(() => {

        alerta.classList.add(
            "d-none"
        );

    }, 3000);

}

</script>

</body>
</html>