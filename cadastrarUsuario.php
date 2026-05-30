<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth.php';
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

    <h1 class="fw-bold">
        Cadastrar Usuário
    </h1>

    <h5 class="text-muted mb-4">
        Cadastre colaboradores e membros do RH
    </h5>

    <div class="container-fluid">

        <!-- DADOS PRINCIPAIS -->

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

                    <select class="form-select" id="tipoUsuario">

                        <option value="colaborador">
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

                    <input type="email" class="form-control">

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Senha
                    </label>

                    <input type="password" class="form-control">

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Cargo
                    </label>

                    <input type="text" class="form-control">

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Setor
                    </label>

                    <input type="text" class="form-control">

                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Horário
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        placeholder="08:00 às 17:00">

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
                        placeholder="5x2, 6x1">

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-semibold">
                        Supervisor
                    </label>

                    <input type="text" class="form-control">

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
                        class="form-control">

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
                    class="btn btn-primary btn-lg px-5">

                    <i class="bi bi-floppy me-2"></i>
                    Salvar Usuário

                </button>

            </div>

        </div>

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

    }else{

        colaboradorCampos.classList.remove('d-none');
        rhCampos.classList.add('d-none');

    }

});

</script>

</body>
</html>