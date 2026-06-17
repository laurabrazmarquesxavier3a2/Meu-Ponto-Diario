<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';

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

    $telefone = trim($_POST['telefone'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $status = trim($_POST['status'] ?? 'ativo');

    $cargo = trim($_POST['cargo'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $horario = trim($_POST['horario'] ?? '08:00');

    $escala = trim($_POST['escala'] ?? '');
    $supervisor = trim($_POST['supervisor'] ?? '');

    if ($nome == '' || $email == '' || $senha == '') {

        $erro = "Preencha todos os campos obrigatórios.";
    } elseif (
        strlen($senha) < 8 ||
        !preg_match('/[A-Z]/', $senha) ||
        !preg_match('/[a-z]/', $senha) ||
        !preg_match('/[0-9]/', $senha)
    ) {

        $erro = "A senha deve ter pelo menos 8 caracteres, uma letra maiúscula, uma letra minúscula e um número.";
    } else {

        $statusPermitidos = ['ativo', 'inativo', 'ferias', 'licenca', 'afastado'];

        if (!in_array($status, $statusPermitidos)) {
            $status = 'ativo';
        }

        $verifica = $con->prepare("
            SELECT id_usuario
            FROM usuarios
            WHERE email = ?
            LIMIT 1
        ");

        if (!$verifica) {
            die("ERRO SQL VERIFICA USUÁRIO: " . $con->error);
        }

        $verifica->bind_param("s", $email);
        $verifica->execute();
        $resultado = $verifica->get_result();

        if ($resultado->num_rows > 0) {

            $erro = "Este e-mail já está cadastrado.";
        } else {

            mysqli_begin_transaction($con);

            try {

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

                if (!$stmtFunc) {
                    throw new Exception("ERRO SQL FUNCIONÁRIO: " . $con->error);
                }

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

                if (!$stmtFunc->execute()) {
                    throw new Exception("ERRO EXEC FUNCIONÁRIO: " . $stmtFunc->error);
                }

                $idFuncionario = $con->insert_id;

                $fotoBanco = null;

                if (!empty($_FILES['foto']['name'])) {

                    if (!is_dir("uploads/perfis")) {
                        mkdir("uploads/perfis", 0777, true);
                    }

                    $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                    $extensoesPermitidas = ['jpg', 'jpeg', 'png'];

                    if (in_array($extensao, $extensoesPermitidas)) {

                        $nomeFoto = uniqid("perfil_") . "." . $extensao;
                        $destino = "uploads/perfis/" . $nomeFoto;

                        if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                            $fotoBanco = $destino;
                        }
                    } else {
                        throw new Exception("Formato de foto inválido. Use JPG, JPEG ou PNG.");
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
                        foto,
                        telefone,
                        cidade,
                        cargo,
                        departamento,
                        id_empresa
                    )
                    VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )
                ");

                if (!$stmtUser) {
                    throw new Exception("ERRO SQL USUÁRIO: " . $con->error);
                }

                $stmtUser->bind_param(
                    "issssssssssi",
                    $idFuncionario,
                    $nome,
                    $email,
                    $senhaHash,
                    $tipo,
                    $status,
                    $fotoBanco,
                    $telefone,
                    $cidade,
                    $cargo,
                    $departamento,
                    $idEmpresa
                );

                if (!$stmtUser->execute()) {
                    throw new Exception("ERRO EXEC USUÁRIO: " . $stmtUser->error);
                }

                mysqli_commit($con);

                $mensagem = "Usuário cadastrado com sucesso.";
            } catch (Exception $e) {

                mysqli_rollback($con);
                $erro = $e->getMessage();
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <style>
        .form-card {
            border-radius: 18px;
        }

        .icon-circle {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #0d6efd;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }

        .preview-wrapper {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
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
                        Cadastrar Usuário
                    </h1>

                    <h5 class="text-muted">
                        Cadastre colaboradores e membros do RH vinculados à empresa.
                    </h5>
                </div>

                <div class="mt-3 mt-lg-0">
                    <span class="badge bg-primary fs-6 px-3 py-2">
                        <i class="bi bi-building me-1"></i>
                        Empresa #<?= $idEmpresa ?>
                    </span>
                </div>

            </div>

            <?php if ($mensagem): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($mensagem) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($erro) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="card border-0 shadow-sm form-card">

                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-person-plus-fill me-2"></i>
                            Novo cadastro
                        </h5>
                    </div>

                    <div class="card-body p-4">

                        <div class="form-section-title">
                            Dados pessoais e login
                        </div>

                        <div class="row g-3 mb-4">

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Nome completo
                                </label>

                                <input
                                    type="text"
                                    name="nome"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    E-mail de login
                                </label>

                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    required>

                                <small class="text-muted">
                                    Esse e-mail será usado para entrar no sistema.
                                </small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Senha
                                </label>

                                <input
                                    type="password"
                                    name="senha"
                                    class="form-control"
                                    minlength="8"
                                    required>

                                <small class="text-muted">
                                    A senha deve conter pelo menos 8 caracteres, uma letra maiúscula, uma letra minúscula e um número.
                                </small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Telefone
                                </label>

                                <input
                                    type="text"
                                    name="telefone"
                                    class="form-control"
                                    placeholder="(11) 99999-9999">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Cidade
                                </label>

                                <input
                                    type="text"
                                    name="cidade"
                                    class="form-control"
                                    placeholder="São Paulo">
                            </div>

                        </div>

                        <div class="form-section-title">
                            Dados profissionais
                        </div>

                        <div class="row g-3 mb-4">

                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Cargo
                                </label>

                                <input
                                    type="text"
                                    name="cargo"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Departamento
                                </label>

                                <input
                                    type="text"
                                    name="departamento"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    Supervisor
                                </label>

                                <input
                                    type="text"
                                    name="supervisor"
                                    class="form-control">
                            </div>

                        </div>

                        <div class="form-section-title">
                            Jornada e acesso
                        </div>

                        <div class="row g-3 mb-4">

                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    Horário padrão
                                </label>

                                <input
                                    type="time"
                                    name="horario"
                                    class="form-control"
                                    value="08:00">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    Escala
                                </label>

                                <input
                                    type="text"
                                    name="escala"
                                    class="form-control"
                                    placeholder="5x2, 6x1">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    Tipo de acesso
                                </label>

                                <select
                                    name="tipo"
                                    id="tipoUsuario"
                                    class="form-select"
                                    required>
                                    <option value="funcionario">
                                        Funcionário
                                    </option>

                                    <option value="rh">
                                        RH
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    Status
                                </label>

                                <select
                                    name="status"
                                    class="form-select"
                                    required>
                                    <option value="ativo">
                                        Ativo
                                    </option>

                                    <option value="inativo">
                                        Inativo
                                    </option>

                                    <option value="ferias">
                                        Férias
                                    </option>

                                    <option value="licenca">
                                        Licença
                                    </option>

                                    <option value="afastado">
                                        Afastado
                                    </option>
                                </select>
                            </div>

                        </div>

                        <div id="rhCampos" class="alert alert-info d-none mb-4">
                            <i class="bi bi-shield-check me-2"></i>
                            Usuários RH terão acesso às telas administrativas da empresa.
                        </div>

                        <div class="form-section-title">
                            Foto de perfil
                        </div>

                        <div class="row g-3 align-items-center">

                            <div class="col-md-2 text-center">
                                <div class="preview-wrapper mx-auto">
                                    <img id="previewFoto" src="">

                                    <i id="iconeFoto" class="bi bi-person fs-1 text-muted"></i>
                                </div>
                            </div>

                            <div class="col-md-10">
                                <label class="form-label fw-bold">
                                    Selecionar imagem
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    name="foto"
                                    id="foto"
                                    accept=".jpg,.jpeg,.png">

                                <small class="text-muted">
                                    Formatos aceitos: JPG, JPEG ou PNG.
                                </small>
                            </div>

                        </div>

                    </div>

                    <div class="card-footer bg-white p-4">

                        <div class="d-flex justify-content-end gap-3">

                            <a href="funcionarios.php" class="btn btn-secondary btn-lg">
                                Cancelar
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-check-lg me-1"></i>
                                Salvar usuário
                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const tipoUsuario = document.getElementById('tipoUsuario');
        const rhCampos = document.getElementById('rhCampos');

        tipoUsuario.addEventListener('change', function() {

            if (this.value === 'rh') {
                rhCampos.classList.remove('d-none');
            } else {
                rhCampos.classList.add('d-none');
            }

        });

        const foto = document.getElementById('foto');
        const previewFoto = document.getElementById('previewFoto');
        const iconeFoto = document.getElementById('iconeFoto');

        foto.addEventListener('change', function() {

            const arquivo = this.files[0];

            if (arquivo) {

                previewFoto.src = URL.createObjectURL(arquivo);
                previewFoto.style.display = 'block';
                iconeFoto.style.display = 'none';

            } else {

                previewFoto.src = '';
                previewFoto.style.display = 'none';
                iconeFoto.style.display = 'block';

            }

        });
    </script>

    <script src="js/theme.js"></script>
    <script src="js/translate.js"></script>

</body>

</html>