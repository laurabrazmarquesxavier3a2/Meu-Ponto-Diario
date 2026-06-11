(function () {

    function pegarTemaSalvo() {
        return (
            localStorage.getItem("temaSistema") ||
            localStorage.getItem("theme") ||
            localStorage.getItem("tema") ||
            "light"
        );
    }

    function salvarTema(tema) {
        localStorage.setItem("temaSistema", tema);
        localStorage.setItem("theme", tema);
        localStorage.setItem("tema", tema);
    }

    function aplicarClasseTema(tema) {
        const html = document.documentElement;
        const body = document.body;

        if (tema === "dark") {
            html.classList.add("dark-mode");

            if (body) {
                body.classList.add("dark-mode");
            }
        } else {
            html.classList.remove("dark-mode");

            if (body) {
                body.classList.remove("dark-mode");
            }
        }
    }

    function injetarCssDarkMode() {

        if (document.getElementById("mpd-dark-mode-css")) {
            return;
        }

        const style = document.createElement("style");
        style.id = "mpd-dark-mode-css";

        style.innerHTML = `
            html.dark-mode,
            body.dark-mode{
                background:#07111f !important;
                color:#e5e7eb !important;
            }

            body.dark-mode,
            body.dark-mode .content,
            body.dark-mode .main,
            body.dark-mode main,
            body.dark-mode .container,
            body.dark-mode .container-fluid,
            body.dark-mode .pedidos-page{
                background:#07111f !important;
                color:#e5e7eb !important;
            }

            body.dark-mode .card,
            body.dark-mode .card-config,
            body.dark-mode .card-dashboard,
            body.dark-mode .solic-card,
            body.dark-mode .kpi-card,
            body.dark-mode .empty-box,
            body.dark-mode .empty-section,
            body.dark-mode .filter-box,
            body.dark-mode .info-box,
            body.dark-mode .modal-content,
            body.dark-mode .config-item,
            body.dark-mode .card-doc,
            body.dark-mode .perfil-card,
            body.dark-mode .box,
            body.dark-mode .card-box,
            body.dark-mode .message-box,
            body.dark-mode .calendar-box,
            body.dark-mode .comunicado-card,
            body.dark-mode .pedidos-card,
            body.dark-mode .list-group-item{
                background:#0f172a !important;
                color:#e5e7eb !important;
                border-color:#334155 !important;
            }

            body.dark-mode .bg-light,
            body.dark-mode .solic-msg,
            body.dark-mode .ocorrencia-descricao,
            body.dark-mode .alert-info,
            body.dark-mode .empty-box,
            body.dark-mode .info-box{
                background:#111827 !important;
                color:#e5e7eb !important;
                border-color:#334155 !important;
            }

            body.dark-mode .text-muted,
            body.dark-mode .text-secondary,
            body.dark-mode small,
            body.dark-mode p,
            body.dark-mode label,
            body.dark-mode .form-text,
            body.dark-mode span{
                color:#cbd5e1 !important;
            }

            body.dark-mode h1,
            body.dark-mode h2,
            body.dark-mode h3,
            body.dark-mode h4,
            body.dark-mode h5,
            body.dark-mode h6,
            body.dark-mode strong,
            body.dark-mode .page-title,
            body.dark-mode .title h1,
            body.dark-mode .title h2,
            body.dark-mode .title p{
                color:#f8fafc !important;
            }

            body.dark-mode .form-control,
            body.dark-mode .form-select,
            body.dark-mode textarea,
            body.dark-mode input{
                background:#111827 !important;
                color:#f8fafc !important;
                border-color:#334155 !important;
            }

            body.dark-mode .form-control::placeholder,
            body.dark-mode textarea::placeholder{
                color:#94a3b8 !important;
            }

            body.dark-mode .border,
            body.dark-mode .rounded{
                border-color:#334155 !important;
            }

            body.dark-mode .table,
            body.dark-mode .table td,
            body.dark-mode .table th{
                background:#0f172a !important;
                color:#e5e7eb !important;
                border-color:#334155 !important;
            }

            body.dark-mode .btn-outline-dark,
            body.dark-mode .btn-outline-secondary,
            body.dark-mode .btn-outline-primary{
                color:#e5e7eb !important;
                border-color:#64748b !important;
            }

            body.dark-mode .form-check-input{
                background-color:#111827 !important;
                border:2px solid #64748b !important;
                box-shadow:none !important;
            }

            body.dark-mode .form-check-input:checked{
                background-color:#0d6efd !important;
                border-color:#0d6efd !important;
            }

            body.dark-mode .form-check-input:checked[type="checkbox"],
            body.dark-mode .form-check-input:checked[type="radio"]{
                background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m6 10 3 3 6-6'/%3e%3c/svg%3e") !important;
            }

            body.dark-mode .form-check-input:focus{
                border-color:#60a5fa !important;
                box-shadow:0 0 0 .25rem rgba(13,110,253,.25) !important;
            }

            body.dark-mode .badge.bg-light,
            body.dark-mode .input-group-text{
                background:#111827 !important;
                color:#e5e7eb !important;
                border-color:#334155 !important;
            }

            body.dark-mode .comunicado-fixado{
                background:#1f2937 !important;
                border-left-color:#f59e0b !important;
            }
        `;

        document.head.appendChild(style);
    }

    function atualizarBotoes(tema) {
        const btnLight = document.getElementById("lightMode");
        const btnDark = document.getElementById("darkMode");

        if (!btnLight || !btnDark) {
            return;
        }

        if (tema === "dark") {
            btnDark.classList.remove("btn-outline-dark", "btn-outline-primary");
            btnDark.classList.add("btn-dark");

            btnLight.classList.remove("btn-primary", "btn-dark");
            btnLight.classList.add("btn-outline-primary");
        } else {
            btnLight.classList.remove("btn-outline-primary", "btn-outline-dark");
            btnLight.classList.add("btn-primary");

            btnDark.classList.remove("btn-dark", "btn-primary");
            btnDark.classList.add("btn-outline-dark");
        }
    }

    function aplicarTema(tema) {
        salvarTema(tema);
        aplicarClasseTema(tema);
        atualizarBotoes(tema);
    }

    aplicarClasseTema(pegarTemaSalvo());

    document.addEventListener("DOMContentLoaded", function () {

        injetarCssDarkMode();

        const temaInicial = pegarTemaSalvo();
        aplicarTema(temaInicial);

        const btnLight = document.getElementById("lightMode");
        const btnDark = document.getElementById("darkMode");

        if (btnLight) {
            btnLight.addEventListener("click", function () {
                aplicarTema("light");
            });
        }

        if (btnDark) {
            btnDark.addEventListener("click", function () {
                aplicarTema("dark");
            });
        }
    });

})();