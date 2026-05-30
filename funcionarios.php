<?php require_once 'auth.php'; ?>

<?php

$funcionarios = [

    [
        "nome"=>"Maria Silva",
        "cargo"=>"Gerente de Projetos",
        "dep"=>"TI",
        "email"=>"maria.silva@empresa.com",
        "tel"=>"(11) 98765-4321",
        "status"=>"Ativo"
    ],

    [
        "nome"=>"João Santos",
        "cargo"=>"Desenvolvedor Sênior",
        "dep"=>"TI",
        "email"=>"joao.santos@empresa.com",
        "tel"=>"(11) 98765-4322",
        "status"=>"Ativo"
    ],

    [
        "nome"=>"Ana Costa",
        "cargo"=>"Analista de RH",
        "dep"=>"RH",
        "email"=>"ana.costa@empresa.com",
        "tel"=>"(11) 98765-4323",
        "status"=>"Ativo"
    ],

    [
        "nome"=>"Pedro Oliveira",
        "cargo"=>"Designer UX/UI",
        "dep"=>"Design",
        "email"=>"pedro.oliveira@empresa.com",
        "tel"=>"(11) 98765-4324",
        "status"=>"Férias"
    ],

    [
        "nome"=>"Lucas Ferreira",
        "cargo"=>"Contador",
        "dep"=>"Financeiro",
        "email"=>"lucas.ferreira@empresa.com",
        "tel"=>"(11) 98765-4325",
        "status"=>"Ativo"
    ],

    [
        "nome"=>"Carla Mendes",
        "cargo"=>"Analista de Marketing",
        "dep"=>"Marketing",
        "email"=>"carla.mendes@empresa.com",
        "tel"=>"(11) 98765-4326",
        "status"=>"Licença"
    ]

];

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

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid">

<div class="mb-4">

    <h1 class="fw-bold mb-1">
        Funcionários
    </h1>

    <p class="text-muted mb-0">
        Gerencie colaboradores e RH da empresa
    </p>

</div>

<div class="card shadow-sm border-0 mb-4">

    <div class="card-body">

        <div class="input-group">

            <span class="input-group-text bg-white">
                <i class="bi bi-search"></i>
            </span>

            <input
                type="text"
                class="form-control"
                placeholder="Pesquisar por nome ou cargo">

        </div>

    </div>

</div>

<div class="row g-4">

<?php foreach($funcionarios as $f): ?>

<div class="col-lg-4 col-md-6">

<div class="card shadow-sm border-0 h-100">

<div class="card-body">

<div class="d-flex align-items-center mb-3">

    <div
        class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
        style="width:60px;height:60px;font-size:22px;font-weight:bold;">

        <?= substr($f['nome'],0,1) ?>

    </div>

    <div class="flex-grow-1">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                <?= $f['nome'] ?>
            </h5>

            <select class="form-select form-select-sm w-auto">

                <option <?= $f['status']=="Ativo" ? "selected" : "" ?>>
                    Ativo
                </option>

                <option <?= $f['status']=="Férias" ? "selected" : "" ?>>
                    Férias
                </option>

                <option <?= $f['status']=="Licença" ? "selected" : "" ?>>
                    Licença
                </option>

                <option <?= $f['status']=="Desligado" ? "selected" : "" ?>>
                    Desligado
                </option>

                <option <?= $f['status']=="Afastado" ? "selected" : "" ?>>
                    Afastado
                </option>

            </select>

        </div>

        <small class="text-muted">
            <?= $f['cargo'] ?>
        </small>

    </div>

</div>

<div class="mb-2">

    <span class="badge bg-light text-dark border">
        <?= $f['dep'] ?>
    </span>

</div>

<hr>

<div class="small text-muted">

    <div class="mb-2">
        <i class="bi bi-envelope me-2"></i>
        <?= $f['email'] ?>
    </div>

    <div>
        <i class="bi bi-telephone me-2"></i>
        <?= $f['tel'] ?>
    </div>

</div>

</div>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>