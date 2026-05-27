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

    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>Pedidos</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/global.css">

    <link rel="stylesheet"
    href="../css/sidebarfunc.css">

    <link rel="stylesheet"
    href="../css/pedidosf.css">

</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'sidebarfunc.php'; ?>

    <!-- MAIN -->
    <main class="pedidos-page">

        <div class="container-fluid">

            <!-- ALERTA SUCESSO -->
            <?php if(isset($_GET['sucesso'])){ ?>

                <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0 rounded-4">

                    <i class="fa-solid fa-circle-check me-2"></i>

                    Licença enviada com sucesso.

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                    </button>

                </div>

            <?php } ?>

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

                                <div class="icon-box bg-primary bg-opacity-10 text-primary p-3 rounded-4">

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

                                <div class="icon-box bg-danger bg-opacity-10 text-danger p-3 rounded-4">

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

                                <div class="mb-3">

                                    <label class="form-label fw-semibold">
                                        Observação
                                    </label>

                                    <textarea
                                        name="observacao"
                                        class="form-control"
                                        rows="3"
                                        placeholder="Digite uma observação..."
                                    ></textarea>

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

<!-- BOOTSTRAP -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS -->
<script>

let mesSelecionado = null;

// LIMITE
const limiteEdicoes = 2;

let edicoesRestantes = limiteEdicoes;

// CONTROLE
let sistemaBloqueado = false;

// DIREITO ÀS FÉRIAS
let possuiDireitoFerias = true;

// LOAD
window.addEventListener("load", () => {

    atualizarBox();

    verificarPermissaoFerias();

});

// VERIFICA DIREITO
function verificarPermissaoFerias(){

    if(!possuiDireitoFerias){

        bloquearSistemaFerias(
            "⚠ Você está fora do prazo para solicitar férias"
        );

    }

}

// BLOQUEAR SISTEMA
function bloquearSistemaFerias(mensagem = null){

    sistemaBloqueado = true;

    document.querySelectorAll(".mes-btn")
    .forEach(btn => {

        btn.disabled = true;

    });

    const btnSolicitar =
    document.querySelector("#btnSolicitar");

    if(btnSolicitar){

        btnSolicitar.disabled = true;

        btnSolicitar.innerHTML =
        "Solicitação Bloqueada";

        btnSolicitar.classList.remove(
            "btn-primary"
        );

        btnSolicitar.classList.add(
            "btn-secondary"
        );

    }

    mesSelecionado = null;

    document.querySelector("#mesSelecionadoTexto")
    .innerHTML = `

        <span class="text-danger fw-bold">
            Solicitação bloqueada
        </span>

    `;

    if(mensagem){

        mostrarAlerta(mensagem);

    }

}

// SELECIONAR MÊS
function selecionarMes(elemento){

    if(
        sistemaBloqueado ||
        edicoesRestantes <= 0
    ){

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

    elemento.classList.remove("btn-light");

    elemento.classList.add(
        "btn-primary",
        "text-white"
    );

    mesSelecionado =
    elemento.innerText;

    atualizarBox();

    mostrarAlerta(
        "✔ " + mesSelecionado + " selecionado"
    );

}

// BOX
function atualizarBox(){

    if(sistemaBloqueado){

        return;

    }

    const texto =
    document.querySelector(
        "#mesSelecionadoTexto"
    );

    if(!mesSelecionado){

        texto.innerHTML = `

            <span class="text-muted">
                Nenhum mês selecionado
            </span>

        `;

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

// SOLICITAR
function solicitarFerias(){

    if(sistemaBloqueado){

        mostrarAlerta(
            "⚠ Solicitação bloqueada"
        );

        return;

    }

    if(!possuiDireitoFerias){

        mostrarAlerta(
            "⚠ Você está fora do prazo"
        );

        return;

    }

    if(!mesSelecionado){

        mostrarAlerta(
            "Selecione um mês"
        );

        return;

    }

    // SALVA TEMPORARIAMENTE
    localStorage.setItem(
        "pedidoFerias",
        mesSelecionado
    );

    mostrarAlerta(
        "🎉 Solicitação enviada para " +
        mesSelecionado
    );

    edicoesRestantes--;

    atualizarBox();

    if(edicoesRestantes <= 0){

        bloquearSistemaFerias(
            "⚠ Limite de solicitações atingido"
        );

    }

    // REDIRECIONA
    setTimeout(() => {

        window.location.href =
        "SoliLic.php";

    }, 1500);

}

// ALERTA
function mostrarAlerta(texto){

    const alerta =
    document.getElementById("alerta");

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