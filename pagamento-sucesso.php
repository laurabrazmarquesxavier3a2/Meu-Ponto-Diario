<?php session_start();?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Pagamento Confirmado</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    min-height: 100vh;
    margin: 0;
    background:
        radial-gradient(circle at top left, rgba(37,99,235,.25), transparent 35%),
        radial-gradient(circle at bottom right, rgba(14,165,233,.18), transparent 35%),
        #f8fbff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', sans-serif;
    overflow: hidden;
}

.success-card {
    background: white;
    border-radius: 34px;
    padding: 45px;
    max-width: 560px;
    width: 92%;
    text-align: center;
    box-shadow: 0 35px 80px rgba(15,23,42,.15);
    position: relative;
    z-index: 2;
}

.logo {
    width: 95px;
    margin-bottom: 22px;
    filter: drop-shadow(0 12px 25px rgba(37,99,235,.25));
}

.check-circle {
    width: 92px;
    height: 92px;
    border-radius: 50%;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    margin: 0 auto 24px;
    box-shadow: 0 18px 40px rgba(34,197,94,.35);
    animation: pulse 1.5s infinite;
}

h1 {
    font-weight: 800;
    color: #07111f;
}

p {
    color: #64748b;
    font-size: 17px;
    line-height: 1.7;
}

.loader {
    height: 8px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 28px;
}

.loader span {
    display: block;
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #2563eb, #06b6d4);
    animation: load 5s linear forwards;
}

.small-text {
    margin-top: 16px;
    font-size: 14px;
    color: #94a3b8;
}

.bg-logo {
    position: absolute;
    width: 900px;
    height: 900px;
    background: url('img/logo-azul.png') center/contain no-repeat;
    opacity: .07;
    animation: float 7s ease-in-out infinite;
}

.btn-login {
    margin-top: 25px;
    border-radius: 999px;
    padding: 13px 28px;
    font-weight: 700;
}

@keyframes load {
    from { width: 0%; }
    to { width: 100%; }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.06); }
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-18px) rotate(2deg); }
}

@media(max-width: 576px) {
    .success-card {
        padding: 34px 24px;
    }

    h1 {
        font-size: 28px;
    }
}
</style>
</head>

<body>

<div class="bg-logo"></div>

<div class="success-card">

    <img src="img/logo-azul.png" class="logo" alt="Meu Ponto Diário">

    <div class="check-circle">
        <i class="bi bi-check-lg"></i>
    </div>

    <h1>Pagamento efetuado!</h1>

    <p class="mt-3">
        Obrigado pelo apoio e por escolher o
        <strong>Meu Ponto Diário</strong>.
        Sua empresa foi cadastrada com sucesso e já pode acessar o sistema.
    </p>

    <div class="loader">
        <span></span>
    </div>

    <div class="small-text">
        Redirecionando para a tela de login...
    </div>

    <a href="login.php?cadastro=ok" class="btn btn-primary btn-login">
        Ir para login agora
    </a>

</div>

<script>
setTimeout(() => {
    window.location.href = "login.php?cadastro=ok";
}, 5000);
</script>
</body>
</html>