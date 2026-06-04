
<?php
require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';


$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_empresa = $_SESSION['id_empresa'] ?? 0;

$mensagem = '';
$erro = '';

if (!$id_usuario || !$id_empresa) {
    die("Usuário não autenticado.");
}

/* BUSCA USUÁRIO */
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

$stmtUsuario->bind_param("ii", $id_usuario, $id_empresa);
$stmtUsuario->execute();

$usuario = $stmtUsuario->get_result()->fetch_assoc();

if (!$usuario) {
    die("Usuário não encontrado.");
}

/* ALTERAR SENHA */
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

        $stmtSenha->bind_param("sii", $novaHash, $id_usuario, $id_empresa);

        if ($stmtSenha->execute()) {
            $mensagem = "Senha alterada com sucesso.";
        } else {
            $erro = "Erro ao alterar senha.";
        }
    }
}

/* SALVAR CONFIGURAÇÕES VISUAIS/SISTEMA EM SESSÃO */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_configuracoes'])) {

    $_SESSION['config_notificacoes'] = [
        'novas_solicitacoes' => isset($_POST['novas_solicitacoes']),
        'aprovacoes_pendentes' => isset($_POST['aprovacoes_pendentes']),
        'alertas_emergencia' => isset($_POST['alertas_emergencia']),
        'resumo_semanal' => isset($_POST['resumo_semanal'])
    ];

    $_SESSION['config_sistema'] = [
        'fuso' => $_POST['fuso'] ?? 'America/Sao_Paulo',
        'formato_data' => $_POST['formato_data'] ?? 'DD/MM/YYYY',
        'idioma' => $_POST['idioma'] ?? 'pt-BR'
    ];

    $mensagem = "Configurações salvas com sucesso.";
}

$configNotificacoes = $_SESSION['config_notificacoes'] ?? [
    'novas_solicitacoes' => true,
    'aprovacoes_pendentes' => true,
    'alertas_emergencia' => true,
    'resumo_semanal' => false
];

$configSistema = $_SESSION['config_sistema'] ?? [
    'fuso' => 'America/Sao_Paulo',
    'formato_data' => 'DD/MM/YYYY',
    'idioma' => 'pt-BR'
];

$sessaoAtual = session_id();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Configurações</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
.config-item{
    transition:.25s;
}

.config-item:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.security-action{
    cursor:pointer;
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
</style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid">

        <h1 class="fw-bold">Configurações</h1>
        <h5 class="text-muted mb-4">Gerencie as configurações do sistema RH</h5>

        <?php if($mensagem): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($mensagem) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($erro) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="row g-4">

                <!-- NOTIFICAÇÕES -->
                <div class="col-md-6">
                    <div class="card card-dashboard p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-bell text-primary me-2"></i>
                            Notificações
                        </h5>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <strong>Novas solicitações</strong><br>
                                <small class="text-muted">Receber alertas de novas solicitações</small>
                            </div>
                            <input type="checkbox" name="novas_solicitacoes" class="form-check-input" <?= $configNotificacoes['novas_solicitacoes'] ? 'checked' : '' ?>>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <strong>Aprovações pendentes</strong><br>
                                <small class="text-muted">Lembrete diário de pendências</small>
                            </div>
                            <input type="checkbox" name="aprovacoes_pendentes" class="form-check-input" <?= $configNotificacoes['aprovacoes_pendentes'] ? 'checked' : '' ?>>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <strong>Alertas de emergência</strong><br>
                                <small class="text-muted">Notificações urgentes de riscos</small>
                            </div>
                            <input type="checkbox" name="alertas_emergencia" class="form-check-input" <?= $configNotificacoes['alertas_emergencia'] ? 'checked' : '' ?>>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Resumo semanal</strong><br>
                                <small class="text-muted">Relatório de atividades da semana</small>
                            </div>
                            <input type="checkbox" name="resumo_semanal" class="form-check-input" <?= $configNotificacoes['resumo_semanal'] ? 'checked' : '' ?>>
                        </div>

                    </div>
                </div>

                <!-- SEGURANÇA -->
                <div class="col-md-6">
                    <div class="card card-dashboard p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-lock text-primary me-2"></i>
                            Segurança
                        </h5>

                        <div
                            class="border rounded p-3 mb-3 config-item security-action"
                            data-bs-toggle="modal"
                            data-bs-target="#modalAlterarSenha"
                        >
                            <strong>
                                <i class="bi bi-key-fill text-primary me-2"></i>
                                Alterar senha
                            </strong><br>
                            <small class="text-muted">Atualizar sua senha de acesso</small>
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

                        <div class="border rounded p-3 mb-3 config-item">
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

                <!-- SISTEMA -->
                <div class="col-md-6">
                    <div class="card card-dashboard p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-database text-primary me-2"></i>
                            Sistema
                        </h5>

                        <label class="fw-semibold mb-1">Fuso Horário</label>
                        <select name="fuso" class="form-select mb-3">
                            <option value="America/Sao_Paulo" <?= $configSistema['fuso'] == 'America/Sao_Paulo' ? 'selected' : '' ?>>
                                América/São_Paulo (UTC-03:00)
                            </option>

                            <option value="America/Manaus" <?= $configSistema['fuso'] == 'America/Manaus' ? 'selected' : '' ?>>
                                América/Manaus (UTC-04:00)
                            </option>
                        </select>

                        <label class="fw-semibold mb-1">Formato de Data</label>
                        <select name="formato_data" class="form-select mb-3">
                            <option value="DD/MM/YYYY" <?= $configSistema['formato_data'] == 'DD/MM/YYYY' ? 'selected' : '' ?>>
                                DD/MM/YYYY
                            </option>

                            <option value="YYYY-MM-DD" <?= $configSistema['formato_data'] == 'YYYY-MM-DD' ? 'selected' : '' ?>>
                                YYYY-MM-DD
                            </option>
                        </select>

                        <label class="fw-semibold mb-1">Idioma</label>
                        <select name="idioma" id="idiomaSistema" class="form-select">
    <option value="pt-BR" <?= $idioma == 'pt-BR' ? 'selected' : '' ?>>
        Português (Brasil)
    </option>

    <option value="en" <?= $idioma == 'en' ? 'selected' : '' ?>>
        English
    </option>
</select>

                    </div>
                </div>

                <!-- APARÊNCIA -->
                <div class="col-md-6">
                    <div class="card card-dashboard p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-palette text-primary me-2"></i>
                            Aparência
                        </h5>

                        <label class="fw-semibold mb-2">Tema</label>

                        <div class="d-flex gap-2">
                            <button type="button" id="lightMode" class="btn btn-primary w-100">
                                Claro
                            </button>

                            <button type="button" id="darkMode" class="btn btn-outline-dark w-100">
                                Escuro
                            </button>
                        </div>

                        <small class="text-muted mt-3 d-block">
                            O tema é salvo no navegador.
                        </small>

                    </div>
                </div>

                <!-- PERMISSÕES -->
                <div class="col-md-6">
                    <div class="card card-dashboard p-4 h-100">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-shield-lock text-primary me-2"></i>
                            Permissões de Usuários
                        </h5>

                        <div class="border rounded p-2 mb-2 config-item">
                            Gerenciar usuários do RH
                        </div>

                        <div class="border rounded p-2 mb-2 config-item">
                            Níveis de acesso
                        </div>

                        <div class="border rounded p-2 mb-2 config-item">
                            Auditoria de ações
                        </div>

                        <div class="border rounded p-2 config-item"
                         onclick="abrirJornada()"
                         style="cursor:pointer;">
                          Jornada de Trabalho
                           </div>

                    </div>
                </div>
                        <!-- JORNADA DE TRABALHO -->
<div id="jornadaContainer" class="col-12" style="display:none;">

    <div class="card card-dashboard p-4">

        <h4 class="fw-bold mb-4">
            <i class="bi bi-clock-history text-primary me-2"></i>
            Jornada de Trabalho
        </h4>

        <div class="row">

            <div class="col-md-4 mb-3">
                <label class="form-label">Nome da Jornada</label>
                <input type="text"
                       class="form-control"
                       placeholder="Administrativo">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Hora Entrada</label>
                <input type="time"
                       class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Hora Saída</label>
                <input type="time"
                       class="form-control">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Carga Horária</label>
                <input type="number"
                       class="form-control"
                       placeholder="9">
            </div>

        </div>

        <button class="btn btn-primary">
            Salvar Jornada
        </button>

    </div>

</div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="ponto.php" class="btn btn-outline-secondary">
                    Cancelar
                </a>

                <button type="submit" name="salvar_configuracoes" class="btn btn-primary">
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

                    <div class="alert alert-info mt-3 mb-0">
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

<script>
function toggleSenha(id, icone){
    const campo = document.getElementById(id);

    if(campo.type === 'password'){
        campo.type = 'text';

        icone.classList.remove('bi-eye-fill');
        icone.classList.add('bi-eye-slash-fill');
    }else{
        campo.type = 'password';

        icone.classList.remove('bi-eye-slash-fill');
        icone.classList.add('bi-eye-fill');
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/theme.js"></script>

<script>

const idiomaSistema = document.getElementById('idiomaSistema');

if(idiomaSistema){

    idiomaSistema.addEventListener('change', function(){

        localStorage.setItem('idiomaSistema', this.value);

    });

}
</script>

<script src="js/translate.js"></script>
<script src="js/translate.js"></script>

<script>

function abrirJornada(){

    const container =
        document.getElementById('jornadaContainer');

    if(container.style.display === 'none'){

        container.style.display = 'block';

    }else{

        container.style.display = 'none';

    }
}

</script>

</body>
</html>

