<?php
session_start();
require_once 'lang.php';

if (!isset($_SESSION['cadastro_empresa'])) {
    header("Location: cadastro-empresa.php");
    exit;
}

$empresa = $_SESSION['cadastro_empresa'];
$plano = $empresa['plano'];

if ($plano == 'medio') {
    $nomePlano = "Plano Médio Porte";
    $valorPlano = "1500,00";
    $usuarios = "50 a 100 usuários";
} else {
    $nomePlano = "Plano Pequeno Porte";
    $valorPlano = "740,00";
    $usuarios = "10 a 49 usuários";
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Pagamento | Meu Ponto Diário</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">

<style>
:root{
    --azul:#2563eb;
    --azul2:#1e40af;
    --escuro:#07111f;
    --texto:#64748b;
    --bg:#f8fbff;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'DM Sans',sans-serif;
}

body{
    background:
        radial-gradient(circle at top left, rgba(37,99,235,.18), transparent 35%),
        radial-gradient(circle at top right, rgba(14,165,233,.14), transparent 30%),
        var(--bg);
    overflow-x:hidden;
    color:var(--escuro);
}

h1,h2,h3,h4,h5{
    font-family:'Sora',sans-serif;
}

.pagamento-page{
    min-height:100vh;
    position:relative;
    overflow:hidden;
    padding:80px 0;
}

.pagamento-bg-logo{
    position:absolute;
    width:1100px;
    height:1100px;
    left:50%;
    top:50%;
    transform:translate(-50%,-50%);
    background:url('img/logo-azul.png') center center no-repeat;
    background-size:contain;
    opacity:.085;
    filter:
        blur(.4px)
        drop-shadow(0 0 160px rgba(37,99,235,.55));
    z-index:0;
    animation:logoPulse 12s ease-in-out infinite;
}

@keyframes logoPulse{
    0%,100%{
        transform:translate(-50%,-50%) scale(1);
    }

    50%{
        transform:translate(-50%,-50%) scale(1.05);
    }
}

.pagamento-page .container{
    position:relative;
    z-index:2;
}

.top-pagamento{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:55px;
    gap:20px;
}

.logo-topo{
    width:72px;
    filter:drop-shadow(0 10px 25px rgba(37,99,235,.35));
}

.btn-voltar{
    display:inline-flex;
    align-items:center;
    gap:10px;
    background:white;
    border:1px solid #dbeafe;
    color:#1e40af;
    text-decoration:none;
    font-weight:800;
    border-radius:16px;
    padding:13px 22px;
    box-shadow:0 14px 30px rgba(15,23,42,.08);
    transition:.25s;
}

.btn-voltar:hover{
    background:#eff6ff;
    color:#1e40af;
    transform:translateY(-3px);
}

.badge-soft{
    background:rgba(37,99,235,.1);
    color:#2563eb;
    border:1px solid rgba(37,99,235,.15);
}

.payment-title{
    font-size:clamp(3rem,5vw,5.6rem);
    line-height:1.02;
    letter-spacing:-3px;
    font-weight:800;
    color:#081224;
    margin-bottom:24px;
}

.payment-title span{
    background:linear-gradient(90deg,#2563eb,#06b6d4);
    -webkit-background-clip:text;
    color:transparent;
}

.payment-subtitle{
    color:#64748b;
    font-size:1.2rem;
    line-height:1.7;
    max-width:650px;
}

.plan-card{
    background:white;
    border-radius:34px;
    padding:38px;
    box-shadow:0 30px 70px rgba(15,23,42,.14);
    border:1px solid #e2e8f0;
    max-width:520px;
    margin-left:auto;
    transform:perspective(1100px) rotateY(-7deg) rotateX(5deg);
    transition:.4s;
}

.plan-card:hover{
    transform:perspective(1100px) rotateY(0deg) rotateX(0deg) translateY(-8px);
}

.plan-icon{
    width:72px;
    height:72px;
    border-radius:22px;
    background:linear-gradient(135deg,#2563eb,#06b6d4);
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:34px;
    margin-bottom:24px;
    box-shadow:0 16px 40px rgba(37,99,235,.28);
}

.plan-name{
    font-weight:800;
    font-size:28px;
    color:#081224;
}

.plan-price{
    font-size:52px;
    font-weight:800;
    letter-spacing:-2px;
    color:#2563eb;
}

.plan-price span{
    font-size:16px;
    color:#64748b;
    font-weight:700;
}

.info-card{
    background:white;
    border-radius:30px;
    box-shadow:0 25px 60px rgba(15,23,42,.12);
    border:1px solid #e2e8f0;
    padding:28px;
    height: auto;
}

.info-title{
    display:flex;
    align-items:center;
    gap:12px;
    font-weight:800;
    color:#081224;
    margin-bottom:18px;
}

.info-title i{
    width:44px;
    height:44px;
    border-radius:14px;
    background:#eff6ff;
    color:#2563eb;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
}

.info-card p{
    color:#64748b;
}

.form-select{
    height:54px;
    border:1px solid #dbeafe;
    border-radius:16px;
    background:#f8fafc;
    font-weight:700;
    color:#081224;
}

.form-select:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 4px rgba(37,99,235,.12);
}

.plan-feature{
    display:flex;
    align-items:center;
    gap:10px;
    color:#081224;
    font-weight:700;
    margin-bottom:13px;
}

.plan-feature i{
    color:#22c55e;
}

.method-card{
    width:100%;
    border:1px solid #dbeafe;
    background:#f8fafc;
    border-radius:18px;
    padding:16px 18px;
    display:flex;
    align-items:center;
    gap:12px;
    cursor:pointer;
    transition:.25s;
    font-weight:800;
    color:#081224;
}

.method-card:hover{
    background:white;
    border-color:#2563eb;
    transform:translateY(-3px);
    box-shadow:0 14px 30px rgba(37,99,235,.12);
}

.method-card input{
    accent-color:#2563eb;
}

.method-card i{
    color:#2563eb;
    font-size:22px;
}

.secure-box{
    background:#eff6ff;
    border:1px solid #dbeafe;
    color:#1e40af;
    padding:16px;
    border-radius:18px;
    font-weight:700;
}

.secure-box i{
    color:#2563eb;
    margin-right:8px;
}

.btn-pay{
    background:linear-gradient(135deg,#2563eb,#1e40af);
    color:white;
    border:none;
    border-radius:18px;
    padding:15px;
    font-weight:800;
    box-shadow:0 15px 35px rgba(37,99,235,.25);
    transition:.25s;
}

.btn-pay:hover{
    color:white;
    transform:translateY(-3px);
}

.btn-back{
    border:1px solid #dbeafe;
    background:white;
    color:#1e40af;
    border-radius:18px;
    padding:15px;
    font-weight:800;
    transition:.25s;
}

.btn-back:hover{
    background:#eff6ff;
    color:#1e40af;
    transform:translateY(-3px);
}

footer{
    background:#173d73;
    color:white;
    padding:45px 0;
    margin-top:100px;
}

footer a{
    color:white;
    text-decoration:none;
    font-weight:600;
    transition:.2s;
}

footer a:hover{
    color:#93c5fd;
}

footer small{
    color:#cbd5e1;
}

.footer-logo{
    width:48px;
    height:auto;
}

@media(max-width:991px){

    .pagamento-page{
        text-align:center;
        padding:60px 0;
    }

    .pagamento-bg-logo{
        width:700px;
        height:700px;
        opacity:.065;
        filter:
            blur(.4px)
            drop-shadow(0 0 120px rgba(37,99,235,.45));
    }

    .top-pagamento{
        justify-content:center;
        margin-bottom:35px;
    }

    .logo-topo{
        display:none;
    }

    .payment-subtitle{
        margin-left:auto;
        margin-right:auto;
    }

    .plan-card{
        transform:none;
        margin:35px auto 0;
    }

    .plan-card:hover{
        transform:translateY(-6px);
    }

}
</style>

</head>

<body>

<section class="pagamento-page">

    <div class="pagamento-bg-logo"></div>

    <div class="container">

        <div class="top-pagamento">

        </div>

        <div class="row align-items-center g-5 mb-5">

            <div class="col-lg-7">

                <span class="badge badge-soft rounded-pill px-3 py-2 mb-4">
                    <i class="bi bi-shield-lock me-1"></i>
                    Pagamento seguro
                </span>

                <h1 class="payment-title">
                    Finalize seu
                    <span>plano</span>
                </h1>

                <p class="payment-subtitle">
                    Confirme os dados da empresa, escolha a forma de pagamento
                    e conclua o cadastro para liberar o acesso ao sistema.
                </p>

            </div>

            <div class="col-lg-5">

                <div class="plan-card">

                    <div class="plan-icon">
                        <i class="bi bi-credit-card-2-front"></i>
                    </div>

                    <div class="plan-name" id="nomePlano">
                        <?= $nomePlano ?>
                    </div>

                    <div class="plan-price mt-3" id="valorPlano">
                        R$ <?= $valorPlano ?>
                        <span>/mês</span>
                    </div>

                    <p class="text-muted mt-3 mb-0">
                        Plano selecionado para ativação da empresa.
                    </p>

                </div>

            </div>

        </div>

        <div class="row g-4">

            <div class="col-lg-5">
<div class="info-card">

    <div class="info-title">
        <i class="bi bi-building"></i>
        <span>Empresa</span>
    </div>

    <p class="mb-1">
        <strong><?= htmlspecialchars($empresa['razao_social']); ?></strong>
    </p>

    <p class="mb-1">
        <?= htmlspecialchars($empresa['cnpj']); ?>
    </p>

    <p class="mb-4">
        <?= htmlspecialchars($empresa['email']); ?>
    </p>

    <hr class="my-4">

    <div class="info-title">
        <i class="bi bi-box-seam"></i>
        <span>Alterar Plano</span>
    </div>

    <select class="form-select" id="plano">

        <option value="pequeno" <?= $plano == 'pequeno' ? 'selected' : '' ?>>
            Pequeno Porte - R$ 740,00/mês
        </option>

        <option value="medio" <?= $plano == 'medio' ? 'selected' : '' ?>>
            Médio Porte - R$ 1500,00/mês
        </option>

    </select>

</div>

            </div>

            <div class="col-lg-7">

                <div class="info-card">

                    <div class="info-title">
                        <i class="bi bi-stars"></i>
                        <span>O que está incluso</span>
                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="plan-feature">
                                <i class="bi bi-check-circle-fill"></i>
                                <span id="usuariosPlano"><?= $usuarios ?></span>
                            </div>

                            <div class="plan-feature">
                                <i class="bi bi-check-circle-fill"></i>
                                Controle de Ponto
                            </div>

                            <div class="plan-feature">
                                <i class="bi bi-check-circle-fill"></i>
                                Gestão de Férias
                            </div>
                        </div>

                        <div class="col-md-6">
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

                    </div>

                    <hr class="my-4">

                    <form action="finalizar-cadastro.php" method="POST">

                        <h5 class="fw-bold mb-3">
                            Forma de Pagamento
                        </h5>

                        <label class="method-card mb-3">
                            <input type="radio" name="forma_pagamento" value="pix" checked>
                            <i class="bi bi-qr-code"></i>
                            PIX
                        </label>

                        <label class="method-card mb-3">
                            <input type="radio" name="forma_pagamento" value="cartao">
                            <i class="bi bi-credit-card"></i>
                            Cartão de Crédito
                        </label>

                        <label class="method-card mb-3">
                            <input type="radio" name="forma_pagamento" value="boleto">
                            <i class="bi bi-receipt"></i>
                            Boleto Bancário
                        </label>

                        <div class="secure-box mt-4">
                            <i class="bi bi-shield-lock-fill"></i>
                            Seus dados são protegidos e criptografados.
                        </div>

                        <div class="row mt-4 g-3">

                            <div class="col-md-5">

                                <a href="cadastro-empresa.php" class="btn btn-back w-100">
                                    <i class="bi bi-arrow-left"></i>
                                    Voltar
                                </a>

                            </div>

                            <div class="col-md-7">

                                <button type="submit" class="btn btn-pay w-100">
                                    <i class="bi bi-lock-fill"></i>
                                    Confirmar Pagamento
                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>

<footer>

    <div class="container">

        <div class="row align-items-center">

            <div class="col-md-6 text-center text-md-start">

                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-3 mb-3">

                    <img
                        src="img/logo-branca.png"
                        alt="Meu Ponto Diário"
                        class="footer-logo"
                    >

                    <div>

                        <h5 class="fw-bold mb-1">
                            Meu Ponto Diário
                        </h5>

                        <small>
                            Gestão de RH, Ponto e Banco de Horas.
                        </small>

                    </div>

                </div>

            </div>

            <div class="col-md-6 text-center text-md-end">

                <a href="index.php" class="me-4">
                    Início
                </a>

                <a href="ajuda.php" class="me-4">
                    Ajuda
                </a>

                <a href="leis.php" class="me-4">
                    Leis Trabalhistas
                </a>

                <a href="login.php">
                    Entrar
                </a>

            </div>

        </div>

        <hr class="border-light opacity-25 my-4">

        <div class="row">

            <div class="col-md-6 text-center text-md-start">

                <small>
                    © <?= date('Y') ?> Meu Ponto Diário. Todos os direitos reservados.
                </small>

            </div>

            <div class="col-md-6 text-center text-md-end">

                <small>
                    Desenvolvido para modernizar a gestão de pessoas.
                </small>

            </div>

        </div>

    </div>

</footer>

<script>
const plano = document.getElementById('plano');
const valor = document.getElementById('valorPlano');
const nome = document.getElementById('nomePlano');
const usuarios = document.getElementById('usuariosPlano');

plano.addEventListener('change', function(){

    if(this.value == 'pequeno'){

        nome.innerHTML = 'Plano Pequeno Porte';
        valor.innerHTML = 'R$ 740,00 <span>/mês</span>';
        usuarios.innerHTML = '10 a 49 usuários';

    }else{

        nome.innerHTML = 'Plano Médio Porte';
        valor.innerHTML = 'R$ 1500,00 <span>/mês</span>';
        usuarios.innerHTML = '50 a 100 usuários';

    }

});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>