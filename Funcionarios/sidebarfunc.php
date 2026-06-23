<?php
require_once '../lang.php';

$pagina = basename($_SERVER['PHP_SELF']);
?>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
    rel="stylesheet"
>

<link rel="stylesheet" href="../css/sidebar.css?v=20">

<!-- LOGO QUE ABRE E FECHA A SIDEBAR -->
<button
    type="button"
    class="mpd-logo-toggle"
    id="mpdLogoToggle"
    aria-label="Abrir menu"
    aria-expanded="false"
>
    <img
        src="../img/logo-branca.png"
        alt="Meu Ponto Diário"
    >
</button>

<!-- FUNDO ESCURO NO MOBILE -->
<div
    class="mpd-sidebar-overlay"
    id="sidebarOverlay"
></div>

<aside
    class="mpd-sidebar"
    id="sidebar"
>

    <div class="mpd-sidebar-header">

        <div class="mpd-brand-text">
            <strong>Meu Ponto Diário</strong>
            <span><?= t('colaborador') ?></span>
        </div>

    </div>

    <div class="mpd-sidebar-section">

        <span class="mpd-section-title">
            <?= t('minha_area') ?>
        </span>

        <a
            href="pontoF.php"
            class="mpd-link <?= $pagina === 'pontoF.php' ? 'active' : '' ?>"
        >
            <i class="bi bi-clock-history"></i>
            <span><?= t('historico_ponto') ?></span>
        </a>

        <a
            href="banco-HorasColab.php"
            class="mpd-link <?= $pagina === 'banco-HorasColab.php' ? 'active' : '' ?>"
        >
            <i class="bi bi-graph-up-arrow"></i>
            <span><?= t('banco_horas') ?></span>
        </a>

        <a
            href="holerite.php"
            class="mpd-link <?= $pagina === 'holerite.php' ? 'active' : '' ?>"
        >
            <i class="bi bi-file-earmark-text"></i>
            <span><?= t('holerite') ?></span>
        </a>

        <a
            href="pedidosf.php"
            class="mpd-link <?= $pagina === 'pedidosf.php' ? 'active' : '' ?>"
        >
            <i class="bi bi-calendar-check"></i>
            <span><?= t('pedidos') ?></span>
        </a>

        <a
            href="SoliLic.php"
            class="mpd-link <?= $pagina === 'SoliLic.php' ? 'active' : '' ?>"
        >
            <i class="bi bi-file-medical"></i>
            <span><?= t('solicitacoes') ?></span>
        </a>

        <a
            href="seguranca.php"
            class="mpd-link <?= $pagina === 'seguranca.php' ? 'active' : '' ?>"
        >
            <i class="bi bi-shield-check"></i>
            <span><?= t('seguranca') ?></span>
        </a>

        <a
            href="comunicafunc.php"
            class="mpd-link <?= $pagina === 'comunicafunc.php' ? 'active' : '' ?>"
        >
            <i class="bi bi-megaphone"></i>
            <span><?= t('comunicados') ?></span>
        </a>

    </div>

    <div class="mpd-sidebar-footer">

        <a
            href="perfilfunc.php"
            class="mpd-link <?= $pagina === 'perfilfunc.php' ? 'active' : '' ?>"
        >
            <i class="bi bi-person"></i>
            <span><?= t('perfil') ?></span>
        </a>

        <a
            href="configuracaofunc.php"
            class="mpd-link <?= $pagina === 'configuracaofunc.php' ? 'active' : '' ?>"
        >
            <i class="bi bi-gear"></i>
            <span><?= t('configuracoes') ?></span>
        </a>

        <a
            href="../logout.php"
            class="mpd-link danger"
            onclick="return confirm('<?= t('confirmar_sair') ?>')"
        >
            <i class="bi bi-box-arrow-right"></i>
            <span><?= t('sair') ?></span>
        </a>

    </div>

</aside>

<script src="../js/sidebar.js?v=20"></script>
<script src="../js/translate.js"></script>