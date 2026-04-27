<?php
$pagina = basename($_SERVER['PHP_SELF']);
?>

<!-- BOTÃO MOBILE -->
<button class="btn btn-primary d-md-none m-3" id="btnSidebar">
    <i class="bi bi-list"></i>
</button>

<!-- OVERLAY (fundo escuro) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<div class="sidebar p-3 fs-5 d-flex flex-column" id="sidebar">

    <!-- TOPO -->
    <div>
        <h5 class="mb-4 fs-3 d-flex align-items-center">
            <img src="img/logo-branca.png" class="logo-branca me-2"> 
            Meu Ponto Diário
        </h5>

        <a href="ponto.php" class="<?= $pagina == 'ponto.php' ? 'active' : '' ?>">
            <i class="bi bi-clock me-2"></i>Ponto
        </a>

        <a href="banco-horas.php" class="<?= $pagina == 'banco-horas.php' ? 'active' : '' ?>">
            <i class="bi bi-hourglass-split me-2"></i>Banco de horas
        </a>

        <a href="ferias.php" class="<?= $pagina == 'ferias.php' ? 'active' : '' ?>">
            <i class="bi bi-umbrella me-2"></i>Férias
        </a>

        <a href="holerite.php" class="<?= $pagina == 'holerite.php' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text me-2"></i>Holerite
        </a>

        <a href="licenca.php" class="<?= $pagina == 'licenca.php' ? 'active' : '' ?>">
            <i class="bi bi-heart-pulse me-2"></i>Licenças médicas
        </a>

        <a href="emergencias.php" class="<?= $pagina == 'emergencias.php' ? 'active' : '' ?>">
            <i class="bi bi-exclamation-triangle me-2"></i>Emergências
        </a>

        <a href="comunicados.php" class="<?= $pagina == 'comunicados.php' ? 'active' : '' ?>">
            <i class="bi bi-bell me-2"></i>Comunicados
        </a>

        <a href="funcionarios.php" class="<?= $pagina == 'funcionarios.php' ? 'active' : '' ?>">
            <i class="bi bi-people me-2"></i>Funcionários
        </a>
    </div>

    <!-- RODAPÉ -->
    <div class="mt-auto">

        <a href="perfil.php" class="<?= $pagina == 'perfil.php' ? 'active' : '' ?>">
            <i class="bi bi-person me-2"></i>Meu perfil
        </a>

        <a href="configuracao.php" class="<?= $pagina == 'configuracao.php' ? 'active' : '' ?>">
            <i class="bi bi-gear me-2"></i>Configurações
        </a>

        <a href="#">
            <i class="bi bi-box-arrow-right me-2"></i>Sair
        </a>

    </div>
</div>

<!-- SCRIPT -->
<script>
const sidebar = document.getElementById("sidebar");
const btn = document.getElementById("btnSidebar");
const overlay = document.getElementById("sidebarOverlay");

btn.addEventListener("click", () => {
    sidebar.classList.toggle("show");
    overlay.classList.toggle("show");
});

overlay.addEventListener("click", () => {
    sidebar.classList.remove("show");
    overlay.classList.remove("show");
});
</script>