<?php
require_once 'lang.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Leis Trabalhistas | Meu Ponto Diário</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/leis.css">
</head>

<body>

<section class="ajuda-hero">

    <div class="hero-bg-logo"></div>

    <div class="container position-relative">

        <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">


        </div>

        <div class="row align-items-center g-5">

            <div class="col-lg-7 reveal">

                <span class="badge badge-soft rounded-pill px-3 py-2 mb-4">
                    <i class="bi bi-journal-text me-1"></i>
                    Guia trabalhista
                </span>

                <h1 class="titulo-ajuda">
                    Leis
                    <span>Trabalhistas</span>
                </h1>

                <p class="subtitulo-ajuda">
                    Entenda os principais pontos sobre jornada, horas extras,
                    banco de horas, férias e obrigações trabalhistas.
                </p>

                <div class="search-box mt-4">

                    <i class="bi bi-search"></i>

                    <input
                        type="text"
                        id="buscarFaq"
                        placeholder="Pesquisar uma lei ou assunto..."
                    >

                </div>

            </div>

            <div class="col-lg-5 reveal">

                <div class="support-card">

                    <div class="support-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>

                    <h3>Informação organizada</h3>

                    <p>
                        Consulte tópicos importantes da rotina trabalhista
                        de forma simples e objetiva.
                    </p>

                    <a href="ajuda.php" class="btn btn-main w-100">
                        Ir para central de ajuda
                        <i class="bi bi-arrow-right ms-2"></i>
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>

<section class="section">

    <div class="container">

        <div class="text-center mb-5 reveal">

            <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">
                Principais tópicos
            </span>

            <h2 class="section-title">
                Regras importantes
            </h2>

            <p class="text-muted fs-5">
                Clique em um assunto para visualizar a explicação.
            </p>

        </div>

        <div class="faq-grid">

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-clock-history"></i>
                    Controle de Jornada
                </div>

                <div class="faq-answer">
                    Empresas devem registrar corretamente os horários de entrada,
                    saída, intervalos e jornada dos funcionários, garantindo
                    controle adequado da carga horária.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-plus-circle-fill"></i>
                    Horas Extras
                </div>

                <div class="faq-answer">
                    Horas trabalhadas além da jornada normal devem ser controladas
                    e compensadas ou remuneradas conforme as regras aplicáveis.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-hourglass-split"></i>
                    Banco de Horas
                </div>

                <div class="faq-answer">
                    O banco de horas deve seguir acordo individual ou coletivo,
                    registrando créditos e débitos de jornada de forma clara.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-cup-hot-fill"></i>
                    Intervalo Intrajornada
                </div>

                <div class="faq-answer">
                    O intervalo para descanso e alimentação deve ser respeitado
                    conforme a jornada diária do colaborador.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-umbrella-fill"></i>
                    Férias
                </div>

                <div class="faq-answer">
                    Após o período aquisitivo, o colaborador tem direito a férias,
                    que devem ser organizadas e registradas pela empresa.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-file-medical-fill"></i>
                    Licença Médica
                </div>

                <div class="faq-answer">
                    Atestados e licenças médicas devem ser enviados pelo colaborador
                    e analisados pelo RH, mantendo registro do período afastado.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-cash-stack"></i>
                    Holerite
                </div>

                <div class="faq-answer">
                    O holerite deve demonstrar salário, descontos, adicionais,
                    benefícios e demais informações referentes ao pagamento.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-building-lock"></i>
                    Dados por Empresa
                </div>

                <div class="faq-answer">
                    Cada empresa deve manter seus próprios registros de funcionários,
                    jornadas, documentos e solicitações de forma separada e segura.
                </div>
            </div>

        </div>

    </div>

</section>

<section class="section pt-0">

    <div class="container reveal">

        <div class="cta-ajuda">

            <div class="row align-items-center g-4">

                <div class="col-lg-2 text-center text-lg-start">
                    <img
                        src="img/logo-azul.png"
                        alt="Meu Ponto Diário"
                        class="cta-logo"
                    >
                </div>

                <div class="col-lg-7">

                    <h2 class="fw-bold">
                        Precisa entender melhor alguma regra?
                    </h2>

                    <p class="mb-0">
                        Acesse a central de ajuda e envie sua dúvida para análise.
                    </p>

                </div>

                <div class="col-lg-3 text-lg-end">

                    <a href="ajuda.php" class="btn btn-light fw-bold rounded-4 px-4 py-3">
                        Abrir ajuda
                    </a>

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
                        class="footer-logo"
                    >

                    <h5 class="fw-bold mb-0">
                        Meu Ponto Diário
                    </h5>

                </div>

                <small>
                    Sistema de Gestão de RH, Ponto e Banco de Horas.
                </small>

            </div>

            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">

                <a href="index.php" class="me-4">
                    Início
                </a>

                <a href="ajuda.php" class="me-4">
                    Ajuda
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/ajuda.js"></script>
<script src="js/translate.js"></script>
</body>
</html>