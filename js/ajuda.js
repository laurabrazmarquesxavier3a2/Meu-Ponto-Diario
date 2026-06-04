document.addEventListener("DOMContentLoaded", () => {

    const reveals = document.querySelectorAll(".reveal");

    function revealOnScroll(){
        reveals.forEach((el) => {
            const top = el.getBoundingClientRect().top;

            if(top < window.innerHeight - 80){
                el.classList.add("active");
            }
        });
    }

    window.addEventListener("scroll", revealOnScroll);
    revealOnScroll();

    const faqCards = document.querySelectorAll(".faq-card");

    faqCards.forEach((card) => {
        card.addEventListener("click", () => {
            card.classList.toggle("active");
        });
    });

    const busca = document.getElementById("buscarFaq");

    if(busca){

        busca.addEventListener("keyup", () => {

            const termo = busca.value.toLowerCase();

            faqCards.forEach((card) => {

                const texto = card.innerText.toLowerCase();

                card.style.display = texto.includes(termo) ? "block" : "none";

            });

        });

    }

    const modal = document.getElementById("modalDuvida");
    const abrir = document.getElementById("abrirFormulario");
    const abrir2 = document.getElementById("abrirFormulario2");
    const fechar = document.querySelector(".fechar");

    function abrirModal(){
        modal.classList.add("active");
    }

    function fecharModal(){
        modal.classList.remove("active");
    }

    if(abrir){
        abrir.addEventListener("click", abrirModal);
    }

    if(abrir2){
        abrir2.addEventListener("click", abrirModal);
    }

    if(fechar){
        fechar.addEventListener("click", fecharModal);
    }

    if(modal){
        modal.addEventListener("click", (e) => {
            if(e.target === modal){
                fecharModal();
            }
        });
    }

    document.addEventListener("keydown", (e) => {
        if(e.key === "Escape"){
            fecharModal();
        }
    });

    const form = document.getElementById("formDuvida");
    const btn = document.getElementById("btnEnviar");

    if(form && btn){

        form.addEventListener("submit", () => {
            btn.disabled = true;
            btn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2"></span>
                Enviando...
            `;
        });

    }

});