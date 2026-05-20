
</body>

</html><!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Central de Ajuda</title>

    <link rel="stylesheet" href="css/ajuda.css">
</head>

<body>

    <!-- NAVBAR -->

    <nav class="navbar">

        <div class="logo">

            <img class="logo-branca"
                src="img/logo-branca.png">

            <span>MEU PONTO DIÁRIO</span>

        </div>

        <div class="menu">

            <a href="sobre.php">Sobre</a>
            <a href="funcionalidades.php">Funcionalidades</a>
            <a href="ajuda.php">Ajuda</a>
            <a href="leis.php">Leis</a>

        </div>

    </nav>

    <!-- CONTAINER -->

    <div class="container">

        <h1 class="titulo-ajuda">
            Central de Ajuda
        </h1>

        <!-- FAQ -->

        <div class="faq-container">

            <!-- CARD 1 -->

            <div class="faq-card">

                <div class="faq-title">
                    Como registrar ponto?
                </div>

                <div class="faq-answer">
                    Faça login no sistema
                    e clique em registrar ponto.
                </div>

            </div>

            <!-- CARD 2 -->

            <div class="faq-card">

                <div class="faq-title">
                    Como gerar relatórios?
                </div>

                <div class="faq-answer">
                    Vá até a área de relatórios
                    dentro do sistema.
                </div>

            </div>

            <!-- CARD 3 -->

            <div class="faq-card">

                <div class="faq-title">
                    Como funciona banco de horas?
                </div>

                <div class="faq-answer">
                    O sistema calcula automaticamente
                    horas extras e saldo.
                </div>

            </div>

        </div>

        <!-- BOTÃO -->

        <div class="duvida-box">

            <button id="abrirFormulario">

                Registrar dúvida

            </button>

        </div>

    </div>

    <!-- MODAL -->

    <div class="modal" id="modalDuvida">

        <div class="modal-content">

            <!-- FECHAR -->

            <span class="fechar">

                &times;

            </span>

            <!-- TITULO -->

            <h2>

                Registrar Dúvida

            </h2>

            <!-- FORM -->

            <form action="registrar_duvida.php" method="POST">

                <!-- NOME -->

                <input
                    type="text"
                    name="nome"
                    placeholder="Seu nome"
                    required>

                <!-- EMAIL -->

                <input
                    type="email"
                    name="email"
                    placeholder="Seu email"
                    required>

                <!-- DÚVIDA -->

                <textarea
                    name="duvida"
                    placeholder="Digite sua dúvida"
                    required></textarea>

                <!-- BOTÃO -->

                <button type="submit">

                    Enviar dúvida

                </button>

            </form>

        </div>

    </div>

    <!-- SCRIPT -->

    <script>

        const modal =
            document.getElementById("modalDuvida");

        const abrir =
            document.getElementById("abrirFormulario");

        const fechar =
            document.querySelector(".fechar");

        abrir.onclick = () => {

            modal.style.display = "flex";

        }

        fechar.onclick = () => {

            modal.style.display = "none";

        }

        window.onclick = (e) => {

            if (e.target == modal) {

                modal.style.display = "none";

            }

        }

    </script>
