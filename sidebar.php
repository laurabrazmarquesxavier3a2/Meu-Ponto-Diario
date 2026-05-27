<?php require_once 'auth.php'; ?>

<?php
$pagina = basename($_SERVER['PHP_SELF']);
?>

<!-- BOTÃO MOBILE -->
<button class="btn btn-primary d-md-none m-3" id="btnSidebar">
    <i class="bi bi-list"></i>
</button>

<!-- OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<div class="sidebar p-3 fs-5 d-flex flex-column" id="sidebar">

    <!-- TOPO -->
    <div>

        <!-- LOGO -->
        <h5 
            class="mb-4 d-flex align-items-center sidebar-logo"
        >

            <img 
                src="img/logo-branca.png"
                class="logo-branca me-2"
            >

            <span>
                Meu Ponto Diário
            </span>

        </h5>

        <!-- LINKS -->
        <a href="ponto.php"
        class="<?= $pagina == 'ponto.php' ? 'active' : '' ?>">

            <i class="bi bi-clock me-2"></i>
            Ponto

        </a>

        <a href="banco-horas.php"
        class="<?= $pagina == 'banco-horas.php' ? 'active' : '' ?>">

            <i class="bi bi-hourglass-split me-2"></i>
            Banco de horas

        </a>

        <a href="ferias.php"
        class="<?= $pagina == 'ferias.php' ? 'active' : '' ?>">

            <i class="bi bi-umbrella me-2"></i>
            Férias

        </a>

        <a href="holerite.php"
        class="<?= $pagina == 'holerite.php' ? 'active' : '' ?>">

            <i class="bi bi-file-earmark-text me-2"></i>
            Holerite

        </a>

        <a href="licenca.php"
        class="<?= $pagina == 'licenca.php' ? 'active' : '' ?>">

            <i class="bi bi-heart-pulse me-2"></i>
            Licenças médicas

        </a>

        <a href="emergencias.php"
        class="<?= $pagina == 'emergencias.php' ? 'active' : '' ?>">

            <i class="bi bi-exclamation-triangle me-2"></i>
            Emergências

        </a>

        <a href="comunicados.php"
        class="<?= $pagina == 'comunicados.php' ? 'active' : '' ?>">

            <i class="bi bi-bell me-2"></i>
            Comunicados

        </a>

        <a href="duvidasRH.php"
        class="<?= $pagina == 'duvidasRH.php' ? 'active' : '' ?>">

         <i class="bi bi-patch-question me-2"></i>
         Dúvidas

        </a>

        <a href="funcionarios.php"
        class="<?= $pagina == 'funcionarios.php' ? 'active' : '' ?>">

            <i class="bi bi-people me-2"></i>
            Funcionários

        </a>

    </div>

    <!-- RODAPÉ -->
    <div class="mt-auto">

        <a href="perfil.php"
        class="<?= $pagina == 'perfil.php' ? 'active' : '' ?>">

            <i class="bi bi-person me-2"></i>
            Meu perfil

        </a>

        <a href="configuracao.php"
        class="<?= $pagina == 'configuracao.php' ? 'active' : '' ?>">

            <i class="bi bi-gear me-2"></i>
            Configurações

        </a>

        <a href="logout.php"
        onclick="return confirm('Deseja realmente sair?')">

            <i class="bi bi-box-arrow-right me-2"></i>
            Sair

        </a>

    </div>

</div>

<!-- ESTILO DA LOGO -->
<style>

.sidebar-logo{

    font-size:32px !important;
    font-weight:700;
    color:white !important;
    line-height:1;
    font-family:'Segoe UI', sans-serif;

}

.sidebar-logo span{

    color:white !important;
    font-family:'Segoe UI', sans-serif;
    font-weight:700;
    letter-spacing:-1px;

}

.logo-branca{

    width:48px;
    height:auto;
    transition:.3s;

}

/* LINKS */

.sidebar a{

    transition:.25s;
    border-radius:10px;
    color:white !important;
    font-family:'Segoe UI', sans-serif;
    font-weight:500;

}

.sidebar a i{

    color:white !important;

}

/* HOVER */

.sidebar a:hover{

    transform:translateX(4px);

}

/* ATIVO */

.sidebar a.active{

    transform:scale(1.02);

}

</style>