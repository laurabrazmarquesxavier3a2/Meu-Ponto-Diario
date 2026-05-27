<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Meu Perfil</title>

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

<!-- CONTENT -->
<div class="content">

    <!-- TITLE -->
    <div class="title text-start">

        <h2>
            Meu Perfil
        </h2>

        <p>
            Gerencie suas informações pessoais e profissionais
        </p>

    </div>

    <!-- ROW -->
    <div class="row g-4">

        <!-- FOTO -->
        <div class="col-12 col-lg-4">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body text-center p-5">

                    <!-- FOTO -->
                    <div class="position-relative d-inline-block mb-4">

                        <img
                        src="../img/user-default.png"
                        id="previewFoto"
                        class="rounded-circle border"
                        width="170"
                        height="170"
                        style="object-fit: cover;">

                        <label
                        for="fotoInput"
                        class="btn btn-primary rounded-circle position-absolute bottom-0 end-0">

                            <i class="fa-solid fa-camera"></i>

                        </label>

                        <input
                        type="file"
                        id="fotoInput"
                        accept="image/*"
                        hidden>

                    </div>

                    <!-- NOME -->
                    <h3 class="fw-bold mb-2">

                        Kevin Nobre Santos

                    </h3>

                    <!-- CARGO -->
                    <p class="text-secondary mb-0">

                        Funcionário

                    </p>

                </div>

            </div>

        </div>

        <!-- INFO -->
        <div class="col-12 col-lg-8">

            <div class="row g-4">

                <!-- PROFISSIONAL -->
                <div class="col-12">

                    <div class="card border-0 shadow-sm rounded-4">

                        <div class="card-body p-4">

                            <h4 class="fw-bold mb-4">

                                <i class="fa-solid fa-user-tie text-primary me-2"></i>

                                Informações Profissionais

                            </h4>

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <strong>
                                        Nome Completo
                                    </strong>

                                    <p class="text-secondary mb-0">

                                        Kevin Nobre Santos

                                    </p>

                                </div>

                                <div class="col-md-6">

                                    <strong>
                                        Departamento
                                    </strong>

                                    <p class="text-secondary mb-0">

                                        Tecnologia da Informação

                                    </p>

                                </div>

                                <div class="col-md-6">

                                    <strong>
                                        Matrícula
                                    </strong>

                                    <p class="text-secondary mb-0">

                                        82257169

                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <!-- JORNADA -->
                <div class="col-12">

                    <div class="card border-0 shadow-sm rounded-4">

                        <div class="card-body p-4">

                            <h4 class="fw-bold mb-4">

                                <i class="fa-solid fa-business-time text-primary me-2"></i>

                                Informações de Jornada

                            </h4>

                            <div class="row g-4">

                                <div class="col-md-4">

                                    <div class="d-flex gap-3 align-items-start">

                                        <i class="fa-regular fa-clock text-primary fs-4"></i>

                                        <div>

                                            <strong>
                                                Tipo de Jornada
                                            </strong>

                                            <p class="text-secondary mb-0">

                                                8h diárias

                                            </p>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="d-flex gap-3 align-items-start">

                                        <i class="fa-solid fa-business-time text-primary fs-4"></i>

                                        <div>

                                            <strong>
                                                Horário
                                            </strong>

                                            <p class="text-secondary mb-0">

                                                09h às 18h

                                            </p>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="d-flex gap-3 align-items-start">

                                        <i class="fa-solid fa-mug-hot text-primary fs-4"></i>

                                        <div>

                                            <strong>
                                                Intervalo
                                            </strong>

                                            <p class="text-secondary mb-0">

                                                1h (12h às 13h)

                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- JS FOTO -->
<script>

const fotoInput = document.getElementById('fotoInput');
const previewFoto = document.getElementById('previewFoto');

fotoInput.addEventListener('change', function(){

    const arquivo = this.files[0];

    if(arquivo){

        const reader = new FileReader();

        reader.onload = function(e){

            previewFoto.src = e.target.result;

        }

        reader.readAsDataURL(arquivo);

    }

});

</script>

<!-- BOOTSTRAP -->
<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>