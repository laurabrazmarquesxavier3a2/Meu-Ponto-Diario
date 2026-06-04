<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../auth.php';
require_once '../config/database.php';
require_once '../lang.php';

$idEmpresa = $_SESSION['id_empresa'] ?? null;
$idUsuario = $_SESSION['id_usuario'] ?? null;
$idFuncionario = $_SESSION['id_funcionario'] ?? null;

if (!$idEmpresa || !$idUsuario) {
    die("Sessão inválida. Faça login novamente.");
}

/* BUSCA FUNCIONÁRIO LOGADO */
if (!$idFuncionario) {
    $stmtUser = $con->prepare("
        SELECT id_funcionario
        FROM usuarios
        WHERE id_usuario = ?
        AND id_empresa = ?
        LIMIT 1
    ");
    $stmtUser->bind_param("ii", $idUsuario, $idEmpresa);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();

    if ($resUser->num_rows > 0) {
        $user = $resUser->fetch_assoc();
        $idFuncionario = $user['id_funcionario'];
        $_SESSION['id_funcionario'] = $idFuncionario;
    }
}

if (!$idFuncionario) {
    die("Funcionário não encontrado para este usuário.");
}

/* DADOS DO FUNCIONÁRIO */
$stmtFunc = $con->prepare("
    SELECT nome, cargo, departamento, escala
    FROM funcionarios
    WHERE id_funcionario = ?
    AND id_empresa = ?
    LIMIT 1
");
$stmtFunc->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtFunc->execute();
$funcionario = $stmtFunc->get_result()->fetch_assoc();

/* PONTO DE HOJE */
$hoje = date('Y-m-d');

$stmtHoje = $con->prepare("
    SELECT *
    FROM pontos
    WHERE id_funcionario = ?
    AND id_empresa = ?
    AND data = ?
    LIMIT 1
");
$stmtHoje->bind_param("iis", $idFuncionario, $idEmpresa, $hoje);
$stmtHoje->execute();
$pontoHoje = $stmtHoje->get_result()->fetch_assoc();

$entradaHoje = $pontoHoje['hora_entrada'] ?? '--:--';
$saidaHoje = $pontoHoje['hora_saida'] ?? '--:--';
$totalHoje = $pontoHoje['total_horas'] ?? '0.00';
$statusHoje = $pontoHoje['status'] ?? 'sem registro';

/* HISTÓRICO DA SEMANA */
$stmtSemana = $con->prepare("
    SELECT *
    FROM pontos
    WHERE id_funcionario = ?
    AND id_empresa = ?
    AND YEARWEEK(data, 1) = YEARWEEK(CURDATE(), 1)
    ORDER BY data ASC
");
$stmtSemana->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtSemana->execute();
$historicoSemana = $stmtSemana->get_result();

/* HISTÓRICO DO MÊS */
$stmtMes = $con->prepare("
    SELECT *
    FROM pontos
    WHERE id_funcionario = ?
    AND id_empresa = ?
    AND MONTH(data) = MONTH(CURDATE())
    AND YEAR(data) = YEAR(CURDATE())
    ORDER BY data DESC
");
$stmtMes->bind_param("ii", $idFuncionario, $idEmpresa);
$stmtMes->execute();
$historicoMes = $stmtMes->get_result();

function formatarHora($hora) {
    if (!$hora) {
        return '--:--';
    }

    return substr($hora, 0, 5);
}

function formatarData($data) {
    return date('d/m/Y', strtotime($data));
}

function diaSemana($data) {
    $dias = [
        'Sunday' => 'Domingo',
        'Monday' => 'Segunda',
        'Tuesday' => 'Terça',
        'Wednesday' => 'Quarta',
        'Thursday' => 'Quinta',
        'Friday' => 'Sexta',
        'Saturday' => 'Sábado'
    ];

    return $dias[date('l', strtotime($data))];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ponto do Funcionário</title>

    <link rel="stylesheet" href="../css/sidebarfunc.css">
    <link rel="stylesheet" href="../css/pontof.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">

</head>

<body>

    <?php include 'sidebarfunc.php'; ?>

    <div class="content">

        <div class="title">

            <h2>Ponto do Funcionário</h2>

            <p>
                <?= htmlspecialchars($funcionario['nome'] ?? 'Funcionário') ?> —
                Empresa #<?= htmlspecialchars($idEmpresa) ?>
            </p>

            <small class="text-muted">
                <?= htmlspecialchars($funcionario['cargo'] ?? '-') ?> |
                <?= htmlspecialchars($funcionario['departamento'] ?? '-') ?> |
                Escala: <?= htmlspecialchars($funcionario['escala'] ?? '-') ?>
            </small>

        </div>

        <div class="top-content">

            <div class="left-side">

                <div class="cards">

                    <div class="card-box">
                        <div>
                            <span>Entrada</span>
                            <strong><?= formatarHora($entradaHoje) ?></strong>
                        </div>
                        <i class="fa-solid fa-right-to-bracket"></i>
                    </div>

                    <div class="card-box">
                        <div>
                            <span>Status</span>
                            <strong><?= htmlspecialchars(ucfirst($statusHoje)) ?></strong>
                        </div>
                        <i class="fa-solid fa-circle-info"></i>
                    </div>

                    <div class="card-box">
                        <div>
                            <span>Horas Hoje</span>
                            <strong><?= htmlspecialchars($totalHoje) ?>h</strong>
                        </div>
                        <i class="fa-solid fa-business-time"></i>
                    </div>

                    <div class="card-box">
                        <div>
                            <span>Saída</span>
                            <strong><?= formatarHora($saidaHoje) ?></strong>
                        </div>
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </div>

                </div>

                <div class="message-box">

                    <div class="message-title">
                        <i class="fa-solid fa-star"></i>
                        Mensagem do Dia
                    </div>

                    <div id="messageText" class="message-text"></div>

                </div>

                <div class="calendar-section">

                    <div class="calendar-box">

                        <div class="calendar-title">
                            <i class="fa-regular fa-calendar"></i>
                            Histórico da Semana
                        </div>

                        <?php if($historicoSemana->num_rows > 0): ?>

                            <?php while($semana = $historicoSemana->fetch_assoc()): ?>

                                <div class="calendar-item">
                                    <strong>
                                        <?= diaSemana($semana['data']) ?>
                                    </strong>

                                    <span>
                                        <?= formatarHora($semana['hora_entrada']) ?>
                                        às
                                        <?= formatarHora($semana['hora_saida']) ?>
                                        —
                                        <?= htmlspecialchars($semana['total_horas'] ?? '0.00') ?>h
                                    </span>
                                </div>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <div class="calendar-item">
                                <strong>Nenhum registro</strong>
                                <span>Sem pontos nesta semana</span>
                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="calendar-box">

                        <div class="calendar-title">
                            <i class="fa-regular fa-calendar-days"></i>
                            Histórico do Mês
                        </div>

                        <?php if($historicoMes->num_rows > 0): ?>

                            <?php while($mes = $historicoMes->fetch_assoc()): ?>

                                <div class="calendar-item">
                                    <strong>
                                        <?= formatarData($mes['data']) ?>
                                    </strong>

                                    <span>
                                        <?= formatarHora($mes['hora_entrada']) ?>
                                        às
                                        <?= formatarHora($mes['hora_saida']) ?>
                                        —
                                        <?= htmlspecialchars($mes['status']) ?>
                                    </span>
                                </div>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <div class="calendar-item">
                                <strong>Nenhum registro</strong>
                                <span>Sem pontos neste mês</span>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <div class="clock-box">

                <i class="fa-regular fa-clock"></i>

                <div id="clock" class="clock"></div>

                <div id="date" class="date"></div>

                <div class="mt-3 text-muted small">
                    ID Funcionário: <?= htmlspecialchars($idFuncionario) ?><br>
                    ID Empresa: <?= htmlspecialchars($idEmpresa) ?>
                </div>

            </div>

        </div>

    </div>

<script>

function updateClock(){

    const now = new Date();

    const time = now.toLocaleTimeString('pt-BR');

    const date = now.toLocaleDateString('pt-BR',{
        weekday:'long',
        day:'numeric',
        month:'long',
        year:'numeric'
    });

    document.getElementById('clock').innerText = time;
    document.getElementById('date').innerText = date;

}

setInterval(updateClock,1000);
updateClock();

const messages = [
    "Você está indo muito bem hoje 🚀",
    "Seu esforço faz diferença 💙",
    "Mais um dia produtivo pela frente ✨",
    "Pequenos avanços geram grandes resultados 📈",
    "Continue firme, você consegue 🔥",
    "Seu trabalho importa muito 👏",
    "Organização é o caminho do sucesso 📅"
];

let currentMessage = 0;

function changeMessage(){

    document.getElementById("messageText").innerText = messages[currentMessage];

    currentMessage++;

    if(currentMessage >= messages.length){
        currentMessage = 0;
    }

}

changeMessage();

setInterval(changeMessage, 4000);

</script>

</body>
</html>