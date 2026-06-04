document.addEventListener("DOMContentLoaded", () => {

    const sidebar = document.getElementById("sidebar");
    const btnSidebar = document.getElementById("btnSidebar");
    const overlay = document.getElementById("sidebarOverlay");

    let timerAbrir = null;
    let timerFechar = null;

    localStorage.removeItem("mpdSidebarCollapsed");

    function aplicarModoSidebar(){
        if(window.innerWidth > 900){
            document.body.classList.add("sidebar-collapsed");

            if(sidebar){
                sidebar.classList.remove("active");
                sidebar.classList.remove("hover-open");
            }

            if(overlay){
                overlay.classList.remove("active");
            }

        }else{
            document.body.classList.remove("sidebar-collapsed");
        }
    }

    aplicarModoSidebar();
    window.addEventListener("resize", aplicarModoSidebar);

    if(sidebar){

        sidebar.addEventListener("mouseenter", () => {

            if(window.innerWidth <= 900){
                return;
            }

            clearTimeout(timerFechar);

            timerAbrir = setTimeout(() => {
                sidebar.classList.add("hover-open");
            }, 450);

        });

        sidebar.addEventListener("mouseleave", () => {

            if(window.innerWidth <= 900){
                return;
            }

            clearTimeout(timerAbrir);

            timerFechar = setTimeout(() => {
                sidebar.classList.remove("hover-open");
            }, 180);

        });

    }

    if(btnSidebar && sidebar && overlay){

        btnSidebar.addEventListener("click", () => {
            sidebar.classList.add("active");
            overlay.classList.add("active");
        });

        overlay.addEventListener("click", () => {
            sidebar.classList.remove("active");
            overlay.classList.remove("active");
        });

    }

    const links = document.querySelectorAll(".mpd-link");

    links.forEach((link) => {

        link.addEventListener("click", () => {

            if(window.innerWidth <= 900 && sidebar && overlay){
                sidebar.classList.remove("active");
                overlay.classList.remove("active");
            }

        });

    });

});