<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'registrar-atividade.php';

$mensagem = '';
$erros = [];
$importadosLista = [];

$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

if (!isset($_SESSION['senha_padrao_importacao'])) {
    $_SESSION['senha_padrao_importacao'] = '123456';
}

$senhaPadraoTexto = $_SESSION['senha_padrao_importacao'];

/* ALTERAR SENHA PADRÃO */
if (isset($_POST['alterar_senha_padrao'])) {

    $novaSenha = trim($_POST['nova_senha_padrao']);

    if ($novaSenha == '') {
        $erros[] = "Informe uma senha padrão válida.";
    } else {
        $_SESSION['senha_padrao_importacao'] = $novaSenha;
        $senhaPadraoTexto = $novaSenha;
        $mensagem = "Senha padrão alterada com sucesso.";
    }
}

/* EDITAR FUNCIONÁRIO */
if (isset($_POST['editar_funcionario'])) {

    $idFuncionario = intval($_POST['id_funcionario']);
    $idUsuario = intval($_POST['id_usuario']);

    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $cidade = trim($_POST['cidade']);
    $cargo = trim($_POST['cargo']);
    $departamento = trim($_POST['departamento']);
    $horario = trim($_POST['horario']);
    $escala = trim($_POST['escala']);
    $supervisor = trim($_POST['supervisor']);
    $tipo = trim($_POST['tipo']);
    $status = trim($_POST['status']);

    if ($nome == '' || $email == '') {

        $erros[] = "Nome e e-mail são obrigatórios.";

    } else {

        $verifica = $con->prepare("
            SELECT id_usuario
            FROM usuarios
            WHERE email = ?
            AND id_usuario <> ?
            AND id_empresa = ?
            LIMIT 1
        ");

        $verifica->bind_param("sii", $email, $idUsuario, $idEmpresa);
        $verifica->execute();

        $resultado = $verifica->get_result();

        if ($resultado->num_rows > 0) {

            $erros[] = "Este e-mail já está sendo usado por outro usuário.";

        } else {

            mysqli_begin_transaction($con);

            try {

                $stmtFunc = $con->prepare("
                    UPDATE funcionarios
                    SET 
                        nome = ?,
                        cargo = ?,
                        departamento = ?,
                        horario_padrao = ?,
                        escala = ?,
                        supervisor = ?
                    WHERE id_funcionario = ?
                    AND id_empresa = ?
                ");

                if (!$stmtFunc) {
                    throw new Exception("Erro SQL funcionário: " . $con->error);
                }

                $stmtFunc->bind_param(
                    "ssssssii",
                    $nome,
                    $cargo,
                    $departamento,
                    $horario,
                    $escala,
                    $supervisor,
                    $idFuncionario,
                    $idEmpresa
                );

                if (!$stmtFunc->execute()) {
                    throw new Exception("Erro ao atualizar funcionário: " . $stmtFunc->error);
                }

                $stmtUser = $con->prepare("
                    UPDATE usuarios
                    SET 
                        nome = ?,
                        email = ?,
                        telefone = ?,
                        cidade = ?,
                        cargo = ?,
                        departamento = ?,
                        tipo = ?,
                        status = ?
                    WHERE id_usuario = ?
                    AND id_funcionario = ?
                    AND id_empresa = ?
                ");

                if (!$stmtUser) {
                    throw new Exception("Erro SQL usuário: " . $con->error);
                }

                $stmtUser->bind_param(
                    "ssssssssiii",
                    $nome,
                    $email,
                    $telefone,
                    $cidade,
                    $cargo,
                    $departamento,
                    $tipo,
                    $status,
                    $idUsuario,
                    $idFuncionario,
                    $idEmpresa
                );

                if (!$stmtUser->execute()) {
                    throw new Exception("Erro ao atualizar usuário: " . $stmtUser->error);
                }

                if ($stmtUser->affected_rows === 0) {
                    $confere = $con->prepare("
                        SELECT id_usuario
                        FROM usuarios
                        WHERE id_usuario = ?
                        AND id_funcionario = ?
                        AND id_empresa = ?
                        LIMIT 1
                    ");

                    $confere->bind_param("iii", $idUsuario, $idFuncionario, $idEmpresa);
                    $confere->execute();

                    if ($confere->get_result()->num_rows == 0) {
                        throw new Exception("Usuário vinculado a este funcionário não foi encontrado.");
                    }
                }

                mysqli_commit($con);

                if (function_exists('registrarAtividade')) {
                    registrarAtividade($con, "Editou o funcionário " . $nome, "primary");
                }

                $mensagem = "Funcionário atualizado com sucesso.";

            } catch (Exception $e) {

                mysqli_rollback($con);
                $erros[] = $e->getMessage();
            }
        }
    }
}

/* EXCLUIR FUNCIONÁRIO */
if (isset($_POST['excluir_funcionario'])) {

    $idFuncionario = intval($_POST['id_funcionario']);

    mysqli_begin_transaction($con);

    try {

        $stmtUser = $con->prepare("
            DELETE FROM usuarios
            WHERE id_funcionario = ?
            AND id_empresa = ?
            AND tipo = 'funcionario'
        ");

        $stmtUser->bind_param("ii", $idFuncionario, $idEmpresa);
        $stmtUser->execute();

        $stmtFunc = $con->prepare("
            DELETE FROM funcionarios
            WHERE id_funcionario = ?
            AND id_empresa = ?
        ");

        $stmtFunc->bind_param("ii", $idFuncionario, $idEmpresa);

        if (!$stmtFunc->execute()) {
            throw new Exception("Erro ao excluir funcionário.");
        }

        mysqli_commit($con);
        $mensagem = "Funcionário excluído com sucesso.";

    } catch (Exception $e) {

        mysqli_rollback($con);
        $erros[] = $e->getMessage();
    }
}

/* LIMPAR FUNCIONÁRIOS */
if (isset($_POST['limpar_funcionarios'])) {

    mysqli_begin_transaction($con);

    try {

        $stmtUser = $con->prepare("
            DELETE FROM usuarios
            WHERE id_empresa = ?
            AND tipo = 'funcionario'
        ");

        $stmtUser->bind_param("i", $idEmpresa);
        $stmtUser->execute();

        $stmtFunc = $con->prepare("
            DELETE FROM funcionarios
            WHERE id_empresa = ?
        ");

        $stmtFunc->bind_param("i", $idEmpresa);

        if (!$stmtFunc->execute()) {
            throw new Exception("Erro ao limpar funcionários.");
        }

        mysqli_commit($con);
        $mensagem = "Todos os funcionários da empresa foram removidos.";

    } catch (Exception $e) {

        mysqli_rollback($con);
        $erros[] = $e->getMessage();
    }
}
/* IMPORTAR CSV COMPLETO */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['arquivo'])) {

    if ($_FILES['arquivo']['error'] == 0) {

        $arquivo = $_FILES['arquivo']['tmp_name'];
        $handle = fopen($arquivo, "r");

        if (!$handle) {
            die("Não foi possível abrir o arquivo.");
        }

        $linha = 0;
        $importados = 0;

        $cabecalho = fgets($handle);
        $separador = substr_count($cabecalho, ";") > substr_count($cabecalho, ",") ? ";" : ",";
        rewind($handle);

        while (($dados = fgetcsv($handle, 4000, $separador)) !== false) {

            $linha++;

            if ($linha == 1) {
                continue;
            }

            if (count($dados) < 11) {
                $erros[] = "Linha $linha ignorada: dados incompletos.";
                continue;
            }

            $nome = trim($dados[0]);
            $email = trim($dados[1]);
            $cargo = trim($dados[2]);
            $departamento = trim($dados[3]);
            $telefone = trim($dados[4]);
            $cidade = trim($dados[5]);
            $escala = trim($dados[6]);
            $supervisor = trim($dados[7]);
            $horario = trim($dados[8]);
            $status = strtolower(trim($dados[9]));
            $senhaCSV = trim($dados[10]);

            if ($nome == '') {
                $erros[] = "Linha $linha ignorada: nome vazio.";
                continue;
            }

            if ($horario == '') {
                $horario = '08:00:00';
            }

            if ($senhaCSV == '') {
                $senhaCSV = $senhaPadraoTexto;
            }

            $statusPermitidos = ['ativo', 'inativo', 'ferias', 'licenca', 'afastado'];

            if (!in_array($status, $statusPermitidos)) {
                $status = 'ativo';
            }

            mysqli_begin_transaction($con);

            try {

                $stmtFunc = $con->prepare("
                    INSERT INTO funcionarios (
                        nome,
                        cargo,
                        departamento,
                        horario_padrao,
                        escala,
                        supervisor,
                        id_empresa
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                if (!$stmtFunc) {
                    throw new Exception("Linha $linha: erro SQL funcionário: " . $con->error);
                }

                $stmtFunc->bind_param(
                    "ssssssi",
                    $nome,
                    $cargo,
                    $departamento,
                    $horario,
                    $escala,
                    $supervisor,
                    $idEmpresa
                );

                if (!$stmtFunc->execute()) {
                    throw new Exception("Linha $linha: erro ao criar funcionário: " . $stmtFunc->error);
                }

                $idFuncionario = $con->insert_id;

                if ($email == '') {
                    $loginBase = strtolower($nome);
                    $loginBase = iconv('UTF-8', 'ASCII//TRANSLIT', $loginBase);
                    $loginBase = preg_replace('/[^a-z0-9]+/', '.', $loginBase);
                    $loginBase = trim($loginBase, '.');

                    if ($loginBase == '') {
                        $loginBase = 'funcionario';
                    }

                    $email = $loginBase . $idFuncionario . '@empresa' . $idEmpresa . '.local';
                }

                $verifica = $con->prepare("
                    SELECT id_usuario
                    FROM usuarios
                    WHERE email = ?
                    LIMIT 1
                ");

                $verifica->bind_param("s", $email);
                $verifica->execute();

                if ($verifica->get_result()->num_rows > 0) {
                    throw new Exception("Linha $linha ignorada: e-mail já cadastrado ($email).");
                }

                $senhaHash = password_hash($senhaCSV, PASSWORD_DEFAULT);

                $stmtUser = $con->prepare("
                    INSERT INTO usuarios (
                        id_funcionario,
                        nome,
                        email,
                        senha,
                        tipo,
                        status,
                        telefone,
                        cidade,
                        cargo,
                        departamento,
                        id_empresa
                    )
                    VALUES (
                        ?, ?, ?, ?,
                        'funcionario',
                        ?,
                        ?, ?, ?, ?, ?
                    )
                ");

                if (!$stmtUser) {
                    throw new Exception("Linha $linha: erro SQL usuário: " . $con->error);
                }

                $stmtUser->bind_param(
                    "issssssssi",
                    $idFuncionario,
                    $nome,
                    $email,
                    $senhaHash,
                    $status,
                    $telefone,
                    $cidade,
                    $cargo,
                    $departamento,
                    $idEmpresa
                );

                if (!$stmtUser->execute()) {
                    throw new Exception("Linha $linha: erro ao criar usuário: " . $stmtUser->error);
                }

                $mesAtual = date('Y-m');

                $stmtBanco = $con->prepare("
                    INSERT INTO banco_horas (
                        id_funcionario,
                        mes,
                        saldo_total,
                        saldo_mes,
                        horas_extras_mes,
                        horas_debito_mes,
                        data_atualizacao,
                        status,
                        id_empresa
                    )
                    VALUES (?, ?, 0, 0, 0, 0, CURDATE(), 'neutro', ?)
                ");

                if ($stmtBanco) {
                    $stmtBanco->bind_param("isi", $idFuncionario, $mesAtual, $idEmpresa);
                    $stmtBanco->execute();
                }

                mysqli_commit($con);

                $importados++;

                $importadosLista[] = [
                    'nome' => $nome,
                    'email' => $email,
                    'cargo' => $cargo,
                    'departamento' => $departamento
                ];

            } catch (Exception $e) {

                mysqli_rollback($con);
                $erros[] = $e->getMessage();
            }
        }

        fclose($handle);

        $mensagem = "$importados funcionários importados com sucesso.";

    } else {
        $erros[] = "Erro ao enviar arquivo.";
    }
}

/* LISTAR FUNCIONÁRIOS */
$stmtLista = $con->prepare("
    SELECT 
        f.id_funcionario,
        f.nome,
        f.cargo,
        f.departamento,
        f.horario_padrao,
        f.escala,
        f.supervisor,

        u.id_usuario,
        u.email,
        u.telefone,
        u.cidade,
        u.tipo,
        u.status
    FROM funcionarios f
    LEFT JOIN usuarios u
    ON u.id_funcionario = f.id_funcionario
    AND u.id_empresa = f.id_empresa
    WHERE f.id_empresa = ?
    ORDER BY f.nome ASC
");

$stmtLista->bind_param("i", $idEmpresa);
$stmtLista->execute();

$funcionarios = $stmtLista->get_result();
$totalFuncionarios = $funcionarios->num_rows;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Importar Funcionários</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">

<style>
.upload-area{
    border:2px dashed #0d6efd;
    border-radius:18px;
    background:#f8fbff;
    transition:.25s;
}
.upload-area:hover{
    background:#eef6ff;
    transform:translateY(-2px);
}
.icon-circle{
    width:52px;
    height:52px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}
.table-card{
    border-radius:18px;
}
.modal{
    z-index:99999 !important;
}
.modal-backdrop{
    z-index:99998 !important;
}
.modal-dialog{
    z-index:100000 !important;
}
.modal-content{
    position:relative;
    z-index:100001 !important;
    pointer-events:auto !important;
}
body.modal-open .sidebar-overlay{
    display:none !important;
    opacity:0 !important;
    pointer-events:none !important;
}
.modal-section-title{
    font-size:14px;
    font-weight:700;
    color:#0d6efd;
    text-transform:uppercase;
    letter-spacing:.5px;
    border-bottom:1px solid #dee2e6;
    padding-bottom:8px;
    margin-bottom:16px;
}
</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid">

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">

        <div>
            <h1 class="fw-bold mb-1">Importar Funcionários</h1>
            <h5 class="text-muted">Cadastre e gerencie colaboradores vinculados à empresa.</h5>
        </div>

        <div class="mt-3 mt-lg-0">
            <span class="badge bg-primary fs-6 px-3 py-2">
                <i class="bi bi-building"></i>
                Empresa #<?= $idEmpresa ?>
            </span>
        </div>

    </div>

    <?php if($mensagem): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $mensagem ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(!empty($erros)): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm">
            <strong>
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Atenção:
            </strong>

            <ul class="mb-0 mt-2">
                <?php foreach($erros as $erro): ?>
                    <li><?= htmlspecialchars($erro) ?></li>
                <?php endforeach; ?>
            </ul>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-circle bg-primary text-white fs-3">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Funcionários cadastrados</p>
                        <h3 class="fw-bold mb-0"><?= $totalFuncionarios ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-circle bg-success text-white fs-3">
                        <i class="bi bi-file-earmark-arrow-up-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Importados agora</p>
                        <h3 class="fw-bold mb-0"><?= count($importadosLista) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-circle bg-warning text-dark fs-3">
                        <i class="bi bi-key-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Senha padrão</p>
                        <h3 class="fw-bold mb-0"><?= htmlspecialchars($senhaPadraoTexto) ?></h3>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        <div class="col-12 col-xl-5">

            <div class="card border-0 shadow-sm table-card">

                <div class="card-body p-4">

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-circle bg-primary text-white fs-3">
                            <i class="bi bi-cloud-upload"></i>
                        </div>

                        <div>
                            <h4 class="fw-bold mb-0">Enviar planilha</h4>
                            <p class="text-muted mb-0">Formato aceito: CSV separado por vírgula.</p>
                        </div>
                    </div>

                    <form method="POST" enctype="multipart/form-data" id="formImportar">

                        <label for="arquivo" class="upload-area p-4 text-center w-100 mb-3">

                            <i class="bi bi-filetype-csv text-primary display-3"></i>

                            <h5 class="fw-bold mt-3">Clique para escolher o arquivo</h5>

                            <p class="text-muted mb-2">ou arraste sua planilha CSV aqui</p>

                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" id="nomeArquivo">
                                Nenhum arquivo selecionado
                            </span>

                            <input
                                type="file"
                                name="arquivo"
                                id="arquivo"
                                class="d-none"
                                accept=".csv"
                                required
                            >

                        </label>

                        <button
                            type="submit"
                            name="importar"
                            class="btn btn-primary btn-lg w-100"
                        >
                            <i class="bi bi-upload me-2"></i>
                            Importar Funcionários
                        </button>

                    </form>

                    <button
                        type="button"
                        class="btn btn-outline-primary w-100 mt-3"
                        data-bs-toggle="modal"
                        data-bs-target="#modalSenhaPadrao"
                    >
                        <i class="bi bi-key-fill me-2"></i>
                        Alterar senha padrão
                    </button>

                    <hr class="my-4">

                    <h5 class="fw-bold">
                        <i class="bi bi-table me-2"></i>
                        Modelo da planilha
                    </h5>

                    <div class="bg-light border rounded-3 p-3 small">
<pre class="mb-0">nome,email,cargo,departamento,telefone,cidade,escala,supervisor,horario_padrao,status,senha
João Silva,joao@empresa.com,Analista de TI,TI,11999991111,São Paulo,5x2,Carlos Mendes,08:00:00,ativo,123456</pre>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        O login do colaborador será feito pelo e-mail cadastrado.
                    </div>

                </div>

            </div>

            <?php if(!empty($importadosLista)): ?>
                <div class="card border-0 shadow-sm mt-4 table-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Funcionários adicionados agora
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach($importadosLista as $item): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-bold">
                                            <?= htmlspecialchars($item['nome']) ?>
                                        </div>

                                        <small class="text-muted">
                                            <?= htmlspecialchars($item['email']) ?>
                                        </small>
                                    </div>

                                    <span class="badge bg-success rounded-pill">
                                        <?= htmlspecialchars($item['departamento']) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <div class="col-12 col-xl-7">

            <div class="card border-0 shadow-sm table-card">

                <div class="card-body p-4">

                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">

                        <div>
                            <h4 class="fw-bold mb-0">Funcionários cadastrados</h4>
                            <p class="text-muted mb-0">Edite dados de login, perfil e informações profissionais.</p>
                        </div>

                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja remover TODOS os funcionários desta empresa?')">
                            <button
                                type="submit"
                                name="limpar_funcionarios"
                                class="btn btn-outline-danger"
                            >
                                <i class="bi bi-trash3 me-1"></i>
                                Limpar todos
                            </button>
                        </form>

                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="text"
                            id="buscarFuncionario"
                            class="form-control"
                            placeholder="Buscar por nome, e-mail, cargo ou departamento..."
                        >
                    </div>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle" id="tabelaFuncionarios">

                            <thead class="table-light">
                                <tr>
                                    <th>Funcionário</th>
                                    <th>Cargo</th>
                                    <th>Departamento</th>
                                    <th>Status</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>

                            <tbody>

                            <?php if($funcionarios->num_rows > 0): ?>

                                <?php while($func = $funcionarios->fetch_assoc()): ?>

                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="icon-circle bg-primary-subtle text-primary fw-bold">
                                                    <?= strtoupper(substr($func['nome'], 0, 1)) ?>
                                                </div>

                                                <div>
                                                    <div class="fw-bold">
                                                        <?= htmlspecialchars($func['nome']) ?>
                                                    </div>

                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($func['email'] ?? '-') ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($func['cargo'] ?? '-') ?>
                                        </td>

                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($func['departamento'] ?? '-') ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php
                                            $status = $func['status'] ?? 'ativo';

                                            if ($status == 'ativo') {
                                                echo '<span class="badge bg-success">Ativo</span>';
                                            } elseif ($status == 'inativo') {
                                                echo '<span class="badge bg-secondary">Inativo</span>';
                                            } elseif ($status == 'ferias') {
                                                echo '<span class="badge bg-info text-dark">Férias</span>';
                                            } elseif ($status == 'licenca') {
                                                echo '<span class="badge bg-warning text-dark">Licença</span>';
                                            } elseif ($status == 'afastado') {
                                                echo '<span class="badge bg-danger">Afastado</span>';
                                            }
                                            ?>
                                        </td>

                                        <td class="text-end">
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm btnEditarFuncionario"
                                                data-id-funcionario="<?= $func['id_funcionario'] ?>"
                                                data-id-usuario="<?= $func['id_usuario'] ?>"
                                                data-nome="<?= htmlspecialchars($func['nome']) ?>"
                                                data-email="<?= htmlspecialchars($func['email'] ?? '') ?>"
                                                data-telefone="<?= htmlspecialchars($func['telefone'] ?? '') ?>"
                                                data-cidade="<?= htmlspecialchars($func['cidade'] ?? '') ?>"
                                                data-cargo="<?= htmlspecialchars($func['cargo'] ?? '') ?>"
                                                data-departamento="<?= htmlspecialchars($func['departamento'] ?? '') ?>"
                                                data-horario="<?= htmlspecialchars($func['horario_padrao'] ?? '') ?>"
                                                data-escala="<?= htmlspecialchars($func['escala'] ?? '') ?>"
                                                data-supervisor="<?= htmlspecialchars($func['supervisor'] ?? '') ?>"
                                                data-tipo="<?= htmlspecialchars($func['tipo'] ?? 'funcionario') ?>"
                                                data-status="<?= htmlspecialchars($func['status'] ?? 'ativo') ?>"
                                            >
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <form
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Deseja excluir este funcionário?')"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="id_funcionario"
                                                    value="<?= $func['id_funcionario'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    name="excluir_funcionario"
                                                    class="btn btn-outline-danger btn-sm"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="bi bi-people text-muted display-1"></i>

                                        <h5 class="fw-bold mt-3">
                                            Nenhum funcionário cadastrado
                                        </h5>

                                        <p class="text-muted">
                                            Importe uma planilha para começar.
                                        </p>
                                    </td>
                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</div>

<!-- MODAL SENHA PADRÃO -->
<div class="modal fade" id="modalSenhaPadrao" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-key-fill me-2"></i>
                    Alterar senha padrão
                </h5>
            </div>

            <form method="POST">

                <div class="modal-body">
                    <label class="form-label fw-bold">
                        Nova senha padrão
                    </label>

                    <input
                        type="text"
                        name="nova_senha_padrao"
                        class="form-control"
                        value="<?= htmlspecialchars($senhaPadraoTexto) ?>"
                        required
                    >

                    <div class="alert alert-info mt-3 mb-0">
                        Essa senha será usada apenas nas próximas importações.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        name="alterar_senha_padrao"
                        class="btn btn-primary"
                    >
                        Salvar senha
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- MODAL EDITAR FUNCIONÁRIO -->
<div class="modal fade" id="modalEditarFuncionario" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    Editar funcionário
                </h5>
            </div>

            <form method="POST">

                <div class="modal-body">

                    <input type="hidden" name="id_funcionario" id="edit_id_funcionario">
                    <input type="hidden" name="id_usuario" id="edit_id_usuario">

                    <div class="modal-section-title">
                        Dados pessoais e login
                    </div>

                    <div class="row g-3 mb-4">

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nome completo</label>
                            <input type="text" name="nome" id="edit_nome" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">E-mail de login</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                            <small class="text-muted">
                                Esse e-mail será usado para o funcionário entrar no sistema.
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Telefone</label>
                            <input type="text" name="telefone" id="edit_telefone" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cidade</label>
                            <input type="text" name="cidade" id="edit_cidade" class="form-control">
                        </div>

                    </div>

                    <div class="modal-section-title">
                        Dados profissionais
                    </div>

                    <div class="row g-3 mb-4">

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cargo</label>
                            <input type="text" name="cargo" id="edit_cargo" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Departamento</label>
                            <input type="text" name="departamento" id="edit_departamento" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Supervisor</label>
                            <input type="text" name="supervisor" id="edit_supervisor" class="form-control">
                        </div>

                    </div>

                    <div class="modal-section-title">
                        Jornada e acesso
                    </div>

                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Horário padrão</label>
                            <input type="time" name="horario" id="edit_horario" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Escala</label>
                            <input type="text" name="escala" id="edit_escala" class="form-control" placeholder="5x2, 6x1">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tipo de acesso</label>
                            <select name="tipo" id="edit_tipo" class="form-select" required>
                                <option value="funcionario">Funcionário</option>
                                <option value="rh">RH</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="ferias">Férias</option>
                                <option value="licenca">Licença</option>
                                <option value="afastado">Afastado</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        name="editar_funcionario"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-check-lg me-1"></i>
                        Salvar alterações
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const inputArquivo = document.getElementById('arquivo');
const nomeArquivo = document.getElementById('nomeArquivo');
const formImportar = document.getElementById('formImportar');

inputArquivo.addEventListener('change', function(){
    if(this.files.length > 0){
        nomeArquivo.innerHTML = '<i class="bi bi-file-earmark-check me-1"></i>' + this.files[0].name;
        nomeArquivo.className = 'badge bg-success-subtle text-success border border-success-subtle';
    }
});

formImportar.addEventListener('submit', function(){
    const botao = this.querySelector('button[type="submit"]');

    botao.disabled = true;
    botao.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Importando...
    `;
});

document.getElementById('buscarFuncionario').addEventListener('keyup', function(){

    const termo = this.value.toLowerCase();
    const linhas = document.querySelectorAll('#tabelaFuncionarios tbody tr');

    linhas.forEach(function(linha){
        const texto = linha.innerText.toLowerCase();

        linha.style.display = texto.includes(termo) ? '' : 'none';
    });
});

document.querySelectorAll('.btnEditarFuncionario').forEach(function(botao){

    botao.addEventListener('click', function(){

        document.getElementById('edit_id_funcionario').value = this.dataset.idFuncionario;
        document.getElementById('edit_id_usuario').value = this.dataset.idUsuario;
        document.getElementById('edit_nome').value = this.dataset.nome;
        document.getElementById('edit_email').value = this.dataset.email;
        document.getElementById('edit_telefone').value = this.dataset.telefone;
        document.getElementById('edit_cidade').value = this.dataset.cidade;
        document.getElementById('edit_cargo').value = this.dataset.cargo;
        document.getElementById('edit_departamento').value = this.dataset.departamento;
        document.getElementById('edit_horario').value = this.dataset.horario;
        document.getElementById('edit_escala').value = this.dataset.escala;
        document.getElementById('edit_supervisor').value = this.dataset.supervisor;
        document.getElementById('edit_tipo').value = this.dataset.tipo;
        document.getElementById('edit_status').value = this.dataset.status;

        const modal = new bootstrap.Modal(
            document.getElementById('modalEditarFuncionario')
        );

        modal.show();
    });
});
</script>

<script src="js/theme.js"></script>

</body>
</html>