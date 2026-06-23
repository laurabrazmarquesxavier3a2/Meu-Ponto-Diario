<?php require_once 'auth.php'; 
require_once 'lang.php';
?>

<?php

include('config/database.php');

$sql = "SELECT * FROM duvidas ORDER BY data_envio DESC";

$result = mysqli_query($con, $sql);

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

        .content{
            margin-left:250px;
            padding:30px;
            min-height:100vh;
        }

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

        .card-duvida{

            background:white;

            border-radius:18px;

            padding:25px;

            border:1px solid #d9d9d9;

            box-shadow:0 2px 10px rgba(0,0,0,.03);

            margin-top:25px;
        }

        .nome{

            font-size:22px;

            font-weight:700;

            color:#14284b;
        }

        .email{

            color:#64748b;

            font-size:15px;
        }

        .mensagem{

            background:#f8fafc;

            border-radius:14px;

            padding:18px;

            margin-top:20px;

            color:#334155;

            line-height:1.6;
        }

        .data{

            color:#94a3b8;

            font-size:14px;

            margin-top:15px;
        }

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

        @media(max-width:768px){

            .content{
                margin-left:0;
                padding:20px;
            }

            .titulo-pagina{
                font-size:35px;
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
        onclick="location.reload()">

            <i class="bi bi-arrow-clockwise me-2"></i>

            Atualizar

        </button>

    </div>

    <?php if(mysqli_num_rows($result) > 0){ ?>

        <?php while($duvida = mysqli_fetch_assoc($result)){ ?>

            <div class="card-duvida">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                    <div>

                        <div class="nome">

                            <?= htmlspecialchars($duvida['nome']) ?>

                        </div>

                        <div class="email">

                            <?= htmlspecialchars($duvida['email']) ?>

                        </div>

                    </div>

                    <span class="badge bg-primary rounded-pill px-3 py-2">

                        Nova dúvida

                    </span>

                </div>

                <div class="mensagem">

                    <?= nl2br(htmlspecialchars($duvida['duvida'])) ?>

                </div>

                <div class="data">

                    <i class="bi bi-clock me-1"></i>

                    <?= date('d/m/Y H:i', strtotime($duvida['data_envio'])) ?>

                </div>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="card-vazio">

            <i class="bi bi-question-circle"></i>

            <h3>
                Nenhuma dúvida encontrada
            </h3>

            <p>
                Ainda não existem dúvidas cadastradas no sistema.
            </p>

        </div>

    <?php } ?>

</div>
<script src="js/theme.js"></script>
</body>

</html>