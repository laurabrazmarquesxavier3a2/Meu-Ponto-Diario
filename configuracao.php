<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_empresa = $_SESSION['id_empresa'] ?? 0;

$mensagem = '';
$erro = '';

if (!$id_usuario || !$id_empresa) {
    die("Usuário não autenticado.");
}

$con->query("
    CREATE TABLE IF NOT EXISTS config_notificacoes (
        id_config INT NOT NULL AUTO_INCREMENT,
        id_usuario INT NOT NULL,
        id_empresa INT NOT NULL,
        novas_solicitacoes TINYINT(1) NOT NULL DEFAULT 1,
        aprovacoes_pendentes TINYINT(1) NOT NULL DEFAULT 1,
        alertas_emergencia TINYINT(1) NOT NULL DEFAULT 1,
        resumo_semanal TINYINT(1) NOT NULL DEFAULT 0,
        data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id_config),
        KEY idx_usuario_empresa (id_usuario, id_empresa)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$stmtUsuario = $con->prepare("
    SELECT 
        id_usuario,
        nome,
        email,
        senha,
        tipo,
        ultimo_login
    FROM usuarios
    WHERE id_usuario = ?
    AND id_empresa = ?
    LIMIT 1
");

if (!$stmtUsuario) {
    die("Erro SQL usuário: " . $con->error);
}

$stmtUsuario->bind_param("ii", $id_usuario, $id_empresa);
$stmtUsuario->execute();

$usuario = $stmtUsuario->get_result()->fetch_assoc();

if (!$usuario) {
    die("Usuário não encontrado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_senha'])) {

    $senhaAtual = $_POST['senha_atual'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if ($senhaAtual == '' || $novaSenha == '' || $confirmarSenha == '') {

        $erro = "Preencha todos os campos de senha.";

    } elseif (!password_verify($senhaAtual, $usuario['senha'])) {

        $erro = "Senha atual incorreta.";

    } elseif ($novaSenha !== $confirmarSenha) {

        $erro = "A nova senha e a confirmação não coincidem.";

    } elseif (strlen($novaSenha) < 6) {

        $erro = "A nova senha deve ter pelo menos 6 caracteres.";

    } else {

        $novaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $stmtSenha = $con->prepare("
            UPDATE usuarios
            SET senha = ?
            WHERE id_usuario = ?
            AND id_empresa = ?
        ");

        if (!$stmtSenha) {
            $erro = "Erro SQL senha: " . $con->error;
        } else {

            $stmtSenha->bind_param("sii", $novaHash, $id_usuario, $id_empresa);

            if ($stmtSenha->execute()) {
                $mensagem = "Senha alterada com sucesso.";
            } else {
                $erro = "Erro ao alterar senha.";
            }
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_configuracoes'])) {

    $novasSolicitacoes = isset($_POST['novas_solicitacoes']) ? 1 : 0;
    $aprovacoesPendentes = isset($_POST['aprovacoes_pendentes']) ? 1 : 0;
    $alertasEmergencia = isset($_POST['alertas_emergencia']) ? 1 : 0;
    $resumoSemanal = isset($_POST['resumo_semanal']) ? 1 : 0;

    $stmtExiste = $con->prepare("
        SELECT id_config
        FROM config_notificacoes
        WHERE id_usuario = ?
        AND id_empresa = ?
        LIMIT 1
    ");

    if (!$stmtExiste) {
        die("Erro SQL ao buscar configuração: " . $con->error);
    }

    $stmtExiste->bind_param("ii", $id_usuario, $id_empresa);
    $stmtExiste->execute();

    $configExistente = $stmtExiste->get_result()->fetch_assoc();

    if ($configExistente) {

        $idConfig = (int)$configExistente['id_config'];

        $stmtConfig = $con->prepare("
            UPDATE config_notificacoes
            SET
                novas_solicitacoes = ?,
                aprovacoes_pendentes = ?,
                alertas_emergencia = ?,
                resumo_semanal = ?
            WHERE id_config = ?
            AND id_usuario = ?
            AND id_empresa = ?
        ");

        if (!$stmtConfig) {
            die("Erro SQL ao atualizar notificações: " . $con->error);
        }

        $stmtConfig->bind_param(
            "iiiiiii",
            $novasSolicitacoes,
            $aprovacoesPendentes,
            $alertasEmergencia,
            $resumoSemanal,
            $idConfig,
            $id_usuario,
            $id_empresa
        );

        $stmtConfig->execute();

    } else {

        $stmtConfig = $con->prepare("
            INSERT INTO config_notificacoes (
                id_usuario,
                id_empresa,
                novas_solicitacoes,
                aprovacoes_pendentes,
                alertas_emergencia,
                resumo_semanal
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        if (!$stmtConfig) {
            die("Erro SQL ao inserir notificações: " . $con->error);
        }

        $stmtConfig->bind_param(
            "iiiiii",
            $id_usuario,
            $id_empresa,
            $novasSolicitacoes,
            $aprovacoesPendentes,
            $alertasEmergencia,
            $resumoSemanal
        );

        $stmtConfig->execute();
    }

    $_SESSION['config_sistema'] = [
        'fuso' => $_POST['fuso'] ?? 'America/Sao_Paulo',
        'formato_data' => $_POST['formato_data'] ?? 'DD/MM/YYYY',
        'idioma' => $_POST['idioma'] ?? 'pt-BR'
    ];

    $_SESSION['idioma'] = $_POST['idioma'] ?? 'pt-BR';

    $_SESSION['config_jornada'] = [
        'nome_jornada' => trim($_POST['nome_jornada'] ?? ''),
        'hora_entrada' => trim($_POST['hora_entrada'] ?? ''),
        'hora_saida' => trim($_POST['hora_saida'] ?? ''),
        'carga_horaria' => trim($_POST['carga_horaria'] ?? '')
    ];

    $mensagem = "Configurações salvas com sucesso.";
}

$configNotificacoes = [
    'novas_solicitacoes' => 1,
    'aprovacoes_pendentes' => 1,
    'alertas_emergencia' => 1,
    'resumo_semanal' => 0
];

$stmtBuscaConfig = $con->prepare("
    SELECT
        novas_solicitacoes,
        aprovacoes_pendentes,
        alertas_emergencia,
        resumo_semanal
    FROM config_notificacoes
    WHERE id_usuario = ?
    AND id_empresa = ?
    LIMIT 1
");

if ($stmtBuscaConfig) {

    $stmtBuscaConfig->bind_param("ii", $id_usuario, $id_empresa);
    $stmtBuscaConfig->execute();

    $resConfig = $stmtBuscaConfig->get_result();

    if ($resConfig->num_rows > 0) {
        $configNotificacoes = $resConfig->fetch_assoc();
    }
}

$configSistema = $_SESSION['config_sistema'] ?? [
    'fuso' => 'America/Sao_Paulo',
    'formato_data' => 'DD/MM/YYYY',
    'idioma' => $_SESSION['idioma'] ?? 'pt-BR'
];

$configJornada = $_SESSION['config_jornada'] ?? [
    'nome_jornada' => '',
    'hora_entrada' => '',
    'hora_saida' => '',
    'carga_horaria' => ''
];

$sessaoAtual = session_id();
$pagina = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Configurações RH</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="css/style.css">

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
    border-radius:26px;
    padding:30px;
    box-shadow:0 16px 40px rgba(13,110,253,.22);
}

.page-header h1{
    font-weight:800;
    margin-bottom:6px;
}

.card-config{
    border:0;
    border-radius:24px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
    background:white;
}

.config-item{
    transition:.25s;
    border-radius:16px;
}

.config-item:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(15,23,42,.08);
}

.password-box{
    position:relative;
}

.password-box input{
    padding-right:48px;
}

.password-box i{
    position:absolute;
    right:16px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:#0d6efd;
    font-size:18px;
}

.config-link{
    cursor:pointer;
    text-decoration:none;
    color:inherit;
    display:block;
}

.config-link:hover{
    color:inherit;
}

.profile-mini{
    display:flex;
    align-items:center;
    gap:14px;
}

.profile-avatar{
    width:56px;
    height:56px;
    border-radius:18px;
    background:#0d6efd;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    font-weight:800;
}

/* DARK MODE */
body.dark-mode,
html.dark-mode{
    background:#07111f !important;
    color:#f8fafc !important;
}

body.dark-mode .content,
body.dark-mode .container-fluid{
    background:#07111f !important;
    color:#f8fafc !important;
}

body.dark-mode .card,
body.dark-mode .card-config,
body.dark-mode .modal-content,
body.dark-mode .config-item{
    background:#0f172a !important;
    color:#f8fafc !important;
    border-color:rgba(255,255,255,.12) !important;
}

body.dark-mode .text-muted,
body.dark-mode small,
body.dark-mode .form-text{
    color:#cbd5e1 !important;
}

body.dark-mode .form-control,
body.dark-mode .form-select{
    background:#111827 !important;
    color:#f8fafc !important;
    border-color:#334155 !important;
}

body.dark-mode .border,
body.dark-mode .rounded{
    border-color:#334155 !important;
}

body.dark-mode .bg-light{
    background:#111827 !important;
}

body.dark-mode .btn-outline-dark{
    color:#f8fafc !important;
    border-color:#f8fafc !important;
}

body.dark-mode .btn-outline-secondary{
    color:#e5e7eb !important;
    border-color:#64748b !important;
}

@media(max-width:991px){
    .content{
        margin-left:0;
        padding:20px;
    }
}
body.dark-mode .form-check-input{
    background-color:#111827 !important;
    border:2px solid #64748b !important;
    box-shadow:none !important;
}

body.dark-mode .form-check-input:checked{
    background-color:#0d6efd !important;
    border-color:#0d6efd !important;
}

body.dark-mode .form-check-input:checked[type="checkbox"]{
    background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m6 10 3 3 6-6'/%3e%3c/svg%3e") !important;
}

body.dark-mode .form-check-input:focus{
    border-color:#60a5fa !important;
    box-shadow:0 0 0 .25rem rgba(13,110,253,.25) !important;
}
</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid">

        <div class="page-header mb-4">

            <h1>
                Configurações
            </h1>

            <p class="mb-0">
                Gerencie notificações, segurança, aparência e preferências do sistema RH.
            </p>

        </div>

        <?php if($mensagem): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-4">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($mensagem) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($erro) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="row g-4">

                <!-- CONTA RH -->
                <div class="col-md-6">

                    <div class="card card-config p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-person-circle text-primary me-2"></i>
                            Conta RH
                        </h5>

                        <div class="profile-mini mb-4">

                            <div class="profile-avatar">
                                <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                            </div>

                            <div>
                                <strong>
                                    <?= htmlspecialchars($usuario['nome']) ?>
                                </strong><br>

                                <small class="text-muted">
                                    <?= htmlspecialchars($usuario['email']) ?>
                                </small>
                            </div>

                        </div>

                        <div class="border rounded p-3 mb-3 config-item">
                            <strong>
                                <i class="bi bi-person-badge text-primary me-2"></i>
                                Tipo de acesso
                            </strong><br>
                            <small class="text-muted">
                                <?= htmlspecialchars($usuario['tipo']) ?>
                            </small>
                        </div>

                        <div class="border rounded p-3 mb-3 config-item">
                            <strong>
                                <i class="bi bi-building text-primary me-2"></i>
                                Empresa
                            </strong><br>
                            <small class="text-muted">
                                ID da empresa: <?= htmlspecialchars($id_empresa) ?>
                            </small>
                        </div>

                        <div class="border rounded p-3 config-item">
                            <strong>
                                <i class="bi bi-person-check text-primary me-2"></i>
                                Usuário logado
                            </strong><br>
                            <small class="text-muted">
                                <?= htmlspecialchars($usuario['nome']) ?> · <?= htmlspecialchars($usuario['email']) ?>
                            </small>
                        </div>

                    </div>

                </div>

                <!-- SEGURANÇA -->
                <div class="col-md-6">

                    <div class="card card-config p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-lock text-primary me-2"></i>
                            Segurança
                        </h5>

                        <div
                            class="border rounded p-3 mb-3 config-item"
                            data-bs-toggle="modal"
                            data-bs-target="#modalAlterarSenha"
                            style="cursor:pointer;"
                        >
                            <strong>
                                <i class="bi bi-key-fill text-primary me-2"></i>
                                Alterar senha
                            </strong><br>
                            <small class="text-muted">
                                Atualizar sua senha de acesso.
                            </small>
                        </div>

                        <div class="border rounded p-3 mb-3 config-item">
                            <strong>
                                <i class="bi bi-pc-display text-primary me-2"></i>
                                Sessão atual
                            </strong><br>
                            <small class="text-muted">
                                ID da sessão: <?= htmlspecialchars(substr($sessaoAtual, 0, 12)) ?>...
                            </small>
                        </div>

                        <div class="border rounded p-3 config-item">
                            <strong>
                                <i class="bi bi-clock-history text-primary me-2"></i>
                                Último acesso
                            </strong><br>
                            <small class="text-muted">
                                <?php if(!empty($usuario['ultimo_login'])): ?>
                                    <?= date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) ?>
                                <?php else: ?>
                                    Nenhum registro encontrado
                                <?php endif; ?>
                            </small>
                        </div>

                    </div>

                </div>

                <!-- NOTIFICAÇÕES -->
                <div class="col-md-6">

                    <div class="card card-config p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-bell text-primary me-2"></i>
                            Notificações do RH
                        </h5>

                        <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-3 config-item">
                            <div>
                                <strong>Novas solicitações</strong><br>
                                <small class="text-muted">
                                    Receber avisos de férias, atestados e pedidos enviados.
                                </small>
                            </div>

                            <input
                                type="checkbox"
                                name="novas_solicitacoes"
                                class="form-check-input"
                                <?= !empty($configNotificacoes['novas_solicitacoes']) ? 'checked' : '' ?>
                            >
                        </div>

                        <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-3 config-item">
                            <div>
                                <strong>Aprovações pendentes</strong><br>
                                <small class="text-muted">
                                    Lembretes sobre solicitações aguardando análise.
                                </small>
                            </div>

                            <input
                                type="checkbox"
                                name="aprovacoes_pendentes"
                                class="form-check-input"
                                <?= !empty($configNotificacoes['aprovacoes_pendentes']) ? 'checked' : '' ?>
                            >
                        </div>

                        <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-3 config-item">
                            <div>
                                <strong>Alertas de emergência</strong><br>
                                <small class="text-muted">
                                    Receber notificações urgentes de ocorrências.
                                </small>
                            </div>

                            <input
                                type="checkbox"
                                name="alertas_emergencia"
                                class="form-check-input"
                                <?= !empty($configNotificacoes['alertas_emergencia']) ? 'checked' : '' ?>
                            >
                        </div>

                        <div class="d-flex justify-content-between align-items-center border rounded p-3 config-item">
                            <div>
                                <strong>Resumo semanal</strong><br>
                                <small class="text-muted">
                                    Receber resumo semanal de atividades.
                                </small>
                            </div>

                            <input
                                type="checkbox"
                                name="resumo_semanal"
                                class="form-check-input"
                                <?= !empty($configNotificacoes['resumo_semanal']) ? 'checked' : '' ?>
                            >
                        </div>

                    </div>

                </div>

                <!-- SISTEMA -->
                <div class="col-md-6">

                    <div class="card card-config p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-gear text-primary me-2"></i>
                            Sistema
                        </h5>

                        <label class="fw-semibold mb-1">
                            Fuso Horário
                        </label>

                        <select name="fuso" class="form-select mb-3">

                            <option value="America/Sao_Paulo" <?= $configSistema['fuso'] == 'America/Sao_Paulo' ? 'selected' : '' ?>>
                                América/São_Paulo (UTC-03:00)
                            </option>

                            <option value="America/Manaus" <?= $configSistema['fuso'] == 'America/Manaus' ? 'selected' : '' ?>>
                                América/Manaus (UTC-04:00)
                            </option>

                            <option value="America/Rio_Branco" <?= $configSistema['fuso'] == 'America/Rio_Branco' ? 'selected' : '' ?>>
                                América/Rio Branco (UTC-05:00)
                            </option>

                        </select>

                        <label class="fw-semibold mb-1">
                            Formato de Data
                        </label>

                        <select name="formato_data" class="form-select mb-3">

                            <option value="DD/MM/YYYY" <?= $configSistema['formato_data'] == 'DD/MM/YYYY' ? 'selected' : '' ?>>
                                DD/MM/YYYY
                            </option>

                            <option value="YYYY-MM-DD" <?= $configSistema['formato_data'] == 'YYYY-MM-DD' ? 'selected' : '' ?>>
                                YYYY-MM-DD
                            </option>

                        </select>

                        <label class="fw-semibold mb-1">
                            Idioma
                        </label>

                        <select name="idioma" id="idiomaSistema" class="form-select">

                            <option value="pt-BR" <?= $configSistema['idioma'] == 'pt-BR' ? 'selected' : '' ?>>
                                Português (Brasil)
                            </option>

                            <option value="en" <?= $configSistema['idioma'] == 'en' ? 'selected' : '' ?>>
                                English
                            </option>

                        </select>

                        <small class="text-muted mt-3 d-block">
                            As opções de sistema são salvas ao clicar em “Salvar Alterações”.
                        </small>

                    </div>

                </div>

                <!-- APARÊNCIA -->
                <div class="col-md-6">

                    <div class="card card-config p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-palette text-primary me-2"></i>
                            Aparência
                        </h5>

                        <label class="fw-semibold mb-2">
                            Tema
                        </label>

                        <div class="d-flex gap-2 mb-3">

                            <button type="button" id="lightMode" class="btn btn-primary w-100">
                                <i class="bi bi-sun-fill me-1"></i>
                                Claro
                            </button>

                            <button type="button" id="darkMode" class="btn btn-outline-dark w-100">
                                <i class="bi bi-moon-stars-fill me-1"></i>
                                Escuro
                            </button>

                        </div>

                        <div class="border rounded p-3 config-item">
                            <strong>
                                <i class="bi bi-browser-chrome text-primary me-2"></i>
                                Tema salvo no navegador
                            </strong><br>
                            <small class="text-muted">
                                O tema permanece nas próximas páginas do sistema.
                            </small>
                        </div>

                    </div>

                </div>

                <!-- ADMINISTRAÇÃO RH -->
                <div class="col-md-6">

                    <div class="card card-config p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-shield-lock text-primary me-2"></i>
                            Administração RH
                        </h5>

                        <a href="funcionarios.php" class="config-link">
                            <div class="border rounded p-3 mb-3 config-item">
                                <strong>
                                    <i class="bi bi-people-fill text-primary me-2"></i>
                                    Gerenciar funcionários
                                </strong><br>
                                <small class="text-muted">
                                    Cadastrar, editar e consultar colaboradores.
                                </small>
                            </div>
                        </a>

                        <a href="perfil.php" class="config-link">
                            <div class="border rounded p-3 mb-3 config-item">
                                <strong>
                                    <i class="bi bi-clipboard-data text-primary me-2"></i>
                                    Auditoria de ações
                                </strong><br>
                                <small class="text-muted">
                                    Consultar atividades recentes registradas no sistema.
                                </small>
                            </div>
                        </a>

                        <div
                            class="border rounded p-3 config-item"
                            onclick="abrirJornada()"
                            style="cursor:pointer;"
                        >
                            <strong>
                                <i class="bi bi-clock-history text-primary me-2"></i>
                                Jornada de trabalho
                            </strong><br>
                            <small class="text-muted">
                                Configurar entrada, saída e carga horária padrão.
                            </small>
                        </div>

                    </div>

                </div>

                <!-- JORNADA DE TRABALHO -->
                <div id="jornadaContainer" class="col-12" style="display:none;">

                    <div class="card card-config p-4">

                        <h4 class="fw-bold mb-4">
                            <i class="bi bi-clock-history text-primary me-2"></i>
                            Jornada de Trabalho
                        </h4>

                        <div class="row">

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">
                                    Nome da Jornada
                                </label>

                                <input
                                    type="text"
                                    name="nome_jornada"
                                    class="form-control"
                                    placeholder="Administrativo"
                                    value="<?= htmlspecialchars($configJornada['nome_jornada']) ?>"
                                >
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">
                                    Hora Entrada
                                </label>

                                <input
                                    type="time"
                                    name="hora_entrada"
                                    class="form-control"
                                    value="<?= htmlspecialchars($configJornada['hora_entrada']) ?>"
                                >
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">
                                    Hora Saída
                                </label>

                                <input
                                    type="time"
                                    name="hora_saida"
                                    class="form-control"
                                    value="<?= htmlspecialchars($configJornada['hora_saida']) ?>"
                                >
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-semibold">
                                    Carga Horária
                                </label>

                                <input
                                    type="number"
                                    name="carga_horaria"
                                    class="form-control"
                                    placeholder="8"
                                    value="<?= htmlspecialchars($configJornada['carga_horaria']) ?>"
                                >
                            </div>

                        </div>

                        <div class="alert alert-info mb-0 rounded-4">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            A jornada será salva junto com as demais configurações ao clicar em “Salvar Alterações”.
                        </div>

                    </div>

                </div>

            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">

                <a href="ponto.php" class="btn btn-outline-secondary">
                    Cancelar
                </a>

                <button type="submit" name="salvar_configuracoes" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>
                    Salvar Alterações
                </button>

            </div>

        </form>

    </div>

</div>

<!-- MODAL ALTERAR SENHA -->
<div class="modal fade" id="modalAlterarSenha" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title fw-bold">
                    <i class="bi bi-key-fill me-2"></i>
                    Alterar senha
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

            </div>

            <form method="POST">

                <div class="modal-body">

                    <label class="form-label fw-bold">
                        Senha atual
                    </label>

                    <div class="password-box mb-3">
                        <input type="password" name="senha_atual" id="senhaAtual" class="form-control" required>
                        <i class="bi bi-eye-fill" onclick="toggleSenha('senhaAtual', this)"></i>
                    </div>

                    <label class="form-label fw-bold">
                        Nova senha
                    </label>

                    <div class="password-box mb-3">
                        <input type="password" name="nova_senha" id="novaSenha" class="form-control" required>
                        <i class="bi bi-eye-fill" onclick="toggleSenha('novaSenha', this)"></i>
                    </div>

                    <label class="form-label fw-bold">
                        Confirmar nova senha
                    </label>

                    <div class="password-box">
                        <input type="password" name="confirmar_senha" id="confirmarSenha" class="form-control" required>
                        <i class="bi bi-eye-fill" onclick="toggleSenha('confirmarSenha', this)"></i>
                    </div>

                    <div class="alert alert-info mt-3 mb-0 rounded-4">
                        A nova senha deve ter pelo menos 6 caracteres.
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" name="alterar_senha" class="btn btn-primary">
                        Salvar nova senha
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/theme.js"></script>
<script>
function toggleSenha(id, icone){
    const campo = document.getElementById(id);

    if(campo.type === 'password'){
        campo.type = 'text';

        icone.classList.remove('bi-eye-fill');
        icone.classList.add('bi-eye-slash-fill');
    } else {
        campo.type = 'password';

        icone.classList.remove('bi-eye-slash-fill');
        icone.classList.add('bi-eye-fill');
    }
}

function abrirJornada(){
    const container = document.getElementById('jornadaContainer');

    if(container.style.display === 'none' || container.style.display === ''){
        container.style.display = 'block';
        localStorage.setItem('jornadaAberta', 'sim');
    } else {
        container.style.display = 'none';
        localStorage.setItem('jornadaAberta', 'nao');
    }
}

const idiomaSistema = document.getElementById('idiomaSistema');

if(idiomaSistema){

    idiomaSistema.addEventListener('change', function(){

        localStorage.setItem('idiomaSistema', this.value);
        localStorage.setItem('idioma', this.value);
    });
}

if(localStorage.getItem('jornadaAberta') === 'sim'){
    const jornadaContainer = document.getElementById('jornadaContainer');

    if(jornadaContainer){
        jornadaContainer.style.display = 'block';
    }
}
</script>
</body>
</html>
