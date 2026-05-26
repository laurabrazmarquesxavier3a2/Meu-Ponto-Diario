<?php
$pagina = basename($_SERVER['PHP_SELF']);
?>

<!-- BOTÃO MOBILE -->
<button class="btn-sidebar-mobile" id="btnSidebar">
    ☰
</button>

<!-- OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
<!-- LOGO -->
<div class="logo">

    <img src="../img/logo-azul.png">

    <span>
        Meu Ponto Diário
    </span>

</div>

    <!-- MENU -->
    <div class="menu">

        <a href="pontoF.php"
        class="<?= $pagina == 'pontoF.php' ? 'active' : '' ?>">

            <i class="fa-regular fa-clock"></i>
            Registro de Ponto

        </a>

        <a href="documentos.php"
        class="<?= $pagina == 'documentos.php' ? 'active' : '' ?>">

            <i class="fa-regular fa-file"></i>
            Solicitar Documento

        </a>

        <a href="pedidos.php"
        class="<?= $pagina == 'pedidos.php' ? 'active' : '' ?>">

            <i class="fa-regular fa-envelope"></i>
            Pedidos

        </a>

        <a href="permissoes.php"
        class="<?= $pagina == 'permissoes.php' ? 'active' : '' ?>">

            <i class="fa-regular fa-calendar"></i>
            Permissões

        </a>

        <a href="seguranca.php"
        class="<?= $pagina == 'seguranca.php' ? 'active' : '' ?>">

            <i class="fa-solid fa-shield"></i>
            Segurança

        </a>

        <a href="perfilfunc.php"
        class="<?= $pagina == 'perfilfunc.php' ? 'active' : '' ?>">

            <i class="fa-regular fa-user"></i>
            Perfil

        </a>

    </div>

</div>

<script src="../js/sidebarfunc.js"></script>