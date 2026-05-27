<?php require_once 'auth.php'; ?>

<?php
$pagina = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>Dúvidas RH</title>

    <!-- BOOTSTRAP -->
    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS GLOBAL -->
    <link
    rel="stylesheet"
    href="css/style.css">

    <style>

        /* CONTEÚDO */
        .content{
            margin-left:250px;
            padding:30px;
            min-height:100vh;
        }

        /* TÍTULO */
        .titulo-pagina{
            font-size:52px;
            font-weight:700;
            color:#1d1d1d;
            line-height:1;
        }

        .subtitulo{
            color:#555;
            font-size:18px;
            margin-top:8px;
        }

        /* BOTÃO */
        .btn-atualizar{

            background:#2563eb;
            border:none;
            color:white;

            padding:14px 26px;

            border-radius:14px;

            font-weight:600;

            transition:.3s;
        }

        .btn-atualizar:hover{
            background:#1d4ed8;
        }

        /* CARD */
        .card-vazio{

            margin-top:30px;

            background:white;

            border-radius:18px;

            padding:100px 20px;

            text-align:center;

            border:1px solid #d9d9d9;

            box-shadow:0 2px 10px rgba(0,0,0,.03);
        }

        .card-vazio i{

            font-size:80px;

            color:#2563eb;

            margin-bottom:25px;
        }

        .card-vazio h3{

            font-size:28px;

            font-weight:700;

            color:#14284b;

            margin-bottom:10px;
        }

        .card-vazio p{

            color:#64748b;

            font-size:17px;

            margin:0;
        }

        /* RESPONSIVO */
        @media(max-width:768px){

            .content{
                margin-left:0;
                padding:20px;
            }

            .titulo-pagina{
                font-size:35px;
            }

            .card-vazio{
                padding:70px 20px;
            }

        }

    </style>

</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>

<!-- CONTEÚDO -->
<div class="content">

    <!-- TOPO -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>

            <h1 class="titulo-pagina">
                Dúvidas dos Funcionários
            </h1>

            <p class="subtitulo">
                Visualize dúvidas enviadas pelos colaboradores
            </p>

        </div>

        <button
        class="btn-atualizar"
        onclick="atualizarDuvidas()">

            <i class="bi bi-arrow-clockwise me-2"></i>

            Atualizar

        </button>

    </div>

    <!-- CARD -->
    <div class="card-vazio">

        <i class="bi bi-question-circle"></i>

        <h3>
            Nenhuma dúvida encontrada
        </h3>

        <p>
            Ainda não existem dúvidas cadastradas no sistema.
        </p>

    </div>

</div>

<!-- ALERTA -->
<div
id="alerta"
class="position-fixed top-0 end-0 p-3"
style="z-index:9999;">
</div>

<!-- JS SIDEBAR -->
<script>

const btnSidebar =
document.getElementById('btnSidebar');

const sidebar =
document.getElementById('sidebar');

const overlay =
document.getElementById('sidebarOverlay');

/* MOBILE */

if(btnSidebar){

    btnSidebar.addEventListener('click', () => {

        sidebar.classList.toggle('show');

        overlay.classList.toggle('show');

    });

}

if(overlay){

    overlay.addEventListener('click', () => {

        sidebar.classList.remove('show');

        overlay.classList.remove('show');

    });

}

/* ALERTA */

function atualizarDuvidas(){

    mostrarAlerta(
        'Nenhuma nova dúvida encontrada.',
        'primary'
    );

}

function mostrarAlerta(texto, tipo){

    const alerta =
    document.getElementById('alerta');

    alerta.innerHTML = `

        <div class="alert alert-${tipo} shadow border-0 rounded-4 px-4 py-3">

            ${texto}

        </div>

    `;

    setTimeout(() => {

        alerta.innerHTML = '';

    }, 3000);

}

</script>

</body>

</html>