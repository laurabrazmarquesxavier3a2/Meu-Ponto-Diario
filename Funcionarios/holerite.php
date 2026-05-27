<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Holerite</title>

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

<!-- CONTEÚDO -->
<div class="content">

    <!-- TÍTULO -->
    <div class="title text-start">

        <h2>
            Holerite
        </h2>

        <p>
            Visualize seus comprovantes de pagamento
        </p>

    </div>

    <!-- CARD -->
    <div class="row">

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card shadow-sm border-0 rounded-4">

                <div class="card-body text-center p-5">

                    <!-- ÍCONE -->
                    <div class="mb-4">

                        <i class="fa-solid fa-money-check-dollar fa-4x text-primary"></i>

                    </div>

                    <!-- TÍTULO -->
                    <h3 class="fw-bold mb-3">

                        Holerite

                    </h3>

                    <!-- TEXTO -->
                    <p class="text-secondary mb-4">

                        Visualize seu comprovante
                        de pagamento mensal

                    </p>

                    <!-- BOTÃO -->
                    <button class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">

                        Visualizar

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- BOOTSTRAP JS -->
<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>