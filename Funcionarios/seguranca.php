<?php
session_start();

require_once '../config/database.php';
require_once '../lang.php';

$idEmpresa = $_SESSION['id_empresa'] ?? 0;
$idUsuario = $_SESSION['id_usuario'] ?? 0;
$idFuncionario = $_SESSION['id_funcionario'] ?? 0;

if (!$idEmpresa || !$idUsuario) {
    die("Sessão inválida. Faça login novamente.");
}

$nomeUsuario = '';

$stmt = $con->prepare("
    SELECT nome, id_funcionario
    FROM usuarios
    WHERE id_usuario = ?
    AND id_empresa = ?
    LIMIT 1
");

$stmt->bind_param("ii", $idUsuario, $idEmpresa);
$stmt->execute();

$usuario = $stmt->get_result()->fetch_assoc();

if ($usuario) {
    $nomeUsuario = $usuario['nome'] ?? '';

    if (!$idFuncionario && !empty($usuario['id_funcionario'])) {
        $idFuncionario = $usuario['id_funcionario'];
        $_SESSION['id_funcionario'] = $idFuncionario;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Segurança</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="../css/sidebarfunc.css">
<link rel="stylesheet" href="../css/global.css">

</head>

<body>

<?php include 'sidebarfunc.php'; ?>

<div class="position-fixed top-0 end-0 p-4" style="z-index:9999">

    <?php if(isset($_GET['sucesso'])) { ?>

        <div class="alert alert-success shadow-lg border-0 rounded-4">
            <i class="fa-solid fa-circle-check me-2"></i>
            Reporte enviado com sucesso!
        </div>

    <?php } ?>

</div>

<div class="content">

    <div class="title text-start">

        <h2>Reportar Ocorrência</h2>

        <p>
            Utilize o formulário abaixo para registrar situações de risco,
            comportamento inadequado ou problemas estruturais.
        </p>

        <small class="text-muted">
            Empresa #<?= htmlspecialchars($idEmpresa) ?>
        </small>

    </div>

    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body p-4 p-lg-5">

            <form
            id="reportForm"
            action="salvar_ocorrencia.php"
            method="POST"
            enctype="multipart/form-data">

                <input type="hidden" name="id_empresa" value="<?= htmlspecialchars($idEmpresa) ?>">
                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($idUsuario) ?>">
                <input type="hidden" name="id_funcionario" value="<?= htmlspecialchars($idFuncionario) ?>">

                <div class="row g-4">

                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Tipo de reporte
                        </label>

                        <div class="d-flex gap-4 mt-2">

                            <div class="form-check">
                                <input
                                class="form-check-input"
                                type="radio"
                                name="tipo_reporte"
                                id="naoAnonimo"
                                value="Identificado"
                                checked>

                                <label class="form-check-label" for="naoAnonimo">
                                    Identificado
                                </label>
                            </div>

                            <div class="form-check">
                                <input
                                class="form-check-input"
                                type="radio"
                                name="tipo_reporte"
                                id="anonimo"
                                value="Anônimo">

                                <label class="form-check-label" for="anonimo">
                                    Anônimo
                                </label>
                            </div>

                        </div>

                    </div>

                    <div class="col-12" id="nomeBox">

                        <label class="form-label fw-semibold">
                            Seu nome
                        </label>

                        <input
                        type="text"
                        class="form-control"
                        id="nomeInput"
                        name="nome"
                        value="<?= htmlspecialchars($nomeUsuario) ?>"
                        readonly>

                    </div>

                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Categoria
                        </label>

                        <select
                        class="form-select form-select-lg"
                        name="categoria"
                        required>

                            <option value="">Selecione uma categoria</option>
                            <option value="Assédio">Assédio</option>
                            <option value="Agressão">Agressão</option>
                            <option value="Discriminação">Discriminação</option>
                            <option value="Problema elétrico">Problema elétrico</option>
                            <option value="Equipamento danificado">Equipamento danificado</option>
                            <option value="Risco de acidente">Risco de acidente</option>
                            <option value="Vazamento">Vazamento</option>
                            <option value="Outro">Outro</option>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Andar
                        </label>

                        <select
                        class="form-select"
                        name="andar"
                        required>

                            <option value="">Selecione</option>
                            <option value="Térreo">Térreo</option>
                            <option value="1º Andar">1º Andar</option>
                            <option value="2º Andar">2º Andar</option>
                            <option value="3º Andar">3º Andar</option>

                        </select>

                    </div>

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Sala
                        </label>

                        <input
                        type="text"
                        class="form-control"
                        name="sala"
                        placeholder="Ex: Sala 12">

                    </div>

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Local específico
                        </label>

                        <input
                        type="text"
                        class="form-control"
                        name="local_especifico"
                        placeholder="Ex: Corredor, laboratório...">

                    </div>

                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Descrição detalhada
                        </label>

                        <textarea
                        class="form-control"
                        name="descricao"
                        rows="6"
                        placeholder="Descreva detalhadamente o ocorrido..."
                        required></textarea>

                    </div>

                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Pessoas envolvidas ou testemunhas
                        </label>

                        <input
                        type="text"
                        class="form-control"
                        name="testemunhas"
                        placeholder="Opcional">

                    </div>

                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Evidências
                        </label>

                        <input
                        type="file"
                        class="form-control"
                        name="evidencia">

                        <div class="form-text">
                            Fotos, vídeos ou documentos
                        </div>

                    </div>

                    <div class="col-12">

                        <button
                        type="submit"
                        class="btn btn-primary btn-lg w-100 rounded-4 py-3 fw-semibold">

                            <i class="fa-solid fa-paper-plane me-2"></i>
                            Enviar ocorrência

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

<script>
const anonimo = document.getElementById('anonimo');
const naoAnonimo = document.getElementById('naoAnonimo');

const nomeBox = document.getElementById('nomeBox');
const nomeInput = document.getElementById('nomeInput');

const nomeOriginal = "<?= htmlspecialchars($nomeUsuario, ENT_QUOTES) ?>";

anonimo.addEventListener('change', () => {
    nomeBox.style.display = 'none';
    nomeInput.value = '';
});

naoAnonimo.addEventListener('change', () => {
    nomeBox.style.display = 'block';
    nomeInput.value = nomeOriginal;
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/theme.js"></script>
</body>
</html>