<?php
require_once 'auth.php';
require_once 'config/database.php';

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

$sqlAtividades = "
SELECT * FROM atividades
WHERE id_usuario = ?
ORDER BY data_atividade DESC
LIMIT 5
";

$stmtAtv = $con->prepare($sqlAtividades);
$stmtAtv->bind_param("i", $id_usuario);
$stmtAtv->execute();

$atividades = $stmtAtv->get_result();

$iniciais = '';

$nomes = explode(' ', $usuario['nome']);

foreach($nomes as $n){
    $iniciais .= strtoupper($n[0]);

    if(strlen($iniciais) >= 2){
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>Meu Perfil</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
      rel="stylesheet">

<style>

.avatar{
    width:100px;
    height:100px;
    border-radius:50%;
    background:linear-gradient(135deg,#2563eb,#1e3a8a);
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-size:32px;
    font-weight:bold;
    margin:auto;
    box-shadow:0 8px 20px rgba(0,0,0,.15);
}

.profile-card{
    overflow:hidden;
}

.profile-header{
    height:90px;
    background:linear-gradient(135deg,#1d4ed8,#2563eb);
    border-radius:16px 16px 0 0;
}

.permission-box{
    background:#f1f5f9;
    transition:.3s;
}

.permission-box:hover{
    transform:translateY(-2px);
}

.activity-item{
    border-left:3px solid #2563eb;
    padding-left:15px;
    margin-bottom:20px;
}

.stat-card i{
    font-size:28px;
    opacity:.7;
}

body.dark-mode .permission-box{
    background:#244266;
    color:white;
}

body.dark-mode .activity-item{
    border-color:#60a5fa;
}

</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid">

    <!-- TOPO -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="fw-bold">
                Meu Perfil
            </h1>

            <h5 class="text-muted">
                Informações da sua conta de RH
            </h5>
        </div>

        <span class="badge bg-success px-3 py-2">
            <i class="bi bi-circle-fill me-1"></i>
            Online
        </span>

    </div>

    <div class="row g-4">

        <!-- PERFIL -->
        <div class="col-lg-4">

            <div class="card card-dashboard shadow-sm border-0 profile-card">

                <div class="profile-header"></div>

                <div class="p-4 text-center">

                    <?php if($usuario['foto']): ?>

                        <img src="uploads/<?= $usuario['foto'] ?>"
                             class="avatar">

                    <?php else: ?>

                        <div class="avatar">
                            <?= $iniciais ?>
                        </div>

                    <?php endif; ?>

                    <h4 class="fw-bold mt-3 mb-1">
                        <?= $usuario['nome'] ?>
                    </h4>

                    <p class="text-primary mb-1">
                        <?= $usuario['cargo'] ?>
                    </p>

                    <p class="text-muted small">
                        <?= $usuario['departamento'] ?>
                    </p>

                    <hr>

                    <div class="text-start">

                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-envelope me-2"></i>
                                <?= $usuario['email'] ?>
                            </small>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-telephone me-2"></i>
                                <?= $usuario['telefone'] ?>
                            </small>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-2"></i>
                                <?= $usuario['cidade'] ?>
                            </small>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-clock-history me-2"></i>

                                Último login:
                                <?= date(
                                    'd/m/Y H:i',
                                    strtotime($usuario['ultimo_login'])
                                ) ?>
                            </small>
                        </div>

                    </div>

                    <button class="btn btn-primary w-100 mt-3">
                        <i class="bi bi-pencil-square me-2"></i>
                        Editar Perfil
                    </button>

                </div>

            </div>

        </div>

        <!-- LADO DIREITO -->
        <div class="col-lg-8">

            <!-- STATS -->
            <div class="row g-3 mb-4">

                <div class="col-md-3">

                    <div class="card card-dashboard stat-card p-3 border-0 shadow-sm">

                        <div class="d-flex justify-content-between">

                            <div>
                                <h3 class="fw-bold">18</h3>

                                <small class="text-muted">
                                    Aprovações
                                </small>
                            </div>

                            <i class="bi bi-check2-circle"></i>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card card-dashboard stat-card p-3 border-0 shadow-sm">

                        <div class="d-flex justify-content-between">

                            <div>
                                <h3 class="fw-bold">42</h3>

                                <small class="text-muted">
                                    Ações Hoje
                                </small>
                            </div>

                            <i class="bi bi-lightning"></i>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card card-dashboard stat-card p-3 border-0 shadow-sm">

                        <div class="d-flex justify-content-between">

                            <div>
                                <h3 class="fw-bold">5</h3>

                                <small class="text-muted">
                                    Anos Empresa
                                </small>
                            </div>

                            <i class="bi bi-briefcase"></i>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card card-dashboard stat-card p-3 border-0 shadow-sm">

                        <div class="d-flex justify-content-between">

                            <div>
                                <h3 class="fw-bold">1.247</h3>

                                <small class="text-muted">
                                    Resolvidas
                                </small>
                            </div>

                            <i class="bi bi-bar-chart"></i>

                        </div>

                    </div>

                </div>

            </div>

            <!-- PERMISSÕES -->
            <div class="card card-dashboard border-0 shadow-sm p-4 mb-4">

                <h5 class="fw-bold mb-4">
                    <i class="bi bi-shield-lock me-2 text-primary"></i>
                    Permissões e Acessos
                </h5>

                <div class="row g-3">

                    <div class="col-md-6">
                        <div class="permission-box rounded p-3">
                            Administrador
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="permission-box rounded p-3">
                            Aprovar Férias
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="permission-box rounded p-3">
                            Aprovar Licenças
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="permission-box rounded p-3">
                            Gerenciar Funcionários
                        </div>
                    </div>

                </div>
            </div>

            <!-- ATIVIDADES -->
            <div class="card card-dashboard border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-4">
                    Atividades Recentes
                </h5>
                <?php while($atividade = $atividades->fetch_assoc()): ?>
                    <div class="activity-item">
                        <div class="d-flex align-items-center mb-1">
                            <span class="badge bg-<?= $atividade['tipo'] ?> me-2">
                                &nbsp;
                            </span>
                            <strong>
                                <?= $atividade['descricao'] ?>
                            </strong>
                        </div>
                        <small class="text-muted">
                            <?= date(
                                'd/m/Y H:i',
                                strtotime($atividade['data_atividade'])
                            ) ?>
                        </small>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
</div>
<script src="js/theme.js"></script>
</body>
</html>