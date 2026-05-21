 <!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Meu Ponto Diário</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <!-- CSS -->
  <link rel="stylesheet" href="../css/style-funcionario.css">
</head>

<body>

<!-- HEADER -->
<header class="topbar">
  <div class="logo"  >
     <img src="../img/logo-azul.png" alt="logo" class="logo-img">
    <span>MEU PONTO DIÁRIO</span>
  </div>

  <div class="user-area">
    <i class="bi bi-bell-fill"></i>
    <span>Kevin Nobre</span>
    <div class="avatar"></div>
  </div>
</header>

<div class="layout">
 
<?php include 'siderbarF.php'; ?>

  <!-- CONTEÚDO -->
  <main class="content">
    <div class="cards">

      <div class="card-box">
        <span>Entrada</span>
        <strong>09:01:30</strong>
      </div>

      <div class="card-box">
        <span>Início Intervalo</span>
        <strong>--:--</strong>
      </div>

      <div class="card-box">
        <span>Fim Intervalo</span>
        <strong>--:--</strong>
      </div>

      <div class="card-box">
        <span>Saída</span>
        <strong>--:--</strong>
      </div>

    </div>

    <!-- RELÓGIO -->
    <div class="clock-box">
      <i class="bi bi-clock"></i>
      <div id="clock" class="clock"></div>
      <div id="date" class="date"></div>
    </div>

  </main>

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