<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Meu Ponto Diário</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: #D6DDDE;
    }

    /* HEADER */
    .header {
      height: 70px;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 20px;
      border-bottom: 1px solid #ccc;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: bold;
      color: #14284b;
      font-size: 20px;
    }

    .user-area {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .avatar {
      width: 40px;
      height: 40px;
      background: #14284b;
      border-radius: 50%;
    }

    /* LAYOUT */
    .container {
      display: flex;
      height: calc(100vh - 70px);
    }

    /* SIDEBAR */
    .sidebar {
      width: 250px;
      background: #fff;
      padding: 20px;
      border-right: 1px solid #ccc;
    }

    .menu button {
      width: 100%;
      padding: 12px;
      margin-bottom: 10px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-align: left;
    }

    .menu .active {
      background: #2d5bd1;
      color: #fff;
    }

    .menu button:not(.active) {
      background: transparent;
    }

    /* MAIN */
    .main {
      flex: 1;
      padding: 30px;
      display: flex;
      gap: 30px;
    }

    .cards {
      display: flex;
      flex-direction: column;
      gap: 20px;
      width: 300px;
    }

    .card {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      display: flex;
      justify-content: space-between;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* CLOCK */
    .clock-box {
      background: #fff;
      border-radius: 20px;
      padding: 30px;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      width: 300px;
      height: fit-content;
    }

    .clock {
      font-size: 48px;
      margin: 15px 0;
    }

    .date {
      color: #555;
    }

  </style>
</head>
<body>

  <!-- HEADER -->
  <div class="header">
    <div class="logo">
      ✔ MEU PONTO DIÁRIO
    </div>

    <div class="user-area">
      🔔
      <span>Kevin Nobre</span>
      <div class="avatar"></div>
    </div>
  </div>

  <!-- CONTAINER -->
  <div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
      <div class="menu">
        <button class="active">Registro do Ponto</button>
        <button>Solicitar Documen.</button>
        <button>Pedidos</button>
        <button>Permissões</button>
        <button>Segurança</button>
        <button>Perfil</button>
      </div>
    </div>

    <!-- MAIN -->
    <div class="main">

      <!-- CARDS -->
      <div class="cards">
        <div class="card">
          <span>Entrada</span>
          <span>09:01:30</span>
        </div>

        <div class="card">
          <span>Início Intervalo</span>
          <span>--:--</span>
        </div>

        <div class="card">
          <span>Fim Intervalo</span>
          <span>--:--</span>
        </div>

        <div class="card">
          <span>Saída</span>
          <span>--:--</span>
        </div>
      </div>

      <!-- CLOCK -->
      <div class="clock-box">
        ⏱
        <div class="clock" id="clock"></div>
        <div class="date" id="date"></div>
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