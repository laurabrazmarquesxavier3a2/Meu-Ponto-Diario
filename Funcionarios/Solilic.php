<?php
$pagina = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pedidos</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS GLOBAL -->
    <link rel="stylesheet" href="../css/global.css">

    <!-- SIDEBAR -->
    <link rel="stylesheet" href="../css/sidebarfunc.css">

</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'sidebarfunc.php'; ?>

    <!-- MAIN -->
    <main class="main-content">

        <!-- TOPO -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 w-100">

            <div>

                <h2 class="fw-bold mb-1">
                    Pedidos
                </h2>

                <p class="text-muted mb-0">
                    Gerencie suas solicitações e pedidos
                </p>

            </div>

        </div>

        <!-- CARD -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center w-100">

            <div class="mb-3">

                <i class="fa-regular fa-envelope-open fs-1 text-secondary"></i>

            </div>

            <h4 class="fw-bold mb-3">
                Nenhum pedido feito no momento.
            </h4>

        </div>

    </main>

</body>

</html>