<?php
$pagina = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>Solicitações e Licenças</title>

    <!-- BOOTSTRAP -->
    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <!-- FONT AWESOME -->
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/global.css">

    <link rel="stylesheet"
    href="../css/sidebarfunc.css">

</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebarfunc.php'; ?>

<!-- MAIN -->
<main class="content">

    <!-- TOPO -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                Solicitações e Licenças
            </h2>

            <p class="text-muted mb-0">
                Acompanhe suas solicitações enviadas ao RH
            </p>

        </div>

    </div>

    <!-- LISTA -->
    <div
        id="listaSolicitacoes"
        class="d-flex flex-column gap-4"
    >

        <!-- VAZIO -->
        <div
            id="semPedidos"
            class="card border-0 shadow-sm rounded-4 p-5 text-center"
        >

            <div class="mb-3">

                <i class="fa-regular fa-folder-open fs-1 text-secondary"></i>

            </div>

            <h4 class="fw-bold mb-2">
                Nenhuma solicitação encontrada
            </h4>

            <p class="text-muted mb-0">
                Seus pedidos enviados aparecerão aqui.
            </p>

        </div>

    </div>

</main>

<!-- JS -->
<script src="../js/solilic.js"></script>

</body>
</html>