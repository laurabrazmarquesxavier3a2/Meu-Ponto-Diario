<?php
$pagina = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="../css/sidebarfunc.css">

<button class="btn-sidebar-mobile" id="btnSidebar">

    <i class="fa-solid fa-bars"></i>

</button>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">

    <div class="logo">

        <img src="../img/logo-azul.png">

        <span>
            Meu Ponto Diário
        </span>

    </div>

    <nav class="menu">

    <a href="banco-HorasColab.php"
    class="<?= $pagina == 'banco-HorasColab.php' ? 'active' : '' ?>">

        <i class="fa-solid fa-chart-line"></i>

        <span>
            Banco de Horas
        </span>

    </a>

        <a href="pontoF.php"
        class="<?= $pagina == 'pontoF.php' ? 'active' : '' ?>">

            <i class="fa-solid fa-clock"></i>

            <span>
                Histórico de Ponto
            </span>

        </a>

        <a href="holerite.php"
        class="<?= $pagina == 'holerite.php' ? 'active' : '' ?>">

            <i class="fa-solid fa-file-lines"></i>

            <span>
                Holerite
            </span>

        </a>

        <a href="pedidosf.php"
        class="<?= $pagina == 'pedidosf.php' ? 'active' : '' ?>">

            <i class="fa-solid fa-calendar-days"></i>

            <span>
                Pedidos
            </span>

        </a>

        <a href="SoliLic.php"
        class="<?= $pagina == 'SoliLic.php' ? 'active' : '' ?>">

            <i class="fa-solid fa-file-circle-plus"></i>

            <span>
                Solicitações E Licenças
            </span>

        </a>

        <a href="seguranca.php"
        class="<?= $pagina == 'seguranca.php' ? 'active' : '' ?>">

            <i class="fa-solid fa-shield-halved"></i>

            <span>
                Segurança
            </span>

        </a>

         <a href="comunicafunc.php"
        class="<?= $pagina == 'comunicafunc.php' ? 'active' : '' ?>">

             <i class="fa-solid fa-bullhorn"></i>

            <span>
                Comunicados
            </span>

        <a href="perfilfunc.php"
        class="<?= $pagina == 'perfilfunc.php' ? 'active' : '' ?>">

            <i class="fa-solid fa-user"></i>

            <span>
                Perfil
            </span>

        </a>

<a href="../login.php"
onclick="return confirm('Deseja realmente sair?')">

    <i class="fa-solid fa-arrow-right-from-bracket me-2"></i>

    <span>
        Sair
    </span>

</a>

    </nav>

</aside>

<script src="../js/sidebarfunc.js"></script>