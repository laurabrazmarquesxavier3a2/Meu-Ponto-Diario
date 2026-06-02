<?php
require_once 'auth.php';
require_once 'config/database.php';

$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

$mensagem = '';
$erro = '';

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
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($erro): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $erro ?>
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
                ?>

                <div
                    class="col-lg-4 col-md-6 funcionario-item"
                    data-status="<?= htmlspecialchars($status) ?>"
                    data-texto="<?= strtolower(htmlspecialchars($f['nome'] . ' ' . $f['cargo'] . ' ' . $f['departamento'] . ' ' . $f['email'] . ' ' . $status)) ?>"
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

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="id_usuario"
                                    value="<?= $f['id_usuario'] ?>"
                                >

                                <label class="form-label small fw-semibold">
                                    Alterar status
                                </label>

                                <div class="input-group">

                                    <select name="status" class="form-select form-select-sm">

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
        }else{
            card.style.display = 'none';
        }

    });
}

pesquisa.addEventListener('keyup', filtrarFuncionarios);
filtroStatus.addEventListener('change', filtrarFuncionarios);
</script>
<script src="js/theme.js"></script>
</body>
</html>