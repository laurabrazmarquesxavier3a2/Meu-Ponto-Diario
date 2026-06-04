<!DOCTYPE html>
<html lang="pt-br">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Ajuda | Meu Ponto Diário</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/ajuda.css">
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
                    <i class="bi bi-life-preserver me-1"></i>
                    Central de suporte
                </span>

                <h1 class="titulo-ajuda">
                    Como podemos
                    <span>ajudar você?</span>
                </h1>

                <p class="subtitulo-ajuda">
                    Encontre respostas rápidas sobre ponto, holerites, férias, licenças,
                    comunicados e acesso ao sistema.
                </p>

                <div class="search-box mt-4">

                    <i class="bi bi-search"></i>

                    <input
                        type="text"
                        id="buscarFaq"
                        placeholder="Pesquisar uma dúvida..."
                    >

                </div>

            </div>

            <div class="col-lg-5 reveal">

                <div class="support-card">

                    <div class="support-icon">
                        <i class="bi bi-headset"></i>
                    </div>

                    <h3>Precisa de suporte?</h3>

                    <p>
                        Registre sua dúvida e nossa equipe irá analisar sua solicitação.
                    </p>

                    <button id="abrirFormulario" class="btn btn-main w-100">
                        Registrar dúvida
                        <i class="bi bi-send ms-2"></i>
                    </button>

                </div>

            </div>

        </div>

    </div>

</section>

<section class="section">

    <div class="container">

        <div class="text-center mb-5 reveal">

            <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">
                Perguntas frequentes
            </span>

            <h2 class="section-title">
                Dúvidas mais comuns
            </h2>

        </div>

        <div class="faq-grid">

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-cash-stack"></i>
                    Como acessar meus holerites?
                </div>

                <div class="faq-answer">
                    Vá até a área de documentos e selecione a opção “Holerites”.
                    Lá você poderá visualizar os comprovantes enviados pelo RH.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-clock-history"></i>
                    Como ver meu registro de ponto?
                </div>

                <div class="faq-answer">
                    Utilize o menu lateral e clique em "Histórico de Ponto".
                    O sistema mostra a batida vinculada ao seu usuário e à sua empresa.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-umbrella-fill"></i>
                    Como solicitar férias?
                </div>

                <div class="faq-answer">
                    Acesse a tela de pedidos, escolha o período desejado e envie a solicitação.
                    O status ficará disponível na área de solicitações.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-file-medical-fill"></i>
                    Como enviar licença médica?
                </div>

                <div class="faq-answer">
                    Na tela de pedidos, envie o atestado em PDF, PNG, JPG ou JPEG.
                    Depois disso, o RH poderá visualizar sua solicitação.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-hourglass-split"></i>
                    Como funciona o banco de horas?
                </div>

                <div class="faq-answer">
                    O banco de horas mostra saldo total, horas extras e débitos.
                    Ele é calculado com base nas informações registradas/importadas pela empresa.
                </div>
            </div>

            <div class="faq-card reveal">
                <div class="faq-title">
                    <i class="bi bi-megaphone-fill"></i>
                    Onde vejo comunicados?
                </div>

                <div class="faq-answer">
                    Os comunicados aparecem na área do colaborador e são enviados pelo RH
                    para manter todos informados sobre avisos importantes.
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
                        Ainda está com dúvida?
                    </h2>

                    <p class="mb-0">
                        Registre sua dúvida detalhadamente para que o suporte consiga entender melhor sua necessidade.
                    </p>

                </div>

                <div class="col-lg-3 text-lg-end">

                    <button class="btn btn-light fw-bold rounded-4 px-4 py-3" id="abrirFormulario2">
                        Falar com suporte
                    </button>

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

<div class="modal-ajuda" id="modalDuvida">

    <div class="modal-content-ajuda">

        <button class="fechar" type="button">
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="modal-icon">
            <i class="bi bi-chat-dots-fill"></i>
        </div>

        <h2>Registrar Dúvida</h2>

        <p class="text-muted mb-4">
            Preencha os dados abaixo para enviar sua solicitação.
        </p>

        <form action="duvidas.php" method="POST" id="formDuvida">

            <input
                type="text"
                name="nome"
                placeholder="Digite seu nome"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Digite seu email"
                required
            >

            <textarea
                name="duvida"
                placeholder="Digite sua dúvida detalhadamente"
                required
            ></textarea>

            <button type="submit" id="btnEnviar">
                Enviar dúvida
            </button>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/ajuda.js"></script>

</body>
</html>