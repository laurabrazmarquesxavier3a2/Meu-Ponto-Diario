<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth.php';
require_once 'config/database.php';

$mensagem = '';
$erro = '';

$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $tipo = $_POST['tipo'] ?? 'funcionario';

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $cargo = trim($_POST['cargo'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $horario = trim($_POST['horario'] ?? '08:00');

    $escala = trim($_POST['escala'] ?? '');
    $supervisor = trim($_POST['supervisor'] ?? '');

    if ($nome == '' || $email == '' || $senha == '') {

        $erro = "Preencha todos os campos obrigatórios.";

    } else {

        $verifica = $con->prepare("
            SELECT id_usuario
            FROM usuarios
            WHERE email = ?
            LIMIT 1
        ");

        $verifica->bind_param("s", $email);
        $verifica->execute();
        $resultado = $verifica->get_result();

        if ($resultado->num_rows > 0) {

            $erro = "Este e-mail já está cadastrado.";

        } else {

            $stmtFunc = $con->prepare("
                INSERT INTO funcionarios (
                    nome,
                    cargo,
                    departamento,
                    horario_padrao,
                    escala,
                    supervisor,
                    id_empresa
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmtFunc->bind_param(
                "ssssssi",
                $nome,
                $cargo,
                $departamento,
                $horario,
                $escala,
                $supervisor,
                $idEmpresa
            );

            if ($stmtFunc->execute()) {

                $idFuncionario = $con->insert_id;

                $fotoBanco = null;

                if (!empty($_FILES['foto']['name'])) {

                    if (!is_dir("uploads/perfis")) {
                        mkdir("uploads/perfis", 0777, true);
                    }

                    $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                    $nomeFoto = uniqid("perfil_") . "." . $extensao;
                    $destino = "uploads/perfis/" . $nomeFoto;

                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                        $fotoBanco = $destino;
                    }
                }

                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

                $stmtUser = $con->prepare("
                    INSERT INTO usuarios (
                        id_funcionario,
                        nome,
                        email,
                        senha,
                        tipo,
                        status,
                        telefone,
                        cidade,
                        foto,
                        cargo,
                        departamento,
                        id_empresa
                    )
                    VALUES (
                        ?, ?, ?, ?, ?, 'ativo',
                        NULL, NULL, ?, ?, ?, ?
                    )
                ");

                $stmtUser->bind_param(
                    "isssssssi",
                    $idFuncionario,
                    $nome,
                    $email,
                    $senhaHash,
                    $tipo,
                    $fotoBanco,
                    $cargo,
                    $departamento,
                    $idEmpresa
                );

                if ($stmtUser->execute()) {

                    $mensagem = "Usuário cadastrado com sucesso.";

                } else {

                    $erro = "Erro ao cadastrar usuário: " . $stmtUser->error;
                }

            } else {

                $erro = "Erro ao cadastrar funcionário: " . $stmtFunc->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Cadastrar Usuário</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <?php if($mensagem): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Sucesso!</strong> <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($erro): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Erro!</strong> <?= $erro ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h1 class="fw-bold">
        Cadastrar Usuário
    </h1>

    <h5 class="text-muted mb-4">
        Cadastre colaboradores e membros do RH
    </h5>

    <div class="container-fluid">

        <form method="POST" enctype="multipart/form-data">

            <div class="card card-dashboard p-4">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <h4 class="fw-bold mb-0">
                        Novo Cadastro
                    </h4>

                    <a href="funcionarios.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Voltar
                    </a>

                </div>

                <div class="row g-4">

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Tipo de Usuário
                        </label>

                        <select 
                            class="form-select"
                            id="tipoUsuario"
                            name="tipo"
                            required
                        >

                            <option value="funcionario">
                                Colaborador
                            </option>

                            <option value="rh">
                                RH
                            </option>

                        </select>

                    </div>

                    <div class="col-md-8">

                        <label class="form-label fw-semibold">
                            Nome Completo
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="nome"
                            required
                        >

                    </div>

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            E-mail
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            name="email"
                            required
                        >

                    </div>

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Senha
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            name="senha"
                            required
                        >

                    </div>

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Cargo
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="cargo"
                            required
                        >

                    </div>

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Setor
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="departamento"
                            required
                        >

                    </div>

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Horário
                        </label>

                        <input
                            type="time"
                            class="form-control"
                            name="horario"
                            value="08:00"
                            required
                        >

                    </div>

                </div>

            </div>

            <div class="card card-dashboard p-4 mt-4" id="colaboradorCampos">

                <h4 class="fw-bold mb-4">
                    <i class="bi bi-person-badge me-2"></i>
                    Informações do Colaborador
                </h4>

                <div class="row g-4">

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Escala
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="escala"
                            placeholder="5x2, 6x1"
                        >

                    </div>

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Supervisor
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="supervisor"
                        >

                    </div>

                </div>

            </div>

            <div class="card card-dashboard p-4 mt-4 d-none" id="rhCampos">

                <h4 class="fw-bold mb-4">
                    <i class="bi bi-shield-check me-2"></i>
                    Informações do RH
                </h4>

                <div class="alert alert-info mb-0">
                    Usuários RH terão acesso às telas administrativas da empresa.
                </div>

            </div>

            <div class="card card-dashboard p-4 mt-4">

                <h4 class="fw-bold mb-4">
                    <i class="bi bi-image me-2"></i>
                    Foto de Perfil
                </h4>

                <div class="row align-items-center">

                    <div class="col-md-2 text-center">

                        <div
                            class="rounded-circle bg-light border mx-auto d-flex align-items-center justify-content-center overflow-hidden"
                            style="width:120px;height:120px;"
                        >

                            <img
                                id="previewFoto"
                                src=""
                                style="width:100%;height:100%;object-fit:cover;display:none;"
                            >

                            <i id="iconeFoto" class="bi bi-person fs-1"></i>

                        </div>

                    </div>

                    <div class="col-md-10">

                        <input
                            type="file"
                            class="form-control"
                            name="foto"
                            id="foto"
                            accept=".jpg,.jpeg,.png"
                        >

                    </div>

                </div>

            </div>

            <div class="card card-dashboard p-4 mt-4">

                <div class="d-flex justify-content-end gap-3">

                    <a href="funcionarios.php"
                       class="btn btn-outline-secondary btn-lg">
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary btn-lg px-5"
                    >
                        <i class="bi bi-floppy me-2"></i>
                        Salvar Usuário
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

const tipoUsuario = document.getElementById('tipoUsuario');
const colaboradorCampos = document.getElementById('colaboradorCampos');
const rhCampos = document.getElementById('rhCampos');

tipoUsuario.addEventListener('change', function(){

    if(this.value === 'rh'){

        rhCampos.classList.remove('d-none');
        colaboradorCampos.classList.add('d-none');

    } else {

        colaboradorCampos.classList.remove('d-none');
        rhCampos.classList.add('d-none');

    }

});

const foto = document.getElementById('foto');
const previewFoto = document.getElementById('previewFoto');
const iconeFoto = document.getElementById('iconeFoto');

foto.addEventListener('change', function(){

    const arquivo = this.files[0];

    if(arquivo){

        previewFoto.src = URL.createObjectURL(arquivo);
        previewFoto.style.display = 'block';
        iconeFoto.style.display = 'none';

    }

});

</script>

</body>
</html>