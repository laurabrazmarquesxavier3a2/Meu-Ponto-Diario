<?php
$funcionarios = [
    ["nome"=>"Maria Silva","cargo"=>"Gerente de Projetos","dep"=>"TI","email"=>"maria.silva@empresa.com","tel"=>"(11) 98765-4321","local"=>"São Paulo, SP","status"=>"Ativo"],
    ["nome"=>"João Santos","cargo"=>"Desenvolvedor Senior","dep"=>"TI","email"=>"joao.santos@empresa.com","tel"=>"(11) 98765-4322","local"=>"São Paulo, SP","status"=>"Ativo"],
    ["nome"=>"Ana Costa","cargo"=>"Analista de RH","dep"=>"RH","email"=>"ana.costa@empresa.com","tel"=>"(11) 98765-4323","local"=>"Rio de Janeiro, RJ","status"=>"Ativo"],
    ["nome"=>"Pedro Oliveira","cargo"=>"Designer UX/UI","dep"=>"Design","email"=>"pedro.oliveira@empresa.com","tel"=>"(11) 98765-4324","local"=>"São Paulo, SP","status"=>"Ferias"],
    ["nome"=>"Lucas Ferreira","cargo"=>"Contador","dep"=>"Financeiro","email"=>"lucas.ferreira@empresa.com","tel"=>"(11) 98765-4325","local"=>"Belo Horizonte, MG","status"=>"Ativo"],
    ["nome"=>"Carla Mendes","cargo"=>"Analista de Marketing","dep"=>"Marketing","email"=>"carla.mendes@empresa.com","tel"=>"(11) 98765-4326","local"=>"São Paulo, SP","status"=>"Licenca"],
];

/* CONTADORES (igual emergências) */
$ativos = 0;
$ferias = 0;
$licenca = 0;

foreach ($funcionarios as $f) {
    if ($f['status'] == "Ativo") $ativos++;
    if ($f['status'] == "Ferias") $ferias++;
    if ($f['status'] == "Licenca") $licenca++;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Funcionários</title>

<link rel="stylesheet" href="css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
.card-dashboard {
    border-radius: 16px;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.avatar {
    width: 50px;
    height: 50px;
    background: #e0e7ff;
    color: #4338ca;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
.status {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
}
.ativo { background:#dcfce7; color:#166534; }
.ferias { background:#fef3c7; color:#92400e; }
.licenca { background:#fee2e2; color:#991b1b; }
.input-search {
    border-radius: 12px;
    padding-left: 40px;
}
.card-dashboard:hover {
    transform: translateY(-3px);
    transition: 0.2s;
}
</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <h1 class="fw-bold">Funcionários</h1>
    <h5 class="text-muted mb-4">Gerencie informações dos colaboradores</h5>
    
    <!-- FILTROS (mantido igual) -->
    <div class="card card-dashboard p-3 mb-4">
        <div class="row g-3 align-items-center">

            <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" class="form-control input-search" placeholder="Buscar por nome ou cargo...">
            </div>

        </div>
    </div>

    <!-- GRID (mesmo visual) -->
    <div class="row g-4">

        <?php foreach($funcionarios as $f): ?>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-dashboard p-4">

                <div class="d-flex justify-content-between mb-3">

                    <div class="avatar">
                        <?= substr($f['nome'],0,1) ?>
                    </div>

                    <!-- STATUS COM LÓGICA PADRÃO -->
                    <?php if ($f['status'] == "Ativo"): ?>
                        <span class="status ativo">Ativo</span>
                    <?php elseif ($f['status'] == "Ferias"): ?>
                        <span class="status ferias">Férias</span>
                    <?php else: ?>
                        <span class="status licenca">Licença</span>
                    <?php endif; ?>

                </div>

                <h5 class="fw-bold mb-1"><?= $f['nome'] ?></h5>
                <small class="text-muted"><?= $f['cargo'] ?></small><br>
                <small class="text-primary"><?= $f['dep'] ?></small>

                <div class="mt-3 small text-muted">
                    <div><i class="bi bi-envelope me-2"></i><?= $f['email'] ?></div>
                    <div><i class="bi bi-telephone me-2"></i><?= $f['tel'] ?></div>
                    <div><i class="bi bi-geo-alt me-2"></i><?= $f['local'] ?></div>
                </div>

            </div>
        </div>

        <?php endforeach; ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>