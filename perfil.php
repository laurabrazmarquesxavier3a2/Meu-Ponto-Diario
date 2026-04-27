<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil RH</title>

  <!-- CSS -->
  <link rel="stylesheet" href="css/style.css">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    .avatar {
      width: 96px;
      height: 96px;
      background-color: #4f46e5;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      color: #fff;
      font-weight: bold;
    }
  </style>
</head>

<body>

<div class="container-fluid">
  <div class="row">

    <!-- SIDEBAR (AGORA VEM DO PHP) -->
    <?php include 'sidebar.php'; ?>

    <!-- CONTEÚDO -->
    <div class="col-12 col-md-9 col-lg-10 content">

      <h1 class="fw-bold">Meu Perfil</h1>
      <h5 class="text-muted mb-4">Informações da sua conta de RH</h5>

      <div class="row g-4">

        <!-- PERFIL -->
        <div class="col-lg-4">
          <div class="card shadow-sm p-4 text-center">

            <div class="avatar mx-auto mb-3">
              JP
            </div>

            <h5 class="fw-bold mb-1">Juliana Pereira</h5>
            <p class="text-primary mb-1">Gerente de RH</p>
            <p class="text-muted small mb-3">Recursos Humanos</p>

            <hr>

            <div class="text-start small text-muted">

              <div class="d-flex align-items-center mb-2">
                <i class="bi bi-envelope me-2"></i>
                juliana.pereira@empresa.com
              </div>

              <div class="d-flex align-items-center mb-2">
                <i class="bi bi-telephone me-2"></i>
                (11) 98765-1234
              </div>

              <div class="d-flex align-items-center mb-2">
                <i class="bi bi-geo-alt me-2"></i>
                São Paulo, SP
              </div>

              <div class="d-flex align-items-center mb-2">
                <i class="bi bi-briefcase me-2"></i>
                ID: RH-001
              </div>

              <div class="d-flex align-items-center">
                <i class="bi bi-calendar me-2"></i>
                Desde 15/05/2020
              </div>

            </div>

            <button class="btn btn-primary w-100 mt-4">
              Editar Perfil
            </button>

          </div>
        </div>

        <!-- LADO DIREITO -->
        <div class="col-lg-8">

          <!-- STATS -->
          <div class="row g-3 mb-4 text-center">

            <div class="col-6 col-md-3">
              <div class="card shadow-sm p-3">
                <h5 class="fw-bold">18</h5>
                <small class="text-muted">Aprovações Pendentes</small>
              </div>
            </div>

            <div class="col-6 col-md-3">
              <div class="card shadow-sm p-3">
                <h5 class="fw-bold">42</h5>
                <small class="text-muted">Ações Hoje</small>
              </div>
            </div>

            <div class="col-6 col-md-3">
              <div class="card shadow-sm p-3">
                <h5 class="fw-bold">5 anos</h5>
                <small class="text-muted">Tempo na Empresa</small>
              </div>
            </div>

            <div class="col-6 col-md-3">
              <div class="card shadow-sm p-3">
                <h5 class="fw-bold">1.247</h5>
                <small class="text-muted">Solicitações Resolvidas</small>
              </div>
            </div>

          </div>

          <!-- PERMISSÕES -->
          <div class="card shadow-sm p-4 mb-4">
            <h5 class="fw-bold mb-3">
              <i class="bi bi-shield-lock me-2 text-primary"></i>
              Permissões e Acessos
            </h5>

            <div class="row g-2">
              <div class="col-md-6">
                <div class="bg-light rounded p-2 d-flex align-items-center">
                  <span class="badge bg-primary me-2">&nbsp;</span>
                  Administrador
                </div>
              </div>

              <div class="col-md-6">
                <div class="bg-light rounded p-2 d-flex align-items-center">
                  <span class="badge bg-primary me-2">&nbsp;</span>
                  Aprovar Férias
                </div>
              </div>

              <div class="col-md-6">
                <div class="bg-light rounded p-2 d-flex align-items-center">
                  <span class="badge bg-primary me-2">&nbsp;</span>
                  Aprovar Licenças
                </div>
              </div>

              <div class="col-md-6">
                <div class="bg-light rounded p-2 d-flex align-items-center">
                  <span class="badge bg-primary me-2">&nbsp;</span>
                  Gerenciar Funcionários
                </div>
              </div>
            </div>
          </div>

          <!-- ATIVIDADES -->
          <div class="card shadow-sm p-4">
            <h5 class="fw-bold mb-3">Atividades Recentes</h5>

            <div class="mb-3 d-flex">
              <span class="badge bg-success rounded-circle me-2">&nbsp;</span>
              <div>
                <small>Aprovado pedido de férias - Maria Silva</small><br>
                <small class="text-muted">Há 1 hora</small>
              </div>
            </div>

            <div class="mb-3 d-flex">
              <span class="badge bg-primary rounded-circle me-2">&nbsp;</span>
              <div>
                <small>Enviado holerite para João Santos</small><br>
                <small class="text-muted">Há 2 horas</small>
              </div>
            </div>

            <div class="mb-3 d-flex">
              <span class="badge bg-warning rounded-circle me-2">&nbsp;</span>
              <div>
                <small>Revisado licença médica - Ana Costa</small><br>
                <small class="text-muted">Há 3 horas</small>
              </div>
            </div>

            <div class="d-flex">
              <span class="badge rounded-circle me-2" style="background-color: purple;">&nbsp;</span>
              <div>
                <small>Atualizado informações de benefícios</small><br>
                <small class="text-muted">Há 5 horas</small>
              </div>
            </div>

          </div>

        </div>
      </div>

    </div>

  </div>
</div>

</body>
</html>