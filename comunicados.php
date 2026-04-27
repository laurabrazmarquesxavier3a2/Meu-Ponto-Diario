<?php
$comunicados = [
    [
        "titulo" => "Nova Política de Home Office",
        "conteudo" => "A partir de abril, todos os funcionários poderão trabalhar em regime híbrido...",
        "categoria" => "Política",
        "fixado" => true,
        "autor" => "Juliana Pereira - RH",
        "data" => "08/03/2026",
        "publico" => "Todos os funcionários"
    ],
    [
        "titulo" => "Festa de Aniversariantes de Março",
        "conteudo" => "Celebraremos os aniversariantes do mês na próxima sexta-feira...",
        "categoria" => "Comemoração",
        "fixado" => true,
        "autor" => "Comitê de Eventos",
        "data" => "07/03/2026",
        "publico" => "Todos os funcionários"
    ],
    [
        "titulo" => "Workshop de Desenvolvimento Pessoal",
        "conteudo" => "Inscrições abertas para o workshop de produtividade...",
        "categoria" => "Evento",
        "fixado" => false,
        "autor" => "T&D",
        "data" => "06/03/2026",
        "publico" => "Todos os funcionários"
    ],
    [
        "titulo" => "Manutenção no Sistema de Ponto",
        "conteudo" => "Sistema passará por manutenção no sábado...",
        "categoria" => "Urgente",
        "fixado" => false,
        "autor" => "TI",
        "data" => "05/03/2026",
        "publico" => "Todos os funcionários"
    ]
];

$fixados = array_filter($comunicados, fn($c) => $c['fixado']);
$normais = array_filter($comunicados, fn($c) => !$c['fixado']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Comunicados</title>

<link rel="stylesheet" href="css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>

/* CARDS PADRÃO */
.card-dashboard {
    border-radius: 14px;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* CARD FIXADO */
.card-fixado {
    background: #fef9e7;
    border-left: 5px solid #f59e0b;
}

/* BADGES SUAVES */
.badge-soft {
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
}

/* CORES */
.badge-politica { background: #e0e7ff; color: #3730a3; }
.badge-evento { background: #f3e8ff; color: #7e22ce; }
.badge-comemoracao { background: #ffe4e6; color: #be123c; }
.badge-urgente { background: #fee2e2; color: #b91c1c; }
.badge-geral { background: #e0f2fe; color: #0369a1; }

</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold">Comunicados</h1>
            <h5 class="text-muted mb-4">Avisos importantes para os funcionários</h5>
            <p class="text-muted"></p>
        </div>

        <button class="btn btn-primary px-4 py-2">
            <i class="bi bi-bell me-2"></i> Novo Comunicado
        </button>
    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="card card-dashboard p-3">
                <small>Total de Comunicados</small>
                <h2 class="fw-bold d-flex justify-content-between">
                    <?= count($comunicados) ?>
                    <i class="bi bi-bell"></i>
                </h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-dashboard p-3">
                <small>Fixados</small>
                <h2 class="fw-bold d-flex justify-content-between">
                    <?= count($fixados) ?>
                    <i class="bi bi-pin"></i>
                </h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-dashboard p-3">
                <small>Este mês</small>
                <h2 class="fw-bold d-flex justify-content-between">
                    6
                    <i class="bi bi-calendar"></i>
                </h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-dashboard p-3">
                <small>Alcance</small>
                <h2 class="fw-bold d-flex justify-content-between">
                    248
                    <i class="bi bi-people"></i>
                </h2>
            </div>
        </div>

    </div>

    <!-- FIXADOS -->
    <?php if(count($fixados) > 0): ?>
        <h5 class="fw-bold mb-3">
            <i class="bi bi-pin-angle me-2 text-warning"></i> Comunicados Fixados
        </h5>

        <?php foreach($fixados as $c): ?>

        <div class="card card-dashboard card-fixado p-4 mb-3">

            <div class="d-flex justify-content-between mb-2">
                <div>
                    <span class="badge-soft badge-politica"><?= $c['categoria'] ?></span>
                </div>
                <small class="text-muted"><?= $c['data'] ?></small>
            </div>

            <h5 class="fw-bold"><?= $c['titulo'] ?></h5>
            <p class="text-muted"><?= $c['conteudo'] ?></p>

            <div class="d-flex justify-content-between small text-muted">
                <span>Por <?= $c['autor'] ?></span>
                <span><i class="bi bi-people"></i> <?= $c['publico'] ?></span>
            </div>

        </div>

        <?php endforeach; ?>
    <?php endif; ?>

    <!-- TODOS -->
    <h5 class="fw-bold mt-4 mb-3">Todos os Comunicados</h5>

    <?php foreach($normais as $c): ?>

    <div class="card card-dashboard p-4 mb-3">

        <div class="d-flex justify-content-between mb-2">
            <span class="badge-soft badge-geral"><?= $c['categoria'] ?></span>
            <small class="text-muted"><?= $c['data'] ?></small>
        </div>

        <h5 class="fw-bold"><?= $c['titulo'] ?></h5>
        <p class="text-muted"><?= $c['conteudo'] ?></p>

        <div class="d-flex justify-content-between small text-muted">
            <span>Por <?= $c['autor'] ?></span>
            <span><i class="bi bi-people"></i> <?= $c['publico'] ?></span>
        </div>

    </div>

    <?php endforeach; ?>

</div>

</body>
</html>