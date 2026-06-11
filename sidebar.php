<?php 
require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';

$pagina = basename($_SERVER['PHP_SELF']);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/sidebar.css">

<button class="mpd-sidebar-toggle d-md-none" id="btnSidebar" type="button">
    <i class="bi bi-list"></i>
</button>

<div class="mpd-sidebar-overlay" id="sidebarOverlay"></div>

<aside class="mpd-sidebar" id="sidebar">

    <div class="mpd-sidebar-header">
        <div class="mpd-brand">
            <div class="mpd-brand-icon">
                <img src="img/logo-branca.png" alt="Meu Ponto Diário">
            </div>

            <div class="mpd-brand-text">
                <strong>Meu Ponto Diário</strong>
                <span>RH</span>
            </div>
        </div>
    </div>

    <div class="mpd-sidebar-section">

        <span class="mpd-section-title"><?= t('gestao') ?></span>

        <a href="ponto.php" class="mpd-link <?= $pagina == 'ponto.php' ? 'active' : '' ?>">
            <i class="bi bi-clock"></i>
            <span><?= t('ponto') ?></span>
        </a>

        <a href="banco-horas.php" class="mpd-link <?= $pagina == 'banco-horas.php' ? 'active' : '' ?>">
            <i class="bi bi-hourglass-split"></i>
            <span><?= t('banco_horas') ?></span>
        </a>

        <a href="ferias.php" class="mpd-link <?= $pagina == 'ferias.php' ? 'active' : '' ?>">
            <i class="bi bi-umbrella"></i>
            <span><?= t('ferias') ?></span>
        </a>

        <a href="holerite.php" class="mpd-link <?= $pagina == 'holerite.php' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span><?= t('holerite') ?></span>
        </a>

        <a href="licenca.php" class="mpd-link <?= $pagina == 'licenca.php' ? 'active' : '' ?>">
            <i class="bi bi-heart-pulse"></i>
            <span><?= t('licencas') ?></span>
        </a>

        <a href="emergencias.php" class="mpd-link <?= $pagina == 'emergencias.php' ? 'active' : '' ?>">
            <i class="bi bi-exclamation-triangle"></i>
            <span><?= t('emergencias') ?></span>
        </a>

        <a href="comunicados.php" class="mpd-link <?= $pagina == 'comunicados.php' ? 'active' : '' ?>">
            <i class="bi bi-bell"></i>
            <span><?= t('comunicados') ?></span>
        </a>

    </div>

    <div class="mpd-sidebar-section">

        <span class="mpd-section-title"><?= t('administracao') ?></span>

        <a href="funcionarios.php" class="mpd-link <?= $pagina == 'funcionarios.php' ? 'active' : '' ?>">
            <i class="bi bi-people"></i>
            <span><?= t('funcionarios') ?></span>
        </a>

        <a href="importar-pontos.php" class="mpd-link <?= $pagina == 'importar-pontos.php' ? 'active' : '' ?>">
            <i class="bi bi-clock-history"></i>
            <span><?= t('importar_pontos') ?></span>
        </a>

        <a href="importar-funcionarios.php" class="mpd-link <?= $pagina == 'importar-funcionarios.php' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-arrow-up"></i>
            <span><?= t('importar_funcionarios') ?></span>
        </a>

        <a href="cadastrarUsuario.php" class="mpd-link <?= $pagina == 'cadastrarUsuario.php' ? 'active' : '' ?>">
            <i class="bi bi-person-fill-add"></i>
            <span><?= t('cadastrar_usuario') ?></span>
        </a>

    </div>

    <div class="mpd-sidebar-footer">

        <a href="perfil.php" class="mpd-link <?= $pagina == 'perfil.php' ? 'active' : '' ?>">
            <i class="bi bi-person"></i>
            <span><?= t('perfil') ?></span>
        </a>

        <a href="configuracao.php" class="mpd-link <?= $pagina == 'configuracao.php' ? 'active' : '' ?>">
            <i class="bi bi-gear"></i>
            <span><?= t('configuracoes') ?></span>
        </a>

        <a href="logout.php" class="mpd-link danger" onclick="return confirm('<?= t('confirmar_sair') ?>')">
            <i class="bi bi-box-arrow-right"></i>
            <span><?= t('sair') ?></span>
        </a>

    </div>

</aside>

<?php include 'sininho.php'; ?>

<script src="js/sidebar.js"></script>
<script src="js/translate.js"></script>