<?php
$pagina = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>Banco de Horas</title>

    <!-- BOOTSTRAP -->
    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet">

    <!-- FONT AWESOME -->
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS GLOBAL -->
    <link
    rel="stylesheet"
    href="../css/global.css">

</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebarfunc.php'; ?>

<!-- CONTENT -->
<div class="content">

    <!-- TÍTULO -->
    <div class="title">

        <h1>
            Banco de Horas
        </h1>

        <p>
            Visualize seu saldo e acompanhe seus registros
        </p>

    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <!-- SALDO -->
        <div class="col-12 col-md-6 col-xl-4">

            <div class="card h-100">

                <div class="d-flex justify-content-between align-items-start mb-4">

                    <div>

                        <p class="text-muted fw-bold mb-2">
                            Saldo Atual
                        </p>

                        <h1
                        class="fw-bold contador mb-0"
                        data-target="12">

                            0h

                        </h1>

                    </div>

                    <div
                    class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                    style="width:60px;height:60px;">

                        <i class="fa-solid fa-clock text-primary fs-3"></i>

                    </div>

                </div>

                <div class="mt-auto">

                    <div class="progress mb-2 rounded-pill"
                    style="height:10px;">

                        <div
                        class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                        style="width:80%"></div>

                    </div>

                    <small class="text-success fw-semibold">
                        +4h este mês
                    </small>

                </div>

            </div>

        </div>

        <!-- EXTRAS -->
        <div class="col-12 col-md-6 col-xl-4">

            <div class="card h-100">

                <div class="d-flex justify-content-between align-items-start mb-4">

                    <div>

                        <p class="text-muted fw-bold mb-2">
                            Horas Extras
                        </p>

                        <h1
                        class="fw-bold contador mb-0"
                        data-target="26">

                            0h

                        </h1>

                    </div>

                    <div
                    class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                    style="width:60px;height:60px;">

                        <i class="fa-solid fa-business-time text-primary fs-3"></i>

                    </div>

                </div>

                <div class="mt-auto">

                    <div class="progress mb-2 rounded-pill"
                    style="height:10px;">

                        <div
                        class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                        style="width:65%"></div>

                    </div>

                    <small class="text-primary fw-semibold">
                        8h aprovadas
                    </small>

                </div>

            </div>

        </div>

        <!-- PENDENTES -->
        <div class="col-12 col-md-6 col-xl-4">

            <div class="card h-100">

                <div class="d-flex justify-content-between align-items-start mb-4">

                    <div>

                        <p class="text-muted fw-bold mb-2">
                            Pendentes
                        </p>

                        <h1
                        class="fw-bold contador mb-0"
                        data-target="3">

                            0h

                        </h1>

                    </div>

                    <div
                    class="bg-warning bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                    style="width:60px;height:60px;">

                        <i class="fa-solid fa-hourglass-half text-warning fs-3"></i>

                    </div>

                </div>

                <div class="mt-auto">

                    <div class="progress mb-2 rounded-pill"
                    style="height:10px;">

                        <div
                        class="progress-bar bg-warning progress-bar-striped progress-bar-animated"
                        style="width:35%"></div>

                    </div>

                    <small class="text-warning fw-semibold">
                        Em análise
                    </small>

                </div>

            </div>

        </div>

    </div>

    <!-- TABELA -->
    <div class="card">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

            <div>

                <h2 class="fw-bold mb-1">
                    Últimos Registros
                </h2>

                <p class="text-muted mb-0">
                    Histórico recente
                </p>

            </div>

            <input
            type="text"
            class="form-control"
            placeholder="Pesquisar..."
            style="max-width:250px;"
            id="pesquisaTabela">

        </div>

        <div class="table-responsive">

            <table class="table align-middle table-hover">

                <thead class="table-light">

                    <tr>

                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Horas</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody id="tabelaHoras">

                    <tr>

                        <td>26/05/2026</td>

                        <td>
                            Hora extra noturna
                        </td>

                        <td class="fw-semibold text-success">
                            +2h
                        </td>

                        <td>

                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                Aprovado
                            </span>

                        </td>

                    </tr>

                    <tr>

                        <td>24/05/2026</td>

                        <td>
                            Compensação de horas
                        </td>

                        <td class="fw-semibold text-danger">
                            -1h
                        </td>

                        <td>

                            <span class="badge bg-danger px-3 py-2 rounded-pill">
                                Descontado
                            </span>

                        </td>

                    </tr>

                    <tr>

                        <td>21/05/2026</td>

                        <td>
                            Trabalho final de semana
                        </td>

                        <td class="fw-semibold text-primary">
                            +5h
                        </td>

                        <td>

                            <span class="badge bg-primary px-3 py-2 rounded-pill">
                                Registrado
                            </span>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- JS -->
<script>

/* CONTADORES */
const counters =
document.querySelectorAll('.contador');

counters.forEach(counter => {

    const updateCounter = () => {

        const target =
        +counter.getAttribute('data-target');

        const current =
        parseInt(counter.innerText);

        const increment =
        target / 25;

        if(current < target){

            counter.innerText =
            Math.ceil(current + increment) + 'h';

            setTimeout(updateCounter, 40);

        }else{

            counter.innerText = target + 'h';

        }

    };

    updateCounter();

});

/* PESQUISA */
const pesquisa =
document.getElementById('pesquisaTabela');

pesquisa.addEventListener('keyup', () => {

    const valor =
    pesquisa.value.toLowerCase();

    const linhas =
    document.querySelectorAll('#tabelaHoras tr');

    linhas.forEach(linha => {

        const texto =
        linha.innerText.toLowerCase();

        linha.style.display =
        texto.includes(valor)
        ? ''
        : 'none';

    });

});

</script>

</body>

</html>