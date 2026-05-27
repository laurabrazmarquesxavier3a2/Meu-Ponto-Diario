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

    // BLOQUEIA MESES
    document.querySelectorAll(".mes-btn")
    .forEach(btn => {

        btn.disabled = true;

    });

    // BOTÃO
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

    // LIMPA
    mesSelecionado = null;

    // BOX
    document.querySelector(
        "#mesSelecionadoTexto"
    ).innerHTML = `

        <span class="text-danger fw-bold">
            Solicitação bloqueada
        </span>

        <br>

        <small class="text-muted">
            Limite de alterações atingido
            ou prazo encerrado.
        </small>

    `;

    if(mensagem){

        mostrarAlerta(mensagem);

    }

}

// SELECIONAR MÊS
function selecionarMes(elemento){

    // BLOQUEADO
    if(
        sistemaBloqueado ||
        edicoesRestantes <= 0
    ){

        mostrarAlerta(
            "⚠ Solicitação bloqueada"
        );

        return;

    }

    // REMOVE ESTILO
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

    // NOVO
    elemento.classList.remove(
        "btn-light"
    );

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

// ATUALIZA BOX
function atualizarBox(){

    if(sistemaBloqueado){

        return;

    }

    const texto =
    document.querySelector(
        "#mesSelecionadoTexto"
    );

    // SEM MÊS
    if(!mesSelecionado){

        texto.innerHTML = `

            <span class="text-muted">

                Nenhum mês selecionado

            </span>

            <br><br>

            <small>
                Alterações restantes:
                ${edicoesRestantes}
            </small>

        `;

        return;

    }

    // COM MÊS
    texto.innerHTML = `

        <strong>
            Mês selecionado:
        </strong>

        <br>

        ${mesSelecionado}

        <br><br>

        <small>
            Alterações restantes:
            ${edicoesRestantes}
        </small>

    `;

}

// SOLICITAR FÉRIAS
function solicitarFerias(){

    // BLOQUEADO
    if(sistemaBloqueado){

        mostrarAlerta(
            "⚠ Solicitação bloqueada"
        );

        return;

    }

    // SEM DIREITO
    if(!possuiDireitoFerias){

        mostrarAlerta(
            "⚠ Você está fora do prazo da solicitação de férias"
        );

        return;

    }

    // SEM MÊS
    if(!mesSelecionado){

        mostrarAlerta(
            "Selecione um mês"
        );

        return;

    }

    // ALERTA
    mostrarAlerta(
        "🎉 Solicitação enviada para " +
        mesSelecionado
    );

    // DIMINUI TENTATIVAS
    edicoesRestantes--;

    atualizarBox();

    // BLOQUEIA AO ACABAR
    if(edicoesRestantes <= 0){

        bloquearSistemaFerias(
            "⚠ Limite de solicitações atingido"
        );

    }

    // FORMULÁRIO TEMPORÁRIO
    const form =
    document.createElement("form");

    form.method = "POST";

    form.action =
    "salvar_ferias.php";

    // INPUT MÊS
    const inputMes =
    document.createElement("input");

    inputMes.type = "hidden";

    inputMes.name = "mes";

    inputMes.value =
    mesSelecionado;

    form.appendChild(inputMes);

    document.body.appendChild(form);

    // ENVIA
    setTimeout(() => {

        form.submit();

    }, 1000);

}

// ALERTA
function mostrarAlerta(texto){

    const alerta =
    document.getElementById("alerta");

    alerta.innerHTML = texto;

    alerta.classList.remove(
        "d-none"
    );

    alerta.classList.add(
        "show"
    );

    setTimeout(() => {

        alerta.classList.add(
            "d-none"
        );

        alerta.classList.remove(
            "show"
        );

    }, 3000);

}