<?php
session_start();
require_once 'lang.php';

$erro = '';

function validarCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

    if (strlen($cnpj) != 14) return false;
    if (preg_match('/(\d)\1{13}/', $cnpj)) return false;

    for ($t = 12; $t < 14; $t++) {
        $soma = 0;
        $pos = $t - 7;

        for ($i = 0; $i < $t; $i++) {
            $soma += intval($cnpj[$i]) * $pos;
            $pos--;
            if ($pos < 2) $pos = 9;
        }

        $digito = ((10 * $soma) % 11) % 10;

        if (intval($cnpj[$t]) !== $digito) {
            return false;
        }
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!validarCNPJ($_POST['cnpj'])) {

        $erro = "CNPJ inválido.";

    } elseif ($_POST['senha'] != $_POST['confirmar_senha']) {

        $erro = "As senhas não coincidem.";

    } else {

        $_SESSION['cadastro_empresa'] = [
            'razao_social' => $_POST['razao_social'],
            'nome_fantasia' => $_POST['nome_fantasia'],
            'cnpj' => preg_replace('/[^0-9]/', '', $_POST['cnpj']),
            'segmento' => $_POST['segmento'],
            'email' => $_POST['email'],
            'telefone' => $_POST['telefone'],
            'responsavel' => $_POST['responsavel'],
            'cargo' => $_POST['cargo'],
            'endereco' => $_POST['endereco'],
            'cidade' => $_POST['cidade'],
            'estado' => $_POST['estado'],
            'cep' => $_POST['cep'],
            'senha' => $_POST['senha'],
            'plano' => $_GET['plano'] ?? 'pequeno'
        ];

        header("Location: pagamento.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Cadastro de Empresa | Meu Ponto Diário</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="css/cadastroEm.css?v=4">
</head>

<body>

<section class="cadastro-page">

    <div class="cadastro-bg-logo"></div>

    <div class="container position-relative">

        <div class="top-cadastro">

        </div>

        <div class="row align-items-center g-5 mb-5">

            <div class="col-lg-7">

                <span class="badge badge-soft rounded-pill px-3 py-2 mb-4">
                    <i class="bi bi-building-check me-1"></i>
                    Cadastro empresarial
                </span>

                <h1 class="cadastro-title">
                    Crie sua conta no
                    <span>Meu Ponto Diário</span>
                </h1>

                <p class="cadastro-subtitle">
                    Cadastre sua empresa, valide o CNPJ e comece a gerenciar ponto,
                    banco de horas, férias, licenças, holerites e comunicados.
                </p>

            </div>

            <div class="col-lg-5">

                <div class="info-card">

                    <div class="info-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>

                    <h3>Ambiente seguro</h3>

                    <p>
                        O CNPJ será validado e consultado para confirmar se pertence
                        a uma empresa real e ativa.
                    </p>

                    <div class="info-check">
                        <i class="bi bi-check-circle-fill"></i>
                        Validação de CNPJ
                    </div>

                    <div class="info-check">
                        <i class="bi bi-check-circle-fill"></i>
                        Dados preenchidos automaticamente
                    </div>

                    <div class="info-check">
                        <i class="bi bi-check-circle-fill"></i>
                        Plano selecionado automaticamente
                    </div>

                </div>

            </div>

        </div>

        <?php if($erro): ?>
            <div class="alert alert-danger rounded-4 fw-bold">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="row g-4">

                <div class="col-lg-6">

                    <div class="form-card">

                        <div class="form-card-header">
                            <div>
                                <i class="bi bi-building"></i>
                            </div>

                            <span>Dados da Empresa</span>
                        </div>

                        <div class="form-card-body">

                            <label>CNPJ</label>
                            <input type="text" name="cnpj" id="cnpj" placeholder="00.000.000/0000-00" required>

                            <label>Razão Social</label>
                            <input type="text" name="razao_social" id="razao_social" placeholder="Razão social" required>

                            <label>Nome Fantasia</label>
                            <input type="text" name="nome_fantasia" id="nome_fantasia" placeholder="Nome fantasia" required>

                            <label>Segmento</label>
                            <select name="segmento" required>
                                <option value="">Selecione o segmento</option>
                                <option>Comércio</option>
                                <option>Serviços</option>
                                <option>Tecnologia</option>
                                <option>Indústria</option>
                                <option>Saúde</option>
                                <option>Educação</option>
                                <option>Construção Civil</option>
                                <option>Logística</option>
                            </select>

                            <label>E-mail Corporativo</label>
                            <input type="email" name="email" id="email" placeholder="empresa@email.com" required>

                            <label>Telefone</label>
                            <input type="text" name="telefone" id="telefone" placeholder="(00) 00000-0000" required>

                            <label>Senha</label>
                            <div class="input-senha">
                                <input
                                    type="password"
                                    name="senha"
                                    id="senha"
                                    placeholder="Crie uma senha"
                                    required
                                >

                                <i
                                    class="bi bi-eye-fill olho-senha"
                                    onclick="toggleSenha('senha', this)"
                                ></i>
                            </div>

                            <label>Confirmar Senha</label>
                            <div class="input-senha">
                                <input
                                    type="password"
                                    name="confirmar_senha"
                                    id="confirmar_senha"
                                    placeholder="Confirme a senha"
                                    required
                                >

                                <i
                                    class="bi bi-eye-fill olho-senha"
                                    onclick="toggleSenha('confirmar_senha', this)"
                                ></i>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="form-card">

                        <div class="form-card-header">
                            <div>
                                <i class="bi bi-person-badge"></i>
                            </div>

                            <span>Responsável e Endereço</span>
                        </div>

                        <div class="form-card-body">

                            <label>Responsável pela Empresa</label>
                            <input type="text" name="responsavel" placeholder="Nome do responsável" required>

                            <label>Cargo do Responsável</label>
                            <input type="text" name="cargo" placeholder="Ex: Gerente de RH" required>

                            <label>Endereço</label>
                            <input type="text" name="endereco" id="endereco" placeholder="Rua, número e bairro" required>

                            <div class="row">

                                <div class="col-md-8">
                                    <label>Cidade</label>
                                    <input type="text" name="cidade" id="cidade" placeholder="Cidade" required>
                                </div>

                                <div class="col-md-4">
                                    <label>UF</label>
                                    <input type="text" name="estado" id="estado" placeholder="SP" maxlength="2" required>
                                </div>

                            </div>

                            <label>CEP</label>
                            <input type="text" name="cep" id="cep" placeholder="00000-000" required>

                            <div class="logo-upload">

                                <label>Logo da Empresa</label>

                                <input
                                    type="file"
                                    name="logo"
                                    id="logo"
                                    accept=".png,.jpg,.jpeg,.svg"
                                >

                                <div class="preview-box">
                                    <img id="preview" class="logo-preview">
                                    <span id="previewTexto">
                                        A prévia da logo aparecerá aqui
                                    </span>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="actions">

                <button
                    type="button"
                    class="btn btn-cadastro"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTermos"
                >
                    Criar conta
                    <i class="bi bi-arrow-right ms-2"></i>
                </button>

                <a href="login.php" class="link-login">
                    Já possui uma conta? Entrar
                </a>

            </div>

        </form>

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

<div class="modal fade" id="modalTermos" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content modal-premium">

            <div class="modal-header">

                <h5 class="modal-title fw-bold">
                    <i class="bi bi-file-earmark-lock me-2"></i>
                    Termos de Uso e Política de Privacidade
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <h6>1. Utilização da Plataforma</h6>
                <p>
                    A plataforma Meu Ponto Diário destina-se à gestão de recursos humanos,
                    controle de jornada, banco de horas, férias, documentos e demais
                    processos administrativos internos das empresas contratantes.
                </p>

                <h6>2. Responsabilidade das Informações</h6>
                <p>
                    A empresa contratante é responsável pela veracidade, legalidade e
                    atualização dos dados cadastrados no sistema, incluindo informações
                    de colaboradores, documentos, jornadas, eventos e registros administrativos.
                </p>

                <h6>3. Disponibilidade do Serviço</h6>
                <p>
                    Empregamos esforços razoáveis para manter a plataforma disponível de forma
                    contínua. Eventuais interrupções poderão ocorrer em razão de manutenção,
                    atualizações, falhas de infraestrutura, serviços de terceiros ou situações
                    fora do nosso controle.
                </p>

                <h6>4. Limitação de Responsabilidade</h6>
                <p>
                    O Meu Ponto Diário atua como ferramenta de apoio à gestão empresarial.
                    As decisões administrativas, trabalhistas, financeiras ou jurídicas tomadas
                    pelos usuários com base nas informações registradas permanecem sob
                    responsabilidade da empresa contratante.
                </p>

                <p>
                    Nossa responsabilidade limita-se à disponibilização e funcionamento da
                    plataforma, não abrangendo prejuízos decorrentes de informações incorretas,
                    omissões, uso inadequado do sistema ou descumprimento de obrigações legais
                    pela empresa usuária.
                </p>

                <h6>5. Segurança e Proteção de Dados</h6>
                <p>
                    Adotamos medidas técnicas e organizacionais razoáveis para proteção dos dados
                    armazenados. Entretanto, nenhum sistema conectado à internet pode garantir
                    segurança absoluta contra todos os riscos existentes.
                </p>

                <h6>6. Serviços de Terceiros</h6>
                <p>
                    Algumas funcionalidades poderão depender de serviços externos, integrações,
                    provedores de hospedagem, APIs, operadoras de comunicação ou outros
                    fornecedores. Não nos responsabilizamos por indisponibilidades ou falhas
                    originadas exclusivamente nesses serviços terceiros.
                </p>

                <h6>7. Backup e Guarda de Informações</h6>
                <p>
                    Embora sejam adotadas rotinas de proteção e armazenamento dos dados,
                    recomendamos que a empresa mantenha cópias próprias dos documentos e
                    informações considerados essenciais para suas operações.
                </p>

                <h6>8. Alterações da Plataforma</h6>
                <p>
                    Poderemos realizar melhorias, correções, atualizações e alterações de
                    funcionalidades visando a evolução do serviço, preservando sempre que
                    possível a continuidade das operações dos usuários.
                </p>

                <h6>9. Aceitação</h6>
                <p>
                    Ao concluir o cadastro e utilizar a plataforma, a empresa declara ter lido,
                    compreendido e concordado com estes Termos de Uso e Política de Privacidade.
                </p>

                <div class="form-check mt-4">

                    <input class="form-check-input" type="checkbox" id="aceitarTermos">

                    <label class="form-check-label fw-semibold" for="aceitarTermos">
                        Li e concordo com os Termos de Uso e Política de Privacidade.
                    </label>

                </div>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-secondary rounded-4 px-4" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" class="btn btn-primary rounded-4 px-4" id="btnFinalizarCadastro">
                    Aceitar e Criar Conta
                </button>

            </div>

        </div>

    </div>

</div>

<script>
const logo = document.getElementById('logo');
const preview = document.getElementById('preview');
const previewTexto = document.getElementById('previewTexto');

if (logo) {
    logo.addEventListener('change', function () {
        const arquivo = this.files[0];

        if (arquivo) {
            preview.src = URL.createObjectURL(arquivo);
            preview.style.display = 'block';
            previewTexto.style.display = 'none';
        }
    });
}

function toggleSenha(id, icone){

    const campo = document.getElementById(id);

    if(campo.type === 'password'){

        campo.type = 'text';

        icone.classList.remove('bi-eye-fill');
        icone.classList.add('bi-eye-slash-fill');

    } else {

        campo.type = 'password';

        icone.classList.remove('bi-eye-slash-fill');
        icone.classList.add('bi-eye-fill');

    }
}

const cnpjInput = document.getElementById('cnpj');

if (cnpjInput) {

    cnpjInput.addEventListener('blur', async function () {

        const cnpj = this.value.replace(/\D/g, '');

        if (cnpj.length !== 14) {
            return;
        }

        const valorOriginal = this.value;

        this.disabled = true;
        this.value = 'Consultando CNPJ...';

        try {

            const resposta = await fetch('consultar_cnpj.php?cnpj=' + cnpj);
            const texto = await resposta.text();
            const dados = JSON.parse(texto);

            this.disabled = false;
            this.value = valorOriginal;

            if (!dados.sucesso) {
                alert(dados.mensagem);
                return;
            }

            document.getElementById('razao_social').value = dados.razao_social || '';
            document.getElementById('nome_fantasia').value = dados.nome_fantasia || '';
            document.getElementById('email').value = dados.email || '';
            document.getElementById('telefone').value = dados.telefone || '';
            document.getElementById('endereco').value = dados.endereco || '';
            document.getElementById('cidade').value = dados.cidade || '';
            document.getElementById('estado').value = dados.estado || '';
            document.getElementById('cep').value = dados.cep || '';

            alert('CNPJ encontrado e ativo.');

        } catch (e) {

            this.disabled = false;
            this.value = valorOriginal;

            alert('Erro ao preencher os dados do CNPJ.');
        }

    });

}

document
.getElementById('btnFinalizarCadastro')
.addEventListener('click', function () {

    const aceitou = document.getElementById('aceitarTermos');

    if (!aceitou.checked) {
        alert('Você precisa aceitar os termos para continuar.');
        return;
    }

    document.querySelector('form').submit();

});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>