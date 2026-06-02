<?php
session_start();

if (!isset($_SESSION['cadastro_empresa'])) {
    header("Location: cadastro-empresa.php");
    exit;
}

$empresa = $_SESSION['cadastro_empresa'];

$plano = $empresa['plano'];

if ($plano == 'medio') {
    $nomePlano = "Plano Médio Porte";
    $valorPlano = "149,99";
    $usuarios = "50 a 100 usuários";
} else {
    $nomePlano = "Plano Pequeno Porte";
    $valorPlano = "79,90";
    $usuarios = "10 a 49 usuários";
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Pagamento - Meu Ponto Diário</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

body{
    font-family:'Poppins',sans-serif;
    background:linear-gradient(135deg,#eef5ff,#dbe9ff);
    min-height:100vh;
}

.payment-wrapper{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:25px 15px;
}

.payment-card{
    width:100%;
    max-width:520px;
    border:none;
    border-radius:24px;
    overflow:hidden;
}

.top-area{
    background:linear-gradient(135deg,#0d6efd,#084298);
    color:white;
    text-align:center;
    padding:25px;
}

.logo{
    width:170px;
    margin-bottom:15px;
}

.plan-tag{
    background:rgba(255,255,255,.15);
    display:inline-block;
    padding:8px 18px;
    border-radius:50px;
    font-size:13px;
    margin-bottom:15px;
}

.plan-name{
    font-size:24px;
    font-weight:700;
}

.plan-price{
    font-size:42px;
    font-weight:700;
}

.plan-price span{
    font-size:15px;
    font-weight:400;
}

.info-box{
    background:#f8fbff;
    border:1px solid #dbe7ff;
    border-radius:14px;
    padding:15px;
}

.method-card{
    border:2px solid #e6ebf5;
    border-radius:14px;
    padding:14px;
    cursor:pointer;
    transition:.25s;
}

.method-card:hover{
    border-color:#0d6efd;
    background:#f8fbff;
}

.method-card i{
    color:#0d6efd;
    margin-right:8px;
}

.secure-box{
    background:#eef7ff;
    border-left:4px solid #0d6efd;
    padding:14px;
    border-radius:10px;
    font-size:14px;
}

.btn-pay{
    background:#0d6efd;
    border:none;
    border-radius:12px;
    padding:12px;
    font-weight:600;
}

.btn-pay:hover{
    background:#0b5ed7;
}

.plan-feature{
    margin-bottom:8px;
}

.plan-feature i{
    color:#198754;
    margin-right:8px;
}

</style>

</head>

<body>

<div class="payment-wrapper">

<div class="card payment-card shadow-lg">

<div class="top-area">

    <img
        src="img/logo-branca.png"
        class="logo"
        alt="Meu Ponto Diário"
    >
    

    <div class="plan-name" id="nomePlano">
        <?= $nomePlano ?>
    </div>

    <div class="plan-price" id="valorPlano">
        R$ <?= $valorPlano ?>
        <span>/mês</span>
    </div>

</div>

<div class="card-body p-4">

    <div class="info-box mb-3">

        <h6 class="fw-bold mb-3">
            <i class="bi bi-building"></i>
            Empresa
        </h6>

        <p class="mb-1">
            <strong><?= $empresa['razao_social']; ?></strong>
        </p>

        <p class="mb-1">
            <?= $empresa['cnpj']; ?>
        </p>

        <p class="mb-0">
            <?= $empresa['email']; ?>
        </p>

    </div>

    <div class="info-box mb-3">

        <h6 class="fw-bold mb-3">
            <i class="bi bi-box-seam"></i>
            Alterar Plano
        </h6>

        <select class="form-select" id="plano">

            <option value="pequeno">
                Pequeno Porte - R$ 79,90/mês
            </option>

            <option value="medio" <?= $plano == 'medio' ? 'selected' : '' ?>>
                Médio Porte - R$ 149,99/mês
            </option>

        </select>

    </div>

    <div class="info-box mb-4">

        <h6 class="fw-bold mb-3">
            O que está incluso
        </h6>

        <div class="plan-feature">
            <i class="bi bi-check-circle-fill"></i>
            <?= $usuarios ?>
        </div>

        <div class="plan-feature">
            <i class="bi bi-check-circle-fill"></i>
            Controle de Ponto
        </div>

        <div class="plan-feature">
            <i class="bi bi-check-circle-fill"></i>
            Gestão de Férias
        </div>

        <div class="plan-feature">
            <i class="bi bi-check-circle-fill"></i>
            Banco de Horas
        </div>

        <div class="plan-feature">
            <i class="bi bi-check-circle-fill"></i>
            Relatórios Automáticos
        </div>

        <div class="plan-feature">
            <i class="bi bi-check-circle-fill"></i>
            Suporte Técnico
        </div>

    </div>

    <form action="finalizar-cadastro.php" method="POST">

        <h6 class="fw-bold mb-3">
            Forma de Pagamento
        </h6>

        <label class="method-card d-block mb-2">
            <input type="radio" name="forma_pagamento" value="pix" checked>
            <i class="bi bi-qr-code"></i>
            PIX
        </label>

        <label class="method-card d-block mb-2">
            <input type="radio" name="forma_pagamento" value="cartao">
            <i class="bi bi-credit-card"></i>
            Cartão de Crédito
        </label>

        <label class="method-card d-block mb-3">
            <input type="radio" name="forma_pagamento" value="boleto">
            <i class="bi bi-receipt"></i>
            Boleto Bancário
        </label>

        <div class="secure-box">

            <i class="bi bi-shield-lock-fill"></i>

            Seus dados são protegidos e criptografados.

        </div>

        <div class="row mt-4">

            <div class="col-5">

                <a
                    href="cadastro-empresa.php"
                    class="btn btn-outline-secondary w-100"
                >
                    <i class="bi bi-arrow-left"></i>
                    Voltar
                </a>

            </div>

            <div class="col-7">

                <button
                    type="submit"
                    class="btn btn-pay text-white w-100"
                >
                    <i class="bi bi-lock-fill"></i>
                    Confirmar Pagamento
                </button>

            </div>

        </div>

    </form>

</div>

</div>

</div>

<script>

const plano = document.getElementById('plano');
const valor = document.getElementById('valorPlano');
const nome = document.getElementById('nomePlano');

plano.addEventListener('change', function(){

    if(this.value == 'pequeno'){

        nome.innerHTML = 'Plano Pequeno Porte';

        valor.innerHTML =
            'R$ 79,90 <span>/mês</span>';

    }else{

        nome.innerHTML = 'Plano Médio Porte';

        valor.innerHTML =
            'R$ 149,99 <span>/mês</span>';

    }

});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>