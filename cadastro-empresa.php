<?php

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($senha != $confirmar_senha) {

        $erro = "As senhas não coincidem.";

    } else {

        $sucesso = "Cadastro realizado com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Cadastro de Empresa</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/cadastroEm.css">


</head>

<body>

<div class="bg-login">

    <div class="login-box">

        <div class="text-center mb-4">
            <img src="img/logo-branca.png" class="logo">
        </div>

        <div class="page-title">
            <h2>Cadastro da Empresa</h2>
            <p>
                Cadastre sua organização e comece a utilizar a plataforma.
            </p>
        </div>

        <?php if($erro): ?>
            <div class="alert alert-danger">
                <?= $erro ?>
            </div>
        <?php endif; ?>

        <?php if($sucesso): ?>
            <div class="alert alert-success">
                <?= $sucesso ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="row g-4">

                <!-- CARD EMPRESA -->
                <div class="col-lg-6">

                    <div class="card card-custom shadow-lg">

                        <div class="card-header-custom">
                            Dados da Empresa
                        </div>

                        <div class="card-body">

                            <input
                                type="text"
                                name="razao_social"
                                class="form-control"
                                placeholder="Razão Social"
                                required
                            >

                            <input
                                type="text"
                                name="nome_fantasia"
                                class="form-control"
                                placeholder="Nome Fantasia"
                                required
                            >

                            <input
                                type="text"
                                name="cnpj"
                                class="form-control"
                                placeholder="CNPJ"
                                required
                            >

                            <select
                                name="segmento"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Segmento de Atuação
                                </option>
                                <option>Comércio</option>
                                <option>Serviços</option>
                                <option>Tecnologia</option>
                                <option>Indústria</option>
                                <option>Saúde</option>
                                <option>Educação</option>
                                <option>Construção Civil</option>
                                <option>Logística</option>
                            </select>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="E-mail Corporativo"
                                required
                            >

                            <input
                                type="text"
                                name="telefone"
                                class="form-control"
                                placeholder="Telefone"
                                required
                            >


                            <input
                                type="password"
                                name="senha"
                                class="form-control"
                                placeholder="Senha"
                                required
                            >

                            <input
                                type="password"
                                name="confirmar_senha"
                                class="form-control"
                                placeholder="Confirmar Senha"
                                required
                            >

                        </div>

                    </div>

                </div>

                <!-- CARD RESPONSÁVEL -->
                <div class="col-lg-6">

                    <div class="card card-custom shadow-lg">

                        <div class="card-header-custom">
                            Responsável e Segurança
                        </div>

                        <div class="card-body">

                            <input
                                type="text"
                                name="responsavel"
                                class="form-control"
                                placeholder="Responsável pela Empresa"
                                required
                            >

                            <input
                                type="text"
                                name="cargo"
                                class="form-control"
                                placeholder="Cargo do Responsável"
                                required
                            >

                            <input
                                type="text"
                                name="endereco"
                                class="form-control"
                                placeholder="Endereço"
                                required
                            >

                            <div class="row">

                                <div class="col-md-8">
                                    <input
                                        type="text"
                                        name="cidade"
                                        class="form-control"
                                        placeholder="Cidade"
                                        required
                                    >
                                </div>

                                <div class="col-md-4">
                                    <input
                                        type="text"
                                        name="estado"
                                        class="form-control"
                                        placeholder="UF"
                                        required
                                    >
                                </div>

                            </div>

                            <input
                                type="text"
                                name="cep"
                                class="form-control"
                                placeholder="CEP"
                                required
                            >

                            <div class="logo-upload">

                                <label class="mb-2">
                                    Logo da Empresa
                                </label>

                                <input
                                    type="file"
                                    name="logo"
                                    id="logo"
                                    class="form-control"
                                    accept=".png,.jpg,.jpeg,.svg"
                                >

                                <img
                                    id="preview"
                                    class="logo-preview"
                                >

                            </div>


                        </div>

                    </div>

                </div>

            </div>

            <div class="actions">
<button
    type="button"
    class="btn btn-login"
    data-bs-toggle="modal"
    data-bs-target="#modalTermos"
>
    Criar Conta
</button>

                <a href="login.php" class="link-login">
                    Já possui uma conta? Entrar
                </a>

            </div>

        </form>

    </div>

</div>
<script>

const logo = document.getElementById('logo');
const preview = document.getElementById('preview');

if(logo){

    logo.addEventListener('change', function(){

        const arquivo = this.files[0];

        if(arquivo){

            preview.src = URL.createObjectURL(arquivo);
            preview.style.display = 'block';

        }

    });

}

</script>

<!-- MODAL TERMOS -->

<div class="modal fade" id="modalTermos" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Termos de Uso e Política de Privacidade
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <h6>1. Utilização da Plataforma</h6>

                <p>
                    A plataforma destina-se à gestão de recursos humanos,
                    controle de colaboradores, documentos, férias,
                    benefícios e demais processos administrativos.
                </p>

                <h6>2. Responsabilidade das Informações</h6>

                <p>
                    A empresa contratante é responsável pela veracidade
                    dos dados cadastrados e pela atualização das
                    informações mantidas no sistema.
                </p>

                <h6>3. Segurança</h6>

                <p>
                    Adotamos medidas de proteção para garantir a
                    confidencialidade dos dados armazenados.
                </p>

                <h6>4. Disponibilidade</h6>

                <p>
                    Buscamos manter o serviço disponível continuamente,
                    podendo ocorrer interrupções programadas para
                    manutenção e melhorias.
                </p>

                <h6>5. Privacidade</h6>

                <p>
                    Os dados fornecidos serão utilizados exclusivamente
                    para operação da plataforma e prestação dos serviços
                    contratados.
                </p>

                <h6>6. Aceitação</h6>

                <p>
                    Ao prosseguir, o usuário declara que leu,
                    compreendeu e concorda com estes termos.
                </p>

                <div class="form-check mt-4">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="aceitarTermos">

                    <label
                        class="form-check-label"
                        for="aceitarTermos">

                        Li e concordo com os Termos de Uso e Política
                        de Privacidade.

                    </label>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="btnFinalizarCadastro">
                    Aceitar e Criar Conta
                </button>

            </div>

        </div>

    </div>

</div>

<script>

document
.getElementById('btnFinalizarCadastro')
.addEventListener('click', function(){

    const aceitou =
        document.getElementById('aceitarTermos');

    if(!aceitou.checked){

        alert(
            'Você precisa aceitar os termos para continuar.'
        );

        return;
    }

document.querySelector('form').submit();

});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>