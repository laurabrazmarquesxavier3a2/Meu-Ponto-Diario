document.addEventListener("DOMContentLoaded", () => {

    /*
    |--------------------------------------------------------------------------
    | ANIMAÇÃO DE ENTRADA
    |--------------------------------------------------------------------------
    |
    | A classe "active" pertence somente aos elementos ".reveal".
    | Os cards não usam essa classe.
    |
    */

    const reveals = document.querySelectorAll(".reveal");

    function revealOnScroll() {

        reveals.forEach((elemento) => {

            if (elemento.classList.contains("active")) {
                return;
            }

            const posicaoTopo = elemento.getBoundingClientRect().top;
            const limiteTela = window.innerHeight - 80;

            if (posicaoTopo < limiteTela) {
                elemento.classList.add("active");
            }

        });

    }

    window.addEventListener("scroll", revealOnScroll, {
        passive: true
    });

    revealOnScroll();


    /*
    |--------------------------------------------------------------------------
    | CARDS DAS LEIS
    |--------------------------------------------------------------------------
    |
    | A classe "faq-open" abre e fecha apenas o conteúdo do card.
    | Ela não interfere na animação reveal.
    |
    */

    const faqCards = document.querySelectorAll(".faq-card");

    faqCards.forEach((card) => {

        const botao = card.querySelector(".faq-button");

        if (!botao) {
            return;
        }

        botao.addEventListener("click", () => {

            const estaAberto = card.classList.contains("faq-open");

            if (estaAberto) {

                card.classList.remove("faq-open");
                botao.setAttribute("aria-expanded", "false");

            } else {

                card.classList.add("faq-open");
                botao.setAttribute("aria-expanded", "true");

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | PESQUISA
    |--------------------------------------------------------------------------
    */

    const busca = document.getElementById("buscarFaq");
    const avisoVazio = document.getElementById("faqEmpty");

    if (busca) {

        busca.addEventListener("input", () => {

            const termo = normalizarTexto(busca.value.trim());

            let quantidadeVisivel = 0;

            faqCards.forEach((card) => {

                const textoCard = normalizarTexto(card.textContent);
                const corresponde = textoCard.includes(termo);

                card.classList.toggle("faq-hidden", !corresponde);

                if (corresponde) {
                    quantidadeVisivel++;
                }

            });

            if (avisoVazio) {

                avisoVazio.classList.toggle(
                    "active",
                    quantidadeVisivel === 0
                );

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZAÇÃO DA PESQUISA
    |--------------------------------------------------------------------------
    |
    | Permite encontrar "ferias" mesmo que o card esteja escrito "Férias".
    |
    */

    function normalizarTexto(texto) {

        return texto
            .toLocaleLowerCase("pt-BR")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");

    }

});