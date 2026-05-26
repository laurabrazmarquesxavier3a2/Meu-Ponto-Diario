const sidebar = document.getElementById("sidebar");

const btn = document.getElementById("btnSidebar");

const overlay = document.getElementById("sidebarOverlay");

// MOBILE
btn.addEventListener("click", () => {

    sidebar.classList.toggle("show");

    overlay.classList.toggle("show");

});

// FECHAR
overlay.addEventListener("click", () => {

    sidebar.classList.remove("show");

    overlay.classList.remove("show");

});

// EFEITO FOFO LINKS
const links = document.querySelectorAll(".sidebar a");

links.forEach(link => {

    link.addEventListener("mouseenter", () => {

        link.style.transition = ".2s";

    });

});

// ANIMAÇÃO LOGO
const logo = document.querySelector(".logo-branca");

if(logo){

    logo.addEventListener("mouseenter", () => {

        logo.style.transform = "rotate(-6deg) scale(1.05)";

    });

    logo.addEventListener("mouseleave", () => {

        logo.style.transform = "rotate(0deg) scale(1)";

    });

}

// ENTRADA SUAVE SIDEBAR
window.addEventListener("load", () => {

    sidebar.style.opacity = "0";

    sidebar.style.transform = "translateX(-20px)";

    setTimeout(() => {

        sidebar.style.transition = ".4s";

        sidebar.style.opacity = "1";

        sidebar.style.transform = "translateX(0px)";

    }, 100);

});