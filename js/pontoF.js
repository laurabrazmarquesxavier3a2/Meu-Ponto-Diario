
function escaparHtml(valor) {

    const elemento =
        document.createElement('div');

    elemento.textContent =
        valor === null ||
        valor === undefined
            ? ''
            : String(valor);

    return elemento.innerHTML;
}

function horaCurta(hora) {

    if (
        !hora ||
        hora === '00:00:00'
    ) {
        return '--:--';
    }

    return String(hora).substring(0, 5);
}

function dataBrasil(data) {

    if (!data) {
        return '-';
    }

    const partes = String(data).split('-');

    if (partes.length !== 3) {
        return data;
    }

    return (
        partes[2] +
        '/' +
        partes[1] +
        '/' +
        partes[0]
    );
}

function primeiraMaiuscula(texto) {

    if (!texto) {
        return '';
    }

    texto = String(texto);

    return (
        texto.charAt(0).toUpperCase() +
        texto.slice(1)
    );
}

function criarCampo(titulo, valor, classeExtra = '') {

    return `
        <div class="modal-record-item ${classeExtra}">
            <span>${escaparHtml(titulo)}</span>
            <strong>${escaparHtml(valor)}</strong>
        </div>
    `;
}

function abrirModalEvento(dataEvento) {

    const evento =
        eventosDoMes[dataEvento];

    if (!evento) {
        return;
    }

    const tipo =
        evento.tipo_evento || 'ponto';

    const titulo =
        document.getElementById('modalTitulo');

    const subtitulo =
        document.getElementById('modalSubtitulo');

    const data =
        document.getElementById('modalData');

    const conteudo =
        document.getElementById('modalConteudo');

    const origem =
        document.getElementById('modalOrigem');

    data.textContent =
        dataBrasil(dataEvento);

    let html = '';

    if (tipo === 'ponto') {

        subtitulo.textContent =
            'Histórico de ponto';

        titulo.textContent =
            'Registro do dia';

        html += criarCampo(
            'Entrada',
            horaCurta(evento.hora_entrada)
        );

        html += criarCampo(
            'Saída do intervalo',
            horaCurta(evento.saida_intervalo)
        );

        html += criarCampo(
            'Retorno do intervalo',
            horaCurta(evento.retorno_intervalo)
        );

        html += criarCampo(
            'Saída',
            horaCurta(evento.hora_saida)
        );

        html += criarCampo(
            'Total trabalhado',
            Number(
                evento.total_horas || 0
            ).toFixed(2) + 'h'
        );

        html += criarCampo(
            'Status',
            primeiraMaiuscula(
                evento.status || 'Sem status'
            )
        );

        origem.textContent =
            evento.origem === 'db_ponto'
                ? 'Registro externo'
                : 'Registro importado';

    } else {

        subtitulo.textContent =
            'Ausência justificada';

        titulo.textContent =
            evento.titulo ||
            evento.status ||
            'Ausência';

        html += criarCampo(
            'Situação',
            evento.status || 'Ausência'
        );

        html += criarCampo(
            'Período',
            dataBrasil(evento.data_inicio) +
            ' até ' +
            dataBrasil(evento.data_fim)
        );

        html += criarCampo(
            'Quantidade de dias',
            String(evento.dias || 0)
        );

        html += criarCampo(
            'Motivo',
            evento.motivo ||
            'Não informado',
            'full'
        );

        if (evento.observacao) {

            html += criarCampo(
                'Observação',
                evento.observacao,
                'full'
            );
        }

        origem.textContent =
            tipo === 'ferias'
                ? 'Férias aprovadas'
                : (
                    tipo === 'licenca'
                        ? 'Licença médica'
                        : 'Afastamento'
                );
    }

    conteudo.innerHTML = html;

    const modal =
        bootstrap.Modal.getOrCreateInstance(
            document.getElementById(
                'modalEvento'
            )
        );

    modal.show();
}

function updateClock() {

    const now = new Date();

    document.getElementById('clock').innerText =
        now.toLocaleTimeString('pt-BR');

    document.getElementById('date').innerText =
        now.toLocaleDateString(
            'pt-BR',
            {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }
        );
}

setInterval(updateClock, 1000);
updateClock();

document.addEventListener(
    'DOMContentLoaded',
    function () {

        document
            .querySelectorAll(
                '.botao-evento-calendario, .botao-ausencia'
            )
            .forEach(function (botao) {

                botao.addEventListener(
                    'click',
                    function () {

                        abrirModalEvento(
                            this.dataset.eventDate
                        );
                    }
                );
            });
    }
);