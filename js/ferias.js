function prepararRejeicao(botao){
    document.getElementById('idFeriasRejeitar').value = botao.dataset.id;
}

function abrirConfigMes(botao) {
    const mes = botao.dataset.mes;
    const nome = botao.dataset.nome;
    const disponivel = botao.dataset.disponivel;
    const limite = botao.dataset.limite;

    document.getElementById('tituloMesConfig').innerText = nome;
    document.getElementById('mesConfigInput').value = mes;

    const tipoLiberado = document.getElementById('tipoLiberado');
    const tipoLimitado = document.getElementById('tipoLimitado');
    const tipoBloqueado = document.getElementById('tipoBloqueado');
    const limiteInput = document.getElementById('limitePedidosInput');

    tipoLiberado.checked = false;
    tipoLimitado.checked = false;
    tipoBloqueado.checked = false;

    limiteInput.value = '';

    if (disponivel === '0') {
        tipoBloqueado.checked = true;
    } else if (limite !== '') {
        tipoLimitado.checked = true;
        limiteInput.value = limite;
    } else {
        tipoLiberado.checked = true;
    }

    toggleLimitePedidos();
}

function toggleLimitePedidos() {
    const tipoLimitado = document.getElementById('tipoLimitado');
    const limiteInput = document.getElementById('limitePedidosInput');

    if (tipoLimitado.checked) {
        limiteInput.disabled = false;
        limiteInput.required = true;
        setTimeout(() => limiteInput.focus(), 100);
    } else {
        limiteInput.disabled = true;
        limiteInput.required = false;
        limiteInput.value = '';
    }
}

const buscar = document.getElementById('buscarFerias');
const linhas = document.querySelectorAll('#tabelaFerias tr');

if (buscar) {
    buscar.addEventListener('keyup', function(){
        const termo = this.value.toLowerCase();

        linhas.forEach(function(linha){
            const texto = linha.innerText.toLowerCase();
            linha.style.display = texto.includes(termo) ? '' : 'none';
        });
    });
}

function prepararAprovacao(botao) {
    document.getElementById('idFeriasAprovar').value =
        botao.getAttribute('data-id');
}
