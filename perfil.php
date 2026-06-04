<?php
require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    die("Usuário não identificado. Faça login novamente.");
}

$mensagem = '';
$erro = '';

/* ALTERAR FOTO DE PERFIL */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_foto'])) {

    if (!empty($_FILES['foto']['name'])) {

        $permitidos = ['jpg', 'jpeg', 'png'];
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($extensao, $permitidos)) {

            $erro = "Envie apenas imagens JPG, JPEG ou PNG.";

        } else {

            if (!is_dir("uploads/perfis")) {
                mkdir("uploads/perfis", 0777, true);
            }

            $nomeFoto = uniqid("perfil_") . "." . $extensao;
            $destino = "uploads/perfis/" . $nomeFoto;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {

                $stmtFoto = $con->prepare("
                    UPDATE usuarios
                    SET foto = ?
                    WHERE id_usuario = ?
                ");

                $stmtFoto->bind_param(
                    "si",
                    $destino,
                    $id_usuario
                );

                if ($stmtFoto->execute()) {
                    $mensagem = "Foto de perfil atualizada com sucesso.";
                } else {
                    $erro = "Erro ao salvar foto no banco.";
                }

            } else {
                $erro = "Erro ao enviar a imagem.";
            }
        }

    } else {
        $erro = "Selecione uma imagem.";
    }
}

/* BUSCAR USUÁRIO */
$sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();

$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    die("Usuário não encontrado.");
}

/* ATIVIDADES RECENTES */
$sqlAtividades = "
SELECT *
FROM atividades
WHERE id_usuario = ?
ORDER BY data_atividade DESC
LIMIT 8
";

$stmtAtv = $con->prepare($sqlAtividades);
$stmtAtv->bind_param("i", $id_usuario);
$stmtAtv->execute();

$atividades = $stmtAtv->get_result();

/* INICIAIS */
$iniciais = '';
$nomes = explode(' ', $usuario['nome']);

foreach ($nomes as $n) {

    if (!empty($n)) {
        $iniciais .= strtoupper($n[0]);
    }

    if (strlen($iniciais) >= 2) {
        break;
    }
}

$fotoUsuario = $usuario['foto'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Meu Perfil</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>

.avatar{
    width:110px;
    height:110px;
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
    object-fit:cover;
}

.profile-card{
    overflow:hidden;
    border-radius:18px;
}

.profile-header{
    height:95px;
    background:linear-gradient(135deg,#1d4ed8,#2563eb);
    border-radius:18px 18px 0 0;
}

.permission-box{
    background:#f1f5f9;
    transition:.3s;
}

.permission-box:hover{
    transform:translateY(-2px);
}

.activity-item{
    border-left:4px solid #2563eb;
    padding-left:15px;
    margin-bottom:20px;
}

.stat-card i{
    font-size:28px;
    opacity:.7;
}

.btn-foto{
    margin-top:-18px;
    position:relative;
    z-index:3;
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

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="fw-bold">
                Meu Perfil
            </h1>

            <h5 class="text-muted">
                Informações da sua conta
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

                    <?php if(!empty($fotoUsuario) && file_exists($fotoUsuario)): ?>

                        <img
                            src="<?= htmlspecialchars($fotoUsuario) ?>"
                            class="avatar"
                        >

                    <?php else: ?>

                        <div class="avatar">
                            <?= htmlspecialchars($iniciais) ?>
                        </div>

                    <?php endif; ?>

                    <button
                        class="btn btn-primary btn-sm rounded-pill btn-foto"
                        data-bs-toggle="modal"
                        data-bs-target="#modalFoto"
                    >
                        <i class="bi bi-camera-fill me-1"></i>
                        Alterar foto
                    </button>

                    <h4 class="fw-bold mt-3 mb-1">
                        <?= htmlspecialchars($usuario['nome']) ?>
                    </h4>

                    <p class="text-primary mb-1">
                        <?= htmlspecialchars($usuario['cargo'] ?? 'Cargo não informado') ?>
                    </p>

                    <p class="text-muted small">
                        <?= htmlspecialchars($usuario['departamento'] ?? 'Departamento não informado') ?>
                    </p>

                    <hr>

                    <div class="text-start">

                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-envelope me-2"></i>
                                <?= htmlspecialchars($usuario['email']) ?>
                            </small>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-telephone me-2"></i>
                                <?= htmlspecialchars($usuario['telefone'] ?? 'Telefone não informado') ?>
                            </small>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-geo-alt me-2"></i>
                                <?= htmlspecialchars($usuario['cidade'] ?? 'Cidade não informada') ?>
                            </small>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="bi bi-clock-history me-2"></i>

                                Último login:

                                <?php
                                if (!empty($usuario['ultimo_login'])) {
                                    echo date('d/m/Y H:i', strtotime($usuario['ultimo_login']));
                                } else {
                                    echo 'Primeiro acesso';
                                }
                                ?>

                            </small>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- CONTEÚDO DIREITO -->
        <div class="col-lg-8">

            <!-- STATS -->
            <div class="row g-3 mb-4">

                <div class="col-md-3">

                    <div class="card card-dashboard stat-card p-3 border-0 shadow-sm">

                        <div class="d-flex justify-content-between">

                            <div>

                                <h3 class="fw-bold">
                                    <?= $atividades->num_rows ?>
                                </h3>

                                <small class="text-muted">
                                    Atividades
                                </small>

                            </div>

                            <i class="bi bi-activity"></i>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card card-dashboard stat-card p-3 border-0 shadow-sm">

                        <div class="d-flex justify-content-between">

                            <div>

                                <h3 class="fw-bold">
                                    <?= ucfirst($usuario['tipo']) ?>
                                </h3>

                                <small class="text-muted">
                                    Perfil
                                </small>

                            </div>

                            <i class="bi bi-person-badge"></i>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card card-dashboard stat-card p-3 border-0 shadow-sm">

                        <div class="d-flex justify-content-between">

                            <div>

                                <h3 class="fw-bold">
                                    <?= ucfirst($usuario['status']) ?>
                                </h3>

                                <small class="text-muted">
                                    Status
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

                                <h3 class="fw-bold">
                                    #<?= $usuario['id_empresa'] ?>
                                </h3>

                                <small class="text-muted">
                                    Empresa
                                </small>

                            </div>

                            <i class="bi bi-building"></i>

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

                    <?php if($usuario['tipo'] == 'rh'): ?>

                        <div class="col-md-6">
                            <div class="permission-box rounded p-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Gerenciar Funcionários
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="permission-box rounded p-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Enviar Holerites
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="permission-box rounded p-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Publicar Comunicados
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="permission-box rounded p-3">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Gerenciar Emergências
                            </div>
                        </div>

                    <?php else: ?>

                        <div class="col-md-6">
                            <div class="permission-box rounded p-3">
                                <i class="bi bi-person-check-fill text-primary me-2"></i>
                                Registrar Ponto
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="permission-box rounded p-3">
                                <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                Visualizar Holerites
                            </div>
                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <!-- ATIVIDADES -->
            <div class="card card-dashboard border-0 shadow-sm p-4">

                <h5 class="fw-bold mb-4">
                    <i class="bi bi-activity me-2 text-primary"></i>
                    Atividades Recentes
                </h5>

                <?php if($atividades->num_rows > 0): ?>

                    <?php while($atividade = $atividades->fetch_assoc()): ?>

                        <div class="activity-item">

                            <div class="d-flex align-items-center mb-1">

                                <span class="badge bg-<?= htmlspecialchars($atividade['tipo']) ?> me-2">
                                    &nbsp;
                                </span>

                                <strong>
                                    <?= htmlspecialchars($atividade['descricao']) ?>
                                </strong>

                            </div>

                            <small class="text-muted">
                                <?= date('d/m/Y H:i', strtotime($atividade['data_atividade'])) ?>
                            </small>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="text-center py-4">

                        <i class="bi bi-clock-history display-4 text-muted"></i>

                        <h5 class="fw-bold mt-3">
                            Nenhuma atividade recente
                        </h5>

                        <p class="text-muted mb-0">
                            Ações importantes como envio de holerite, cadastro de funcionário,
                            publicação de comunicado e resolução de emergências aparecerão aqui.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

</div>

<!-- MODAL FOTO -->
<div class="modal fade" id="modalFoto" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <form method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    <i class="bi bi-camera-fill me-2"></i>
                    Alterar foto de perfil
                </h5>

                

            </div>

            <div class="modal-body">

                <label class="form-label fw-bold">
                    Escolha uma nova foto
                </label>

                <input
                    type="file"
                    name="foto"
                    id="novaFoto"
                    class="form-control"
                    accept=".jpg,.jpeg,.png"
                    required
                >

                <div class="text-center mt-4">

                    <img
                        id="previewNovaFoto"
                        src=""
                        class="avatar"
                        style="display:none;"
                    >

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    name="alterar_foto"
                    class="btn btn-primary"
                >
                    <i class="bi bi-save me-2"></i>
                    Salvar foto
                </button>

            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/theme.js"></script>

<script>

const novaFoto = document.getElementById('novaFoto');
const previewNovaFoto = document.getElementById('previewNovaFoto');

novaFoto.addEventListener('change', function(){

    const arquivo = this.files[0];

    if(arquivo){

        previewNovaFoto.src = URL.createObjectURL(arquivo);
        previewNovaFoto.style.display = 'flex';

    }

});

</script>
<script src="js/translate.js"></script>
</body>
</html>