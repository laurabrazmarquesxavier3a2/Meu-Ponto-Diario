/* LOADER */

window.addEventListener('load', () => {
    const loader = document.getElementById('pageLoader');

    if (loader) {
        setTimeout(() => {
            loader.classList.add('hide');
        }, 450);
    }
});


/* REVEAL AO ROLAR */

const revealElements = document.querySelectorAll('.reveal');

function revealOnScroll() {
    const windowHeight = window.innerHeight;

    revealElements.forEach((element) => {
        const elementTop = element.getBoundingClientRect().top;

        if (elementTop < windowHeight - 90) {
            element.classList.add('active');
        }
    });
}

window.addEventListener('scroll', revealOnScroll);
window.addEventListener('load', revealOnScroll);


/* LOGO TOPO */

const topLogo = document.getElementById('topLogo');

if (topLogo) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 80) {
            topLogo.classList.add('scrolled');
        } else {
            topLogo.classList.remove('scrolled');
        }
    });

    topLogo.addEventListener('mousemove', (e) => {
        const rect = topLogo.getBoundingClientRect();

        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const rotateX = ((y / rect.height) - 0.5) * -8;
        const rotateY = ((x / rect.width) - 0.5) * 8;

        topLogo.style.transform = `
            translateY(-4px)
            scale(1.08)
            rotateX(${rotateX}deg)
            rotateY(${rotateY}deg)
        `;
    });

    topLogo.addEventListener('mouseleave', () => {
        topLogo.style.transform = '';
    });
}


/* BOTÃO VOLTAR AO TOPO */

const backTop = document.getElementById('backTop');

if (backTop) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 500) {
            backTop.classList.add('show');
        } else {
            backTop.classList.remove('show');
        }
    });

    backTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}


/* PARTÍCULAS */

const particlesBox = document.getElementById('particlesBox');

if (particlesBox) {
    const totalParticles = 26;

    for (let i = 0; i < totalParticles; i++) {
        const particle = document.createElement('span');

        particle.classList.add('particle');

        particle.style.left = `${Math.random() * 100}%`;
        particle.style.top = `${Math.random() * 100}%`;
        particle.style.animationDuration = `${5 + Math.random() * 7}s`;
        particle.style.animationDelay = `${Math.random() * 5}s`;
        particle.style.opacity = `${0.25 + Math.random() * 0.75}`;
        particle.style.transform = `scale(${0.7 + Math.random() * 1.1})`;

        particlesBox.appendChild(particle);
    }
}


/* EFEITO 3D NOS CARDS DOS MEMBROS */

const memberCards = document.querySelectorAll('.member-card');

memberCards.forEach((card) => {
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();

        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const rotateX = ((y / rect.height) - 0.5) * -6;
        const rotateY = ((x / rect.width) - 0.5) * 6;

        card.style.transform = `
            translateY(-12px)
            rotateX(${rotateX}deg)
            rotateY(${rotateY}deg)
        `;
    });

    card.addEventListener('mouseleave', () => {
        card.style.transform = '';
    });
});


/* SCROLL SUAVE PARA LINKS INTERNOS */

const internalLinks = document.querySelectorAll('a[href^="#"]');

internalLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
        const targetId = link.getAttribute('href');

        if (targetId.length > 1) {
            const target = document.querySelector(targetId);

            if (target) {
                e.preventDefault();

                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});