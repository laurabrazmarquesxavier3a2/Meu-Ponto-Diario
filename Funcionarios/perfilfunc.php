<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$id_empresa = $_SESSION['id_empresa'] ?? 0;

if (!$id_usuario || !$id_empresa) {
    die("Usuário não autenticado. Faça login novamente.");
}

/* SALVAR FOTO */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {

    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {

        $pasta = '../uploads/perfis/';

        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extensao, $permitidas)) {

            $nomeArquivo = 'perfil_' . $id_usuario . '_' . uniqid() . '.' . $extensao;
            $destino = $pasta . $nomeArquivo;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {

                $caminhoBanco = 'uploads/perfis/' . $nomeArquivo;

                $stmtFoto = $con->prepare("
                    UPDATE usuarios
                    SET foto = ?
                    WHERE id_usuario = ?
                    AND id_empresa = ?
                ");

                $stmtFoto->bind_param("sii", $caminhoBanco, $id_usuario, $id_empresa);
                $stmtFoto->execute();

                header("Location: perfilfunc.php");
                exit;
            }
        }
    }
}

/* BUSCAR USUÁRIO LOGADO */
$sql = "
SELECT
    u.id_usuario,
    u.id_funcionario,
    u.nome,
    u.email,
    u.tipo,
    u.status,
    u.telefone,
    u.cidade,
    u.foto,
    u.cargo AS cargo_usuario,
    u.departamento AS departamento_usuario,

    f.cargo AS cargo_funcionario,
    f.departamento AS departamento_funcionario,
    f.horario_padrao,
    f.escala,
    f.supervisor

FROM usuarios u

LEFT JOIN funcionarios f
    ON f.id_funcionario = u.id_funcionario
    AND f.id_empresa = u.id_empresa

WHERE u.id_usuario = ?
AND u.id_empresa = ?
LIMIT 1
";

$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Erro SQL: " . $con->error);
}

$stmt->bind_param("ii", $id_usuario, $id_empresa);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Perfil não encontrado para este usuário.");
}

$usuario = $resultado->fetch_assoc();

$nome = $usuario['nome'] ?: 'Usuário';
$email = $usuario['email'] ?: '-';
$telefone = $usuario['telefone'] ?: '-';
$cidade = $usuario['cidade'] ?: '-';

$cargo = $usuario['cargo_funcionario'] ?: ($usuario['cargo_usuario'] ?: 'Funcionário');
$departamento = $usuario['departamento_funcionario'] ?: ($usuario['departamento_usuario'] ?: '-');

$tipo = ucfirst($usuario['tipo'] ?: 'Funcionário');
$status = ucfirst($usuario['status'] ?: 'Ativo');

$horario = $usuario['horario_padrao'] ?: '09:00:00';
$escala = $usuario['escala'] ?: 'Não informada';
$supervisor = $usuario['supervisor'] ?: 'Não informado';

$foto = $usuario['foto'] ?? '';

$temFoto = false;

if (!empty($foto) && file_exists('../' . $foto)) {
    $temFoto = true;
}

$partesNome = explode(' ', trim($nome));

if (count($partesNome) >= 2) {
    $iniciais = strtoupper(substr($partesNome[0], 0, 1) . substr(end($partesNome), 0, 1));
} else {
    $iniciais = strtoupper(substr($nome, 0, 2));
}

$horaEntrada = date('H:i', strtotime($horario));
$horaSaida = date('H:i', strtotime($horario . ' +9 hours'));
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Meu Perfil</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="../css/global.css">

<style>
.avatar-iniciais {
    width: 170px;
    height: 170px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d6efd, #224da8);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 58px;
    font-weight: 800;
    border: 4px solid #fff;
    box-shadow: 0 8px 25px rgba(0,0,0,.15);
}

.avatar-img {
    width: 170px;
    height: 170px;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: 0 8px 25px rgba(0,0,0,.15);
}
</style>
</head>

<body>

<?php include 'sidebarfunc.php'; ?>

<div class="content">

    <div class="title text-start">
        <h2>Meu Perfil</h2>
        <p>Gerencie suas informações pessoais e profissionais</p>
    </div>

    <div class="row g-4">

        <div class="col-12 col-lg-4">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body text-center p-5">

                    <form method="POST" enctype="multipart/form-data">

                        <div class="position-relative d-inline-block mb-4">

                            <?php if ($temFoto): ?>

                                <img
                                src="../<?= htmlspecialchars($foto) ?>"
                                class="avatar-img"
                                id="previewFoto">

                            <?php else: ?>

                                <div class="avatar-iniciais" id="previewIniciais">
                                    <?= htmlspecialchars($iniciais) ?>
                                </div>

                            <?php endif; ?>

                            <label
                            for="fotoInput"
                            class="btn btn-primary rounded-circle position-absolute bottom-0 end-0 shadow">

                                <i class="fa-solid fa-camera"></i>

                            </label>

                            <input
                            type="file"
                            name="foto"
                            id="fotoInput"
                            accept="image/*"
                            hidden>

                        </div>

                    </form>

                    <h3 class="fw-bold mb-2">
                        <?= htmlspecialchars($nome) ?>
                    </h3>

                    <p class="text-secondary mb-1">
                        <?= htmlspecialchars($cargo) ?>
                    </p>

                    <span class="badge bg-primary rounded-pill px-3 py-2 mb-4">
                        <?= htmlspecialchars($status) ?>
                    </span>

                    <hr>

                    <div class="text-start mt-4">

                        <p class="mb-2">
                            <strong>Email:</strong><br>
                            <span class="text-secondary"><?= htmlspecialchars($email) ?></span>
                        </p>

                        <p class="mb-2">
                            <strong>Telefone:</strong><br>
                            <span class="text-secondary"><?= htmlspecialchars($telefone) ?></span>
                        </p>

                        <p class="mb-2">
                            <strong>Cidade:</strong><br>
                            <span class="text-secondary"><?= htmlspecialchars($cidade) ?></span>
                        </p>

                        <p class="mb-0">
                            <strong>Departamento:</strong><br>
                            <span class="text-secondary"><?= htmlspecialchars($departamento) ?></span>
                        </p>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-12 col-lg-8">

            <div class="row g-4">

                <div class="col-12">

                    <div class="card border-0 shadow-sm rounded-4">

                        <div class="card-body p-4">

                            <h4 class="fw-bold mb-4">
                                <i class="fa-solid fa-user-tie text-primary me-2"></i>
                                Informações Profissionais
                            </h4>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <strong>Nome Completo</strong>
                                    <p class="text-secondary mb-0">
                                        <?= htmlspecialchars($nome) ?>
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <strong>Email</strong>
                                    <p class="text-secondary mb-0">
                                        <?= htmlspecialchars($email) ?>
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <strong>Cargo</strong>
                                    <p class="text-secondary mb-0">
                                        <?= htmlspecialchars($cargo) ?>
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <strong>Departamento</strong>
                                    <p class="text-secondary mb-0">
                                        <?= htmlspecialchars($departamento) ?>
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <strong>Tipo de Conta</strong>
                                    <p class="text-secondary mb-0">
                                        <?= htmlspecialchars($tipo) ?>
                                    </p>
                                </div>

                                <div class="col-md-6">
                                    <strong>Supervisor</strong>
                                    <p class="text-secondary mb-0">
                                        <?= htmlspecialchars($supervisor) ?>
                                    </p>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-12">

                    <div class="card border-0 shadow-sm rounded-4">

                        <div class="card-body p-4">

                            <h4 class="fw-bold mb-4">
                                <i class="fa-solid fa-business-time text-primary me-2"></i>
                                Informações de Jornada
                            </h4>

                            <div class="row g-4">

                                <div class="col-md-4">

                                    <div class="d-flex gap-3 align-items-start">

                                        <i class="fa-regular fa-clock text-primary fs-4"></i>

                                        <div>
                                            <strong>Escala</strong>
                                            <p class="text-secondary mb-0">
                                                <?= htmlspecialchars($escala) ?>
                                            </p>
                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="d-flex gap-3 align-items-start">

                                        <i class="fa-solid fa-business-time text-primary fs-4"></i>

                                        <div>
                                            <strong>Horário</strong>
                                            <p class="text-secondary mb-0">
                                                <?= $horaEntrada ?> às <?= $horaSaida ?>
                                            </p>
                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-4">

                                    <div class="d-flex gap-3 align-items-start">

                                        <i class="fa-solid fa-mug-hot text-primary fs-4"></i>

                                        <div>
                                            <strong>Intervalo</strong>
                                            <p class="text-secondary mb-0">
                                                1h
                                            </p>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
const fotoInput = document.getElementById('fotoInput');

fotoInput.addEventListener('change', function(){
    if (this.files.length > 0) {
        this.closest('form').submit();
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>