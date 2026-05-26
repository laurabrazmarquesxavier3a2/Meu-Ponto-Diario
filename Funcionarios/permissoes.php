<?php
$meses = [
    "Janeiro",
    "Fevereiro",
    "Março",
    "Abril",
    "Maio",
    "Junho"
];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Permissões</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/sidebarfunc.css">
    <link rel="stylesheet" href="../css/permissoes.css">

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'sidebarfunc.php'; ?>

    <!-- CONTEÚDO -->
    <div class="content">

        <!-- TÍTULO -->
        <div class="title">

            <h2>Permissões</h2>

            <p>
                Gerencie suas solicitações de ausências e benefícios
            </p>

        </div>

        <div class="cards">

            <!-- CARD FÉRIAS -->
            <div class="card">

                <h3>Solicitar Férias</h3>

                <p>
                    Escolha um mês disponível para solicitar férias.
                </p>

                <!-- MESES -->
                <div class="meses">

                    <?php foreach($meses as $mes){ ?>

                        <div 
                            class="mes-card"
                            onclick="selecionarMes(this)"
                        >
                            <?php echo $mes; ?>
                        </div>

                    <?php } ?>

                </div>

                <!-- BOX -->
                <div class="mes-selecionado">

                    <div id="mesSelecionadoTexto">

                        Nenhum mês selecionado

                    </div>

                    <button
                        class="btn-editar"
                        onclick="editarMes()"
                    >
                        Editar
                    </button>

                </div>
<!-- BOTÃO -->
<button
    id="btnSolicitar"
    class="btn btn-primary btn-solicitar w-100 mt-4"
    onclick="solicitarFerias()"
>
    Solicitar Férias
</button>
            </div>

            <!-- CARD LICENÇA -->
            <div class="card">

                <h3>Enviar Licença Médica</h3>

                <p>
                    Envie um documento PDF da sua licença médica para o RH.
                </p>

                <input
                    type="file"
                    accept=".pdf"
                    class="form-control"
                    id="pdfInput"
                >

                <button
                    class="btn btn-danger w-100 mt-3"
                    onclick="enviarPDF()"
                >
                    Enviar PDF
                </button>

            </div>

        </div>

        <!-- ALERTA -->
        <div class="alerta" id="alerta">

            Ação realizada

        </div>

    </div>

    <!-- JS -->
    <script src="../js/permissoes.js"></script>

</body>
</html>