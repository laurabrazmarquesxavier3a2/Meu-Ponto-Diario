<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';

if (file_exists('registrar-atividade.php')) {
    require_once 'registrar-atividade.php';
}

$id_empresa = $_SESSION['id_empresa'] ?? 0;

if (!$id_empresa) {
    die("Erro: empresa não identificada. Faça login novamente.");
}

$mensagem = '';
$erro = '';

$mesesFerias = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

/* TABELA DOS MESES */
$con->query("
    CREATE TABLE IF NOT EXISTS ferias_meses_disponiveis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_empresa INT NOT NULL,
        mes TINYINT NOT NULL,
        disponivel TINYINT NOT NULL DEFAULT 1,
        limite_pedidos INT NULL DEFAULT NULL,
        atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY empresa_mes (id_empresa, mes)
    )
");

/* GARANTE COLUNA limite_pedidos */
$checkColuna = $con->query("SHOW COLUMNS FROM ferias_meses_disponiveis LIKE 'limite_pedidos'");

if ($checkColuna && $checkColuna->num_rows == 0) {
    $con->query("
        ALTER TABLE ferias_meses_disponiveis
        ADD COLUMN limite_pedidos INT NULL DEFAULT NULL AFTER disponivel
    ");
}

/* CORRIGE limite_pedidos PARA ACEITAR NULL */
$con->query("
    ALTER TABLE ferias_meses_disponiveis
    MODIFY limite_pedidos INT NULL DEFAULT NULL
");

/* GARANTE OS 12 MESES */
foreach ($mesesFerias as $numMes => $nomeMes) {
    $stmtMes = $con->prepare("
        INSERT IGNORE INTO ferias_meses_disponiveis 
        (id_empresa, mes, disponivel, limite_pedidos)
        VALUES (?, ?, 1, NULL)
    ");

    if ($stmtMes) {
        $stmtMes->bind_param("ii", $id_empresa, $numMes);
        $stmtMes->execute();
        $stmtMes->close();
    }
}

/* AÇÕES */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* SALVAR CONFIGURAÇÃO DO MÊS */
    if (isset($_POST['salvar_config_mes'])) {

        $mesConfig = intval($_POST['mes_config'] ?? 0);
        $tipoConfig = $_POST['tipo_config'] ?? 'liberado';
        $limitePedidos = $_POST['limite_pedidos'] ?? null;

        if ($mesConfig < 1 || $mesConfig > 12) {
            $erro = "Mês inválido.";
        } else {

            /* BLOQUEADO */
            if ($tipoConfig === 'bloqueado') {

                $stmtConfig = $con->prepare("
                    INSERT INTO ferias_meses_disponiveis
                        (id_empresa, mes, disponivel, limite_pedidos)
                    VALUES
                        (?, ?, 0, NULL)
                    ON DUPLICATE KEY UPDATE
                        disponivel = 0,
                        limite_pedidos = NULL,
                        atualizado_em = NOW()
                ");

                if (!$stmtConfig) {
                    $erro = "Erro ao preparar bloqueio: " . $con->error;
                } else {
                    $stmtConfig->bind_param("ii", $id_empresa, $mesConfig);

                    if ($stmtConfig->execute()) {
                        $mensagem = $mesesFerias[$mesConfig] . " bloqueado para solicitações.";

                        if (function_exists('registrarAtividade')) {
                            registrarAtividade(
                                $con,
                                "Bloqueou solicitações de férias para " . $mesesFerias[$mesConfig],
                                "warning"
                            );
                        }
                    } else {
                        $erro = "Erro ao bloquear mês: " . $stmtConfig->error;
                    }

                    $stmtConfig->close();
                }

            /* LIMITADO */
            } elseif ($tipoConfig === 'limitado') {

                $limitePedidosFinal = intval($limitePedidos);

                if ($limitePedidosFinal <= 0) {
                    $erro = "Informe uma quantidade válida de pedidos para o mês.";
                } else {

                    $stmtConfig = $con->prepare("
                        INSERT INTO ferias_meses_disponiveis
                            (id_empresa, mes, disponivel, limite_pedidos)
                        VALUES
                            (?, ?, 1, ?)
                        ON DUPLICATE KEY UPDATE
                            disponivel = 1,
                            limite_pedidos = VALUES(limite_pedidos),
                            atualizado_em = NOW()
                    ");

                    if (!$stmtConfig) {
                        $erro = "Erro ao preparar limite: " . $con->error;
                    } else {
                        $stmtConfig->bind_param(
                            "iii",
                            $id_empresa,
                            $mesConfig,
                            $limitePedidosFinal
                        );

                        if ($stmtConfig->execute()) {
                            $mensagem = $mesesFerias[$mesConfig] . " limitado a " . $limitePedidosFinal . " pedidos.";

                            if (function_exists('registrarAtividade')) {
                                registrarAtividade(
                                    $con,
                                    "Limitou solicitações de férias para " . $mesesFerias[$mesConfig],
                                    "primary"
                                );
                            }
                        } else {
                            $erro = "Erro ao limitar mês: " . $stmtConfig->error;
                        }

                        $stmtConfig->close();
                    }
                }

            /* LIBERADO SEM LIMITE */
            } else {

                $stmtConfig = $con->prepare("
                    INSERT INTO ferias_meses_disponiveis
                        (id_empresa, mes, disponivel, limite_pedidos)
                    VALUES
                        (?, ?, 1, NULL)
                    ON DUPLICATE KEY UPDATE
                        disponivel = 1,
                        limite_pedidos = NULL,
                        atualizado_em = NOW()
                ");

                if (!$stmtConfig) {
                    $erro = "Erro ao preparar liberação: " . $con->error;
                } else {
                    $stmtConfig->bind_param("ii", $id_empresa, $mesConfig);

                    if ($stmtConfig->execute()) {
                        $mensagem = $mesesFerias[$mesConfig] . " liberado sem limite.";

                        if (function_exists('registrarAtividade')) {
                            registrarAtividade(
                                $con,
                                "Liberou solicitações de férias para " . $mesesFerias[$mesConfig],
                                "success"
                            );
                        }
                    } else {
                        $erro = "Erro ao liberar mês: " . $stmtConfig->error;
                    }

                    $stmtConfig->close();
                }
            }
        }
    }

    /* APROVAR / REJEITAR */
    if (!isset($_POST['salvar_config_mes'])) {

        $id_ferias = intval($_POST['id_ferias'] ?? 0);

        if (!$id_ferias) {
            $erro = "Pedido inválido.";
        }

        if (!$erro && isset($_POST['aprovar'])) {

            $msg = "Sua solicitação de férias foi aprovada pelo RH.";

            $stmt = $con->prepare("
                UPDATE ferias
                SET 
                    status = 'aprovado',
                    data_visto = NOW(),
                    mensagem_colaborador = ?,
                    motivo_rejeicao = NULL
                WHERE id_ferias = ?
                AND id_empresa = ?
            ");

            if (!$stmt) {
                $erro = "Erro ao preparar aprovação: " . $con->error;
            } else {
                $stmt->bind_param("sii", $msg, $id_ferias, $id_empresa);

                if ($stmt->execute()) {
                    $mensagem = "Solicitação aprovada com sucesso.";

                    if (function_exists('registrarAtividade')) {
                        registrarAtividade($con, "Aprovou uma solicitação de férias", "success");
                    }
                } else {
                    $erro = "Erro ao aprovar solicitação.";
                }

                $stmt->close();
            }
        }

        if (!$erro && isset($_POST['rejeitar'])) {

            $motivo = trim($_POST['motivo_rejeicao'] ?? '');

            if ($motivo == '') {
                $motivo = "Solicitação rejeitada pelo RH.";
            }

            $msg = "Sua solicitação de férias foi rejeitada pelo RH.";

            $stmt = $con->prepare("
                UPDATE ferias
                SET 
                    status = 'rejeitado',
                    data_visto = NOW(),
                    mensagem_colaborador = ?,
                    motivo_rejeicao = ?
                WHERE id_ferias = ?
                AND id_empresa = ?
            ");

            if (!$stmt) {
                $erro = "Erro ao preparar rejeição: " . $con->error;
            } else {
                $stmt->bind_param("ssii", $msg, $motivo, $id_ferias, $id_empresa);

                if ($stmt->execute()) {
                    $mensagem = "Solicitação rejeitada.";

                    if (function_exists('registrarAtividade')) {
                        registrarAtividade($con, "Rejeitou uma solicitação de férias", "danger");
                    }
                } else {
                    $erro = "Erro ao rejeitar solicitação.";
                }

                $stmt->close();
            }
        }
    }
}

/* MESES CONFIGURADOS */
$mesesConfigurados = [];

$stmtMeses = $con->prepare("
    SELECT 
        mes, 
        disponivel,
        limite_pedidos
    FROM ferias_meses_disponiveis
    WHERE id_empresa = ?
    ORDER BY mes ASC
");

$stmtMeses->bind_param("i", $id_empresa);
$stmtMeses->execute();
$resMeses = $stmtMeses->get_result();

while ($rowMes = $resMeses->fetch_assoc()) {
    $mesesConfigurados[(int)$rowMes['mes']] = [
        'disponivel' => (int)$rowMes['disponivel'],
        'limite_pedidos' => $rowMes['limite_pedidos'] !== null ? (int)$rowMes['limite_pedidos'] : null,
        'usados' => 0
    ];
}

$stmtMeses->close();

/* PEDIDOS POR MÊS */
$stmtPedidosMes = $con->prepare("
    SELECT 
        MONTH(data_inicio) AS mes,
        COUNT(*) AS total
    FROM ferias
    WHERE id_empresa = ?
    GROUP BY MONTH(data_inicio)
");

$stmtPedidosMes->bind_param("i", $id_empresa);
$stmtPedidosMes->execute();
$resPedidosMes = $stmtPedidosMes->get_result();

while ($rowPedidoMes = $resPedidosMes->fetch_assoc()) {
    $mesPedido = (int)$rowPedidoMes['mes'];

    if (isset($mesesConfigurados[$mesPedido])) {
        $mesesConfigurados[$mesPedido]['usados'] = (int)$rowPedidoMes['total'];
    }
}

$stmtPedidosMes->close();

$totalMesesDisponiveis = 0;

foreach ($mesesConfigurados as $config) {
    if (!empty($config['disponivel'])) {
        $totalMesesDisponiveis++;
    }
}

/* CARDS */
/* ==========================================================
   INDICADORES (MESMA LÓGICA DO BANCO DE HORAS)
   ========================================================== */

$pendentes = 0;
$aprovados = 0;
$rejeitados = 0;
$totalSolicitacoes = 0;

$proximoInicio = null;
$ultimaAprovacao = null;

$stmtIndicadores = $con->prepare("
    SELECT
        id_ferias,
        data_inicio,
        data_fim,
        data_solicitacao,
        status
    FROM ferias
    WHERE id_empresa = ?
");

$stmtIndicadores->bind_param("i", $id_empresa);
$stmtIndicadores->execute();

$resIndicadores = $stmtIndicadores->get_result();

while ($row = $resIndicadores->fetch_assoc()) {

    $totalSolicitacoes++;

    switch ($row['status']) {

        case 'pendente':
            $pendentes++;
            break;

        case 'aprovado':
            $aprovados++;

            if (
                $ultimaAprovacao === null ||
                strtotime($row['data_solicitacao']) >
                strtotime($ultimaAprovacao['data_solicitacao'])
            ) {
                $ultimaAprovacao = $row;
            }

            break;

        case 'rejeitado':
            $rejeitados++;
            break;
    }

    if (
        $row['status'] === 'aprovado' &&
        strtotime($row['data_inicio']) >= strtotime(date('Y-m-d'))
    ) {

        if (
            $proximoInicio === null ||
            strtotime($row['data_inicio']) <
            strtotime($proximoInicio['data_inicio'])
        ) {
            $proximoInicio = $row;
        }
    }
}

$stmtIndicadores->close();

/* LISTAGEM */
$stmt = $con->prepare("
    SELECT
        fe.id_ferias,
        fe.data_inicio,
        fe.data_fim,
        fe.dias,
        fe.data_solicitacao,
        fe.status,
        fe.data_visto,
        fe.mensagem_colaborador,
        fe.motivo_rejeicao,
        f.nome
    FROM ferias fe
    INNER JOIN funcionarios f
        ON f.id_funcionario = fe.id_funcionario
        AND f.id_empresa = fe.id_empresa
    WHERE fe.id_empresa = ?
    ORDER BY fe.data_solicitacao DESC
");

$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$query = $stmt->get_result();

function badgeStatus($status) {
    if ($status == 'pendente') return '<span class="badge-status badge-pendente">Pendente</span>';
    if ($status == 'visto') return '<span class="badge-status badge-visto">Visto</span>';
    if ($status == 'aprovado') return '<span class="badge-status badge-aprovado">Aprovado</span>';
    if ($status == 'rejeitado') return '<span class="badge-status badge-rejeitado">Rejeitado</span>';
    return '<span class="badge-status badge-indefinido">Indefinido</span>';
}

function classeMes($config) {
    if (empty($config['disponivel'])) {
        return 'month-bloqueado';
    }

    if ($config['limite_pedidos'] !== null) {
        return 'month-limitado';
    }

    return 'month-liberado';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Pedidos de Férias</title>

<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/ferias.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid">

    <div class="dashboard-header">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <h1 class="dashboard-title">Pedidos de Férias</h1>
                <p class="dashboard-subtitle">
                    Gerencie solicitações, aprovações e regras mensais para os colaboradores.
                </p>
            </div>

            <div class="d-flex align-items-start">
                <span class="year-pill">
                    <i class="bi bi-calendar-check me-1"></i>
                    <?= date('Y') ?>
                </span>
            </div>
        </div>
    </div>

    <?php if($mensagem): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($erro): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($erro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">

        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Pendentes</div>
                        <h2 class="kpi-value"><?= (int)$pendentes ?></h2>
                        <div class="kpi-small">Aguardando análise</div>
                    </div>
                    <div class="kpi-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label"> Rejeitados</div>
                        <h2 class="kpi-value"><?= (int)$rejeitados ?></h2>
                        <div class="kpi-small">Solicitações recusadas</div>
                    </div>
                    <div class="kpi-icon">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Aprovados</div>
                        <h2 class="kpi-value"><?= (int)$aprovados ?></h2>
                        <div class="kpi-small">Solicitações aceitas</div>
                    </div>
                    <div class="kpi-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Total de solicitações</div>
                        <h2 class="kpi-value"><?= $totalSolicitacoes ?></h2>
                        <div class="kpi-small">Pedidos registrados</div>
                    </div>
                    <div class="kpi-icon">
                        <i class="bi bi-calendar2-week"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="panel-card mb-4">
        <div class="panel-header">
            <h5>Configuração mensal de férias</h5>
            <p>Clique em um mês para liberar, bloquear ou limitar solicitações.</p>
        </div>

        <div class="panel-body-custom">
            <div class="month-grid">
                <?php foreach ($mesesFerias as $numMes => $nomeMes): ?>
                    <?php
                    $config = $mesesConfigurados[$numMes] ?? [
                        'disponivel' => 1,
                        'limite_pedidos' => null,
                        'usados' => 0
                    ];

                    $classe = classeMes($config);
                    ?>

                    <button
                        type="button"
                        class="month-card <?= htmlspecialchars($classe) ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#modalConfigMes"
                        data-mes="<?= (int)$numMes ?>"
                        data-nome="<?= htmlspecialchars($nomeMes) ?>"
                        data-disponivel="<?= (int)$config['disponivel'] ?>"
                        data-limite="<?= $config['limite_pedidos'] !== null ? (int)$config['limite_pedidos'] : '' ?>"
                        onclick="abrirConfigMes(this)"
                    >
                        <?= htmlspecialchars($nomeMes) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card-header">
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <h5>Solicitações</h5>
                    <p>Controle os pedidos enviados pelos funcionários.</p>
                </div>

                <div class="col-lg-5">
                    <input
                        type="text"
                        class="form-control search-input"
                        id="buscarFerias"
                        placeholder="Pesquisar por funcionário, status ou período..."
                    >
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>Período</th>
                        <th>Dias</th>
                        <th>Solicitação</th>
                        <th>Status</th>
                        <th>Mensagem</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>

                <tbody id="tabelaFerias">
                <?php if ($query->num_rows > 0): ?>
                    <?php while ($ferias = $query->fetch_assoc()): ?>
                        <?php
                        $inicial = mb_strtoupper(mb_substr($ferias['nome'], 0, 1, 'UTF-8'), 'UTF-8');
                        ?>

                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="employee-avatar">
                                        <?= htmlspecialchars($inicial) ?>
                                    </div>
                                    <span class="employee-name">
                                        <?= htmlspecialchars($ferias['nome']) ?>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <?= date('d/m/Y', strtotime($ferias['data_inicio'])) ?>
                                -
                                <?= date('d/m/Y', strtotime($ferias['data_fim'])) ?>
                            </td>

                            <td><?= (int)$ferias['dias'] ?> dias</td>

                            <td><?= date('d/m/Y', strtotime($ferias['data_solicitacao'])) ?></td>

                            <td><?= badgeStatus($ferias['status']) ?></td>

                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars($ferias['mensagem_colaborador'] ?? '-') ?>

                                    <?php if($ferias['status'] == 'rejeitado' && !empty($ferias['motivo_rejeicao'])): ?>
                                        <br>
                                        <strong>Motivo:</strong>
                                        <?= htmlspecialchars($ferias['motivo_rejeicao']) ?>
                                    <?php endif; ?>
                                </small>
                            </td>

                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id_ferias" value="<?= $ferias['id_ferias'] ?>">

                                       <button
                                        type="button"
                                        class="btn btn-success btn-sm btn-action"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAprovar"
                                        data-id="<?= $ferias['id_ferias'] ?>"
                                        onclick="prepararAprovacao(this)"
                                    >
                                        <i class="bi bi-check-lg"></i>
                                        Aprovar
                                    </button>
                                    </form>

                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm btn-action"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalRejeitar"
                                        data-id="<?= $ferias['id_ferias'] ?>"
                                        onclick="prepararRejeicao(this)"
                                    >
                                        <i class="bi bi-x-lg"></i>
                                        Rejeitar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                Nenhuma solicitação de férias encontrada.
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</div>

<!-- MODAL CONFIGURAR MÊS -->
<div class="modal fade" id="modalConfigMes" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-gear me-2 text-primary"></i>
                    Configurar <span id="tituloMesConfig">mês</span>
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="mes_config" id="mesConfigInput">

                <div class="d-grid gap-3">

                    <label class="config-box">
                        <input
                            type="radio"
                            name="tipo_config"
                            value="liberado"
                            id="tipoLiberado"
                            onchange="toggleLimitePedidos()"
                        >
                        <span class="radio-fake"></span>

                        <div>
                            <strong>Liberado sem limite</strong>
                            <div class="text-muted small">
                                Funcionários poderão solicitar férias neste mês sem limite de quantidade.
                            </div>
                        </div>
                    </label>

                    <label class="config-box">
                        <input
                            type="radio"
                            name="tipo_config"
                            value="limitado"
                            id="tipoLimitado"
                            onchange="toggleLimitePedidos()"
                        >
                        <span class="radio-fake"></span>

                        <div class="w-100">
                            <strong>Liberado com limite</strong>
                            <div class="text-muted small mb-2">
                                Defina quantos pedidos poderão ser feitos neste mês.
                            </div>

                            <input
                                type="number"
                                name="limite_pedidos"
                                id="limitePedidosInput"
                                class="form-control"
                                min="1"
                                placeholder="Ex: 5"
                                disabled
                            >
                        </div>
                    </label>

                    <label class="config-box">
                        <input
                            type="radio"
                            name="tipo_config"
                            value="bloqueado"
                            id="tipoBloqueado"
                            onchange="toggleLimitePedidos()"
                        >
                        <span class="radio-fake"></span>

                        <div>
                            <strong>Bloqueado para solicitações</strong>
                            <div class="text-muted small">
                                Funcionários não poderão solicitar férias neste mês.
                            </div>
                        </div>
                    </label>

                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary btn-action"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    name="salvar_config_mes"
                    class="btn btn-primary btn-action"
                >
                    <i class="bi bi-save me-1"></i>
                    Salvar configuração
                </button>
            </div>

        </form>
    </div>
</div>

<!-- MODAL REJEITAR -->
<div class="modal fade" id="modalRejeitar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle me-2 text-danger"></i>
                    Rejeitar solicitação
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="id_ferias" id="idFeriasRejeitar">

                <label class="form-label fw-bold">
                    Motivo da rejeição
                </label>

                <textarea
                    name="motivo_rejeicao"
                    class="form-control"
                    rows="4"
                    placeholder="Ex: Período indisponível para o setor."
                    required
                ></textarea>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary btn-action"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    name="rejeitar"
                    class="btn btn-danger btn-action"
                >
                    Rejeitar pedido
                </button>
            </div>

        </form>
    </div>
</div>

<div class="modal fade" id="modalAprovar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle text-success me-2"></i>
                    Aprovar solicitação
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">
                Tem certeza que deseja aprovar esta solicitação?

                <input
                    type="hidden"
                    name="id_ferias"
                    id="idFeriasAprovar">
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button
                    type="submit"
                    name="aprovar"
                    class="btn btn-success">
                    Confirmar Aprovação
                </button>
            </div>

        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/ferias.js"></script>
<script src="js/theme.js"></script>
</body>
</html>