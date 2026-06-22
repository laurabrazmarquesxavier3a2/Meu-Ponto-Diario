<?php
require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

$mensagem = '';
$erro = '';

function calcularDataFim(string $dataInicio, int $dias): string
{
    if ($dataInicio === '' || $dias <= 0) {
        return '';
    }

    $data = new DateTime($dataInicio);
    $data->modify('+' . ($dias - 1) . ' days');

    return $data->format('Y-m-d');
}

function removerAusenciasAtuaisEFuturas(
    mysqli $con,
    int $idFuncionario,
    int $idEmpresa,
    string $tabela,
    string $campoStatus
): void {
    $tabelasPermitidas = [
        'ferias' => 'status',
        'licencas_medicas' => 'status',
        'afastamentos' => 'status'
    ];

    if (
        !isset($tabelasPermitidas[$tabela]) ||
        $tabelasPermitidas[$tabela] !== $campoStatus
    ) {
        throw new Exception('Tabela de ausência inválida.');
    }

    $sql = "
        DELETE FROM {$tabela}
        WHERE id_funcionario = ?
          AND id_empresa = ?
          AND data_fim >= CURDATE()
    ";

    $stmt = $con->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            'Erro ao limpar períodos anteriores: ' . $con->error
        );
    }

    $stmt->bind_param('ii', $idFuncionario, $idEmpresa);

    if (!$stmt->execute()) {
        throw new Exception(
            'Erro ao limpar períodos anteriores: ' . $stmt->error
        );
    }

    $stmt->close();
}

/* EDITAR FUNCIONÁRIO */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_funcionario'])) {

    $idFuncionario = intval($_POST['id_funcionario'] ?? 0);
    $idUsuario = intval($_POST['id_usuario'] ?? 0);

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $horario = trim($_POST['horario'] ?? '');
    $tipo = trim($_POST['tipo'] ?? 'funcionario');
    $status = trim($_POST['status'] ?? 'ativo');

    $dataInicioAusencia = trim(
        $_POST['data_inicio_ausencia'] ?? ''
    );

    $duracaoAusencia = (int) (
        $_POST['duracao_ausencia'] ?? 0
    );

    $motivoAusencia = trim(
        $_POST['motivo_ausencia'] ?? ''
    );

    $dataFimAusencia = calcularDataFim(
        $dataInicioAusencia,
        $duracaoAusencia
    );

    if ($nome == '' || $email == '' || $cargo == '' || $departamento == '') {

        $erro = "Preencha nome, e-mail, cargo e departamento.";

    } else {

        $statusPermitidos = ['ativo', 'inativo', 'ferias', 'licenca', 'afastado'];

        if (!in_array($status, $statusPermitidos)) {
            $status = 'ativo';
        }

        $tiposPermitidos = ['funcionario', 'rh'];

        if (!in_array($tipo, $tiposPermitidos)) {
            $tipo = 'funcionario';
        }

        if (
            in_array($status, ['ferias', 'licenca', 'afastado'], true) &&
            (
                $dataInicioAusencia === '' ||
                $duracaoAusencia <= 0
            )
        ) {
            $erro =
                'Informe a data de início e a duração do período.';
        }

        if (
            $status === 'ferias' &&
            $duracaoAusencia > 30
        ) {
            $erro =
                'O período de férias não pode ultrapassar 30 dias.';
        }

        if ($erro !== '') {
            // Interrompe antes de consultar e atualizar o banco.
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

        $resultadoVerifica = $verifica->get_result();

        if ($resultadoVerifica->num_rows > 0) {

            $erro = "Este e-mail já está sendo usado por outro usuário.";

        } else {

            mysqli_begin_transaction($con);

            try {

                $stmtFunc = $con->prepare("
                    UPDATE funcionarios
                    SET
                        nome = ?,
                        cargo = ?,
                        departamento = ?,
                        horario_padrao = ?
                    WHERE id_funcionario = ?
                    AND id_empresa = ?
                ");

                if (!$stmtFunc) {
                    throw new Exception("Erro SQL funcionário: " . $con->error);
                }

                $stmtFunc->bind_param(
                    "ssssii",
                    $nome,
                    $cargo,
                    $departamento,
                    $horario,
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
                    "sssssssiii",
                    $nome,
                    $email,
                    $telefone,
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

                /*
                |--------------------------------------------------------------------------
                | PERÍODO DE FÉRIAS
                |--------------------------------------------------------------------------
                */

                if ($status === 'ferias') {

                    removerAusenciasAtuaisEFuturas(
                        $con,
                        $idFuncionario,
                        (int) $idEmpresa,
                        'ferias',
                        'status'
                    );

                    $stmtFerias = $con->prepare("
                        INSERT INTO ferias (
                            id_funcionario,
                            id_empresa,
                            data_inicio,
                            data_fim,
                            dias,
                            status,
                            data_solicitacao,
                            data_visto,
                            mensagem_colaborador,
                            alteracoes_restantes
                        )
                        VALUES (
                            ?, ?, ?, ?, ?,
                            'aprovado',
                            NOW(),
                            NOW(),
                            ?,
                            0
                        )
                    ");

                    if (!$stmtFerias) {
                        throw new Exception(
                            'Erro SQL férias: ' . $con->error
                        );
                    }

                    $mensagemFerias =
                        $motivoAusencia !== ''
                            ? $motivoAusencia
                            : 'Férias cadastradas pelo RH no perfil do funcionário.';

                    $stmtFerias->bind_param(
                        'iissis',
                        $idFuncionario,
                        $idEmpresa,
                        $dataInicioAusencia,
                        $dataFimAusencia,
                        $duracaoAusencia,
                        $mensagemFerias
                    );

                    if (!$stmtFerias->execute()) {
                        throw new Exception(
                            'Erro ao cadastrar férias: ' .
                            $stmtFerias->error
                        );
                    }

                    $stmtFerias->close();

                    /*
                     * Remove pontos que estejam dentro das férias.
                     */
                    $stmtLimparPontos = $con->prepare("
                        DELETE FROM pontos
                        WHERE id_funcionario = ?
                          AND id_empresa = ?
                          AND data BETWEEN ? AND ?
                    ");

                    if (!$stmtLimparPontos) {
                        throw new Exception(
                            'Erro ao preparar limpeza de pontos: ' .
                            $con->error
                        );
                    }

                    $stmtLimparPontos->bind_param(
                        'iiss',
                        $idFuncionario,
                        $idEmpresa,
                        $dataInicioAusencia,
                        $dataFimAusencia
                    );

                    $stmtLimparPontos->execute();
                    $stmtLimparPontos->close();
                }

                /*
                |--------------------------------------------------------------------------
                | PERÍODO DE LICENÇA MÉDICA
                |--------------------------------------------------------------------------
                */

                if ($status === 'licenca') {

                    removerAusenciasAtuaisEFuturas(
                        $con,
                        $idFuncionario,
                        (int) $idEmpresa,
                        'licencas_medicas',
                        'status'
                    );

                    $stmtLicenca = $con->prepare("
                        INSERT INTO licencas_medicas (
                            id_funcionario,
                            arquivo_atestado,
                            tipo_arquivo,
                            motivo,
                            data_inicio,
                            data_fim,
                            dias,
                            observacao,
                            id_empresa,
                            status,
                            data_visto,
                            mensagem_colaborador
                        )
                        VALUES (
                            ?,
                            'cadastro-rh-sem-anexo',
                            'sem_anexo',
                            ?,
                            ?, ?,
                            ?,
                            'Licença cadastrada diretamente pelo RH na edição do funcionário.',
                            ?,
                            'visto',
                            NOW(),
                            'Licença registrada e validada pelo RH.'
                        )
                    ");

                    if (!$stmtLicenca) {
                        throw new Exception(
                            'Erro SQL licença médica: ' . $con->error
                        );
                    }

                    $motivoFinalLicenca =
                        $motivoAusencia !== ''
                            ? $motivoAusencia
                            : 'Licença médica cadastrada pelo RH.';

                    $stmtLicenca->bind_param(
                        'isssii',
                        $idFuncionario,
                        $motivoFinalLicenca,
                        $dataInicioAusencia,
                        $dataFimAusencia,
                        $duracaoAusencia,
                        $idEmpresa
                    );

                    if (!$stmtLicenca->execute()) {
                        throw new Exception(
                            'Erro ao cadastrar licença médica: ' .
                            $stmtLicenca->error
                        );
                    }

                    $stmtLicenca->close();

                    $stmtLimparPontos = $con->prepare("
                        DELETE FROM pontos
                        WHERE id_funcionario = ?
                          AND id_empresa = ?
                          AND data BETWEEN ? AND ?
                    ");

                    if (!$stmtLimparPontos) {
                        throw new Exception(
                            'Erro ao preparar limpeza de pontos: ' .
                            $con->error
                        );
                    }

                    $stmtLimparPontos->bind_param(
                        'iiss',
                        $idFuncionario,
                        $idEmpresa,
                        $dataInicioAusencia,
                        $dataFimAusencia
                    );

                    $stmtLimparPontos->execute();
                    $stmtLimparPontos->close();
                }

                /*
                |--------------------------------------------------------------------------
                | PERÍODO DE AFASTAMENTO
                |--------------------------------------------------------------------------
                */

                if ($status === 'afastado') {

                    removerAusenciasAtuaisEFuturas(
                        $con,
                        $idFuncionario,
                        (int) $idEmpresa,
                        'afastamentos',
                        'status'
                    );

                    $stmtAfastamento = $con->prepare("
                        INSERT INTO afastamentos (
                            id_funcionario,
                            id_empresa,
                            tipo,
                            motivo,
                            data_inicio,
                            data_fim,
                            dias,
                            status,
                            observacao
                        )
                        VALUES (
                            ?, ?,
                            'Afastamento temporário',
                            ?,
                            ?, ?,
                            ?,
                            'aprovado',
                            'Cadastrado pelo RH na edição do funcionário.'
                        )
                    ");

                    if (!$stmtAfastamento) {
                        throw new Exception(
                            'Erro SQL afastamento: ' . $con->error
                        );
                    }

                    $motivoFinalAfastamento =
                        $motivoAusencia !== ''
                            ? $motivoAusencia
                            : 'Afastamento cadastrado pelo RH.';

                    $stmtAfastamento->bind_param(
                        'iisssi',
                        $idFuncionario,
                        $idEmpresa,
                        $motivoFinalAfastamento,
                        $dataInicioAusencia,
                        $dataFimAusencia,
                        $duracaoAusencia
                    );

                    if (!$stmtAfastamento->execute()) {
                        throw new Exception(
                            'Erro ao cadastrar afastamento: ' .
                            $stmtAfastamento->error
                        );
                    }

                    $stmtAfastamento->close();

                    $stmtLimparPontos = $con->prepare("
                        DELETE FROM pontos
                        WHERE id_funcionario = ?
                          AND id_empresa = ?
                          AND data BETWEEN ? AND ?
                    ");

                    if (!$stmtLimparPontos) {
                        throw new Exception(
                            'Erro ao preparar limpeza de pontos: ' .
                            $con->error
                        );
                    }

                    $stmtLimparPontos->bind_param(
                        'iiss',
                        $idFuncionario,
                        $idEmpresa,
                        $dataInicioAusencia,
                        $dataFimAusencia
                    );

                    $stmtLimparPontos->execute();
                    $stmtLimparPontos->close();
                }

                /*
                 * Ao voltar para ativo ou inativo, remove apenas períodos
                 * atuais e futuros. Registros históricos continuam salvos.
                 */
                if (in_array($status, ['ativo', 'inativo'], true)) {

                    removerAusenciasAtuaisEFuturas(
                        $con,
                        $idFuncionario,
                        (int) $idEmpresa,
                        'ferias',
                        'status'
                    );

                    removerAusenciasAtuaisEFuturas(
                        $con,
                        $idFuncionario,
                        (int) $idEmpresa,
                        'licencas_medicas',
                        'status'
                    );

                    removerAusenciasAtuaisEFuturas(
                        $con,
                        $idFuncionario,
                        (int) $idEmpresa,
                        'afastamentos',
                        'status'
                    );
                }

                mysqli_commit($con);

                if ($status === 'ferias') {
                    $mensagem =
                        'Funcionário atualizado e férias cadastradas de ' .
                        date('d/m/Y', strtotime($dataInicioAusencia)) .
                        ' até ' .
                        date('d/m/Y', strtotime($dataFimAusencia)) .
                        '.';
                } elseif ($status === 'licenca') {
                    $mensagem =
                        'Funcionário atualizado e licença cadastrada de ' .
                        date('d/m/Y', strtotime($dataInicioAusencia)) .
                        ' até ' .
                        date('d/m/Y', strtotime($dataFimAusencia)) .
                        '.';
                } elseif ($status === 'afastado') {
                    $mensagem =
                        'Funcionário atualizado e afastamento cadastrado de ' .
                        date('d/m/Y', strtotime($dataInicioAusencia)) .
                        ' até ' .
                        date('d/m/Y', strtotime($dataFimAusencia)) .
                        '.';
                } else {
                    $mensagem = 'Funcionário atualizado com sucesso.';
                }

            } catch (Exception $e) {

                mysqli_rollback($con);
                $erro = $e->getMessage();
            }
        }
        }
    }
}

/* BUSCAR FUNCIONÁRIOS DA EMPRESA */
$stmt = $con->prepare("
    SELECT
        funcionarios.id_funcionario,
        funcionarios.nome,
        funcionarios.cargo,
        funcionarios.departamento,
        funcionarios.horario_padrao,

        usuarios.id_usuario,
        usuarios.email,
        usuarios.telefone,
        usuarios.status,
        usuarios.tipo,
        usuarios.foto,

        (
            SELECT fe.data_inicio
            FROM ferias AS fe
            WHERE fe.id_funcionario = funcionarios.id_funcionario
              AND fe.id_empresa = funcionarios.id_empresa
              AND fe.status = 'aprovado'
              AND fe.data_fim >= CURDATE()
            ORDER BY fe.data_inicio DESC
            LIMIT 1
        ) AS ferias_inicio,

        (
            SELECT fe.dias
            FROM ferias AS fe
            WHERE fe.id_funcionario = funcionarios.id_funcionario
              AND fe.id_empresa = funcionarios.id_empresa
              AND fe.status = 'aprovado'
              AND fe.data_fim >= CURDATE()
            ORDER BY fe.data_inicio DESC
            LIMIT 1
        ) AS ferias_dias,

        (
            SELECT fe.mensagem_colaborador
            FROM ferias AS fe
            WHERE fe.id_funcionario = funcionarios.id_funcionario
              AND fe.id_empresa = funcionarios.id_empresa
              AND fe.status = 'aprovado'
              AND fe.data_fim >= CURDATE()
            ORDER BY fe.data_inicio DESC
            LIMIT 1
        ) AS ferias_motivo,

        (
            SELECT lm.data_inicio
            FROM licencas_medicas AS lm
            WHERE lm.id_funcionario = funcionarios.id_funcionario
              AND lm.id_empresa = funcionarios.id_empresa
              AND lm.status IN ('visto', 'aprovado')
              AND lm.data_fim >= CURDATE()
            ORDER BY lm.data_inicio DESC
            LIMIT 1
        ) AS licenca_inicio,

        (
            SELECT lm.dias
            FROM licencas_medicas AS lm
            WHERE lm.id_funcionario = funcionarios.id_funcionario
              AND lm.id_empresa = funcionarios.id_empresa
              AND lm.status IN ('visto', 'aprovado')
              AND lm.data_fim >= CURDATE()
            ORDER BY lm.data_inicio DESC
            LIMIT 1
        ) AS licenca_dias,

        (
            SELECT lm.motivo
            FROM licencas_medicas AS lm
            WHERE lm.id_funcionario = funcionarios.id_funcionario
              AND lm.id_empresa = funcionarios.id_empresa
              AND lm.status IN ('visto', 'aprovado')
              AND lm.data_fim >= CURDATE()
            ORDER BY lm.data_inicio DESC
            LIMIT 1
        ) AS licenca_motivo,

        (
            SELECT af.data_inicio
            FROM afastamentos AS af
            WHERE af.id_funcionario = funcionarios.id_funcionario
              AND af.id_empresa = funcionarios.id_empresa
              AND af.status = 'aprovado'
              AND af.data_fim >= CURDATE()
            ORDER BY af.data_inicio DESC
            LIMIT 1
        ) AS afastamento_inicio,

        (
            SELECT af.dias
            FROM afastamentos AS af
            WHERE af.id_funcionario = funcionarios.id_funcionario
              AND af.id_empresa = funcionarios.id_empresa
              AND af.status = 'aprovado'
              AND af.data_fim >= CURDATE()
            ORDER BY af.data_inicio DESC
            LIMIT 1
        ) AS afastamento_dias,

        (
            SELECT af.motivo
            FROM afastamentos AS af
            WHERE af.id_funcionario = funcionarios.id_funcionario
              AND af.id_empresa = funcionarios.id_empresa
              AND af.status = 'aprovado'
              AND af.data_fim >= CURDATE()
            ORDER BY af.data_inicio DESC
            LIMIT 1
        ) AS afastamento_motivo

    FROM funcionarios
    LEFT JOIN usuarios
    ON usuarios.id_funcionario = funcionarios.id_funcionario
    AND usuarios.id_empresa = funcionarios.id_empresa
    WHERE funcionarios.id_empresa = ?
    ORDER BY funcionarios.nome ASC
");

$stmt->bind_param("i", $idEmpresa);
$stmt->execute();
$funcionarios = $stmt->get_result();

/* CONTADORES */
$total = 0;
$ativos = 0;
$ferias = 0;
$licencas = 0;
$afastados = 0;

$listaFuncionarios = [];

while ($f = $funcionarios->fetch_assoc()) {

    $listaFuncionarios[] = $f;
    $total++;

    if ($f['status'] == 'ativo') {
        $ativos++;
    }

    if ($f['status'] == 'ferias') {
        $ferias++;
    }

    if ($f['status'] == 'licenca') {
        $licencas++;
    }

    if ($f['status'] == 'afastado') {
        $afastados++;
    }
}

function badgeStatus($status) {

    if ($status == 'ativo') {
        return '<span class="badge bg-success">Ativo</span>';
    }

    if ($status == 'inativo') {
        return '<span class="badge bg-secondary">Inativo</span>';
    }

    if ($status == 'ferias') {
        return '<span class="badge bg-info text-dark">Férias</span>';
    }

    if ($status == 'licenca') {
        return '<span class="badge bg-warning text-dark">Licença</span>';
    }

    if ($status == 'afastado') {
        return '<span class="badge bg-danger">Afastado</span>';
    }

    return '<span class="badge bg-light text-dark border">Sem status</span>';
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Funcionários</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="css/style.css">

<style>
.card-funcionario{
    border-radius:18px;
    transition:.25s;
}

.card-funcionario:hover{
    transform:translateY(-4px);
}

.avatar-funcionario{
    width:64px;
    height:64px;
    border-radius:50%;
    object-fit:cover;
}

.avatar-letra{
    width:64px;
    height:64px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    font-weight:700;
}

.icon-card{
    width:48px;
    height:48px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.modal{
    z-index:99999 !important;
}

.modal-backdrop{
    z-index:99998 !important;
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

#edit_data_fim_ausencia[readonly],
</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

<div class="container-fluid">

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">

        <div>
            <h1 class="fw-bold mb-1">
                Funcionários
            </h1>

            <p class="text-muted mb-0">
                Gerencie colaboradores e RH da empresa
            </p>
        </div>

        <div class="mt-3 mt-lg-0 d-flex gap-2">

            <a href="cadastrarUsuario.php" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>
                Cadastrar usuário
            </a>

            <a href="importar-funcionarios.php" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-arrow-up me-2"></i>
                Importar
            </a>

        </div>

    </div>

    <?php if($mensagem): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($mensagem) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($erro): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($erro) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- CARDS RESUMO -->
    <div class="row g-4 mb-4">

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-primary text-white fs-4">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Total</p>
                        <h3 class="fw-bold mb-0"><?= $total ?></h3>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-success text-white fs-4">
                        <i class="bi bi-person-check-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Ativos</p>
                        <h3 class="fw-bold mb-0"><?= $ativos ?></h3>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-info text-dark fs-4">
                        <i class="bi bi-umbrella-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Férias</p>
                        <h3 class="fw-bold mb-0"><?= $ferias ?></h3>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-card bg-warning text-dark fs-4">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>

                    <div>
                        <p class="text-muted mb-0">Licenças/Afast.</p>
                        <h3 class="fw-bold mb-0"><?= $licencas + $afastados ?></h3>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- BUSCA -->
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="row g-3 align-items-center">

                <div class="col-md-8">

                    <div class="input-group">

                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="text"
                            class="form-control"
                            id="pesquisarFuncionario"
                            placeholder="Pesquisar por nome, cargo, setor, e-mail ou status">

                    </div>

                </div>

                <div class="col-md-4">

                    <select class="form-select" id="filtroStatus">
                        <option value="">Todos os status</option>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                        <option value="ferias">Férias</option>
                        <option value="licenca">Licença</option>
                        <option value="afastado">Afastado</option>
                    </select>

                </div>

            </div>

        </div>

    </div>

    <!-- LISTA -->
    <div class="row g-4" id="listaFuncionarios">

        <?php if(count($listaFuncionarios) > 0): ?>

            <?php foreach($listaFuncionarios as $f): ?>

                <?php
                $status = $f['status'] ?? 'ativo';
                $telefone = $f['telefone'] ?? '-';
                $tipo = $f['tipo'] ?? 'funcionario';
                $idUsuario = $f['id_usuario'] ?? 0;
                ?>

                <div
                    class="col-lg-4 col-md-6 funcionario-item"
                    data-status="<?= htmlspecialchars($status) ?>"
                    data-texto="<?= strtolower(htmlspecialchars(($f['nome'] ?? '') . ' ' . ($f['cargo'] ?? '') . ' ' . ($f['departamento'] ?? '') . ' ' . ($f['email'] ?? '') . ' ' . $status)) ?>"
                >

                    <div class="card shadow-sm border-0 h-100 card-funcionario">

                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">

                                <?php if(!empty($f['foto'])): ?>

                                    <img
                                        src="<?= htmlspecialchars($f['foto']) ?>"
                                        class="avatar-funcionario me-3"
                                    >

                                <?php else: ?>

                                    <div class="avatar-letra bg-primary text-white me-3">
                                        <?= strtoupper(substr($f['nome'], 0, 1)) ?>
                                    </div>

                                <?php endif; ?>

                                <div class="flex-grow-1">

                                    <div class="d-flex justify-content-between align-items-start gap-2">

                                        <div>
                                            <h5 class="mb-1">
                                                <?= htmlspecialchars($f['nome']) ?>
                                            </h5>

                                            <small class="text-muted">
                                                <?= htmlspecialchars($f['cargo'] ?? '-') ?>
                                            </small>
                                        </div>

                                        <?= badgeStatus($status) ?>

                                    </div>

                                </div>

                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">

                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-building me-1"></i>
                                    <?= htmlspecialchars($f['departamento'] ?? '-') ?>
                                </span>

                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-shield-check me-1"></i>
                                    <?= $tipo == 'rh' ? 'RH' : 'Colaborador' ?>
                                </span>

                            </div>

                            <hr>

                            <div class="small text-muted">

                                <div class="mb-2">
                                    <i class="bi bi-envelope me-2"></i>
                                    <?= htmlspecialchars($f['email'] ?? '-') ?>
                                </div>

                                <div class="mb-2">
                                    <i class="bi bi-telephone me-2"></i>
                                    <?= htmlspecialchars($telefone) ?>
                                </div>

                                <div>
                                    <i class="bi bi-clock me-2"></i>
                                    <?= htmlspecialchars($f['horario_padrao'] ?? '-') ?>
                                </div>

                            </div>

                            <hr>

                            <div class="d-flex gap-2 mb-3">

                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm w-100 btnEditarFuncionario"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarFuncionario"
                                    data-id-funcionario="<?= htmlspecialchars($f['id_funcionario']) ?>"
                                    data-id-usuario="<?= htmlspecialchars($idUsuario) ?>"
                                    data-nome="<?= htmlspecialchars($f['nome'] ?? '') ?>"
                                    data-email="<?= htmlspecialchars($f['email'] ?? '') ?>"
                                    data-telefone="<?= htmlspecialchars($f['telefone'] ?? '') ?>"
                                    data-cargo="<?= htmlspecialchars($f['cargo'] ?? '') ?>"
                                    data-departamento="<?= htmlspecialchars($f['departamento'] ?? '') ?>"
                                    data-horario="<?= htmlspecialchars($f['horario_padrao'] ?? '') ?>"
                                    data-tipo="<?= htmlspecialchars($tipo) ?>"
                                    data-status="<?= htmlspecialchars($status) ?>"
                                    data-ferias-inicio="<?= htmlspecialchars($f['ferias_inicio'] ?? '') ?>"
                                    data-ferias-dias="<?= htmlspecialchars($f['ferias_dias'] ?? '') ?>"
                                    data-ferias-motivo="<?= htmlspecialchars($f['ferias_motivo'] ?? '') ?>"
                                    data-licenca-inicio="<?= htmlspecialchars($f['licenca_inicio'] ?? '') ?>"
                                    data-licenca-dias="<?= htmlspecialchars($f['licenca_dias'] ?? '') ?>"
                                    data-licenca-motivo="<?= htmlspecialchars($f['licenca_motivo'] ?? '') ?>"
                                    data-afastamento-inicio="<?= htmlspecialchars($f['afastamento_inicio'] ?? '') ?>"
                                    data-afastamento-dias="<?= htmlspecialchars($f['afastamento_dias'] ?? '') ?>"
                                    data-afastamento-motivo="<?= htmlspecialchars($f['afastamento_motivo'] ?? '') ?>"
                                    <?= !$idUsuario ? 'disabled' : '' ?>
                                >
                                    <i class="bi bi-pencil-square me-1"></i>
                                    Editar
                                </button>

                            </div>


                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="card border-0 shadow-sm">

                    <div class="card-body text-center py-5">

                        <i class="bi bi-people display-1 text-muted"></i>

                        <h4 class="fw-bold mt-3">
                            Nenhum funcionário cadastrado
                        </h4>

                        <p class="text-muted">
                            Cadastre manualmente ou importe uma planilha CSV.
                        </p>

                        <div class="d-flex justify-content-center gap-2">

                            <a href="cadastrarUsuario.php" class="btn btn-primary">
                                <i class="bi bi-person-plus me-2"></i>
                                Cadastrar usuário
                            </a>

                            <a href="importar-funcionarios.php" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-arrow-up me-2"></i>
                                Importar CSV
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

</div>

<!-- MODAL EDITAR FUNCIONÁRIO -->
<div class="modal fade" id="modalEditarFuncionario" tabindex="-1">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>
                    Editar funcionário
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

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
                            <label class="form-label fw-bold">Horário padrão</label>
                            <input type="time" name="horario" id="edit_horario" class="form-control">
                        </div>

                    </div>

                    <div class="modal-section-title">
                        Acesso e status
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de acesso</label>
                            <select name="tipo" id="edit_tipo" class="form-select" required>
                                <option value="funcionario">Funcionário</option>
                                <option value="rh">RH</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="edit_status" class="form-select" required>
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="ferias">Férias</option>
                                <option value="licenca">Licença</option>
                                <option value="afastado">Afastado</option>
                            </select>

                            <small class="text-muted">
                                Ao selecionar férias, licença ou afastamento,
                                os campos de período aparecerão abaixo.
                            </small>
                        </div>

                    </div>

                    <div
                        id="blocoPeriodoAusencia"
                        class="mt-4 d-none"
                    >

                        <div class="modal-section-title">
                            Período do status
                        </div>

                        <div class="alert alert-light border mb-3">
                            O período será usado para férias, licença médica ou afastamento. A data final será calculada automaticamente.
                        </div>

                        <div class="row g-3">

                            <div class="col-md-4">

                                <label class="form-label fw-bold">
                                    Data de início
                                </label>

                                <input
                                    type="date"
                                    name="data_inicio_ausencia"
                                    id="edit_data_inicio_ausencia"
                                    class="form-control"
                                >

                            </div>

                            <div class="col-md-4">

                                <label class="form-label fw-bold">
                                    Duração em dias
                                </label>

                                <input
                                    type="number"
                                    name="duracao_ausencia"
                                    id="edit_duracao_ausencia"
                                    class="form-control"
                                    min="1"
                                    max="365"
                                    placeholder="Ex.: 30"
                                >

                                <small
                                    class="text-muted"
                                    id="ajudaDuracaoAusencia"
                                ></small>

                            </div>

                            <div class="col-md-4">

                                <label class="form-label fw-bold">
                                    Data final
                                </label>

                                <input
                                    type="date"
                                    id="edit_data_fim_ausencia"
                                    class="form-control"
                                    readonly
                                >

                            </div>

                            <div class="col-12">

                                <label class="form-label fw-bold">
                                    Motivo ou observação
                                </label>

                                <textarea
                                    name="motivo_ausencia"
                                    id="edit_motivo_ausencia"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Descreva o motivo ou uma observação sobre o período"
                                ></textarea>

                            </div>

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
const pesquisa = document.getElementById('pesquisarFuncionario');
const filtroStatus = document.getElementById('filtroStatus');
const funcionarios = document.querySelectorAll('.funcionario-item');

function filtrarFuncionarios(){

    const termo = pesquisa.value.toLowerCase();
    const status = filtroStatus.value;

    funcionarios.forEach(function(card){

        const texto = card.dataset.texto;
        const statusCard = card.dataset.status;

        const combinaTexto = texto.includes(termo);
        const combinaStatus = status === '' || statusCard === status;

        if(combinaTexto && combinaStatus){
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }

    });
}

pesquisa.addEventListener('keyup', filtrarFuncionarios);
filtroStatus.addEventListener('change', filtrarFuncionarios);

/* PERÍODO DO MODAL DE EDIÇÃO */
const statusEdicao = document.getElementById('edit_status');
const blocoPeriodoAusencia = document.getElementById('blocoPeriodoAusencia');
const dataInicioAusencia = document.getElementById('edit_data_inicio_ausencia');
const duracaoAusencia = document.getElementById('edit_duracao_ausencia');
const dataFimAusencia = document.getElementById('edit_data_fim_ausencia');
const motivoAusencia = document.getElementById('edit_motivo_ausencia');
const ajudaDuracaoAusencia = document.getElementById('ajudaDuracaoAusencia');

function formatarDataInput(data) {
    if (!data) {
        return '';
    }

    return String(data).substring(0, 10);
}

function calcularFimPeriodo() {

    const inicio = dataInicioAusencia.value;
    const dias = parseInt(duracaoAusencia.value || '0', 10);

    if (!inicio || dias <= 0) {
        dataFimAusencia.value = '';
        return;
    }

    const partes = inicio.split('-');

    const data = new Date(
        Number(partes[0]),
        Number(partes[1]) - 1,
        Number(partes[2])
    );

    data.setDate(data.getDate() + dias - 1);

    const ano = data.getFullYear();
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const dia = String(data.getDate()).padStart(2, '0');

    dataFimAusencia.value = `${ano}-${mes}-${dia}`;
}

function atualizarCamposPeriodo(limpar = false) {

    const status = statusEdicao.value;
    const mostrar =
        status === 'ferias' ||
        status === 'licenca' ||
        status === 'afastado';

    blocoPeriodoAusencia.classList.toggle('d-none', !mostrar);

    dataInicioAusencia.required = mostrar;
    duracaoAusencia.required = mostrar;

    if (!mostrar) {
        if (limpar) {
            dataInicioAusencia.value = '';
            duracaoAusencia.value = '';
            dataFimAusencia.value = '';
            motivoAusencia.value = '';
        }

        return;
    }

    if (status === 'ferias') {

        duracaoAusencia.max = '30';
        ajudaDuracaoAusencia.textContent =
            'Para férias, informe de 1 a 30 dias.';

        if (!duracaoAusencia.value) {
            duracaoAusencia.value = '30';
        }

    } else if (status === 'licenca') {

        duracaoAusencia.max = '365';
        ajudaDuracaoAusencia.textContent =
            'Informe a duração da licença médica.';

        if (!duracaoAusencia.value) {
            duracaoAusencia.value = '1';
        }

    } else {

        duracaoAusencia.max = '365';
        ajudaDuracaoAusencia.textContent =
            'Informe a quantidade de dias do afastamento.';

        if (!duracaoAusencia.value) {
            duracaoAusencia.value = '1';
        }
    }

    if (!dataInicioAusencia.value) {
        dataInicioAusencia.value =
            new Date().toISOString().split('T')[0];
    }

    calcularFimPeriodo();
}

statusEdicao.addEventListener('change', function() {
    atualizarCamposPeriodo(true);
});

dataInicioAusencia.addEventListener(
    'change',
    calcularFimPeriodo
);

duracaoAusencia.addEventListener(
    'input',
    calcularFimPeriodo
);

/* PREENCHER MODAL DE EDIÇÃO */
document.querySelectorAll('.btnEditarFuncionario').forEach(function(botao){

    botao.addEventListener('click', function(){

        document.getElementById('edit_id_funcionario').value = this.dataset.idFuncionario;
        document.getElementById('edit_id_usuario').value = this.dataset.idUsuario;
        document.getElementById('edit_nome').value = this.dataset.nome;
        document.getElementById('edit_email').value = this.dataset.email;
        document.getElementById('edit_telefone').value = this.dataset.telefone;
        document.getElementById('edit_cargo').value = this.dataset.cargo;
        document.getElementById('edit_departamento').value = this.dataset.departamento;
        document.getElementById('edit_horario').value = this.dataset.horario;
        document.getElementById('edit_tipo').value = this.dataset.tipo;
        document.getElementById('edit_status').value = this.dataset.status;

        dataInicioAusencia.value = '';
        duracaoAusencia.value = '';
        dataFimAusencia.value = '';
        motivoAusencia.value = '';

        if (this.dataset.status === 'ferias') {

            dataInicioAusencia.value =
                formatarDataInput(
                    this.dataset.feriasInicio
                );

            duracaoAusencia.value =
                this.dataset.feriasDias || '30';

            motivoAusencia.value =
                this.dataset.feriasMotivo || '';

        } else if (this.dataset.status === 'licenca') {

            dataInicioAusencia.value =
                formatarDataInput(
                    this.dataset.licencaInicio
                );

            duracaoAusencia.value =
                this.dataset.licencaDias || '1';

            motivoAusencia.value =
                this.dataset.licencaMotivo || '';

        } else if (this.dataset.status === 'afastado') {

            dataInicioAusencia.value =
                formatarDataInput(
                    this.dataset.afastamentoInicio
                );

            duracaoAusencia.value =
                this.dataset.afastamentoDias || '1';

            motivoAusencia.value =
                this.dataset.afastamentoMotivo || '';
        }

        atualizarCamposPeriodo(false);
        calcularFimPeriodo();

    });

});
</script>

<script src="js/theme.js"></script>
<script src="js/translate.js"></script>

</body>
</html>