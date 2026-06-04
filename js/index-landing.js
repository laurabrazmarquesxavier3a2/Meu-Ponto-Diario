// ===============================
// ANIMAÇÕES DA LANDING PAGE
// Meu Ponto Diário
// ===============================

document.addEventListener("DOMContentLoaded", () => {

    // REVEAL AO ROLAR
    const reveals = document.querySelectorAll(".reveal");

    function revealOnScroll() {
        reveals.forEach((el) => {
            const top = el.getBoundingClientRect().top;

            if (top < window.innerHeight - 80) {
                el.classList.add("active");
            }
        });
    }

    window.addEventListener("scroll", revealOnScroll);
    revealOnScroll();


    // CONTADORES ANIMADOS
    const counters = document.querySelectorAll(".counter");
    let countersStarted = false;

    function startCounters() {

        if (countersStarted) return;

        const hero = document.querySelector(".hero");

        if (!hero) return;

        const heroTop = hero.getBoundingClientRect().top;

        if (heroTop < window.innerHeight) {

            countersStarted = true;

            counters.forEach((counter) => {

                const target = Number(counter.dataset.target);
                let value = 0;
                const increment = Math.max(1, Math.ceil(target / 60));

                const timer = setInterval(() => {

                    value += increment;

                    if (value >= target) {
                        value = target;
                        clearInterval(timer);
                    }

                    if (target === 24) {
                        counter.innerText = value + "h";
                    } else {
                        counter.innerText = value + "+";
                    }

                }, 22);
            });
        }
    }

    window.addEventListener("scroll", startCounters);
    startCounters();


    // SCROLL SUAVE
    document.querySelectorAll('a[href^="#"]').forEach((link) => {

        link.addEventListener("click", function (e) {

            const target = document.querySelector(this.getAttribute("href"));

            if (target) {
                e.preventDefault();

                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            }

        });

    });


    // LOGO 3D COM LED
    const logo = document.querySelector(".logo-led");

    if (logo) {

        document.addEventListener("mousemove", (e) => {

            const rect = logo.getBoundingClientRect();

            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;

            logo.style.transform =
                `translateY(-3px) rotateX(${(-y / 12)}deg) rotateY(${x / 12}deg)`;

        });

        document.addEventListener("mouseleave", () => {
            logo.style.transform =
                "translateY(0) rotateX(0deg) rotateY(0deg)";
        });

    }


    // EFEITO 3D NO PAINEL
    const mock = document.querySelector(".dashboard-mock");

    if (mock) {

        mock.addEventListener("mousemove", (e) => {

            const rect = mock.getBoundingClientRect();

            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const rotateY = ((x / rect.width) - 0.5) * 10;
            const rotateX = ((y / rect.height) - 0.5) * -10;

            mock.style.transform =
                `perspective(1100px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px)`;

        });

        mock.addEventListener("mouseleave", () => {

            mock.style.transform =
                "perspective(1100px) rotateY(-7deg) rotateX(5deg)";

        });

    }


    // EFEITO DE DIGITAÇÃO NO SUBTÍTULO
    const subtitle = document.querySelector(".brand-subtitle");

    if (subtitle) {

        const textoOriginal = subtitle.innerText;
        subtitle.innerText = "";

        let i = 0;

        const typing = setInterval(() => {

            subtitle.innerText += textoOriginal.charAt(i);
            i++;

            if (i >= textoOriginal.length) {
                clearInterval(typing);
            }

        }, 70);

    }


    // BRILHO NO BOTÃO PRINCIPAL
    document.querySelectorAll(".btn-main").forEach((btn) => {

        btn.addEventListener("mousemove", (e) => {

            const rect = btn.getBoundingClientRect();

            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            btn.style.background =
                `radial-gradient(circle at ${x}px ${y}px, #60a5fa, #2563eb 35%, #1e40af 100%)`;

        });

        btn.addEventListener("mouseleave", () => {

            btn.style.background =
                "linear-gradient(135deg,#2563eb,#1d4ed8)";

        });

    });


    // PARTÍCULAS AZUIS NO FUNDO
    const hero = document.querySelector(".hero");

    if (hero) {

        const particleBox = document.createElement("div");
        particleBox.className = "particles-box";
        hero.appendChild(particleBox);

        for (let i = 0; i < 22; i++) {

            const particle = document.createElement("span");

            particle.className = "particle";

            particle.style.left = Math.random() * 100 + "%";
            particle.style.top = Math.random() * 100 + "%";
            particle.style.animationDelay = Math.random() * 5 + "s";
            particle.style.animationDuration = 4 + Math.random() * 6 + "s";

            particleBox.appendChild(particle);

        }

    }


    // BOTÃO VOLTAR AO TOPO
    const backTop = document.createElement("button");

    backTop.className = "back-top";
    backTop.innerHTML = `<i class="bi bi-arrow-up"></i>`;

    document.body.appendChild(backTop);

    window.addEventListener("scroll", () => {

        if (window.scrollY > 500) {
            backTop.classList.add("show");
        } else {
            backTop.classList.remove("show");
        }

    });

    backTop.addEventListener("click", () => {

        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });

    });


    // MINI LOADER AO ABRIR A PÁGINA
    const loader = document.createElement("div");

    loader.className = "page-loader";
    loader.innerHTML = `
        <div class="loader-content">
            <div class="loader-glow"></div>
            <img src="img/logo-azul.png" alt="Meu Ponto Diário">
            <span>Carregando experiência...</span>
        </div>
    `;

    document.body.prepend(loader);

    setTimeout(() => {
        loader.classList.add("hide");
    }, 900);

    setTimeout(() => {
        loader.remove();
    }, 1500);

});