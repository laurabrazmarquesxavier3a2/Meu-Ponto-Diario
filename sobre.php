<?php
require_once 'lang.php';
?>

<!doctype html>
<html lang="pt-BR">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Sobre o Projeto – Meu Ponto Diário</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/sobre.css">

</head>

<body>

<div class="page-loader" id="pageLoader">
    <div class="loader-content">
        <div class="loader-glow"></div>
        <img src="img/logo-azul.png" alt="Meu Ponto Diário">
        <span>Carregando projeto...</span>
    </div>
</div>


<a href="index.php" class="top-logo" id="topLogo" aria-label="Voltar para início">
    <img src="img/logo-azul.png" alt="Meu Ponto Diário">
</a>

<section class="about-hero">

    <div class="particles-box" id="particlesBox"></div>

    <div class="about-bg-logo"></div>

    <div class="container position-relative">

        <div class="row align-items-center g-5">

            <div class="col-lg-7 reveal">

                <span class="badge badge-soft rounded-pill px-3 py-2 mb-4">
                    <i class="bi bi-people-fill me-1"></i>
                    Sobre o projeto
                </span>

                <h1 class="about-title mb-4">
                    <span class="gradient-text">Meu Ponto Diário</span>
                </h1>

                <p class="about-desc mb-4">
                    O Meu Ponto Diário é um sistema de gestão de RH desenvolvido para centralizar processos importantes de uma empresa, como registro de ponto, banco de horas, férias, licenças médicas, holerites, comunicados e gerenciamento de funcionários.
                </p>

                <p class="about-desc mb-5">
                    A proposta do projeto é tornar a rotina do RH mais organizada, visual e eficiente, reduzindo o uso de planilhas e facilitando o acesso às informações tanto para administradores quanto para colaboradores.
                </p>

                <div class="d-flex flex-wrap gap-3">

                    <a href="#membros" class="btn btn-main">
                        Ver integrantes
                        <i class="bi bi-arrow-down ms-2"></i>
                    </a>

                    <a href="#projeto" class="btn btn-ghost">
                        Sobre a ideia
                    </a>

                </div>

            </div>

            <div class="col-lg-5 reveal">

                <div class="project-glass-card">

                    <div class="project-card-header">
                        <img src="img/logo-azul.png" alt="Meu Ponto Diário">

                        <div>
                            <h4>Meu Ponto Diário</h4>
                            <small>Gestão de RH inteligente</small>
                        </div>
                    </div>

                    <div class="project-line"></div>

                    <div class="project-mini-grid">

                        <div class="mini-stat">
                            <i class="bi bi-clock-history"></i>
                            <strong>Ponto</strong>
                            <span>Controle de jornada</span>
                        </div>

                        <div class="mini-stat">
                            <i class="bi bi-hourglass-split"></i>
                            <strong>Banco</strong>
                            <span>Horas extras</span>
                        </div>

                        <div class="mini-stat">
                            <i class="bi bi-umbrella-fill"></i>
                            <strong>Férias</strong>
                            <span>Solicitações</span>
                        </div>

                        <div class="mini-stat">
                            <i class="bi bi-megaphone-fill"></i>
                            <strong>Avisos</strong>
                            <span>Comunicados</span>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<section class="section" id="projeto">

    <div class="container">

        <div class="text-center mb-5 reveal">

            <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">
                A ideia
            </span>

            <h2 class="section-title display-5">
                Um sistema pensado para simplificar o RH
            </h2>

            <p class="text-muted fs-5 mx-auto" style="max-width:760px;">
                O projeto foi criado para apresentar uma solução moderna, organizada e prática para empresas que precisam acompanhar informações dos funcionários com mais segurança e clareza.
            </p>

        </div>

        <div class="row g-4">

            <div class="col-md-6 col-lg-3 reveal">
                <div class="about-feature">
                    <div class="about-icon">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>

                    <h5>Segurança</h5>

                    <p>
                        Separação de dados por empresa, permitindo que cada organização acesse apenas suas próprias informações.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 reveal">
                <div class="about-feature">
                    <div class="about-icon">
                        <i class="bi bi-speedometer2"></i>
                    </div>

                    <h5>Agilidade</h5>

                    <p>
                        Ações simples para cadastrar, consultar, filtrar e acompanhar dados importantes da rotina de RH.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 reveal">
                <div class="about-feature">
                    <div class="about-icon">
                        <i class="bi bi-kanban-fill"></i>
                    </div>

                    <h5>Organização</h5>

                    <p>
                        Informações centralizadas em painéis, cards, tabelas e páginas específicas para cada função.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 reveal">
                <div class="about-feature">
                    <div class="about-icon">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>

                    <h5>Crescimento</h5>

                    <p>
                        Uma base preparada para evoluir com novas funcionalidades conforme a empresa aumenta sua equipe.
                    </p>
                </div>
            </div>

        </div>

    </div>

</section>

<section class="section timeline-section">

    <div class="container">

        <div class="row align-items-center g-5">

            <div class="col-lg-5 reveal">

                <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">
                    Desenvolvimento
                </span>

                <h2 class="section-title display-5">
                    Como o projeto foi estruturado
                </h2>

                <p class="text-muted fs-5">
                    A construção do Meu Ponto Diário foi dividida em etapas para transformar a ideia inicial em um sistema funcional, visual e organizado.
                </p>

            </div>

            <div class="col-lg-7">

                <div class="timeline-item reveal">
                    <div class="timeline-number">1</div>

                    <div>
                        <h5>Planejamento da solução</h5>
                        <p>
                            Definição do problema, público-alvo, funcionalidades principais e fluxo de uso do sistema.
                        </p>
                    </div>
                </div>

                <div class="timeline-item reveal">
                    <div class="timeline-number">2</div>

                    <div>
                        <h5>Criação das telas</h5>
                        <p>
                            Desenvolvimento da interface com foco em navegação simples, identidade visual azul e layout profissional.
                        </p>
                    </div>
                </div>

                <div class="timeline-item reveal">
                    <div class="timeline-number">3</div>

                    <div>
                        <h5>Banco de dados e funcionalidades</h5>
                        <p>
                            Organização das tabelas, cadastros, login, funcionários, ponto, banco de horas e solicitações.
                        </p>
                    </div>
                </div>

                <div class="timeline-item reveal">
                    <div class="timeline-number">4</div>

                    <div>
                        <h5>Apresentação final</h5>
                        <p>
                            Ajustes visuais, explicação dos recursos e preparação para demonstrar o funcionamento do projeto.
                        </p>
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>

<section class="section" id="membros">

    <div class="container">

        <div class="text-center mb-5 reveal">

            <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">
                Equipe
            </span>

            <h2 class="section-title display-5">
                Integrantes do projeto
            </h2>

        </div>

        <div class="row g-4 justify-content-center">


            <div class="col-md-6 col-lg-4 reveal">
                <div class="member-card">

                    <div class="member-avatar">
                        <span>AB</span>
                    </div>

                    <h4> Ana Beatriz Vidal </h4>

                    <span class="member-role">
                        Back End / Pesquisa / Front End / Design
                    </span>

                    <p>
                        Ana Beatriz Vidal participou do desenvolvimento do back-end, realizou pesquisas para o projeto, contribuiu para o front-end e participou do design da interface.

                </div>
            </div>


            <div class="col-md-6 col-lg-4 reveal">
                <div class="member-card">

                    <div class="member-avatar">
                        <span>CA</span>
                    </div>

                    <h4>Carlos Antônio</h4>

                    <span class="member-role">
                        Banco De Dados / Back End
                    </span>

                    <p>
                        Carlos Antônio foi responsável pela estruturação do banco de dados e desenvolvimento do back-end, garantindo a organização e funcionalidade do sistema.
                    </p>

                </div>
            </div>


            <div class="col-md-6 col-lg-4 reveal">
                <div class="member-card">

                    <div class="member-avatar">
                        <span>GG</span>
                    </div>

                    <h4>Guilherme Gonçalves</h4>

                    <span class="member-role">
                        Banco De Dados / Segurança Da Informação / Back End
                    </span>

                    <p>
                        Guilherme Gonçalves contribuiu para a estruturação do banco de dados, implementação de medidas de segurança da informação e desenvolvimento do back-end do projeto.
                    </p>

                </div>
            </div>


            <div class="col-md-6 col-lg-4 reveal">
                <div class="member-card">

                    <div class="member-avatar">
                        <span>LB</span>
                    </div>

                    <h4> Laura Braz</h4>

                    <span class="member-role">
                        Front End / Pesquisa / Documentação / Design
                    </span>

                    <p>
                        Laura Braz contribuiu para o desenvolvimento do front-end, realizou pesquisas e documentou o projeto, além de participar do design da interface.
                    </p>

                </div>
            </div>


            <div class="col-md-6 col-lg-4 reveal">
                <div class="member-card">

                    <div class="member-avatar">
                        <span>LS</span>
                    </div>

                    <h4>Lyvia Santos</h4>

                    <span class="member-role">
                        Front End / Back End / Design / Segurança Da Informação
                    </span>

                    <p>
                        Lyvia Santos participou do desenvolvimento do front-end e back-end, contribuiu para o design da interface e implementou medidas de segurança da informação no projeto.
                    </p>

                </div>
            </div>


        </div>

    </div>

</section>


<footer>
        <div class="container">

            <div class="row align-items-center">

                <div class="col-md-6 text-center text-md-start">

                    <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-2">

                        <img
                            src="img/logo-branca.png"
                            alt="Meu Ponto Diário"
                            class="footer-logo">

                        <h5 class="fw-bold mb-0">
                            Meu Ponto Diário
                        </h5>

                    </div>

                    <small>
                        Sistema de Gestão de RH, Ponto e Banco de Horas.
                    </small>

                </div>

                <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">

                    <a href="#funcionalidades" class="me-4">
                        Funcionalidades
                    </a>

                    <a href="#planos" class="me-4">
                        Planos
                    </a>

                    <a href="ajuda.php" class="me-4">
                        Ajuda
                    </a>

                    <a href="leis.php" class="me-4">
                        Leis
                    </a>

                    <a href="login.php">
                        Entrar
                    </a>

                </div>

            </div>

            <hr class="border-light opacity-25 my-4">

            <div class="text-center">

                <small>
                    © 2026 Meu Ponto Diário · Todos os direitos reservados.
                </small>

            </div>

        </div>

    </footer>
    
</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/sobre.js"></script>

</body>

</html>