<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../auth.php';
require_once '../config/database.php';
require_once '../lang.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_empresa = $_SESSION['id_empresa'] ?? 0;

$mensagem = '';
$erro = '';

if (!$id_usuario || !$id_empresa) {
    die("Usuário não autenticado.");
}

/*BUSCAR USUÁRIO*/
$stmtUsuario = $con->prepare("
    SELECT 
        id_usuario,
        id_funcionario,
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

$id_funcionario = $usuario['id_funcionario'] ?? 0;

$funcionario = null;

if ($id_funcionario) {

    $stmtFunc = $con->prepare("
        SELECT 
            nome,
            cargo,
            departamento,
            escala,
            supervisor
        FROM funcionarios
        WHERE id_funcionario = ?
        AND id_empresa = ?
        LIMIT 1
    ");

    if ($stmtFunc) {
        $stmtFunc->bind_param("ii", $id_funcionario, $id_empresa);
        $stmtFunc->execute();

        $funcionario = $stmtFunc->get_result()->fetch_assoc();
    }
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

    $_SESSION['config_sistema_func'] = [
        'fuso' => $_POST['fuso'] ?? 'America/Sao_Paulo',
        'formato_data' => $_POST['formato_data'] ?? 'DD/MM/YYYY',
        'idioma' => $_POST['idioma'] ?? 'pt-BR'
    ];

    $_SESSION['idioma'] = $_POST['idioma'] ?? 'pt-BR';

    $mensagem = "Preferências salvas com sucesso.";
}

$configSistema = $_SESSION['config_sistema_func'] ?? [
    'fuso' => 'America/Sao_Paulo',
    'formato_data' => 'DD/MM/YYYY',
    'idioma' => $_SESSION['idioma'] ?? 'pt-BR'
];

$sessaoAtual = session_id();
$pagina = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Configurações</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

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

<?php include 'sidebarfunc.php'; ?>

<div class="content">

    <div class="container-fluid">

        <div class="page-header mb-4">

            <h1>
                Configurações
            </h1>

            <p class="mb-0">
                Gerencie sua conta, segurança, aparência e preferências pessoais.
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

                <!-- MINHA CONTA -->
                <div class="col-md-6">

                    <div class="card card-config p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-person-circle text-primary me-2"></i>
                            Minha conta
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
                                <i class="bi bi-briefcase text-primary me-2"></i>
                                Cargo
                            </strong><br>
                            <small class="text-muted">
                                <?= htmlspecialchars($funcionario['cargo'] ?? 'Não informado') ?>
                            </small>
                        </div>

                        <div class="border rounded p-3 mb-3 config-item">
                            <strong>
                                <i class="bi bi-diagram-3 text-primary me-2"></i>
                                Departamento
                            </strong><br>
                            <small class="text-muted">
                                <?= htmlspecialchars($funcionario['departamento'] ?? 'Não informado') ?>
                            </small>
                        </div>

                        <div class="border rounded p-3 config-item">
                            <strong>
                                <i class="bi bi-person-badge text-primary me-2"></i>
                                Tipo de acesso
                            </strong><br>
                            <small class="text-muted">
                                Colaborador
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
                                Atualize sua senha de acesso.
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

                <!-- PREFERÊNCIAS -->
                <div class="col-md-6">

                    <div class="card card-config p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-gear text-primary me-2"></i>
                            Preferências
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
                            Essas opções são pessoais e não afetam outros colaboradores.
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
                                O tema permanece nas próximas páginas do colaborador.
                            </small>
                        </div>

                    </div>

                </div>

            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">

                <a href="perfil.php" class="btn btn-outline-secondary">
                    Cancelar
                </a>

                <button type="submit" name="salvar_configuracoes" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>
                    Salvar Preferências
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

                    <label class="form-label fw-bold">Senha atual</label>

                    <div class="password-box mb-3">
                        <input type="password" name="senha_atual" id="senhaAtual" class="form-control" required>
                        <i class="bi bi-eye-fill" onclick="toggleSenha('senhaAtual', this)"></i>
                    </div>

                    <label class="form-label fw-bold">Nova senha</label>

                    <div class="password-box mb-3">
                        <input type="password" name="nova_senha" id="novaSenha" class="form-control" required>
                        <i class="bi bi-eye-fill" onclick="toggleSenha('novaSenha', this)"></i>
                    </div>

                    <label class="form-label fw-bold">Confirmar nova senha</label>

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

<script src="../js/theme.js"></script>

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

const idiomaSistema = document.getElementById('idiomaSistema');

if(idiomaSistema){
    idiomaSistema.addEventListener('change', function(){
        localStorage.setItem('idiomaSistema', this.value);
        localStorage.setItem('idioma', this.value);
    });
}
</script>

<script src="../js/theme.js"></script>
<script src="../js/translate.js"></script>

</body>
</html>