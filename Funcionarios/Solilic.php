<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../auth.php';
require_once '../config/database.php';
require_once '../lang.php';

$idFuncionario = $_SESSION['id_funcionario'] ?? null;
$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idFuncionario || !$idEmpresa) {
    die("Sessão inválida. Faça login novamente.");
}

$solicitacoes = [];

/* FÉRIAS */
$stmtFerias = $con->prepare("
    SELECT
        'Férias' AS tipo,
        data_inicio,
        data_fim,
        dias,
        status,
        data_solicitacao AS data_envio,
        data_visto,
        mensagem_colaborador,
        motivo_rejeicao
    FROM ferias
    WHERE id_funcionario = ?
    AND id_empresa = ?
");

$stmtFerias->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtFerias->execute();

$resFerias = $stmtFerias->get_result();

while ($f = $resFerias->fetch_assoc()) {
    $solicitacoes[] = $f;
}

/* LICENÇAS */
$stmtLicencas = $con->prepare("
    SELECT
        'Licença Médica' AS tipo,
        data_inicio,
        data_fim,
        dias,
        status,
        data_envio,
        NULL AS data_visto,
        NULL AS mensagem_colaborador,
        NULL AS motivo_rejeicao
    FROM licencas_medicas
    WHERE id_funcionario = ?
    AND id_empresa = ?
");

$stmtLicencas->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtLicencas->execute();

$resLicencas = $stmtLicencas->get_result();

while ($l = $resLicencas->fetch_assoc()) {
    $solicitacoes[] = $l;
}

usort($solicitacoes, function($a, $b) {
    return strtotime($b['data_envio']) - strtotime($a['data_envio']);
});

function badgeStatus($status) {
    if ($status == 'pendente') {
        return '<span class="badge bg-warning text-dark">Em andamento</span>';
    }

    if ($status == 'visto') {
        return '<span class="badge bg-info text-dark">Visualizado pelo RH</span>';
    }

    if ($status == 'aprovado') {
        return '<span class="badge bg-success">Aprovado</span>';
    }

    if ($status == 'rejeitado') {
        return '<span class="badge bg-danger">Rejeitado</span>';
    }

    return '<span class="badge bg-secondary">Indefinido</span>';
}

function textoMensagem($item) {
    if (!empty($item['mensagem_colaborador'])) {
        return $item['mensagem_colaborador'];
    }

    if ($item['status'] == 'pendente') {
        return 'Aguardando análise do RH.';
    }

    if ($item['status'] == 'visto') {
        return 'Sua solicitação foi visualizada pelo RH.';
    }

    if ($item['status'] == 'aprovado') {
        return 'Sua solicitação foi aprovada pelo RH.';
    }

    if ($item['status'] == 'rejeitado') {
        return 'Sua solicitação foi rejeitada pelo RH.';
    }

    return '-';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Minhas Solicitações</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="../css/global.css">
<link rel="stylesheet" href="../css/sidebarfunc.css">
</head>

<body>

<?php include 'sidebarfunc.php'; ?>

<div class="content">

<div class="container-fluid">

    <h2 class="fw-bold mb-1">Minhas Solicitações</h2>

    <p class="text-muted mb-4">
        Acompanhe seus pedidos de férias e licenças médicas.
    </p>

    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body p-4">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>Tipo</th>
                            <th>Período</th>
                            <th>Dias</th>
                            <th>Enviado em</th>
                            <th>Status</th>
                            <th>Mensagem</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if(count($solicitacoes) > 0): ?>

                        <?php foreach($solicitacoes as $s): ?>

                            <tr>
                                <td>
                                    <?php if($s['tipo'] == 'Férias'): ?>
                                        <i class="fa-solid fa-umbrella-beach text-primary me-2"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-file-medical text-danger me-2"></i>
                                    <?php endif; ?>

                                    <?= htmlspecialchars($s['tipo']) ?>
                                </td>

                                <td>
                                    <?= date('d/m/Y', strtotime($s['data_inicio'])) ?>
                                    -
                                    <?= date('d/m/Y', strtotime($s['data_fim'])) ?>
                                </td>

                                <td>
                                    <?= (int)$s['dias'] ?> dias
                                </td>

                                <td>
                                    <?= date('d/m/Y H:i', strtotime($s['data_envio'])) ?>
                                </td>

                                <td>
                                    <?= badgeStatus($s['status']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(textoMensagem($s)) ?>

                                    <?php if(!empty($s['data_visto']) && in_array($s['status'], ['visto', 'aprovado', 'rejeitado'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            Atualizado em <?= date('d/m/Y H:i', strtotime($s['data_visto'])) ?>
                                        </small>
                                    <?php endif; ?>

                                    <?php if($s['status'] == 'rejeitado' && !empty($s['motivo_rejeicao'])): ?>
                                        <br>
                                        <small class="text-danger">
                                            Motivo: <?= htmlspecialchars($s['motivo_rejeicao']) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fa-regular fa-folder-open display-4 d-block mb-3"></i>
                                Nenhuma solicitação encontrada.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</div>

</body>
</html>