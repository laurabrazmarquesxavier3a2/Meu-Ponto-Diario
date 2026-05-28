<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ajuda</title>

    <link rel="stylesheet" href="css/ajuda.css">
</head>

<body>

    <!-- BOTÃO VOLTAR -->
    <div class="topo-botao">

        <a
        href="index.php"
        class="btn-voltar">

            ← Voltar para página inicial

        </a>

    </div>

    <?php include 'topnav.php'; ?>

    <div class="container">

        <h1 class="titulo-ajuda">
            Central de Ajuda
        </h1>

        <div class="faq-container">

            <div class="faq-card">
                <div class="faq-title">
                    Como acessar meus holerites?
                </div>
                <div class="faq-answer">
                    Vá até a área de documentos e selecione a opção "Holerites".
                </div>
            </div>

            <div class="faq-card">
                <div class="faq-title">
                    Como registrar meu ponto?
                </div>
                <div class="faq-answer">
                    Utilize o menu lateral e clique em "Registrar Ponto".
                </div>
            </div>

            <div class="faq-card">
                <div class="faq-title">
                    Como solicitar suporte?
                </div>
                <div class="faq-answer">
                    Entre em contato com o RH ou utilize a área de suporte.
                </div>
            </div>

        </div> <div style="display: flex; justify-content: center; margin-top: 3rem; margin-bottom: 3rem; width: 100%;">
            <div style="text-align: left;">
                <button
                    id="abrirFormulario"
                    class="btn btn-primary px-5 py-3 rounded-4 shadow"
                    style="cursor: pointer; text-align: left;">
                    Registrar dúvida
                </button>
            </div>
        </div>

    </div> <div class="modal" id="modalDuvida">
        <div class="modal-content">
            
            <span class="fechar">&times;</span>

            <h2>Registrar Dúvida</h2>

            <form action="duvidas.php" method="POST" id="formDuvida">
                <input
                    type="text"
                    name="nome"
                    placeholder="Digite seu nome"
                    required>

                <input
                    type="email"
                    name="email"
                    placeholder="Digite seu email"
                    required>

                <textarea
                    name="duvida"
                    placeholder="Digite sua dúvida detalhadamente"
                    required></textarea>

                <button type="submit" id="btnEnviar">
                    Enviar dúvida
                </button>
            </form>

        </div>
    </div>

    <script>
        // MODAL
        const modal = document.getElementById("modalDuvida");
        const abrir = document.getElementById("abrirFormulario");
        const fechar = document.querySelector(".fechar");

        // ABRIR MODAL
        abrir.addEventListener("click", () => {
            modal.style.display = "flex";
        });

        // FECHAR MODAL
        fechar.addEventListener("click", () => {
            modal.style.display = "none";
        });

        // FECHAR CLICANDO FORA
        window.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });

        // BOTÃO ENVIAR
        const form = document.getElementById("formDuvida");
        const btn = document.getElementById("btnEnviar");

        form.addEventListener("submit", () => {
            btn.disabled = true;
            btn.innerText = "Enviando...";
        });
    </script>
    
</body>

</html>