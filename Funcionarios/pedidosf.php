<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../auth.php';
require_once '../config/database.php';
require_once '../lang.php';

$idUsuario = $_SESSION['id_usuario'] ?? null;
$idFuncionario = $_SESSION['id_funcionario'] ?? null;
$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idUsuario || !$idFuncionario || !$idEmpresa) {
    die("Sessão inválida. Faça login novamente.");
}

$mensagem = '';
$erro = '';

$meses = [
    "Janeiro" => 1,
    "Fevereiro" => 2,
    "Março" => 3,
    "Abril" => 4,
    "Maio" => 5,
    "Junho" => 6,
    "Julho" => 7,
    "Agosto" => 8,
    "Setembro" => 9,
    "Outubro" => 10,
    "Novembro" => 11,
    "Dezembro" => 12
];

/* CRIA TABELA CASO NÃO EXISTA */
$con->query("
    CREATE TABLE IF NOT EXISTS ferias_meses_disponiveis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_empresa INT NOT NULL,
        mes TINYINT NOT NULL,
        disponivel TINYINT NOT NULL DEFAULT 1,
        atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY empresa_mes (id_empresa, mes)
    )
");

/* GARANTE OS 12 MESES */
foreach ($meses as $nomeMes => $numeroMes) {
    $stmtMes = $con->prepare("
        INSERT IGNORE INTO ferias_meses_disponiveis
        (id_empresa, mes, disponivel)
        VALUES (?, ?, 1)
    ");
    $stmtMes->bind_param("ii", $idEmpresa, $numeroMes);
    $stmtMes->execute();
}

/* MESES DISPONÍVEIS */
$mesesDisponiveis = [];

$stmtMeses = $con->prepare("
    SELECT mes, disponivel
    FROM ferias_meses_disponiveis
    WHERE id_empresa = ?
");

$stmtMeses->bind_param("i", $idEmpresa);
$stmtMeses->execute();
$resMeses = $stmtMeses->get_result();

while ($row = $resMeses->fetch_assoc()) {
    $mesesDisponiveis[(int)$row['mes']] = (int)$row['disponivel'];
}

/* BUSCAR FUNCIONÁRIO */
$stmtFunc = $con->prepare("
    SELECT nome
    FROM funcionarios
    WHERE id_funcionario = ?
    AND id_empresa = ?
    LIMIT 1
");

$stmtFunc->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtFunc->execute();

$funcionario = $stmtFunc->get_result()->fetch_assoc();
$nomeFuncionario = $funcionario['nome'] ?? 'Colaborador';

/* BUSCAR PEDIDO PENDENTE */
$stmtPedido = $con->prepare("
    SELECT 
        id_ferias,
        data_inicio,
        data_fim,
        alteracoes_restantes
    FROM ferias
    WHERE id_funcionario = ?
    AND id_empresa = ?
    AND status = 'pendente'
    LIMIT 1
");

$stmtPedido->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtPedido->execute();

$pedidoAtual = $stmtPedido->get_result()->fetch_assoc();

$possuiPedidoPendente = $pedidoAtual ? true : false;
$alteracoesRestantes = $pedidoAtual['alteracoes_restantes'] ?? 2;

/* SOLICITAR OU ALTERAR FÉRIAS */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_ferias'])) {

    $mesNome = $_POST['mes_ferias'] ?? '';

    if (!isset($meses[$mesNome])) {

        $erro = "Selecione um mês válido.";

    } else {

        $numeroMes = $meses[$mesNome];

        if (empty($mesesDisponiveis[$numeroMes])) {

            $erro = "Este mês não está disponível para solicitação de férias.";

        } else {

            $anoAtual = date('Y');

            if ($numeroMes < date('n')) {
                $anoAtual++;
            }

            $dataInicio = date('Y-m-d', strtotime("$anoAtual-$numeroMes-01"));
            $dataFim = date('Y-m-d', strtotime($dataInicio . ' +29 days'));
            $dias = 30;

            if ($pedidoAtual && $alteracoesRestantes <= 0) {

                $erro = "Você já usou suas 2 alterações. Aguarde o RH visualizar sua solicitação.";

            } elseif ($pedidoAtual) {

                $novoRestante = $alteracoesRestantes - 1;

                $stmt = $con->prepare("
                    UPDATE ferias
                    SET 
                        data_inicio = ?,
                        data_fim = ?,
                        dias = ?,
                        alteracoes_restantes = ?,
                        mensagem_colaborador = 'Solicitação alterada. Aguardando visualização do RH.'
                    WHERE id_ferias = ?
                    AND id_funcionario = ?
                    AND id_empresa = ?
                ");

                if (!$stmt) {
                    die("Erro SQL alterar férias: " . $con->error);
                }

                $stmt->bind_param(
                    "ssiiiii",
                    $dataInicio,
                    $dataFim,
                    $dias,
                    $novoRestante,
                    $pedidoAtual['id_ferias'],
                    $idFuncionario,
                    $idEmpresa
                );

                if ($stmt->execute()) {
                    $mensagem = "Solicitação de férias alterada com sucesso. Alterações restantes: " . $novoRestante;
                    $alteracoesRestantes = $novoRestante;
                    $possuiPedidoPendente = true;
                } else {
                    $erro = "Erro ao alterar solicitação: " . $stmt->error;
                }

            } else {

                $stmt = $con->prepare("
                    INSERT INTO ferias (
                        id_funcionario,
                        data_inicio,
                        data_fim,
                        dias,
                        status,
                        data_solicitacao,
                        mensagem_colaborador,
                        alteracoes_restantes,
                        id_empresa
                    )
                    VALUES (
                        ?, ?, ?, ?,
                        'pendente',
                        NOW(),
                        'Solicitação enviada. Aguardando visualização do RH.',
                        2,
                        ?
                    )
                ");

                if (!$stmt) {
                    die("Erro SQL férias: " . $con->error);
                }

                $stmt->bind_param(
                    "issii",
                    $idFuncionario,
                    $dataInicio,
                    $dataFim,
                    $dias,
                    $idEmpresa
                );

                if ($stmt->execute()) {
                    $mensagem = "Solicitação de férias enviada para o RH.";
                    $possuiPedidoPendente = true;
                    $alteracoesRestantes = 2;
                } else {
                    $erro = "Erro ao solicitar férias: " . $stmt->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Pedidos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="../css/global.css">
<link rel="stylesheet" href="../css/sidebarfunc.css">
<link rel="stylesheet" href="../css/pedidosf.css">

</head>

<body>

<?php include 'sidebarfunc.php'; ?>

<main class="pedidos-page">

<div class="container-fluid">

    <?php if(isset($_GET['sucesso'])): ?>

        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0 rounded-4">
            <i class="fa-solid fa-circle-check me-2"></i>
            Licença enviada com sucesso. Agora ela está em andamento até o RH visualizar.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

    <?php endif; ?>

    <?php if($mensagem): ?>

        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0 rounded-4">
            <i class="fa-solid fa-circle-check me-2"></i>
            <?= htmlspecialchars($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

    <?php endif; ?>

    <?php if($erro): ?>

        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm border-0 rounded-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <?= htmlspecialchars($erro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

    <?php endif; ?>

    <div class="pedidos-header mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                Pedidos
            </h2>

            <p class="text-muted mb-0">
                Olá, <?= htmlspecialchars($nomeFuncionario) ?>. Solicite férias ou envie licenças médicas.
            </p>
        </div>

        <div class="header-badge">
            <i class="fa-solid fa-calendar-check me-2"></i>
            Solicitações
        </div>

    </div>

    <div class="row g-4">

        <div class="col-12 col-xl-6">

            <div class="card border-0 shadow-sm rounded-4 h-100 pedidos-card">

                <div class="card-body p-4">

                    <div class="d-flex align-items-center gap-3 mb-4">

                        <div class="icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="fa-solid fa-umbrella-beach"></i>
                        </div>

                        <div>
                            <h4 class="fw-bold mb-1">
                                Solicitar Férias
                            </h4>

                            <p class="text-muted mb-0">
                                Escolha um mês liberado pelo RH
                            </p>
                        </div>

                    </div>

                    <?php if($possuiPedidoPendente): ?>

                        <div class="alert alert-warning rounded-4">

                            <i class="fa-solid fa-clock me-2"></i>

                            Você já possui uma solicitação em andamento.
                            Alterações restantes:
                            <strong><?= (int)$alteracoesRestantes ?></strong>

                        </div>

                    <?php endif; ?>

                    <form method="POST" id="formFerias">

                        <input type="hidden" name="mes_ferias" id="mesFerias">

                        <div class="row g-3">

                            <?php foreach($meses as $mes => $numero): ?>

                                <?php
                                    $mesDisponivel = !empty($mesesDisponiveis[$numero]);
                                    $limiteAlteracoes = ($possuiPedidoPendente && $alteracoesRestantes <= 0);
                                    $bloqueado = !$mesDisponivel || $limiteAlteracoes;
                                ?>

                                <div class="col-6">

                                    <button
                                        type="button"
                                        class="btn w-100 py-3 fw-semibold mes-btn <?= $mesDisponivel ? 'btn-light border' : 'btn-secondary mes-indisponivel' ?>"
                                        onclick="selecionarMes(this, '<?= htmlspecialchars($mes) ?>')"
                                        <?= $bloqueado ? 'disabled' : '' ?>
                                    >
                                        <span><?= htmlspecialchars($mes) ?></span>

                                        <?php if(!$mesDisponivel): ?>
                                            <small>Indisponível</small>
                                        <?php endif; ?>
                                    </button>

                                </div>

                            <?php endforeach; ?>

                        </div>

                        <div class="info-box mt-4">

                            <div id="mesSelecionadoTexto">
                                Nenhum mês selecionado
                            </div>

                            <small>
                                Apenas meses liberados pelo RH podem ser solicitados.
                            </small>

                        </div>

                        <button
                            type="submit"
                            name="solicitar_ferias"
                            id="btnSolicitar"
                            class="btn btn-primary w-100 py-3 fw-semibold mt-4"
                            <?= ($possuiPedidoPendente && $alteracoesRestantes <= 0) ? 'disabled' : '' ?>
                        >

                            <i class="fa-solid fa-paper-plane me-2"></i>

                            <?= $possuiPedidoPendente ? 'Alterar Solicitação' : 'Solicitar Férias' ?>

                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-12 col-xl-6">

            <div class="card border-0 shadow-sm rounded-4 h-100 pedidos-card">

                <div class="card-body p-4">

                    <div class="d-flex align-items-center gap-3 mb-4">

                        <div class="icon-box bg-danger bg-opacity-10 text-danger">
                            <i class="fa-solid fa-file-medical"></i>
                        </div>

                        <div>
                            <h4 class="fw-bold mb-1">
                                Licença Médica
                            </h4>

                            <p class="text-muted mb-0">
                                Envie seu atestado médico para o RH
                            </p>
                        </div>

                    </div>

                    <form
                        action="enviar_licenca.php"
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Motivo
                            </label>

                            <input
                                type="text"
                                name="motivo"
                                class="form-control"
                                required
                            >

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Data início
                                </label>

                                <input
                                    type="date"
                                    name="data_inicio"
                                    class="form-control"
                                    required
                                >

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Data fim
                                </label>

                                <input
                                    type="date"
                                    name="data_fim"
                                    class="form-control"
                                    required
                                >

                            </div>

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Observação
                            </label>

                            <textarea
                                name="observacao"
                                class="form-control"
                                rows="3"
                                placeholder="Digite uma observação..."
                            ></textarea>

                        </div>

                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Atestado
                            </label>

                            <input
                                type="file"
                                name="arquivo"
                                accept=".pdf,.png,.jpg,.jpeg"
                                class="form-control"
                                required
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-danger w-100 py-3 fw-semibold"
                        >

                            <i class="fa-solid fa-upload me-2"></i>

                            Enviar Licença

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</main>

<div
    id="alerta"
    class="position-fixed bottom-0 end-0 m-4 alert alert-dark shadow d-none"
    style="z-index:9999;"
></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function selecionarMes(elemento, mes){

    if(elemento.disabled){
        return;
    }

    document.querySelectorAll(".mes-btn").forEach(btn => {
        btn.classList.remove("btn-primary", "text-white");
        btn.classList.add("btn-light");
    });

    elemento.classList.remove("btn-light");
    elemento.classList.add("btn-primary", "text-white");

    document.getElementById("mesFerias").value = mes;

    document.getElementById("mesSelecionadoTexto").innerHTML = `
        <strong>Mês selecionado:</strong><br>
        ${mes}
    `;

    mostrarAlerta("✔ " + mes + " selecionado");
}

document.getElementById('formFerias').addEventListener('submit', function(e){

    if(!document.getElementById("mesFerias").value){

        e.preventDefault();

        mostrarAlerta("Selecione um mês disponível antes de solicitar.");
    }

});

function mostrarAlerta(texto){

    const alerta = document.getElementById("alerta");

    alerta.innerHTML = texto;

    alerta.classList.remove("d-none");

    setTimeout(() => {
        alerta.classList.add("d-none");
    }, 3000);
}

</script>

</body>
</html>