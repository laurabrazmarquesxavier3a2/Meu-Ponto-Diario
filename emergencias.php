<?php
$emergencias = [
    [
        "tipo" => "Risco",
        "titulo" => "Vazamento de água no 3º andar",
        "descricao" => "Vazamento significativo próximo à sala de reuniões.",
        "responsavel" => "João Santos",
        "local" => "3º Andar - Sala 302",
        "prioridade" => "Alta",
        "status" => "Em andamento",
        "data" => "09/03/2026 10:30",
        "telefone" => "(11) 98765-4322"
    ],
    [
        "tipo" => "Saúde",
        "titulo" => "Funcionário com mal-estar",
        "descricao" => "Colaboradora relatou tontura e náusea.",
        "responsavel" => "Ana Costa",
        "local" => "2º Andar - Marketing",
        "prioridade" => "Crítica",
        "status" => "Em andamento",
        "data" => "09/03/2026 09:15",
        "telefone" => "(11) 98765-4323"
    ],
    [
        "tipo" => "Segurança",
        "titulo" => "Acesso não autorizado",
        "descricao" => "Pessoa desconhecida no estacionamento.",
        "responsavel" => "Segurança - Carlos",
        "local" => "Subsolo",
        "prioridade" => "Alta",
        "status" => "Resolvido",
        "data" => "08/03/2026 16:45"
    ]
];

$abertas = 0;
$andamento = 0;
$criticas = 0;

foreach ($emergencias as $e) {
    if ($e['status'] == "Aberto") $abertas++;
    if ($e['status'] == "Em andamento") $andamento++;
    if ($e['prioridade'] == "Crítica") $criticas++;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Emergências</title>

<link rel="stylesheet" href="css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <h1 class="fw-bold">Emergências e Riscos</h1>
    <h5 class="text-muted mb-4">Reporte e gerenciamento de emergências</h5>

    <!-- BOTÃO -->
    <button class="btn btn-danger mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Reportar Emergência
    </button>

    <!-- CARDS (PADRÃO IGUAL AO PONTO) -->
    <div class="row g-4 mb-4">

        <div class="col-12 col-md-3">
            <div class="card card-dashboard p-3">
                <h5>Emergências abertas</h5>
                <h1 class="fw-bold d-flex justify-content-between">
                    <?= $abertas ?>
                    <i class="bi bi-exclamation-circle text-danger"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card card-dashboard p-3">
                <h5>Em andamento</h5>
                <h1 class="fw-bold d-flex justify-content-between">
                    <?= $andamento ?>
                    <i class="bi bi-clock text-warning"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card card-dashboard p-3">
                <h5>Prioridadde crítica</h5>
                <h1 class="fw-bold d-flex justify-content-between">
                    <?= $criticas ?>
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                </h1>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card card-dashboard p-3">
                <h5>Total de reportes</h5>
                <h1 class="fw-bold d-flex justify-content-between">
                    <?= count($emergencias) ?>
                    <i class="bi bi-exclamation-triangle text-primary"></i>
                </h1>
            </div>
        </div>

    </div>

    <!-- LISTA -->
    <?php foreach ($emergencias as $e): ?>

        <div class="card card-dashboard mb-3 p-3">

            <div class="d-flex justify-content-between">

                <div>

                    <!-- BADGES -->
                    <div class="mb-2">
                        <span class="badge bg-primary"><?= $e['tipo'] ?></span>

                        <?php if ($e['prioridade'] == "Crítica"): ?>
                            <span class="badge bg-danger"><?= $e['prioridade'] ?></span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark"><?= $e['prioridade'] ?></span>
                        <?php endif; ?>

                        <?php if ($e['status'] == "Resolvido"): ?>
                            <span class="badge bg-success"><?= $e['status'] ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= $e['status'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- TEXTO -->
                    <h5 class="fw-bold"><?= $e['titulo'] ?></h5>
                    <p class="text-muted"><?= $e['descricao'] ?></p>

                    <!-- INFOS -->
                    <small class="d-block">
                        <i class="bi bi-geo-alt me-1"></i> <?= $e['local'] ?>
                    </small>

                    <small class="d-block">
                        <i class="bi bi-clock me-1"></i> <?= $e['data'] ?>
                    </small>

                    <?php if (isset($e['telefone'])): ?>
                        <small class="d-block">
                            <i class="bi bi-telephone me-1"></i> <?= $e['telefone'] ?>
                        </small>
                    <?php endif; ?>

                    <small class="text-muted">
                        Reportado por: <strong><?= $e['responsavel'] ?></strong>
                    </small>

                </div>

                <!-- AÇÕES -->
                <div class="d-flex flex-column gap-2">
                    <?php if ($e['status'] != "Resolvido"): ?>
                        <button class="btn btn-sm btn-success">
                            <i class="bi bi-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger">
                            <i class="bi bi-x"></i>
                        </button>
                    <?php endif; ?>
                </div>

            </div>

        </div>

    <?php endforeach; ?>

    <!-- CONTATOS -->
    <div class="card card-dashboard p-4 mt-4 border-danger">

        <h5 class="fw-bold text-danger mb-3">
            <i class="bi bi-telephone me-2"></i> Contatos de Emergência
        </h5>

        <div class="row">

            <div class="col-md-4 mb-2">
                <strong>SAMU</strong><br>192
            </div>

            <div class="col-md-4 mb-2">
                <strong>Bombeiros</strong><br>193
            </div>

            <div class="col-md-4 mb-2">
                <strong>Polícia</strong><br>190
            </div>

            <div class="col-md-4 mb-2">
                <strong>Segurança</strong><br>(11) 3000-0000
            </div>

            <div class="col-md-4 mb-2">
                <strong>Enfermaria</strong><br>Ramal 100
            </div>

            <div class="col-md-4 mb-2">
                <strong>RH</strong><br>(11) 98765-1234
            </div>

        </div>

    </div>

</div>

</body>
</html>