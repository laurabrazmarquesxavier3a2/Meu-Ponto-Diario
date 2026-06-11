<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';


$idEmpresa = $_SESSION['id_empresa'] ?? null;
$nomeUsuario = $_SESSION['nome'] ?? 'Administrador';

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['salvar_comunicado'])) {

        $titulo = trim($_POST['titulo']);
        $conteudo = trim($_POST['conteudo']);
        $categoria = trim($_POST['categoria']);
        $publico = trim($_POST['publico']);
        $fixado = isset($_POST['fixado']) ? 1 : 0;

        if ($titulo == '' || $conteudo == '') {
            $erro = "Preencha título e conteúdo.";
        } else {

            $stmt = $con->prepare("
                INSERT INTO comunicados
                (
                    titulo,
                    conteudo,
                    categoria,
                    fixado,
                    autor,
                    publico,
                    id_empresa
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sssissi",
                $titulo,
                $conteudo,
                $categoria,
                $fixado,
                $nomeUsuario,
                $publico,
                $idEmpresa
            );

            if ($stmt->execute()) {
                $mensagem = "Comunicado publicado com sucesso.";
            } else {
                $erro = "Erro ao publicar comunicado: " . $stmt->error;
            }
        }
    }

    if (isset($_POST['excluir_comunicado'])) {

        $id = intval($_POST['id']);

        $stmt = $con->prepare("
            DELETE FROM comunicados
            WHERE id = ?
            AND id_empresa = ?
        ");

        $stmt->bind_param("ii", $id, $idEmpresa);

        if ($stmt->execute()) {
            $mensagem = "Comunicado excluído com sucesso.";
        } else {
            $erro = "Erro ao excluir comunicado.";
        }
    }

    if (isset($_POST['alternar_fixado'])) {

        $id = intval($_POST['id']);
        $fixadoAtual = intval($_POST['fixado_atual']);
        $novoFixado = $fixadoAtual ? 0 : 1;

        $stmt = $con->prepare("
            UPDATE comunicados
            SET fixado = ?
            WHERE id = ?
            AND id_empresa = ?
        ");

        $stmt->bind_param("iii", $novoFixado, $id, $idEmpresa);

        if ($stmt->execute()) {
            $mensagem = $novoFixado ? "Comunicado fixado." : "Comunicado desfixado.";
        } else {
            $erro = "Erro ao alterar fixação.";
        }
    }
}

$totalComunicados = 0;
$totalFixados = 0;
$mesComunicados = 0;

$stmtTotal = $con->prepare("
    SELECT COUNT(*) AS total
    FROM comunicados
    WHERE id_empresa = ?
");
$stmtTotal->bind_param("i", $idEmpresa);
$stmtTotal->execute();
$totalComunicados = $stmtTotal->get_result()->fetch_assoc()['total'];

$stmtFixados = $con->prepare("
    SELECT COUNT(*) AS total
    FROM comunicados
    WHERE id_empresa = ?
    AND fixado = 1
");
$stmtFixados->bind_param("i", $idEmpresa);
$stmtFixados->execute();
$totalFixados = $stmtFixados->get_result()->fetch_assoc()['total'];

$stmtMes = $con->prepare("
    SELECT COUNT(*) AS total
    FROM comunicados
    WHERE id_empresa = ?
    AND MONTH(data_publicacao) = MONTH(CURRENT_DATE())
    AND YEAR(data_publicacao) = YEAR(CURRENT_DATE())
");
$stmtMes->bind_param("i", $idEmpresa);
$stmtMes->execute();
$mesComunicados = $stmtMes->get_result()->fetch_assoc()['total'];

$stmtComunicados = $con->prepare("
    SELECT *
    FROM comunicados
    WHERE id_empresa = ?
    ORDER BY fixado DESC, data_publicacao DESC
");

$stmtComunicados->bind_param("i", $idEmpresa);
$stmtComunicados->execute();
$resultado = $stmtComunicados->get_result();

function badgeCategoria($categoria) {

    if ($categoria == 'Urgente') {
        return 'bg-danger';
    }

    if ($categoria == 'Evento') {
        return 'bg-success';
    }

    if ($categoria == 'Comemoração') {
        return 'bg-warning text-dark';
    }

    if ($categoria == 'Aviso') {
        return 'bg-info text-dark';
    }

    return 'bg-primary';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Comunicados</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
.card-dashboard{
    border-radius:16px;
    border:none;
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
}

.card-comunicado{
    border-radius:18px;
    transition:.25s;
}

.card-comunicado:hover{
    transform:translateY(-3px);
}

.card-fixado{
    background:#fff9e6;
    border-left:6px solid #f59e0b !important;
}

.icon-card{
    width:50px;
    height:50px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.modal-header{
    min-height:70px;
}

.modal-title{
    padding-right:60px;
}

.btn-x{
    position:absolute;
    right:20px;
    top:18px;
}
</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid">

        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">

            <div>
                <h1 class="fw-bold mb-1">
                    Comunicados
                </h1>

                <h5 class="text-muted">
                    Avisos importantes para os funcionários da empresa
                </h5>
            </div>

            <button
                class="btn btn-primary px-4 py-2 mt-3 mt-lg-0"
                data-bs-toggle="modal"
                data-bs-target="#modalComunicado"
            >
                <i class="bi bi-megaphone-fill me-2"></i>
                Novo comunicado
            </button>

        </div>

        <?php if($mensagem): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $mensagem ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= $erro ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-4">

            <div class="col-12 col-md-3">
                <div class="card card-dashboard h-100">
                    <div class="card-body d-flex align-items-center gap-3">

                        <div class="icon-card bg-primary text-white fs-4">
                            <i class="bi bi-bell-fill"></i>
                        </div>

                        <div>
                            <small class="text-muted">Total</small>
                            <h2 class="fw-bold mb-0"><?= $totalComunicados ?></h2>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="card card-dashboard h-100">
                    <div class="card-body d-flex align-items-center gap-3">

                        <div class="icon-card bg-warning text-dark fs-4">
                            <i class="bi bi-pin-angle-fill"></i>
                        </div>

                        <div>
                            <small class="text-muted">Fixados</small>
                            <h2 class="fw-bold mb-0"><?= $totalFixados ?></h2>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="card card-dashboard h-100">
                    <div class="card-body d-flex align-items-center gap-3">

                        <div class="icon-card bg-success text-white fs-4">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>

                        <div>
                            <small class="text-muted">Este mês</small>
                            <h2 class="fw-bold mb-0"><?= $mesComunicados ?></h2>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="card card-dashboard h-100">
                    <div class="card-body d-flex align-items-center gap-3">

                        <div class="icon-card bg-info text-dark fs-4">
                            <i class="bi bi-people-fill"></i>
                        </div>

                        <div>
                            <small class="text-muted">Alcance</small>
                            <h2 class="fw-bold mb-0">RH</h2>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                <div class="input-group">

                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>

                    <input
                        type="text"
                        id="buscarComunicado"
                        class="form-control"
                        placeholder="Buscar comunicado por título, conteúdo, categoria ou autor..."
                    >

                </div>

            </div>
        </div>

        <div class="row g-4" id="listaComunicados">

            <?php if($resultado->num_rows > 0): ?>

                <?php while($row = $resultado->fetch_assoc()): ?>

                    <div
                        class="col-12 comunicado-item"
                        data-texto="<?= strtolower(htmlspecialchars($row['titulo'] . ' ' . $row['conteudo'] . ' ' . $row['categoria'] . ' ' . $row['autor'])) ?>"
                    >

                        <div class="card card-dashboard card-comunicado p-4 <?= $row['fixado'] ? 'card-fixado' : '' ?>">

                            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">

                                <div class="d-flex align-items-center gap-2 flex-wrap">

                                    <span class="badge <?= badgeCategoria($row['categoria']) ?>">
                                        <?= htmlspecialchars($row['categoria']) ?>
                                    </span>

                                    <?php if($row['fixado']): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-pin-angle-fill"></i>
                                            Fixado
                                        </span>
                                    <?php endif; ?>

                                    <span class="badge bg-light text-dark border">
                                        <?= htmlspecialchars($row['publico'] ?? 'Todos') ?>
                                    </span>

                                </div>

                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($row['data_publicacao'])) ?>
                                </small>

                            </div>

                            <h4 class="fw-bold mb-3">
                                <?= htmlspecialchars($row['titulo']) ?>
                            </h4>

                            <p class="text-muted mb-3">
                                <?= nl2br(htmlspecialchars($row['conteudo'])) ?>
                            </p>

                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">

                                <small class="text-secondary">
                                    <i class="bi bi-person-circle me-1"></i>
                                    Por <?= htmlspecialchars($row['autor']) ?>
                                </small>

                                <div class="d-flex gap-2">

                                    <form method="POST">

                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="fixado_atual" value="<?= $row['fixado'] ?>">

                                        <button
                                            type="submit"
                                            name="alternar_fixado"
                                            class="btn btn-outline-warning btn-sm"
                                        >
                                            <i class="bi bi-pin-angle"></i>
                                            <?= $row['fixado'] ? 'Desfixar' : 'Fixar' ?>
                                        </button>

                                    </form>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Deseja excluir este comunicado?')"
                                    >

                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                        <button
                                            type="submit"
                                            name="excluir_comunicado"
                                            class="btn btn-outline-danger btn-sm"
                                        >
                                            <i class="bi bi-trash"></i>
                                            Excluir
                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="col-12">

                    <div class="card card-dashboard p-5 text-center">

                        <i class="bi bi-bell-slash display-1 text-secondary"></i>

                        <h4 class="mt-3 fw-bold">
                            Nenhum comunicado encontrado
                        </h4>

                        <p class="text-muted">
                            Publique o primeiro comunicado para sua equipe.
                        </p>

                        <button
                            class="btn btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalComunicado"
                        >
                            <i class="bi bi-megaphone-fill me-2"></i>
                            Novo comunicado
                        </button>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<div class="modal fade" id="modalComunicado" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <form method="POST" class="modal-content border-0 shadow">

            <div class="modal-header bg-primary text-white position-relative">

                <h5 class="modal-title fw-bold">
                    <i class="bi bi-megaphone-fill me-2"></i>
                    Novo Comunicado
                </h5>

        

            </div>

            <div class="modal-body p-4">

                <div class="mb-3">

                    <label class="form-label fw-bold">
                        Título
                    </label>

                    <input
                        type="text"
                        name="titulo"
                        class="form-control"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label fw-bold">
                        Conteúdo
                    </label>

                    <textarea
                        name="conteudo"
                        class="form-control"
                        rows="5"
                        required
                    ></textarea>

                </div>

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Categoria
                        </label>

                        <select
                            name="categoria"
                            class="form-select"
                        >
                            <option>Aviso</option>
                            <option>Política</option>
                            <option>Evento</option>
                            <option>Comemoração</option>
                            <option>Urgente</option>
                        </select>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label fw-bold">
                            Público
                        </label>

                        <select
                            name="publico"
                            class="form-select"
                        >
                            <option>Todos</option>
                            <option>RH</option>
                            <option>Funcionários</option>
                            <option>Gestores</option>
                        </select>

                    </div>

                </div>

                <div class="form-check mt-4">

                    <input
                        type="checkbox"
                        name="fixado"
                        class="form-check-input"
                        id="fixado"
                    >

                    <label class="form-check-label fw-semibold" for="fixado">
                        Fixar comunicado no topo
                    </label>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    name="salvar_comunicado"
                    class="btn btn-primary"
                >
                    <i class="bi bi-send-fill me-2"></i>
                    Publicar
                </button>

            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const buscar = document.getElementById('buscarComunicado');
const comunicados = document.querySelectorAll('.comunicado-item');

buscar.addEventListener('keyup', function(){

    const termo = this.value.toLowerCase();

    comunicados.forEach(function(item){

        const texto = item.dataset.texto;

        if(texto.includes(termo)){
            item.style.display = '';
        }else{
            item.style.display = 'none';
        }

    });

});
</script>
<script src="js/theme.js"></script>
<script src="js/translate.js"></script>
</body>
</html>