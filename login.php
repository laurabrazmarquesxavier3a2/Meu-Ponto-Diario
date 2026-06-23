<?php
session_start();
require_once 'config/database.php';
require_once 'lang.php';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    $sql = "SELECT * FROM usuarios
            WHERE email = ?
            AND status = 'ativo'
            LIMIT 1";

    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $usuario = $resultado->fetch_assoc();

        if (password_verify($senha, $usuario['senha'])) {

            session_regenerate_id(true);

            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['id_empresa'] = $usuario['id_empresa'];
            $_SESSION['id_funcionario'] = $usuario['id_funcionario'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['tipo'] = $usuario['tipo'];

            $update = "UPDATE usuarios
                       SET ultimo_login = NOW()
                       WHERE id_usuario = ?";

            $stmtUpdate = $con->prepare($update);
            $stmtUpdate->bind_param("i", $usuario['id_usuario']);
            $stmtUpdate->execute();

            if ($usuario['tipo'] == 'empresa' || $usuario['tipo'] == 'rh') {
                header("Location: ponto.php");
                exit;
            }

            if ($usuario['tipo'] == 'funcionario') {
                header("Location: Funcionarios/pontoF.php");
                exit;
            }

        } else {
            $erro = "Senha incorreta.";
        }

    } else {
        $erro = "Usuário não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Login | Meu Ponto Diário</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/login.css">

</head>

<body>

    <section class="login-page">

    <div class="login-bg-logo"></div>

    <div class="particles-box" id="particles"></div>

    <div class="container">


        <div class="row align-items-center g-5">

            <div class="col-lg-6">

                <span class="badge badge-soft rounded-pill px-3 py-2 mb-4">
                    <i class="bi bi-shield-lock me-1"></i>
                    Acesso seguro
                </span>
                

                <h1 class="login-title">
                    Entre no
                    <span>Meu Ponto Diário</span>
                </h1>

                <p class="login-subtitle">
                    Acesse sua área de RH ou colaborador para gerenciar ponto,
                    banco de horas, férias, licenças, holerites e comunicados.
                </p>

                <div class="login-benefits">

                    <div class="benefit-card">
                        <i class="bi bi-clock-history"></i>
                        <div>
                            <strong>Ponto e jornada</strong>
                            <small>Controle completo de registros.</small>
                        </div>
                    </div>

                    <div class="benefit-card">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            <strong>Dados seguros</strong>
                            <small>Acesso separado por empresa.</small>
                        </div>
                    </div>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="login-card">

                    <div class="text-center mb-4">

                        <img src="img/logo-azul.png" alt="Meu Ponto Diário" class="logo-card">

                        <h2 class="fw-bold mt-3 mb-1">
                            Bem-vindo de volta
                        </h2>

                        <p class="text-muted mb-0">
                            Faça login para continuar.
                        </p>

                    </div>

                    <?php if(isset($_GET['cadastro'])): ?>
                        <div class="alert alert-success rounded-4">
                            <i class="bi bi-check-circle me-2"></i>
                            Conta criada com sucesso. Agora faça login para acessar o sistema.
                        </div>
                    <?php endif; ?>

                    <?php if($erro): ?>
                        <div class="alert alert-danger rounded-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($erro) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <label class="form-label">
                            E-mail
                        </label>

                        <div class="input-box">
                            <i class="bi bi-envelope"></i>

                            <input
                                type="email"
                                name="email"
                                placeholder="Digite seu e-mail"
                                required
                            >
                        </div>

                        <label class="form-label mt-3">
                            Senha
                        </label>

                        <div class="input-box">
                            <i class="bi bi-lock"></i>

                            <input
                                type="password"
                                name="senha"
                                id="senha"
                                placeholder="Digite sua senha"
                                required
                            >

                            <button type="button" class="toggle-password" id="toggleSenha">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>

                        <button type="submit" class="btn btn-login mt-4">
                            Entrar no sistema
                            <i class="bi bi-arrow-right ms-2"></i>
                        </button>

                        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">


                            <a href="cadastro-empresa.php" class="create-account">
                                Criar Empresa
                            </a>

                        </div>

                    </form>

                </div>

            </div>


</section>

<script>
const toggleSenha = document.getElementById('toggleSenha');
const senha = document.getElementById('senha');

if(toggleSenha && senha){
    toggleSenha.addEventListener('click', function(){
        const type = senha.getAttribute('type') === 'password' ? 'text' : 'password';
        senha.setAttribute('type', type);

        this.innerHTML = type === 'password'
            ? '<i class="bi bi-eye"></i>'
            : '<i class="bi bi-eye-slash"></i>';
    });
}
</script>


<script>
const particlesBox = document.getElementById('particles');

for(let i = 0; i < 35; i++){

    const particle = document.createElement('span');

    particle.classList.add('particle');

    particle.style.left = Math.random() * 100 + '%';

    particle.style.bottom = '-' + Math.random() * 150 + 'px';

    particle.style.width = (Math.random() * 8 + 4) + 'px';

    particle.style.height = particle.style.width;

    particle.style.animationDuration =
        (Math.random() * 8 + 6) + 's';

    particle.style.animationDelay =
        (Math.random() * 6) + 's';

    particlesBox.appendChild(particle);
}
</script>



<style>footer{
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
}</style>


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
</body>
</html>