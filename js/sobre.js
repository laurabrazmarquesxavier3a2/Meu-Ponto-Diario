// animação ao carregar a página

window.addEventListener("load", () => {

    document.querySelector(".hero")
        .classList.add("show");

});


// animação ao descer a página

const sections =
    document.querySelectorAll(".sobre-tcc");

window.addEventListener("scroll", () => {

    sections.forEach(sec => {

        const top =
            sec.getBoundingClientRect().top;

        if(top < window.innerHeight - 100){

            sec.classList.add("show");

        }

    });

});


// efeito no botão

const botao =
    document.querySelector(".btn");

botao.addEventListener("mousemove", (e) => {

    const x =
        e.offsetX;

    const y =
        e.offsetY;

    botao.style.background =
        `radial-gradient(circle at ${x}px ${y}px,
        #4c8dff,
        #2463eb)`;

});

botao.addEventListener("mouseleave", () => {

    botao.style.background =
        "#2463eb";

});