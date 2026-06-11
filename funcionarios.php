<?php
require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

$mensagem = '';
$erro = '';

/* EDITAR FUNCIONÁRIO */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_funcionario'])) {

    $idFuncionario = intval($_POST['id_funcionario'] ?? 0);
    $idUsuario = intval($_POST['id_usuario'] ?? 0);

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $horario = trim($_POST['horario'] ?? '');
    $tipo = trim($_POST['tipo'] ?? 'funcionario');
    $status = trim($_POST['status'] ?? 'ativo');

    if ($nome == '' || $email == '' || $cargo == '' || $departamento == '') {

        $erro = "Preencha nome, e-mail, cargo e departamento.";

    } else {

        $statusPermitidos = ['ativo', 'inativo', 'ferias', 'licenca', 'afastado'];

        if (!in_array($status, $statusPermitidos)) {
            $status = 'ativo';
        }

        $tiposPermitidos = ['funcionario', 'rh'];

        if (!in_array($tipo, $tiposPermitidos)) {
            $tipo = 'funcionario';
        }

        $verifica = $con->prepare("
            SELECT id_usuario
            FROM usuarios
            WHERE email = ?
            AND id_usuario <> ?
            AND id_empresa = ?
            LIMIT 1
        ");

        $verifica->bind_param("sii", $email, $idUsuario, $idEmpresa);
        $verifica->execute();

        $resultadoVerifica = $verifica->get_result();

        if ($resultadoVerifica->num_rows > 0) {

            $erro = "Este e-mail já está sendo usado por outro usuário.";

        } else {

            mysqli_begin_transaction($con);

            try {

                $stmtFunc = $con->prepare("
                    UPDATE funcionarios
                    SET
                        nome = ?,
                        cargo = ?,
                        departamento = ?,
                        horario_padrao = ?
                    WHERE id_funcionario = ?
                    AND id_empresa = ?
                ");

                if (!$stmtFunc) {
                    throw new Exception("Erro SQL funcionário: " . $con->error);
                }

                $stmtFunc->bind_param(
                    "ssssii",
                    $nome,
                    $cargo,
                    $departamento,
                    $horario,
                    $idFuncionario,
                    $idEmpresa
                );

                if (!$stmtFunc->execute()) {
                    throw new Exception("Erro ao atualizar funcionário: " . $stmtFunc->error);
                }

                $stmtUser = $con->prepare("
                    UPDATE usuarios
                    SET
                        nome = ?,
                        email = ?,
                        telefone = ?,
                        cargo = ?,
                        departamento = ?,
                        tipo = ?,
                        status = ?
                    WHERE id_usuario = ?
                    AND id_funcionario = ?
                    AND id_empresa = ?
                ");

                if (!$stmtUser) {
                    throw new Exception("Erro SQL usuário: " . $con->error);
                }

                $stmtUser->bind_param(
                    "sssssssiii",
                    $nome,
                    $email,
                    $telefone,
                    $cargo,
                    $departamento,
                    $tipo,
                    $status,
                    $idUsuario,
                    $idFuncionario,
                    $idEmpresa
                );

                if (!$stmtUser->execute()) {
                    throw new Exception("Erro ao atualizar usuário: " . $stmtUser->error);
                }

                mysqli_commit($con);

                $mensagem = "Funcionário atualizado com sucesso.";

            } catch (Exception $e) {

                mysqli_rollback($con);
                $erro = $e->getMessage();
            }
        }
    }
}

/* ALTERAR STATUS */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_status'])) {

    $idUsuario = intval($_POST['id_usuario']);
    $status = $_POST['status'];

    $stmt = $con->prepare("
        UPDATE usuarios
        SET status = ?
        WHERE id_usuario = ?
        AND id_empresa = ?
    ");

    $stmt->bind_param(
        "sii",
        $status,
        $idUsuario,
        $idEmpresa
    );

    if ($stmt->execute()) {
        $mensagem = "Status atualizado com sucesso.";
    } else {
        $erro = "Erro ao atualizar status.";
    }
}

/* BUSCAR FUNCIONÁRIOS DA EMPRESA */
$stmt = $con->prepare("
    SELECT
        funcionarios.id_funcionario,
        funcionarios.nome,
        funcionarios.cargo,
        funcionarios.departamento,
        funcionarios.horario_padrao,

        usuarios.id_usuario,
        usuarios.email,
        usuarios.telefone,
        usuarios.status,
        usuarios.tipo,
        usuarios.foto
    FROM funcionarios
    LEFT JOIN usuarios
    ON usuarios.id_funcionario = funcionarios.id_funcionario
    AND usuarios.id_empresa = funcionarios.id_empresa
    WHERE funcionarios.id_empresa = ?
    ORDER BY funcionarios.nome ASC
");

$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$funcionarios = $stmt->get_result();

/* CONTADORES */
$total = 0;
$ativos = 0;
$ferias = 0;
$licencas = 0;
$afastados = 0;

$listaFuncionarios = [];

while ($f = $funcionarios->fetch_assoc()) {

    $listaFuncionarios[] = $f;
    $total++;

    if ($f['status'] == 'ativo') {
        $ativos++;
    }

    if ($f['status'] == 'ferias') {
        $ferias++;
    }

    if ($f['status'] == 'licenca') {
        $licencas++;
    }

    if ($f['status'] == 'afastado') {
        $afastados++;
    }
}

function badgeStatus($status) {

    if ($status == 'ativo') {
        return '<span class="badge bg-success">Ativo</span>';
    }

    if ($status == 'inativo') {
        return '<span class="badge bg-secondary">Inativo</span>';
    }

    if ($status == 'ferias') {
        return '<span class="badge bg-info text-dark">Férias</span>';
    }

    if ($status == 'licenca') {
        return '<span class="badge bg-warning text-dark">Licença</span>';
    }

    if ($status == 'afastado') {
        return '<span class="badge bg-danger">Afastado</span>';
    }

    return '<span class="badge bg-light text-dark border">Sem status</span>';
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Funcionários</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="css/style.css">

<style>
.card-funcionario{
    border-radius:18px;
    transition:.25s;
}

.card-funcionario:hover{
    transform:translateY(-4px);
}

.avatar-funcionario{
    width:64px;
    height:64px;
    border-radius:50%;
    object-fit:cover;
}

.avatar-letra{
    width:64px;
    height:64px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    font-weight:700;
}

.icon-card{
    width:48px;
    height:48px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.modal{
    z-index:99999 !important;
}

.modal-backdrop{
    z-index:99998 !important;
}

.modal-section-title{
    font-size:14px;
    font-weight:700;
    color:#0d6efd;
    text-transform:uppercase;
    letter-spacing:.5px;
    border-bottom:1px solid #dee2e6;
    padding-bottom:8px;
    margin-bottom:16px;
}
</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid">

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">

        <div>
            <h1 class="fw-bold mb-1">
                Funcionários
            </h1>

            <p class="text-muted mb-0">
                Gerencie colaboradores e RH da empresa
            </p>
        </div>

        <div class="mt-3 mt-lg-0 d-flex gap-2">

            <a href="cadastrarUsuario.php" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>
                Cadastrar usuário
            </a>

            <a href="importar-funcionarios.php" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-arrow-up me-2"></i>
                Importar
            </a>

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

    <!-- CARDS RESUMO -->
    <div class="row g-4 mb-4">

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-primary text-white fs-4">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Total</p>
                        <h3 class="fw-bold mb-0"><?= $total ?></h3>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-success text-white fs-4">
                        <i class="bi bi-person-check-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Ativos</p>
                        <h3 class="fw-bold mb-0"><?= $ativos ?></h3>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-info text-dark fs-4">
                        <i class="bi bi-umbrella-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Férias</p>
                        <h3 class="fw-bold mb-0"><?= $ferias ?></h3>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-warning text-dark fs-4">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Licenças/Afast.</p>
                        <h3 class="fw-bold mb-0"><?= $licencas + $afastados ?></h3>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- BUSCA -->
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="row g-3 align-items-center">

                <div class="col-md-8">

                    <div class="input-group">

                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="text"
                            class="form-control"
                            id="pesquisarFuncionario"
                            placeholder="Pesquisar por nome, cargo, setor, e-mail ou status">

                    </div>

                </div>

                <div class="col-md-4">

                    <select class="form-select" id="filtroStatus">
                        <option value="">Todos os status</option>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                        <option value="ferias">Férias</option>
                        <option value="licenca">Licença</option>
                        <option value="afastado">Afastado</option>
                    </select>

                </div>

            </div>

        </div>

    </div>

    <!-- LISTA -->
    <div class="row g-4" id="listaFuncionarios">

        <?php if(count($listaFuncionarios) > 0): ?>

            <?php foreach($listaFuncionarios as $f): ?>

                <?php
                $status = $f['status'] ?? 'ativo';
                $telefone = $f['telefone'] ?? '-';
                $tipo = $f['tipo'] ?? 'funcionario';
                $idUsuario = $f['id_usuario'] ?? 0;
                ?>

                <div
                    class="col-lg-4 col-md-6 funcionario-item"
                    data-status="<?= htmlspecialchars($status) ?>"
                    data-texto="<?= strtolower(htmlspecialchars(($f['nome'] ?? '') . ' ' . ($f['cargo'] ?? '') . ' ' . ($f['departamento'] ?? '') . ' ' . ($f['email'] ?? '') . ' ' . $status)) ?>"
                >

                    <div class="card shadow-sm border-0 h-100 card-funcionario">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <?php if(!empty($f['foto'])): ?>

                                    <img
                                        src="<?= htmlspecialchars($f['foto']) ?>"
                                        class="avatar-funcionario me-3"
                                    >

                                <?php else: ?>

                                    <div class="avatar-letra bg-primary text-white me-3">
                                        <?= strtoupper(substr($f['nome'], 0, 1)) ?>
                                    </div>

                                <?php endif; ?>

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-start gap-2">

                                        <div>
                                            <h5 class="mb-1">
                                                <?= htmlspecialchars($f['nome']) ?>
                                            </h5>

                                            <small class="text-muted">
                                                <?= htmlspecialchars($f['cargo'] ?? '-') ?>
                                            </small>
                                        </div>

                                        <?= badgeStatus($status) ?>

                                    </div>

                                </div>

                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">

                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-building me-1"></i>
                                    <?= htmlspecialchars($f['departamento'] ?? '-') ?>
                                </span>

                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-shield-check me-1"></i>
                                    <?= $tipo == 'rh' ? 'RH' : 'Colaborador' ?>
                                </span>

                            </div>

                            <hr>

                            <div class="small text-muted">

                                <div class="mb-2">
                                    <i class="bi bi-envelope me-2"></i>
                                    <?= htmlspecialchars($f['email'] ?? '-') ?>
                                </div>

                                <div class="mb-2">
                                    <i class="bi bi-telephone me-2"></i>
                                    <?= htmlspecialchars($telefone) ?>
                                </div>

                                <div>
                                    <i class="bi bi-clock me-2"></i>
                                    <?= htmlspecialchars($f['horario_padrao'] ?? '-') ?>
                                </div>

                            </div>

                            <hr>

                            <div class="d-flex gap-2 mb-3">

                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm w-100 btnEditarFuncionario"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarFuncionario"
                                    data-id-funcionario="<?= htmlspecialchars($f['id_funcionario']) ?>"
                                    data-id-usuario="<?= htmlspecialchars($idUsuario) ?>"
                                    data-nome="<?= htmlspecialchars($f['nome'] ?? '') ?>"
                                    data-email="<?= htmlspecialchars($f['email'] ?? '') ?>"
                                    data-telefone="<?= htmlspecialchars($f['telefone'] ?? '') ?>"
                                    data-cargo="<?= htmlspecialchars($f['cargo'] ?? '') ?>"
                                    data-departamento="<?= htmlspecialchars($f['departamento'] ?? '') ?>"
                                    data-horario="<?= htmlspecialchars($f['horario_padrao'] ?? '') ?>"
                                    data-tipo="<?= htmlspecialchars($tipo) ?>"
                                    data-status="<?= htmlspecialchars($status) ?>"
                                    <?= !$idUsuario ? 'disabled' : '' ?>
                                >
                                    <i class="bi bi-pencil-square me-1"></i>
                                    Editar
                                </button>

                            </div>

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="id_usuario"
                                    value="<?= htmlspecialchars($idUsuario) ?>"
                                >

                                <label class="form-label small fw-semibold">
                                    Alterar status
                                </label>

                                <div class="input-group">

                                    <select name="status" class="form-select form-select-sm" <?= !$idUsuario ? 'disabled' : '' ?>>

                                        <option value="ativo" <?= $status == 'ativo' ? 'selected' : '' ?>>
                                            Ativo
                                        </option>

                                        <option value="inativo" <?= $status == 'inativo' ? 'selected' : '' ?>>
                                            Inativo
                                        </option>

                                        <option value="ferias" <?= $status == 'ferias' ? 'selected' : '' ?>>
                                            Férias
                                        </option>

                                        <option value="licenca" <?= $status == 'licenca' ? 'selected' : '' ?>>
                                            Licença
                                        </option>

                                        <option value="afastado" <?= $status == 'afastado' ? 'selected' : '' ?>>
                                            Afastado
                                        </option>

                                    </select>

                                    <button
                                        type="submit"
                                        name="alterar_status"
                                        class="btn btn-primary btn-sm"
                                        <?= !$idUsuario ? 'disabled' : '' ?>
                                    >
                                        <i class="bi bi-check-lg"></i>
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="card border-0 shadow-sm">

                    <div class="card-body text-center py-5">

                        <i class="bi bi-people display-1 text-muted"></i>

                        <h4 class="fw-bold mt-3">
                            Nenhum funcionário cadastrado
                        </h4>

                        <p class="text-muted">
                            Cadastre manualmente ou importe uma planilha CSV.
                        </p>

                        <div class="d-flex justify-content-center gap-2">

                            <a href="cadastrarUsuario.php" class="btn btn-primary">
                                <i class="bi bi-person-plus me-2"></i>
                                Cadastrar usuário
                            </a>

                            <a href="importar-funcionarios.php" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-arrow-up me-2"></i>
                                Importar CSV
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

</div>

<!-- MODAL EDITAR FUNCIONÁRIO -->
<div class="modal fade" id="modalEditarFuncionario" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>
                    Editar funcionário
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

            </div>

            <form method="POST">

                <div class="modal-body">

                    <input type="hidden" name="id_funcionario" id="edit_id_funcionario">
                    <input type="hidden" name="id_usuario" id="edit_id_usuario">

                    <div class="modal-section-title">
                        Dados pessoais e login
                    </div>

                    <div class="row g-3 mb-4">

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nome completo</label>
                            <input type="text" name="nome" id="edit_nome" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">E-mail de login</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>

                            <small class="text-muted">
                                Esse e-mail será usado para o funcionário entrar no sistema.
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Telefone</label>
                            <input type="text" name="telefone" id="edit_telefone" class="form-control">
                        </div>

                    </div>

                    <div class="modal-section-title">
                        Dados profissionais
                    </div>

                    <div class="row g-3 mb-4">

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cargo</label>
                            <input type="text" name="cargo" id="edit_cargo" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Departamento</label>
                            <input type="text" name="departamento" id="edit_departamento" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Horário padrão</label>
                            <input type="time" name="horario" id="edit_horario" class="form-control">
                        </div>

                    </div>

                    <div class="modal-section-title">
                        Acesso e status
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de acesso</label>
                            <select name="tipo" id="edit_tipo" class="form-select" required>
                                <option value="funcionario">Funcionário</option>
                                <option value="rh">RH</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="ferias">Férias</option>
                                <option value="licenca">Licença</option>
                                <option value="afastado">Afastado</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        name="editar_funcionario"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-check-lg me-1"></i>
                        Salvar alterações
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const pesquisa = document.getElementById('pesquisarFuncionario');
const filtroStatus = document.getElementById('filtroStatus');
const funcionarios = document.querySelectorAll('.funcionario-item');

function filtrarFuncionarios(){

    const termo = pesquisa.value.toLowerCase();
    const status = filtroStatus.value;

    funcionarios.forEach(function(card){

        const texto = card.dataset.texto;
        const statusCard = card.dataset.status;

        const combinaTexto = texto.includes(termo);
        const combinaStatus = status === '' || statusCard === status;

        if(combinaTexto && combinaStatus){
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }

    });
}

pesquisa.addEventListener('keyup', filtrarFuncionarios);
filtroStatus.addEventListener('change', filtrarFuncionarios);

/* PREENCHER MODAL DE EDIÇÃO */
document.querySelectorAll('.btnEditarFuncionario').forEach(function(botao){

    botao.addEventListener('click', function(){

        document.getElementById('edit_id_funcionario').value = this.dataset.idFuncionario;
        document.getElementById('edit_id_usuario').value = this.dataset.idUsuario;
        document.getElementById('edit_nome').value = this.dataset.nome;
        document.getElementById('edit_email').value = this.dataset.email;
        document.getElementById('edit_telefone').value = this.dataset.telefone;
        document.getElementById('edit_cargo').value = this.dataset.cargo;
        document.getElementById('edit_departamento').value = this.dataset.departamento;
        document.getElementById('edit_horario').value = this.dataset.horario;
        document.getElementById('edit_tipo').value = this.dataset.tipo;
        document.getElementById('edit_status').value = this.dataset.status;

    });

});
</script>

<script src="js/theme.js"></script>
<script src="js/translate.js"></script>

</body>
</html>