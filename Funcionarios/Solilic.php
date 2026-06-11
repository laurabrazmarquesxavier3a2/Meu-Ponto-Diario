<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../auth.php';
require_once '../config/database.php';
require_once '../lang.php';

$idUsuario = $_SESSION['id_usuario'] ?? null;
$idFuncionario = $_SESSION['id_funcionario'] ?? null;
$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idUsuario || !$idEmpresa) {
    die("Sessão inválida. Faça login novamente.");
}

/*
========================================
BUSCAR ID_FUNCIONARIO CASO NÃO ESTEJA NA SESSÃO
========================================
*/

if (!$idFuncionario) {

    $stmtUser = $con->prepare("
        SELECT id_funcionario
        FROM usuarios
        WHERE id_usuario = ?
        AND id_empresa = ?
        LIMIT 1
    ");

    if (!$stmtUser) {
        die("Erro SQL usuário: " . $con->error);
    }

    $stmtUser->bind_param("ii", $idUsuario, $idEmpresa);
    $stmtUser->execute();

    $dadosUser = $stmtUser->get_result()->fetch_assoc();

    if (!empty($dadosUser['id_funcionario'])) {
        $idFuncionario = $dadosUser['id_funcionario'];
        $_SESSION['id_funcionario'] = $idFuncionario;
    }
}

if (!$idFuncionario) {
    die("Funcionário não encontrado.");
}

/*
========================================
LISTAS
========================================
*/

$feriasLista = [];
$licencasLista = [];
$ocorrenciasLista = [];

/*
========================================
FÉRIAS
========================================
*/

$stmtFerias = $con->prepare("
    SELECT
        id_ferias,
        data_inicio,
        data_fim,
        dias,
        status,
        data_solicitacao,
        data_visto,
        mensagem_colaborador,
        alteracoes_restantes
    FROM ferias
    WHERE id_funcionario = ?
    AND id_empresa = ?
    ORDER BY data_solicitacao DESC
");

if (!$stmtFerias) {
    die("Erro SQL férias: " . $con->error);
}

$stmtFerias->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtFerias->execute();

$resFerias = $stmtFerias->get_result();

while ($f = $resFerias->fetch_assoc()) {
    $feriasLista[] = $f;
}

/*
========================================
LICENÇAS MÉDICAS / ATESTADOS
========================================
*/

$stmtLicencas = $con->prepare("
    SELECT
        id,
        id_funcionario,
        arquivo_atestado,
        tipo_arquivo,
        motivo,
        data_inicio,
        data_fim,
        dias,
        observacao,
        status,
        data_envio,
        data_visto,
        mensagem_colaborador
    FROM licencas_medicas
    WHERE id_funcionario = ?
    AND id_empresa = ?
    ORDER BY data_envio DESC
");

if (!$stmtLicencas) {
    die("Erro SQL licenças: " . $con->error);
}

$stmtLicencas->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtLicencas->execute();

$resLicencas = $stmtLicencas->get_result();

while ($l = $resLicencas->fetch_assoc()) {
    $licencasLista[] = $l;
}

/*
========================================
OCORRÊNCIAS DO USUÁRIO
========================================
*/

$stmtOcorrencias = $con->prepare("
    SELECT
        id_ocorrencia,
        tipo_reporte,
        categoria,
        andar,
        sala,
        local_especifico,
        descricao,
        testemunhas,
        evidencia,
        status,
        data_ocorrencia
    FROM ocorrencias
    WHERE id_empresa = ?
    AND id_usuario = ?
    ORDER BY data_ocorrencia DESC
");

if (!$stmtOcorrencias) {
    die("Erro SQL ocorrências: " . $con->error);
}

$stmtOcorrencias->bind_param("ii", $idEmpresa, $idUsuario);
$stmtOcorrencias->execute();

$resOcorrencias = $stmtOcorrencias->get_result();

while ($o = $resOcorrencias->fetch_assoc()) {
    $ocorrenciasLista[] = $o;
}

/*
========================================
CONTADORES
========================================
*/

$totalFerias = count($feriasLista);
$totalLicencas = count($licencasLista);
$totalOcorrencias = count($ocorrenciasLista);
$totalSolicitacoes = $totalFerias + $totalLicencas + $totalOcorrencias;

/*
========================================
FUNÇÕES VISUAIS
========================================
*/

function textoStatusSolic($status) {

    if ($status === 'pendente') {
        return 'Em andamento';
    }

    if ($status === 'visto') {
        return 'Visualizado pelo RH';
    }

    if ($status === 'aprovado') {
        return 'Aprovado';
    }

    if ($status === 'rejeitado') {
        return 'Rejeitado';
    }

    if ($status === 'aberta') {
        return 'Aberta';
    }

    if ($status === 'em_analise') {
        return 'Em análise';
    }

    if ($status === 'resolvida') {
        return 'Resolvida';
    }

    return 'Em andamento';
}

function badgeStatusSolic($status) {

    if ($status === 'visto') {
        return 'bg-info text-dark';
    }

    if ($status === 'aprovado' || $status === 'resolvida') {
        return 'bg-success';
    }

    if ($status === 'rejeitado') {
        return 'bg-danger';
    }

    if ($status === 'em_analise') {
        return 'bg-warning text-dark';
    }

    if ($status === 'aberta') {
        return 'bg-primary';
    }

    return 'bg-warning text-dark';
}

function mensagemFerias($item) {

    if (!empty($item['mensagem_colaborador'])) {
        return $item['mensagem_colaborador'];
    }

    if ($item['status'] === 'visto') {
        return 'Sua solicitação de férias foi visualizada pelo RH.';
    }

    if ($item['status'] === 'aprovado') {
        return 'Sua solicitação de férias foi aprovada.';
    }

    if ($item['status'] === 'rejeitado') {
        return 'Sua solicitação de férias foi rejeitada.';
    }

    return 'Aguardando visualização do RH.';
}

function mensagemLicenca($item) {

    if (!empty($item['mensagem_colaborador'])) {
        return $item['mensagem_colaborador'];
    }

    if ($item['status'] === 'visto') {
        return 'Seu atestado foi visualizado pelo RH.';
    }

    if ($item['status'] === 'aprovado') {
        return 'Sua licença médica foi aprovada.';
    }

    if ($item['status'] === 'rejeitado') {
        return 'Sua licença médica foi rejeitada.';
    }

    return 'Aguardando visualização do RH.';
}

function mensagemOcorrencia($item) {

    if ($item['status'] === 'aberta') {
        return 'Sua ocorrência foi registrada e está aguardando análise.';
    }

    if ($item['status'] === 'em_analise') {
        return 'Sua ocorrência está em análise pelo RH.';
    }

    if ($item['status'] === 'resolvida') {
        return 'Sua ocorrência foi marcada como resolvida.';
    }

    return 'Status da ocorrência atualizado.';
}

function linkArquivoSolic($arquivo) {

    if (empty($arquivo)) {
        return '';
    }

    $arquivo = str_replace('\\', '/', $arquivo);
    $arquivo = ltrim($arquivo, '/');

    if (substr($arquivo, 0, 3) === '../') {
        $arquivo = substr($arquivo, 3);
    }

    if (substr($arquivo, 0, 2) === './') {
        $arquivo = substr($arquivo, 2);
    }

    /*
        solic.php fica dentro da pasta do colaborador.
        Então arquivos salvos como uploads/... precisam virar ../uploads/...
    */
    if (file_exists(__DIR__ . '/../' . $arquivo)) {
        return '../' . $arquivo;
    }

    if (file_exists(__DIR__ . '/' . $arquivo)) {
        return $arquivo;
    }

    return '../' . $arquivo;
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

<style>
body{
    background:#f4f7fb;
}

.content{
    margin-left:280px;
    min-height:100vh;
    padding:30px;
}

.page-header{
    background:linear-gradient(135deg,#0d6efd,#1e40af);
    color:white;
    border-radius:24px;
    padding:28px;
    box-shadow:0 16px 40px rgba(13,110,253,.22);
}

.page-header h2{
    font-weight:800;
    margin-bottom:6px;
}

.kpi-card{
    border:0;
    border-radius:22px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
}

.kpi-icon{
    width:48px;
    height:48px;
    border-radius:16px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
}

.section-title{
    margin-top:35px;
    margin-bottom:18px;
}

.solic-card{
    border:0;
    border-radius:22px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
    transition:.25s ease;
}

.solic-card:hover{
    transform:translateY(-3px);
}

.solic-icon{
    width:52px;
    height:52px;
    border-radius:18px;
    background:#f1f5f9;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:23px;
    flex-shrink:0;
}

.solic-msg{
    background:#f8fafc;
    border-radius:16px;
    padding:14px;
    color:#475569;
}

.empty-section{
    background:white;
    border-radius:22px;
    padding:28px;
    text-align:center;
    color:#64748b;
    box-shadow:0 10px 30px rgba(15,23,42,.06);
}

.empty-box{
    background:white;
    border-radius:24px;
    padding:50px;
    text-align:center;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
}

@media(max-width:991px){
    .content{
        margin-left:0;
        padding:20px;
    }
}
</style>

</head>

<body>

<?php include 'sidebarfunc.php'; ?>

<div class="content">

<div class="container-fluid">

    <div class="page-header mb-4">

        <h2>
            Minhas Solicitações
        </h2>

        <p class="mb-0">
            Acompanhe separadamente suas férias, atestados e ocorrências.
        </p>

    </div>

    <!-- CARDS RESUMO -->
    <div class="row g-4 mb-4">

        <div class="col-12 col-md-3">
            <div class="card kpi-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Total</p>
                        <h3 class="fw-bold mb-0"><?= $totalSolicitacoes ?></h3>
                    </div>

                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card kpi-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Férias</p>
                        <h3 class="fw-bold mb-0"><?= $totalFerias ?></h3>
                    </div>

                    <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-umbrella-beach"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card kpi-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Atestados</p>
                        <h3 class="fw-bold mb-0"><?= $totalLicencas ?></h3>
                    </div>

                    <div class="kpi-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fa-solid fa-file-medical"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card kpi-card">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Ocorrências</p>
                        <h3 class="fw-bold mb-0"><?= $totalOcorrencias ?></h3>
                    </div>

                    <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- SEÇÃO FÉRIAS -->
    <section class="mb-5">

        <div class="section-title d-flex align-items-center justify-content-between flex-wrap gap-2">

            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fa-solid fa-umbrella-beach text-primary me-2"></i>
                    Férias
                </h3>

                <p class="text-muted mb-0">
                    Acompanhe suas solicitações de férias.
                </p>
            </div>

            <span class="badge bg-primary rounded-pill px-3 py-2">
                <?= $totalFerias ?> solicitações
            </span>

        </div>

        <?php if(count($feriasLista) > 0): ?>

            <div class="row g-4">

                <?php foreach($feriasLista as $item): ?>

                    <div class="col-12">

                        <div class="card solic-card">

                            <div class="card-body p-4">

                                <div class="d-flex gap-3">

                                    <div class="solic-icon">
                                        <i class="fa-solid fa-umbrella-beach text-primary"></i>
                                    </div>

                                    <div class="flex-grow-1">

                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">

                                            <h5 class="fw-bold mb-0">
                                                Solicitação de férias
                                            </h5>

                                            <span class="badge <?= badgeStatusSolic($item['status']) ?> rounded-pill px-3 py-2">
                                                <?= textoStatusSolic($item['status']) ?>
                                            </span>

                                        </div>

                                        <p class="mb-1 text-muted">
                                            <i class="fa-solid fa-calendar-days me-1"></i>
                                            <?= date('d/m/Y', strtotime($item['data_inicio'])) ?>
                                            até
                                            <?= date('d/m/Y', strtotime($item['data_fim'])) ?>

                                            <?php if(!empty($item['dias'])): ?>
                                                —
                                                <?= (int)$item['dias'] ?> dias
                                            <?php endif; ?>
                                        </p>

                                        <p class="mb-2 text-muted">
                                            <i class="fa-solid fa-clock me-1"></i>
                                            Enviado em:
                                            <?= date('d/m/Y H:i', strtotime($item['data_solicitacao'])) ?>
                                        </p>

                                        <div class="solic-msg mt-3">
                                            <strong>Atualização:</strong><br>
                                            <?= nl2br(htmlspecialchars(mensagemFerias($item))) ?>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-section">
                <i class="fa-solid fa-umbrella-beach fa-2x mb-2"></i>
                <p class="mb-0">
                    Nenhuma solicitação de férias encontrada.
                </p>
            </div>

        <?php endif; ?>

    </section>

    <!-- SEÇÃO ATESTADOS -->
    <section class="mb-5">

        <div class="section-title d-flex align-items-center justify-content-between flex-wrap gap-2">

            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fa-solid fa-file-medical text-danger me-2"></i>
                    Atestados e Licenças Médicas
                </h3>

                <p class="text-muted mb-0">
                    Acompanhe seus atestados enviados para o RH.
                </p>
            </div>

            <span class="badge bg-danger rounded-pill px-3 py-2">
                <?= $totalLicencas ?> atestados
            </span>

        </div>

        <?php if(count($licencasLista) > 0): ?>

            <div class="row g-4">

                <?php foreach($licencasLista as $item): ?>

                    <?php
                        $linkArquivo = linkArquivoSolic($item['arquivo_atestado'] ?? '');
                    ?>

                    <div class="col-12">

                        <div class="card solic-card">

                            <div class="card-body p-4">

                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">

                                    <div class="d-flex gap-3">

                                        <div class="solic-icon">
                                            <i class="fa-solid fa-file-medical text-danger"></i>
                                        </div>

                                        <div>

                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">

                                                <h5 class="fw-bold mb-0">
                                                    Licença Médica
                                                </h5>

                                                <span class="badge <?= badgeStatusSolic($item['status']) ?> rounded-pill px-3 py-2">
                                                    <?= textoStatusSolic($item['status']) ?>
                                                </span>

                                            </div>

                                            <?php if(!empty($item['motivo'])): ?>
                                                <p class="mb-1">
                                                    <strong>Motivo:</strong>
                                                    <?= htmlspecialchars($item['motivo']) ?>
                                                </p>
                                            <?php endif; ?>

                                            <p class="mb-1 text-muted">
                                                <i class="fa-solid fa-calendar-days me-1"></i>
                                                <?= date('d/m/Y', strtotime($item['data_inicio'])) ?>
                                                até
                                                <?= date('d/m/Y', strtotime($item['data_fim'])) ?>

                                                <?php if(!empty($item['dias'])): ?>
                                                    —
                                                    <?= (int)$item['dias'] ?> dias
                                                <?php endif; ?>
                                            </p>

                                            <p class="mb-2 text-muted">
                                                <i class="fa-solid fa-clock me-1"></i>
                                                Enviado em:
                                                <?= date('d/m/Y H:i', strtotime($item['data_envio'])) ?>
                                            </p>

                                        </div>

                                    </div>

                                    <div class="text-lg-end">

                                        <?php if(!empty($linkArquivo)): ?>
                                            <a
                                                href="<?= htmlspecialchars($linkArquivo) ?>"
                                                target="_blank"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 mb-2"
                                            >
                                                <i class="fa-solid fa-eye me-1"></i>
                                                Ver atestado
                                            </a>
                                        <?php endif; ?>

                                    </div>

                                </div>

                                <div class="solic-msg mt-3">

                                    <strong>Atualização:</strong><br>
                                    <?= nl2br(htmlspecialchars(mensagemLicenca($item))) ?>

                                    <?php if(!empty($item['observacao'])): ?>
                                        <hr>
                                        <strong>Observação:</strong><br>
                                        <?= nl2br(htmlspecialchars($item['observacao'])) ?>
                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-section">
                <i class="fa-solid fa-file-medical fa-2x mb-2"></i>
                <p class="mb-0">
                    Nenhum atestado enviado até o momento.
                </p>
            </div>

        <?php endif; ?>

    </section>

    <!-- SEÇÃO OCORRÊNCIAS -->
    <section class="mb-5">

        <div class="section-title d-flex align-items-center justify-content-between flex-wrap gap-2">

            <div>
                <h3 class="fw-bold mb-1">
                    <i class="fa-solid fa-shield-halved text-warning me-2"></i>
                    Ocorrências
                </h3>

                <p class="text-muted mb-0">
                    Acompanhe o status das ocorrências enviadas por você.
                </p>
            </div>

            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                <?= $totalOcorrencias ?> ocorrências
            </span>

        </div>

        <?php if(count($ocorrenciasLista) > 0): ?>

            <div class="row g-4">

                <?php foreach($ocorrenciasLista as $item): ?>

                    <?php
                        $linkArquivo = linkArquivoSolic($item['evidencia'] ?? '');
                    ?>

                    <div class="col-12">

                        <div class="card solic-card">

                            <div class="card-body p-4">

                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">

                                    <div class="d-flex gap-3">

                                        <div class="solic-icon">
                                            <i class="fa-solid fa-shield-halved text-warning"></i>
                                        </div>

                                        <div>

                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">

                                                <h5 class="fw-bold mb-0">
                                                    Ocorrência
                                                </h5>

                                                <span class="badge <?= badgeStatusSolic($item['status']) ?> rounded-pill px-3 py-2">
                                                    <?= textoStatusSolic($item['status']) ?>
                                                </span>

                                            </div>

                                            <?php if(!empty($item['categoria'])): ?>
                                                <p class="mb-1">
                                                    <strong>Categoria:</strong>
                                                    <?= htmlspecialchars($item['categoria']) ?>
                                                </p>
                                            <?php endif; ?>

                                            <p class="mb-1 text-muted">
                                                <i class="fa-solid fa-location-dot me-1"></i>
                                                <?= htmlspecialchars($item['andar']) ?>

                                                <?php if(!empty($item['sala'])): ?>
                                                    —
                                                    Sala <?= htmlspecialchars($item['sala']) ?>
                                                <?php endif; ?>
                                            </p>

                                            <p class="mb-2 text-muted">
                                                <i class="fa-solid fa-clock me-1"></i>
                                                Enviado em:
                                                <?= date('d/m/Y H:i', strtotime($item['data_ocorrencia'])) ?>
                                            </p>

                                        </div>

                                    </div>

                                    <div class="text-lg-end">

                                        <?php if(!empty($linkArquivo)): ?>
                                            <a
                                                href="<?= htmlspecialchars($linkArquivo) ?>"
                                                target="_blank"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3 mb-2"
                                            >
                                                <i class="fa-solid fa-eye me-1"></i>
                                                Ver evidência
                                            </a>
                                        <?php endif; ?>

                                    </div>

                                </div>

                                <div class="solic-msg mt-3">

                                    <strong>Atualização:</strong><br>
                                    <?= nl2br(htmlspecialchars(mensagemOcorrencia($item))) ?>

                                    <?php if(!empty($item['descricao'])): ?>
                                        <hr>
                                        <strong>Descrição:</strong><br>
                                        <?= nl2br(htmlspecialchars($item['descricao'])) ?>
                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-section">
                <i class="fa-solid fa-shield-halved fa-2x mb-2"></i>
                <p class="mb-0">
                    Nenhuma ocorrência enviada por você até o momento.
                </p>
            </div>

        <?php endif; ?>

    </section>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/theme.js"></script>
<script src="../js/translate.js"></script>
</body>
</html>