<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Segurança</title>

<!-- BOOTSTRAP -->
<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- FONT AWESOME -->
<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- CSS GLOBAL -->
<link rel="stylesheet" href="../css/global.css">

</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebarfunc.php'; ?>

<!-- ALERT -->
<div
class="position-fixed top-0 end-0 p-4"
style="z-index:9999">

    <div
    id="alertBox"
    class="alert alert-success shadow-lg border-0 rounded-4 d-none">

        <i class="fa-solid fa-circle-check me-2"></i>

        Reporte enviado com sucesso!

    </div>

</div>

<!-- CONTENT -->
<div class="content">

    <!-- TITLE -->
    <div class="title text-start">

        <h2>
            Reportar Ocorrência
        </h2>

        <p>
            Utilize o formulário abaixo para registrar situações de risco,
            comportamento inadequado ou problemas estruturais.
        </p>

    </div>

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body p-4 p-lg-5">

            <!-- FORM -->
            <form id="reportForm">

                <div class="row g-4">

                    <!-- CATEGORIA -->
                    <div class="col-12">

                        <label class="form-label fw-semibold">

                            Categoria

                        </label>

                        <select
                        class="form-select form-select-lg"
                        required>

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

                    </div>

                    <!-- DESCRIÇÃO -->
                    <div class="col-12">

                        <label class="form-label fw-semibold">

                            Descrição detalhada

                        </label>

                        <textarea
                        class="form-control"
                        rows="6"
                        placeholder="Descreva detalhadamente o ocorrido..."
                        required></textarea>

                    </div>

                    <!-- TESTEMUNHAS -->
                    <div class="col-12">

                        <label class="form-label fw-semibold">

                            Pessoas envolvidas ou testemunhas

                        </label>

                        <input
                        type="text"
                        class="form-control"
                        placeholder="Opcional">

                    </div>

                    <!-- EVIDÊNCIAS -->
                    <div class="col-12">

                        <label class="form-label fw-semibold">

                            Evidências

                        </label>

                        <input
                        type="file"
                        class="form-control"
                        multiple>

                        <div class="form-text">

                            Fotos, vídeos ou documentos

                        </div>

                    </div>

                    <!-- BOTÃO -->
                    <div class="col-12">

                        <button
                        class="btn btn-primary btn-lg w-100 rounded-4 py-3 fw-semibold">

                            <i class="fa-solid fa-paper-plane me-2"></i>

                            Enviar ocorrência

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- JS -->
<script>

const form = document.getElementById('reportForm');
const alertBox = document.getElementById('alertBox');

form.addEventListener('submit', function(e){

    e.preventDefault();

    alertBox.classList.remove('d-none');

    setTimeout(() => {

        alertBox.classList.add('d-none');

    }, 3500);

    form.reset();

});

</script>

<!-- BOOTSTRAP -->
<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>