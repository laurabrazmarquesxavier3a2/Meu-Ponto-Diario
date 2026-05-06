 <!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Meu Ponto Diário</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet/">

  <!-- CSS -->
  <link rel="stylesheet" href="css/style.funcionario.css">
  <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<!-- HEADER -->
<nav class="navbar navbar-light bg-white shadow-sm px-4 justify-content-between">
  <span class="fw-bold text-primary fs-5">
    ✔ MEU PONTO DIÁRIO
  </span>

  <div class="d-flex align-items-center gap-3">
    <span>🔔</span>
    <span>Kevin Nobre</span>
    <div class="avatar"></div>
  </div>
</nav>

<div class="d-flex">

  <!-- SIDEBAR -->
  <div class="sidebar bg-white border-end p-3">
    <button class="btn btn-primary w-100 text-start mb-2">Registro do Ponto</button>
    <button class="btn btn-light w-100 text-start mb-2">Solicitar Documen.</button>
    <button class="btn btn-light w-100 text-start mb-2">Pedidos</button>
    <button class="btn btn-light w-100 text-start mb-2">Permissões</button>
    <button class="btn btn-light w-100 text-start mb-2">Segurança</button>
    <button class="btn btn-light w-100 text-start">Perfil</button>
  </div>

  <!-- MAIN -->
  <div class="container-fluid p-4">

    <div class="row">

      <!-- CARDS -->
      <div class="col-md-4 d-flex flex-column gap-3">

        <div class="card card-custom p-3 d-flex flex-row justify-content-between">
          <span>Entrada</span>
          <span>09:01:30</span>
        </div>

        <div class="card card-custom p-3 d-flex flex-row justify-content-between">
          <span>Início Intervalo</span>
          <span>--:--</span>
        </div>

        <div class="card card-custom p-3 d-flex flex-row justify-content-between">
          <span>Fim Intervalo</span>
          <span>--:--</span>
        </div>

        <div class="card card-custom p-3 d-flex flex-row justify-content-between">
          <span>Saída</span>
          <span>--:--</span>
        </div>

      </div>

      <!-- RELÓGIO -->
      <div class="col-md-4">
        <div class="card clock-box p-4 text-center">
          ⏱
          <div class="clock" id="clock"></div>
          <div id="date" class="text-muted"></div>
        </div>
      </div>

    </div>

  </div>

</div>

<!-- JS -->
<script>
function updateClock() {
  const now = new Date();

  const time = now.toLocaleTimeString('pt-BR');
  const date = now.toLocaleDateString('pt-BR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });

  document.getElementById('clock').innerText = time;
  document.getElementById('date').innerText = date;
}

setInterval(updateClock, 1000);
updateClock();
</script>

</body>
</html>