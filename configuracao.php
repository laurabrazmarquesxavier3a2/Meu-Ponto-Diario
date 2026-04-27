<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Configurações</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <h1 class="fw-bold">Configurações</h1>
    <h5 class="text-muted mb-4">Gerencie as configurações do sistema RH</h5>

    <div class="row g-4">

        <!-- NOTIFICAÇÕES -->
        <div class="col-md-6">
            <div class="card card-dashboard p-4 h-100">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-bell text-primary me-2"></i> Notificações
                </h5>

                <?php
                $notificacoes = [
                    ["titulo" => "Novas solicitações", "desc" => "Receber alertas de novas solicitações", "checked" => true],
                    ["titulo" => "Aprovações pendentes", "desc" => "Lembrete diário de pendências", "checked" => true],
                    ["titulo" => "Alertas de emergência", "desc" => "Notificações urgentes de riscos", "checked" => true],
                    ["titulo" => "Resumo semanal", "desc" => "Relatório de atividades da semana", "checked" => false],
                ];
                ?>

                <?php foreach ($notificacoes as $n): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong><?= $n['titulo'] ?></strong><br>
                            <small class="text-muted"><?= $n['desc'] ?></small>
                        </div>
                        <input type="checkbox" class="form-check-input" <?= $n['checked'] ? 'checked' : '' ?>>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- SEGURANÇA -->
        <div class="col-md-6">
            <div class="card card-dashboard p-4 h-100">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-lock text-primary me-2"></i> Segurança
                </h5>

                <?php
                $seguranca = [
                    ["titulo" => "Alterar senha", "desc" => "Atualizar sua senha de acesso"],
                    ["titulo" => "Autenticação em 2 fatores", "desc" => "Ativar camada extra de segurança"],
                    ["titulo" => "Sessões ativas", "desc" => "Gerenciar dispositivos conectados"],
                    ["titulo" => "Histórico de acessos", "desc" => "Ver logs de login"],
                ];
                ?>

                <?php foreach ($seguranca as $s): ?>
                    <div class="border rounded p-3 mb-3 config-item">
                        <strong><?= $s['titulo'] ?></strong><br>
                        <small class="text-muted"><?= $s['desc'] ?></small>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- SISTEMA -->
        <div class="col-md-6">
            <div class="card card-dashboard p-4 h-100">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-database text-primary me-2"></i> Sistema
                </h5>

                <label class="fw-semibold mb-1">Fuso Horário</label>
                <select class="form-select mb-3">
                    <option>América/São_Paulo (UTC-03:00)</option>
                    <option>América/Manaus (UTC-04:00)</option>
                </select>

                <label class="fw-semibold mb-1">Formato de Data</label>
                <select class="form-select mb-3">
                    <option>DD/MM/YYYY</option>
                    <option>YYYY-MM-DD</option>
                </select>

                <label class="fw-semibold mb-1">Idioma</label>
                <select class="form-select">
                    <option>Português (Brasil)</option>
                    <option>English</option>
                </select>

            </div>
        </div>

        <!-- APARÊNCIA -->
        <div class="col-md-6">
            <div class="card card-dashboard p-4 h-100">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-palette text-primary me-2"></i> Aparência
                </h5>

                <label class="fw-semibold mb-2">Tema</label>

                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-outline-primary active w-100">Claro</button>
                    <button class="btn btn-outline-secondary w-100">Escuro</button>
                    <button class="btn btn-outline-secondary w-100">Auto</button>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Modo compacto</strong><br>
                        <small class="text-muted">Reduz espaçamentos da interface</small>
                    </div>
                    <input type="checkbox" class="form-check-input">
                </div>

            </div>
        </div>

        <!-- PERMISSÕES -->
        <div class="col-md-6">
            <div class="card card-dashboard p-4 h-100">

                <h5 class="fw-bold mb-3">
                    <i class="bi bi-shield-lock text-primary me-2"></i> Permissões de Usuários
                </h5>

                <?php
                $permissoes = [
                    "Gerenciar usuários do RH",
                    "Níveis de acesso",
                    "Auditoria de ações"
                ];
                ?>

                <?php foreach ($permissoes as $p): ?>
                    <div class="border rounded p-2 mb-2 config-item">
                        <?= $p ?>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

    </div>

    <!-- BOTÕES -->
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button class="btn btn-outline-secondary">Cancelar</button>
        <button class="btn btn-primary">Salvar Alterações</button>
    </div>

</div>

</body>
</html>