<?php
$pagina = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Segurança</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet"
    href="../css/seguranca.css">

</head>

<body>

    <!-- ALERT -->
    <div class="custom-alert" id="alertBox">

        <i class="fa-solid fa-circle-check"></i>
        Reporte enviado com sucesso!

    </div>

    <!-- SIDEBAR -->
    <div class="sidebar">

        <div class="logo">

            <img src="../img/logo-azul.png">

            <span>
                Meu Ponto Diário
            </span>

        </div>

        <div class="menu">

            <a href="pontoF.php">
                <i class="fa-regular fa-clock"></i>
                Registro de Ponto
            </a>

            <a href="documentos.php">
                <i class="fa-regular fa-file"></i>
                Solicitar Documento
            </a>

            <a href="pedidos.php">
                <i class="fa-regular fa-envelope"></i>
                Pedidos
            </a>

            <a href="permissoes.php">
                <i class="fa-regular fa-calendar"></i>
                Permissões
            </a>

            <a href="seguranca.php" class="active">
                <i class="fa-solid fa-shield"></i>
                Segurança
            </a>

            <a href="perfil.php">
                <i class="fa-regular fa-user"></i>
                Perfil
            </a>

        </div>

    </div>

    <!-- MAIN -->
    <div class="main">

        <!-- BOLHAS -->
        <div class="bg-circle circle1"></div>
        <div class="bg-circle circle2"></div>

        <!-- CARD -->
        <div class="security-card">

            <h1 class="title">
                Reportar Ocorrência
            </h1>

            <p class="subtitle">
                Utilize o formulário abaixo para registrar qualquer situação de risco,
                comportamento inadequado ou problema estrutural.
            </p>

            <!-- FORM -->
            <form id="reportForm">

                <div class="row g-4">
<!-- CATEGORIAS MELHORES -->
<select class="form-select" required>

    <option value="">
        Selecione uma categoria
    </option>

    <option>
        Assédio
    </option>

    <option>
        Agressão
    </option>

    <option>
        Discriminação
    </option>

    <option>
        Problema elétrico
    </option>

    <option>
        Equipamento danificado
    </option>

    <option>
        Risco de acidente
    </option>

    <option>
        Vazamento
    </option>

    <option>
        Outro
    </option>

</select>

                    <!-- DESCRIÇÃO -->
                    <div class="col-12">

                        <label class="form-label fw-bold">
                            Descrição detalhada
                        </label>

                        <textarea class="form-control"
                        rows="6"
                        placeholder="Descreva detalhadamente o ocorrido..."
                        required></textarea>

                    </div>

                    <!-- TESTEMUNHAS -->
                    <div class="col-12">

                        <label class="form-label fw-bold">
                            Pessoas envolvidas ou testemunhas
                        </label>

                        <input type="text"
                        class="form-control"
                        placeholder="Opcional">

                    </div>

                    <!-- UPLOAD -->
                    <div class="col-12">

                        <label class="form-label fw-bold mb-3">
                            Evidências
                        </label>

                        <div class="upload-box">

                            <i class="fa-solid fa-cloud-arrow-up"></i>

                            <h5>
                                Clique para enviar arquivos
                            </h5>

                            <p class="text-muted">
                                Fotos, vídeos ou documentos
                            </p>

                            <input type="file"
                            class="form-control mt-3"
                            multiple>

                        </div>

                    </div>

                    <!-- BOTÃO -->
                    <div class="col-12">

                        <button class="btn-report w-100">

                            <i class="fa-solid fa-paper-plane"></i>
                            Enviar ocorrência

                        </button>

                    </div>

                </div>

            </form>

            <!-- REPORTES -->
            <div class="reports">

                <h3 class="mb-4 mt-5">
                    Últimos reportes
                </h3>

                <div class="report-item">

                    <div>

                        <strong>
                            Assédio moral no setor administrativo
                        </strong>

                        <p class="text-muted mb-0">
                            25/05/2026
                        </p>

                    </div>

                    <div class="status analysis">
                        Em análise
                    </div>

                </div>

                <div class="report-item">

                    <div>

                        <strong>
                            Vazamento próximo ao laboratório
                        </strong>

                        <p class="text-muted mb-0">
                            24/05/2026
                        </p>

                    </div>

                    <div class="status urgent">
                        Urgente
                    </div>

                </div>

                <div class="report-item">

                    <div>

                        <strong>
                            Equipamento elétrico com defeito
                        </strong>

                        <p class="text-muted mb-0">
                            22/05/2026
                        </p>

                    </div>

                    <div class="status resolved">
                        Resolvido
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- BOOTSTRAP -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS -->
    <script>

        const form = document.getElementById('reportForm');
        const alertBox = document.getElementById('alertBox');

        form.addEventListener('submit', function(e){

            e.preventDefault();

            alertBox.classList.add('show');

            setTimeout(() => {

                alertBox.classList.remove('show');

            }, 3500);

            form.reset();

        });

        // EFEITO PARALLAX

        document.addEventListener('mousemove', (e) => {

            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;

            document.querySelector('.circle1').style.transform =
            `translate(${x * 30}px, ${y * 30}px)`;

            document.querySelector('.circle2').style.transform =
            `translate(-${x * 30}px, -${y * 30}px)`;

        });

        // ANIMAÇÃO DOS REPORTES

        const reports = document.querySelectorAll('.report-item');

        reports.forEach((item, index) => {

            item.style.opacity = "0";
            item.style.transform = "translateY(20px)";

            setTimeout(() => {

                item.style.transition = ".5s";
                item.style.opacity = "1";
                item.style.transform = "translateY(0)";

            }, 300 + (index * 180));

        });

    </script>

</body>

</html>