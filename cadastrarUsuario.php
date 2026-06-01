 <?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth.php';
require_once 'config/database.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

 $tipo = $_POST['tipo'] ?? '';

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

$cargo = trim($_POST['cargo'] ?? '');
$departamento = trim($_POST['departamento'] ?? '');
$horario = trim($_POST['horario'] ?? '');

    $escala = trim($_POST['escala'] ?? '');
    $supervisor = trim($_POST['supervisor'] ?? '');

    $verifica = $con->prepare(
        "SELECT id_usuario
         FROM usuarios
         WHERE email = ?"
    );

    $verifica->bind_param("s", $email);
    $verifica->execute();

    $resultado = $verifica->get_result();

    if ($resultado->num_rows > 0) {

        echo "<script>
                alert('Este e-mail já está cadastrado.');
              </script>";

    } else {

        $stmtFunc = $con->prepare(
            "INSERT INTO funcionarios
            (
                nome,
                cargo,
                departamento,
                horario_padrao,
                escala,
                supervisor
            )
            VALUES
            (?, ?, ?, ?, ?, ?)"
        );

        $stmtFunc->bind_param(
            "ssssss",
            $nome,
            $cargo,
            $departamento,
            $horario,
            $escala,
            $supervisor
        );

        $stmtFunc->execute();

        $idFuncionario = $con->insert_id;

        $senhaHash = password_hash(
            $senha,
            PASSWORD_DEFAULT
        );

        $stmtUser = $con->prepare(
            "INSERT INTO usuarios
            (
                id_funcionario,
                nome,
                email,
                senha,
                tipo,
                cargo,
                departamento
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmtUser->bind_param(
            "issssss",
            $idFuncionario,
            $nome,
            $email,
            $senhaHash,
            $tipo,
            $cargo,
            $departamento
        );

        if ($stmtUser->execute()) {

             $mensagem = '
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>Sucesso!</strong> Usuário cadastrado com sucesso.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>';

        } else {

            echo "<script>
                    alert('Erro ao cadastrar usuário.');
                  </script>";
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

   <?php echo $mensagem; ?>

    <h1 class="fw-bold">
        Cadastrar Usuário
    </h1>

    <h5 class="text-muted mb-4">
        Cadastre colaboradores e membros do RH
    </h5>

    <div class="container-fluid">

    <form method="POST">

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
                      name="tipo">

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

                    <input type="text" class="form-control">

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        E-mail
                    </label>

                     <input
                            type="email"
                            class="form-control"
                            name="email"
                               required>

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Senha
                    </label>

                     <input
                       type="password"
                       class="form-control"
                       name="senha"
                       required>

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Cargo
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        name="cargo"
                        required>

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Setor
                    </label>

                     <input
                        type="text"
                         class="form-control"
                         name="departamento"
                          required>

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
                       required>
                </div>

            </div>

        </div>

        <!-- COLABORADOR -->

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
                        placeholder="5x2, 6x1">

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Supervisor
                    </label>

                    <input type="text" 
                    class="form-control"
                    name="supervisor">

                </div>

            </div>

        </div>

        <!-- RH -->

        <div class="card card-dashboard p-4 mt-4 d-none" id="rhCampos">

            <h4 class="fw-bold mb-4">

                <i class="bi bi-shield-check me-2"></i>
                Informações do RH

            </h4>

            <div class="row g-4">

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Nível de Acesso
                    </label>

                    <select class="form-select">

                        <option>Operacional</option>
                        <option>Gestor</option>
                        <option>Administrador</option>

                    </select>

                </div>

            </div>

        </div>

        <!-- FOTO -->

        <div class="card card-dashboard p-4 mt-4">

            <h4 class="fw-bold mb-4">

                <i class="bi bi-image me-2"></i>
                Foto de Perfil

            </h4>

            <div class="row align-items-center">

                <div class="col-md-2 text-center">

                    <div
                        class="rounded-circle bg-light border mx-auto d-flex align-items-center justify-content-center"
                        style="width:120px;height:120px;">

                        <i class="bi bi-person fs-1"></i>

                    </div>

                </div>

                <div class="col-md-10">

                    <input
                        type="file"
                        class="form-control"
                        disabled>
                </div>

            </div>

        </div>

        <!-- AÇÕES -->

        <div class="card card-dashboard p-4 mt-4">

            <div class="d-flex justify-content-end gap-3">

                <a href="funcionarios.php"
                   class="btn btn-outline-secondary btn-lg">

                    Cancelar

                </a>

                <button
                    type="submit"
                    class="btn btn-primary btn-lg px-5">

                    <i class="bi bi-floppy me-2"></i>
                    Salvar Usuário

                </button>

            </div>

        </div>

    </div>

</div>

</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

const tipoUsuario = document.getElementById('tipoUsuario');
const colaboradorCampos = document.getElementById('colaboradorCampos');
const rhCampos = document.getElementById('rhCampos');

tipoUsuario.addEventListener('change', function(){

    if(this.value === 'rh'){

        rhCampos.classList.remove('d-none');
        colaboradorCampos.classList.add('d-none');

    }else{

        colaboradorCampos.classList.remove('d-none');
        rhCampos.classList.add('d-none');

    }

});

</script>

</body>
</html>