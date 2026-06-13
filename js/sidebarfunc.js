document.addEventListener("DOMContentLoaded", function () {

    const sidebar = document.getElementById("sidebar");
    const logoToggle = document.getElementById("mpdLogoToggle");
    const overlay = document.getElementById("sidebarOverlay");
    const links = document.querySelectorAll(".mpd-link");

    if (!sidebar || !logoToggle) {
        return;
    }

    /*
    Remove qualquer estado antigo que ainda possa ter sido
    deixado pelo JavaScript anterior.
    */
    sidebar.classList.remove(
        "active",
        "hover-open",
        "open"
    );

    document.body.classList.remove(
        "sidebar-open",
        "sidebar-collapsed"
    );

    if (overlay) {
        overlay.classList.remove("active");
    }

    function estaAberta() {
        if (window.innerWidth <= 900) {
            return sidebar.classList.contains("active");
        }

        return document.body.classList.contains("sidebar-open");
    }

    function abrirSidebar() {
        if (window.innerWidth <= 900) {
            sidebar.classList.add("active");

            if (overlay) {
                overlay.classList.add("active");
            }
        } else {
            document.body.classList.add("sidebar-open");
        }

        logoToggle.classList.add("aberta");
        logoToggle.setAttribute("aria-expanded", "true");
        logoToggle.setAttribute("aria-label", "Fechar menu");
    }

    function fecharSidebar() {
        sidebar.classList.remove(
            "active",
            "hover-open",
            "open"
        );

        document.body.classList.remove(
            "sidebar-open",
            "sidebar-collapsed"
        );

        if (overlay) {
            overlay.classList.remove("active");
        }

        logoToggle.classList.remove("aberta");
        logoToggle.setAttribute("aria-expanded", "false");
        logoToggle.setAttribute("aria-label", "Abrir menu");
    }

    function alternarSidebar(event) {
        event.preventDefault();
        event.stopPropagation();

        if (estaAberta()) {
            fecharSidebar();
        } else {
            abrirSidebar();
        }
    }

    /*
    A sidebar abre somente por este clique.
    Não existe mouseenter nem mouseleave.
    */
    logoToggle.addEventListener("click", alternarSidebar);

    if (overlay) {
        overlay.addEventListener("click", fecharSidebar);
    }

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            fecharSidebar();
        }
    });

    links.forEach(function (link) {
        link.addEventListener("click", function () {
            if (window.innerWidth <= 900) {
                fecharSidebar();
            }
        });
    });

    window.addEventListener("resize", function () {
        fecharSidebar();
    });

});