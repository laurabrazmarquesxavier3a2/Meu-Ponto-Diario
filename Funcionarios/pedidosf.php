<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../auth.php';
require_once '../config/database.php';
require_once '../lang.php';
require_once '../notific.php';

$idUsuario = $_SESSION['id_usuario'] ?? null;
$idFuncionario = $_SESSION['id_funcionario'] ?? null;
$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idUsuario || !$idEmpresa) {
    die("Sessão inválida. Faça login novamente.");
}

/* BUSCA O FUNCIONÁRIO CASO NÃO ESTEJA NA SESSÃO */
if (!$idFuncionario) {

    $stmtBuscaFunc = $con->prepare("
        SELECT id_funcionario
        FROM usuarios
        WHERE id_usuario = ?
        AND id_empresa = ?
        LIMIT 1
    ");

    if (!$stmtBuscaFunc) {
        die("Erro SQL usuário: " . $con->error);
    }

    $stmtBuscaFunc->bind_param(
        "ii",
        $idUsuario,
        $idEmpresa
    );

    $stmtBuscaFunc->execute();

    $dadosUsuario = $stmtBuscaFunc
        ->get_result()
        ->fetch_assoc();

    if (!empty($dadosUsuario['id_funcionario'])) {
        $idFuncionario = (int)$dadosUsuario['id_funcionario'];
        $_SESSION['id_funcionario'] = $idFuncionario;
    }

    $stmtBuscaFunc->close();
}

if (!$idFuncionario) {
    die("Funcionário não encontrado.");
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

/*
========================================
CRIA TABELA DOS MESES
========================================
*/

$con->query("
    CREATE TABLE IF NOT EXISTS ferias_meses_disponiveis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_empresa INT NOT NULL,
        mes TINYINT NOT NULL,
        disponivel TINYINT NOT NULL DEFAULT 1,
        limite_pedidos INT NULL DEFAULT NULL,
        atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY empresa_mes (id_empresa, mes)
    )
");

/*
========================================
GARANTE A COLUNA limite_pedidos
========================================
*/

$resColuna = $con->query("
    SHOW COLUMNS
    FROM ferias_meses_disponiveis
    LIKE 'limite_pedidos'
");

if (!$resColuna || $resColuna->num_rows === 0) {
    $con->query("
        ALTER TABLE ferias_meses_disponiveis
        ADD COLUMN limite_pedidos INT NULL DEFAULT NULL
        AFTER disponivel
    ");
}

/* GARANTE QUE A COLUNA ACEITE NULL */
$con->query("
    ALTER TABLE ferias_meses_disponiveis
    MODIFY limite_pedidos INT NULL DEFAULT NULL
");


foreach ($meses as $nomeMes => $numeroMes) {

    $stmtMes = $con->prepare("
        INSERT IGNORE INTO ferias_meses_disponiveis (
            id_empresa,
            mes,
            disponivel,
            limite_pedidos
        )
        VALUES (?, ?, 1, NULL)
    ");

    if ($stmtMes) {
        $stmtMes->bind_param(
            "ii",
            $idEmpresa,
            $numeroMes
        );

        $stmtMes->execute();
        $stmtMes->close();
    }
}

$mesesDisponiveis = [];
$limitesPedidos = [];
$pedidosPorMes = [];

$stmtMeses = $con->prepare("
    SELECT
        mes,
        disponivel,
        limite_pedidos
    FROM ferias_meses_disponiveis
    WHERE id_empresa = ?
");

if (!$stmtMeses) {
    die("Erro SQL meses: " . $con->error);
}

$stmtMeses->bind_param(
    "i",
    $idEmpresa
);

$stmtMeses->execute();

$resMeses = $stmtMeses->get_result();

while ($row = $resMeses->fetch_assoc()) {

    $numeroMes = (int)$row['mes'];

    $mesesDisponiveis[$numeroMes] =
        (int)$row['disponivel'];

    $limitesPedidos[$numeroMes] =
        $row['limite_pedidos'] !== null
            ? (int)$row['limite_pedidos']
            : 0;
}

$stmtMeses->close();


foreach ($meses as $nomeMes => $numeroMes) {

    $anoReferencia = (int)date('Y');

    if ($numeroMes < (int)date('n')) {
        $anoReferencia++;
    }

    $stmtUso = $con->prepare("
        SELECT COUNT(*) AS total
        FROM ferias
        WHERE id_empresa = ?
        AND MONTH(data_inicio) = ?
        AND YEAR(data_inicio) = ?
        AND status IN (
            'pendente',
            'visto',
            'aprovado'
        )
    ");

    if ($stmtUso) {

        $stmtUso->bind_param(
            "iii",
            $idEmpresa,
            $numeroMes,
            $anoReferencia
        );

        $stmtUso->execute();

        $uso = $stmtUso
            ->get_result()
            ->fetch_assoc();

        $pedidosPorMes[$numeroMes] =
            (int)($uso['total'] ?? 0);

        $stmtUso->close();

    } else {

        $pedidosPorMes[$numeroMes] = 0;
    }
}

$stmtFunc = $con->prepare("
    SELECT nome
    FROM funcionarios
    WHERE id_funcionario = ?
    AND id_empresa = ?
    LIMIT 1
");

if (!$stmtFunc) {
    die("Erro SQL funcionário: " . $con->error);
}

$stmtFunc->bind_param(
    "ii",
    $idFuncionario,
    $idEmpresa
);

$stmtFunc->execute();

$funcionario = $stmtFunc
    ->get_result()
    ->fetch_assoc();

$nomeFuncionario =
    $funcionario['nome'] ?? 'Colaborador';

$stmtFunc->close();


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
    ORDER BY data_solicitacao DESC
    LIMIT 1
");

if (!$stmtPedido) {
    die("Erro SQL pedido férias: " . $con->error);
}

$stmtPedido->bind_param(
    "ii",
    $idFuncionario,
    $idEmpresa
);

$stmtPedido->execute();

$pedidoAtual = $stmtPedido
    ->get_result()
    ->fetch_assoc();

$stmtPedido->close();

$possuiPedidoPendente = !empty($pedidoAtual);

$alteracoesRestantes =
    $pedidoAtual['alteracoes_restantes'] ?? 2;


if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['solicitar_ferias'])
) {

    $mesNome = trim(
        $_POST['mes_ferias'] ?? ''
    );

    if (!isset($meses[$mesNome])) {

        $erro = "Selecione um mês válido.";

    } else {

        $numeroMes = $meses[$mesNome];

        $limiteMes =
            $limitesPedidos[$numeroMes] ?? 0;

        $usadosMes =
            $pedidosPorMes[$numeroMes] ?? 0;

        $atingiuLimite =
            $limiteMes > 0 &&
            $usadosMes >= $limiteMes;

        $mesLiberado =
            !empty($mesesDisponiveis[$numeroMes]);

        if (!$mesLiberado) {

            $erro = "Este mês não está disponível para solicitação de férias.";

        } elseif ($atingiuLimite) {

            $erro = "Este mês atingiu o limite de solicitações de férias.";

        } else {

            $anoAtual = (int)date('Y');

            if ($numeroMes < (int)date('n')) {
                $anoAtual++;
            }

            $dataInicio = sprintf(
                '%04d-%02d-01',
                $anoAtual,
                $numeroMes
            );

            $dataFim = date(
                'Y-m-d',
                strtotime($dataInicio . ' +29 days')
            );

            $dias = 30;

            if (
                $pedidoAtual &&
                $alteracoesRestantes <= 0
            ) {

                $erro = "Você já usou suas 2 alterações. Aguarde o RH visualizar sua solicitação.";

            } elseif ($pedidoAtual) {

                $novoRestante =
                    (int)$alteracoesRestantes - 1;

                $stmt = $con->prepare("
                    UPDATE ferias
                    SET
                        data_inicio = ?,
                        data_fim = ?,
                        dias = ?,
                        alteracoes_restantes = ?,
                        mensagem_colaborador =
                            'Solicitação alterada. Aguardando visualização do RH.'
                    WHERE id_ferias = ?
                    AND id_funcionario = ?
                    AND id_empresa = ?
                ");

                if (!$stmt) {
                    die(
                        "Erro SQL alterar férias: " .
                        $con->error
                    );
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

                    criarNotificacaoParaRHForcada(
                        $con,
                        $idEmpresa,
                        'Solicitação de férias alterada',
                        $nomeFuncionario .
                        ' alterou uma solicitação de férias para ' .
                        $mesNome . '.',
                        'ferias.php',
                        'solicitacao'
                    );

                    $mensagem =
                        "Solicitação de férias alterada com sucesso. " .
                        "Alterações restantes: " .
                        $novoRestante;

                    $alteracoesRestantes =
                        $novoRestante;

                    $possuiPedidoPendente = true;

                } else {

                    $erro =
                        "Erro ao alterar solicitação: " .
                        $stmt->error;
                }

                $stmt->close();

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
                        ?,
                        ?,
                        ?,
                        ?,
                        'pendente',
                        NOW(),
                        'Solicitação enviada. Aguardando visualização do RH.',
                        2,
                        ?
                    )
                ");

                if (!$stmt) {
                    die(
                        "Erro SQL férias: " .
                        $con->error
                    );
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

                    criarNotificacaoParaRHForcada(
                        $con,
                        $idEmpresa,
                        'Nova solicitação de férias',
                        $nomeFuncionario .
                        ' enviou uma nova solicitação de férias para ' .
                        $mesNome . '.',
                        'ferias.php',
                        'solicitacao'
                    );

                    $mensagem =
                        "Solicitação de férias enviada para o RH.";

                    $possuiPedidoPendente = true;
                    $alteracoesRestantes = 2;

                } else {

                    $erro =
                        "Erro ao solicitar férias: " .
                        $stmt->error;
                }

                $stmt->close();
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
<link rel="stylesheet" href="../css/sidebar.css?v=20">
<link rel="stylesheet" href="../css/pedidosf.css?v=2">

</head>
<body>
<?php include 'sidebarfunc.php'; ?>

<main class="pedidos-page">

<div class="container-fluid">

    <?php if (isset($_GET['sucesso'])): ?>

        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0 rounded-4">

            <i class="fa-solid fa-circle-check me-2"></i>

            Licença enviada com sucesso. Agora ela está em andamento até o RH visualizar.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>

    <?php if ($mensagem): ?>

        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0 rounded-4">

            <i class="fa-solid fa-circle-check me-2"></i>

            <?= htmlspecialchars($mensagem) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>

    <?php if ($erro): ?>

        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm border-0 rounded-4">

            <i class="fa-solid fa-triangle-exclamation me-2"></i>

            <?= htmlspecialchars($erro) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

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

        <!-- FÉRIAS -->
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

                    <?php if ($possuiPedidoPendente): ?>

                        <div class="alert alert-warning rounded-4">

                            <i class="fa-solid fa-clock me-2"></i>

                            Você já possui uma solicitação em andamento.
                            Alterações restantes:

                            <strong>
                                <?= (int)$alteracoesRestantes ?>
                            </strong>

                        </div>

                    <?php endif; ?>

                    <form method="POST" id="formFerias">

                        <input
                            type="hidden"
                            name="mes_ferias"
                            id="mesFerias"
                        >

                        <div class="row g-3">

                            <?php foreach ($meses as $mes => $numero): ?>

                                <?php
                                $limiteMes =
                                    $limitesPedidos[$numero] ?? 0;

                                $usadosMes =
                                    $pedidosPorMes[$numero] ?? 0;

                                $atingiuLimite =
                                    $limiteMes > 0 &&
                                    $usadosMes >= $limiteMes;

                                $mesLiberadoRH =
                                    !empty(
                                        $mesesDisponiveis[$numero]
                                    );

                                $mesDisponivel =
                                    $mesLiberadoRH &&
                                    !$atingiuLimite;

                                $limiteAlteracoes =
                                    $possuiPedidoPendente &&
                                    $alteracoesRestantes <= 0;

                                $bloqueado =
                                    !$mesDisponivel ||
                                    $limiteAlteracoes;
                                ?>

                                <div class="col-6">

                                    <button
                                        type="button"
                                        class="btn w-100 py-3 fw-semibold mes-btn <?= $mesDisponivel ? 'btn-light border' : 'btn-secondary mes-indisponivel' ?>"
                                        onclick="selecionarMes(this, '<?= htmlspecialchars($mes) ?>')"
                                        <?= $bloqueado ? 'disabled' : '' ?>
                                    >

                                        <span>
                                            <?= htmlspecialchars($mes) ?>
                                        </span>

                                        <?php if ($atingiuLimite): ?>

                                            <small class="d-block">
                                                Limite atingido
                                            </small>

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
                                Apenas meses liberados pelo RH e dentro do limite podem ser solicitados.
                            </small>

                        </div>

                        <button
                            type="submit"
                            name="solicitar_ferias"
                            id="btnSolicitar"
                            class="btn btn-primary w-100 py-3 fw-semibold mt-4"
                            <?= (
                                $possuiPedidoPendente &&
                                $alteracoesRestantes <= 0
                            ) ? 'disabled' : '' ?>
                        >

                            <i class="fa-solid fa-paper-plane me-2"></i>

                            <?= $possuiPedidoPendente
                                ? 'Alterar Solicitação'
                                : 'Solicitar Férias'
                            ?>

                        </button>

                    </form>

                </div>

            </div>

        </div>

        <!-- LICENÇA MÉDICA -->
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
function selecionarMes(elemento, mes) {

    if (elemento.disabled) {
        return;
    }

    document
        .querySelectorAll(".mes-btn")
        .forEach(function (btn) {

            btn.classList.remove(
                "btn-primary",
                "text-white"
            );

            btn.classList.add("btn-light");
        });

    elemento.classList.remove("btn-light");

    elemento.classList.add(
        "btn-primary",
        "text-white"
    );

    document
        .getElementById("mesFerias")
        .value = mes;

    document
        .getElementById("mesSelecionadoTexto")
        .innerHTML = `
            <strong>Mês selecionado:</strong><br>
            ${mes}
        `;

    mostrarAlerta(
        "✔ " + mes + " selecionado"
    );
}

const formFerias =
    document.getElementById('formFerias');

if (formFerias) {

    formFerias.addEventListener(
        'submit',
        function (event) {

            const mesSelecionado =
                document
                    .getElementById("mesFerias")
                    .value;

            if (!mesSelecionado) {

                event.preventDefault();

                mostrarAlerta(
                    "Selecione um mês disponível antes de solicitar."
                );
            }
        }
    );
}

function mostrarAlerta(texto) {

    const alerta =
        document.getElementById("alerta");

    if (!alerta) {
        return;
    }

    alerta.textContent = texto;

    alerta.classList.remove("d-none");

    setTimeout(function () {
        alerta.classList.add("d-none");
    }, 3000);
}
</script>
<script src="../js/theme.js"></script>
</body>
</html>