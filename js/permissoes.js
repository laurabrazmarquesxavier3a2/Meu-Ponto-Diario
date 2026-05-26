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

    const cards = document.querySelectorAll(".card");

    cards.forEach((card, index) => {

        setTimeout(() => {

            card.style.opacity = "1";
            card.style.transform = "translateY(0px)";

        }, index * 180);

    });

    atualizarBox();

    verificarPermissaoFerias();

});

// VERIFICA
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
    document.querySelectorAll(".mes-card")
    .forEach(card => {

        card.style.pointerEvents = "none";
        card.style.opacity = "0.5";
        card.style.filter = "grayscale(40%)";

        card.classList.remove("active");

    });

    // BLOQUEIA BOTÃO SOLICITAR
    const btnSolicitar =
    document.querySelector(".btn-solicitar");

    if(btnSolicitar){

        btnSolicitar.disabled = true;

        btnSolicitar.innerHTML =
        "Limite Excedido";

        btnSolicitar.classList.remove(
            "btn-primary"
        );

        btnSolicitar.classList.add(
            "btn-secondary"
        );

        btnSolicitar.style.cursor =
        "not-allowed";

        btnSolicitar.style.opacity =
        "0.7";

    }

    // BLOQUEIA EDITAR
    const btnEditar =
    document.querySelector(".btn-editar");

    if(btnEditar){

        btnEditar.disabled = true;

        btnEditar.style.opacity = "0.5";
        btnEditar.style.cursor = "not-allowed";

    }

    // LIMPA
    mesSelecionado = null;

    // BOX
    document.querySelector("#mesSelecionadoTexto")
    .innerHTML = `

        <span style="
            color:#dc3545;
            font-weight:700;
            font-size:15px;
        ">
            Solicitação bloqueada
        </span>

        <br>

        <small style="
            color:#777;
        ">
            Limite de alterações atingido
            ou prazo encerrado.
        </small>

    `;

    if(mensagem){

        mostrarAlerta(mensagem);

    }

}

// SELECIONAR
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

    // REMOVE ANTIGOS
    document.querySelectorAll(".mes-card")
    .forEach(card => {

        card.classList.remove("active");

    });

    // NOVO
    elemento.classList.add("active");

    mesSelecionado = elemento.innerText;

    atualizarBox();

    // EFEITO
    elemento.style.transform = "scale(1.08)";

    setTimeout(() => {

        elemento.style.transform = "scale(1.03)";

    }, 150);

    mostrarAlerta(
        "✔ " + mesSelecionado + " selecionado"
    );

}

// BOX
function atualizarBox(){

    // BLOQUEADO
    if(sistemaBloqueado){

        return;

    }

    const texto =
    document.querySelector("#mesSelecionadoTexto");

    // SEM MÊS
    if(!mesSelecionado){

        texto.innerHTML = `

            <span style="color:#777;">

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

// EDITAR
function editarMes(){

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

    // SEM MÊS
    if(!mesSelecionado){

        mostrarAlerta(
            "Nenhum mês selecionado"
        );

        return;

    }

    // CONFIRMA
    const confirmar = confirm(

        `Tem certeza que deseja editar?\n\n` +

        `O mês selecionado anteriormente será removido.\n\n` +

        `Você possui apenas ${edicoesRestantes} alteração(ões) restante(s).`

    );

    if(!confirmar){

        return;

    }

    // REMOVE
    document.querySelectorAll(".mes-card")
    .forEach(card => {

        card.classList.remove("active");

    });

    // LIMPA
    mesSelecionado = null;

    // REDUZ
    edicoesRestantes--;

    atualizarBox();

    mostrarAlerta(

        `✏ Alteração realizada. Restam ${edicoesRestantes} edição(ões)`

    );

    // LIMITE ATINGIDO
    if(edicoesRestantes <= 0){

        bloquearSistemaFerias(
            "⚠ Limite de alterações atingido"
        );

    }

}

// SOLICITAR
function solicitarFerias(){

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

    mostrarAlerta(
        "🎉 Solicitação enviada para " + mesSelecionado
    );

    bloquearSistemaFerias();

}

// PDF
function enviarPDF(){

    const arquivo =
    document.getElementById("pdfInput");

    if(arquivo.files.length === 0){

        mostrarAlerta(
            "Selecione um documento PDF"
        );

        return;

    }

    const nomeArquivo =
    arquivo.files[0].name;

    mostrarAlerta(
        "📄 " + nomeArquivo + " enviado com sucesso"
    );

}

// ALERTA
function mostrarAlerta(texto){

    const alerta =
    document.getElementById("alerta");

    alerta.innerHTML = texto;

    alerta.classList.add("show");

    setTimeout(() => {

        alerta.classList.remove("show");

    }, 3000);

}