<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Perfil</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/sidebarfunc.css">
    <link rel="stylesheet" href="../css/perfil.css">

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <link rel="stylesheet"
    href="../css/perfilfunc.css">

    <!-- GOOGLE FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">

</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'sidebarfunc.php'; ?>

    <!-- CONTENT -->
    <div class="content">

        <!-- TITLE -->
        <div class="title">

            <h2>Meu Perfil</h2>

            <p>
                Gerencie suas informações pessoais e profissionais
            </p>

        </div>

        <!-- PERFIL TOP -->
        <div class="perfil-top">

            <!-- FOTO -->
            <div class="foto-card">

                <div class="foto-box">

                    <img
                        src="../img/user-default.png"
                        id="previewFoto"
                    >

                    <label for="fotoInput" class="btn-foto">

                        <i class="fa-solid fa-camera"></i>

                    </label>

                    <input
                        type="file"
                        id="fotoInput"
                        accept="image/*"
                        hidden
                    >

                </div>

                <h3>Kevin Nobre Santos</h3>

                <span>Funcionário</span>

            </div>

            <!-- INFO -->
            <div class="info-grid">

                <!-- CARD 1 -->
                <div class="info-card">

                    <h3>

                        <i class="fa-solid fa-user-tie"></i>

                        Informações Profissionais

                    </h3>

                    <div class="info-item">

                        <strong>Nome Completo:</strong>

                        <span>Kevin Nobre Santos</span>

                    </div>

                    <div class="info-item">

                        <strong>Departamento:</strong>

                        <span>Tecnologia da Informação</span>

                    </div>

                    <div class="info-item">

                        <strong>Matrícula:</strong>

                        <span>82257169</span>

                    </div>

                </div>

                <!-- CARD 2 -->
                <div class="info-card">

                    <h3>

                        <i class="fa-solid fa-business-time"></i>

                        Informações de Jornada

                    </h3>

                    <div class="jornada-item">

                        <i class="fa-regular fa-clock"></i>

                        <div>

                            <strong>Tipo de Jornada:</strong>

                            <span>8h diárias</span>

                        </div>

                    </div>

                    <div class="jornada-item">

                        <i class="fa-solid fa-business-time"></i>

                        <div>

                            <strong>Horário Padrão</strong>

                            <span>09h às 18h</span>

                        </div>

                    </div>

                    <div class="jornada-item">

                        <i class="fa-solid fa-mug-hot"></i>

                        <div>

                            <strong>Intervalo:</strong>

                            <span>1h (12h às 13h)</span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

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

function removerFoto(){

    previewFoto.src = "../img/user-default.png";

    fotoInput.value = "";

}

function visualizarFoto(){

    window.open(previewFoto.src, '_blank');

}

</script>

</body>
</html>