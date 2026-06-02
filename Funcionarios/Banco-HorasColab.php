<?php
require_once '../auth.php';
require_once '../config/database.php';

$pagina = basename($_SERVER['PHP_SELF']);

$idFuncionario = $_SESSION['id_funcionario'] ?? null;
$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idFuncionario || !$idEmpresa) {
    die("Funcionário ou empresa não identificados. Faça login novamente.");
}

/*
==========================
CONFIGURAÇÃO
==========================
*/

$cargaDiaria = 8.00;
$mesAtual = date('Y-m');

/*
==========================
FUNÇÕES
==========================
*/

function formatarHoras($horas) {

    $sinal = $horas < 0 ? '-' : '';
    $horas = abs($horas);

    $h = floor($horas);
    $m = round(($horas - $h) * 60);

    if ($m == 60) {
        $h++;
        $m = 0;
    }

    return $sinal . $h . "h" . str_pad($m, 2, "0", STR_PAD_LEFT);
}

function statusSaldo($saldo) {

    if ($saldo > 0) {
        return "positivo";
    }

    if ($saldo < 0) {
        return "negativo";
    }

    return "neutro";
}

/*
==========================
DADOS DO FUNCIONÁRIO
==========================
*/

$stmtFunc = $con->prepare("
    SELECT *
    FROM funcionarios
    WHERE id_funcionario = ?
    AND id_empresa = ?
    LIMIT 1
");

$stmtFunc->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtFunc->execute();

$funcionario = $stmtFunc->get_result()->fetch_assoc();

if (!$funcionario) {
    die("Funcionário não encontrado para esta empresa.");
}

/*
==========================
CALCULAR BANCO DE HORAS COM BASE NOS PONTOS
==========================
*/

$stmtPontos = $con->prepare("
    SELECT
        id_ponto,
        data,
        hora_entrada,
        hora_saida,
        total_horas,
        status
    FROM pontos
    WHERE id_funcionario = ?
    AND id_empresa = ?
    AND status = 'completo'
    AND total_horas IS NOT NULL
    ORDER BY data DESC
");

$stmtPontos->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtPontos->execute();

$resultPontos = $stmtPontos->get_result();

$saldoTotal = 0;
$saldoMes = 0;
$horasExtrasMes = 0;
$horasDebitoMes = 0;
$registros = [];

while ($ponto = $resultPontos->fetch_assoc()) {

    $data = $ponto['data'];
    $totalHoras = floatval($ponto['total_horas']);

    $diferenca = $totalHoras - $cargaDiaria;

    $saldoTotal += $diferenca;

    if (date('Y-m', strtotime($data)) == $mesAtual) {

        $saldoMes += $diferenca;

        if ($diferenca > 0) {
            $horasExtrasMes += $diferenca;
        }

        if ($diferenca < 0) {
            $horasDebitoMes += abs($diferenca);
        }
    }

    $registros[] = [
        'data' => $data,
        'descricao' => $diferenca >= 0 ? 'Horas extras trabalhadas' : 'Horas em débito',
        'horas' => $diferenca,
        'entrada' => $ponto['hora_entrada'],
        'saida' => $ponto['hora_saida'],
        'total' => $totalHoras,
        'status' => $diferenca >= 0 ? 'Extra' : 'Débito'
    ];
}

/*
==========================
PENDENTES / EM ANDAMENTO
==========================
*/

$stmtPendentes = $con->prepare("
    SELECT COUNT(*) AS total
    FROM pontos
    WHERE id_funcionario = ?
    AND id_empresa = ?
    AND status = 'em andamento'
");

$stmtPendentes->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtPendentes->execute();

$pendentes = $stmtPendentes->get_result()->fetch_assoc()['total'] ?? 0;

/*
==========================
SINCRONIZAR TABELA banco_horas
==========================
*/

$statusBanco = statusSaldo($saldoTotal);
$dataAtualizacao = date('Y-m-d');

$stmtExiste = $con->prepare("
    SELECT id_banco
    FROM banco_horas
    WHERE id_funcionario = ?
    AND id_empresa = ?
    AND mes = ?
    LIMIT 1
");

$stmtExiste->bind_param("iis", $idFuncionario, $idEmpresa, $mesAtual);
$stmtExiste->execute();

$existeBanco = $stmtExiste->get_result()->fetch_assoc();

if ($existeBanco) {

    $stmtUpdate = $con->prepare("
        UPDATE banco_horas
        SET
            saldo_total = ?,
            saldo_mes = ?,
            horas_extras_mes = ?,
            horas_debito_mes = ?,
            data_atualizacao = ?,
            status = ?
        WHERE id_banco = ?
    ");

    $stmtUpdate->bind_param(
        "ddddssi",
        $saldoTotal,
        $saldoMes,
        $horasExtrasMes,
        $horasDebitoMes,
        $dataAtualizacao,
        $statusBanco,
        $existeBanco['id_banco']
    );

    $stmtUpdate->execute();

} else {

    $stmtInsert = $con->prepare("
        INSERT INTO banco_horas (
            id_funcionario,
            mes,
            saldo_total,
            saldo_mes,
            horas_extras_mes,
            horas_debito_mes,
            data_atualizacao,
            status,
            id_empresa
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmtInsert->bind_param(
        "isddddssi",
        $idFuncionario,
        $mesAtual,
        $saldoTotal,
        $saldoMes,
        $horasExtrasMes,
        $horasDebitoMes,
        $dataAtualizacao,
        $statusBanco,
        $idEmpresa
    );

    $stmtInsert->execute();
}

/*
==========================
ÚLTIMOS REGISTROS
==========================
*/

$ultimosRegistros = array_slice($registros, 0, 10);

$progressoSaldo = min(100, abs($saldoTotal) * 5);
$progressoExtra = min(100, $horasExtrasMes * 10);
$progressoDebito = min(100, $horasDebitoMes * 10);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Banco de Horas</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link
rel="stylesheet"
href="../css/global.css">

<style>

.card{
    border:0;
    border-radius:18px;
    box-shadow:0 8px 24px rgba(0,0,0,.06);
}

.saldo-positivo{
    color:#198754;
}

.saldo-negativo{
    color:#dc3545;
}

.saldo-neutro{
    color:#0d6efd;
}

.badge-extra{
    background:#198754;
}

.badge-debito{
    background:#dc3545;
}

.registro-vazio{
    min-height:250px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
}

</style>

</head>

<body>

<?php include 'sidebarfunc.php'; ?>

<div class="content">

    <div class="title">

        <h1>
            Banco de Horas
        </h1>

        <p>
            Visualize seu saldo real calculado pelos seus registros de ponto
        </p>

    </div>

    <div class="alert alert-info border-0 shadow-sm rounded-4">

        <i class="fa-solid fa-circle-info me-2"></i>

        O cálculo considera uma carga diária padrão de
        <strong><?= formatarHoras($cargaDiaria) ?></strong>.
        Horas acima disso entram como extras; abaixo disso entram como débito.

    </div>

    <div class="row g-4 mb-4">

        <!-- SALDO ATUAL -->
        <div class="col-12 col-md-6 col-xl-4">

            <div class="card h-100 p-4">

                <div class="d-flex justify-content-between align-items-start mb-4">

                    <div>

                        <p class="text-muted fw-bold mb-2">
                            Saldo Atual
                        </p>

                        <h1
                        class="fw-bold mb-0
                        <?= $saldoTotal > 0 ? 'saldo-positivo' : ($saldoTotal < 0 ? 'saldo-negativo' : 'saldo-neutro') ?>">

                            <?= formatarHoras($saldoTotal) ?>

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
                        class="progress-bar
                        <?= $saldoTotal >= 0 ? 'bg-success' : 'bg-danger' ?>
                        progress-bar-striped progress-bar-animated"
                        style="width:<?= $progressoSaldo ?>%"></div>

                    </div>

                    <small class="<?= $saldoTotal >= 0 ? 'text-success' : 'text-danger' ?> fw-semibold">
                        <?= $saldoTotal >= 0 ? 'Saldo positivo' : 'Saldo negativo' ?>
                    </small>

                </div>

            </div>

        </div>

        <!-- EXTRAS DO MÊS -->
        <div class="col-12 col-md-6 col-xl-4">

            <div class="card h-100 p-4">

                <div class="d-flex justify-content-between align-items-start mb-4">

                    <div>

                        <p class="text-muted fw-bold mb-2">
                            Horas Extras no Mês
                        </p>

                        <h1 class="fw-bold mb-0 text-success">
                            <?= formatarHoras($horasExtrasMes) ?>
                        </h1>

                    </div>

                    <div
                    class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                    style="width:60px;height:60px;">

                        <i class="fa-solid fa-business-time text-success fs-3"></i>

                    </div>

                </div>

                <div class="mt-auto">

                    <div class="progress mb-2 rounded-pill"
                    style="height:10px;">

                        <div
                        class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                        style="width:<?= $progressoExtra ?>%"></div>

                    </div>

                    <small class="text-success fw-semibold">
                        Acumulado em <?= date('m/Y') ?>
                    </small>

                </div>

            </div>

        </div>

        <!-- DÉBITOS DO MÊS -->
        <div class="col-12 col-md-6 col-xl-4">

            <div class="card h-100 p-4">

                <div class="d-flex justify-content-between align-items-start mb-4">

                    <div>

                        <p class="text-muted fw-bold mb-2">
                            Débitos no Mês
                        </p>

                        <h1 class="fw-bold mb-0 text-danger">
                            <?= formatarHoras($horasDebitoMes) ?>
                        </h1>

                    </div>

                    <div
                    class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                    style="width:60px;height:60px;">

                        <i class="fa-solid fa-hourglass-half text-danger fs-3"></i>

                    </div>

                </div>

                <div class="mt-auto">

                    <div class="progress mb-2 rounded-pill"
                    style="height:10px;">

                        <div
                        class="progress-bar bg-danger progress-bar-striped progress-bar-animated"
                        style="width:<?= $progressoDebito ?>%"></div>

                    </div>

                    <small class="text-danger fw-semibold">
                        <?= $pendentes ?> registro(s) em andamento
                    </small>

                </div>

            </div>

        </div>

    </div>

    <!-- TABELA -->
    <div class="card p-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

            <div>

                <h2 class="fw-bold mb-1">
                    Últimos Registros
                </h2>

                <p class="text-muted mb-0">
                    Histórico calculado pelos seus pontos completos
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
                        <th>Entrada</th>
                        <th>Saída</th>
                        <th>Total Trabalhado</th>
                        <th>Diferença</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody id="tabelaHoras">

                    <?php if(count($ultimosRegistros) > 0): ?>

                        <?php foreach($ultimosRegistros as $r): ?>

                            <tr>

                                <td>
                                    <?= date('d/m/Y', strtotime($r['data'])) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r['entrada']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($r['saida']) ?>
                                </td>

                                <td>
                                    <?= formatarHoras($r['total']) ?>
                                </td>

                                <td class="fw-semibold <?= $r['horas'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= $r['horas'] >= 0 ? '+' : '' ?>
                                    <?= formatarHoras($r['horas']) ?>
                                </td>

                                <td>
                                    <?php if($r['horas'] >= 0): ?>

                                        <span class="badge badge-extra px-3 py-2 rounded-pill">
                                            Extra
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-debito px-3 py-2 rounded-pill">
                                            Débito
                                        </span>

                                    <?php endif; ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="6">

                                <div class="registro-vazio">

                                    <i class="fa-solid fa-clock-rotate-left display-4 text-muted mb-3"></i>

                                    <h5 class="fw-bold">
                                        Nenhum registro completo encontrado
                                    </h5>

                                    <p class="text-muted mb-0">
                                        Quando seus pontos forem finalizados, o banco de horas aparecerá aqui.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<script>

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