<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

/*
    GERADOR DE PONTOS AUTOMÁTICOS

    ORIGEM:
    db_mpd.usuarios

    DESTINO:
    db_ponto.registros_ponto
*/


function conectarBanco($nomeBanco) {

    $tentativas = [
        ["localhost", "root", "", $nomeBanco],
        ["127.0.0.1", "root", "", $nomeBanco],
        ["localhost", "root", "usbw", $nomeBanco],
        ["127.0.0.1", "root", "usbw", $nomeBanco],
    ];

    foreach ($tentativas as $t) {

        [$host, $user, $pass, $db] = $t;

        $conexao = @new mysqli($host, $user, $pass, $db);

        if (!$conexao->connect_error) {
            $conexao->set_charset("utf8mb4");
            return $conexao;
        }
    }

    die("
        <div style='font-family:Arial;padding:30px'>
            <h2 style='color:#b91c1c'>Erro ao conectar no banco {$nomeBanco}</h2>
            <p>Não foi possível conectar usando:</p>
            <ul>
                <li>root com senha vazia</li>
                <li>root com senha usbw</li>
            </ul>
            <p>Confira no phpMyAdmin se o banco <strong>{$nomeBanco}</strong> existe e qual senha do MySQL está sendo usada.</p>
        </div>
    ");
}


/*
    Conexões
*/

$conUsuarios = conectarBanco("db_mpd");
$conPonto = conectarBanco("db_ponto");


$mensagem = '';
$erro = '';

$totalCriados = 0;
$totalAtualizados = 0;
$totalPulados = 0;


function tabelaExiste($conexao, $tabela) {
    $tabela = $conexao->real_escape_string($tabela);

    $resultado = $conexao->query("SHOW TABLES LIKE '$tabela'");

    return $resultado && $resultado->num_rows > 0;
}


function colunaExiste($conexao, $tabela, $coluna) {
    $tabela = $conexao->real_escape_string($tabela);
    $coluna = $conexao->real_escape_string($coluna);

    $resultado = $conexao->query("SHOW COLUMNS FROM `$tabela` LIKE '$coluna'");

    return $resultado && $resultado->num_rows > 0;
}


function horaParaMinutos($hora) {
    if (!$hora) {
        $hora = '08:00:00';
    }

    $partes = explode(':', $hora);

    $h = intval($partes[0] ?? 8);
    $m = intval($partes[1] ?? 0);

    return ($h * 60) + $m;
}


function minutosParaHora($minutos) {
    $h = floor($minutos / 60);
    $m = $minutos % 60;

    return sprintf('%02d:%02d:00', $h, $m);
}


function ehSabado($data) {
    return date('N', strtotime($data)) == 6;
}


function ehDomingo($data) {
    return date('N', strtotime($data)) == 7;
}


function gerarHorarioEntrada($horarioPadrao) {
    $base = horaParaMinutos($horarioPadrao);

    $chanceAtraso = rand(1, 100);

    if ($chanceAtraso <= 25) {
        return minutosParaHora($base + rand(10, 45));
    }

    return minutosParaHora($base + rand(-10, 8));
}


function gerarSaidaIntervalo() {
    return minutosParaHora((12 * 60) + rand(-5, 10));
}


function gerarRetornoIntervalo() {
    return minutosParaHora((13 * 60) + rand(-5, 15));
}


function gerarSaida($entrada) {
    $entradaMin = horaParaMinutos($entrada);

    return minutosParaHora($entradaMin + (9 * 60) + rand(-10, 20));
}


function deveTrabalharNoDia($data, $escala) {
    $escala = strtolower(trim($escala));

    if (ehDomingo($data)) {
        return false;
    }

    if ($escala === '5x2' && ehSabado($data)) {
        return false;
    }

    return true;
}


/*
    PROCESSAMENTO
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dataInicio = $_POST['data_inicio'] ?? '';
    $dataFim = $_POST['data_fim'] ?? '';
    $sobrescrever = isset($_POST['sobrescrever']);

    if ($dataInicio === '' || $dataFim === '') {

        $erro = 'Selecione a data inicial e a data final.';

    } elseif ($dataInicio > $dataFim) {

        $erro = 'A data inicial não pode ser maior que a data final.';

    } elseif (!tabelaExiste($conUsuarios, 'usuarios')) {

        $erro = 'A tabela usuarios não existe no banco db_mpd.';

    } elseif (!tabelaExiste($conPonto, 'registros_ponto')) {

        $erro = 'A tabela registros_ponto não existe no banco db_ponto.';

    } else {

        $temNome = colunaExiste($conUsuarios, 'usuarios', 'nome');
        $temEmail = colunaExiste($conUsuarios, 'usuarios', 'email');
        $temEscala = colunaExiste($conUsuarios, 'usuarios', 'escala');
        $temHorarioPadrao = colunaExiste($conUsuarios, 'usuarios', 'horario_padrao');
        $temStatus = colunaExiste($conUsuarios, 'usuarios', 'status');
        $temTipo = colunaExiste($conUsuarios, 'usuarios', 'tipo');

        if (!$temEmail) {

            $erro = 'A tabela usuarios não possui a coluna email.';

        } else {

            $campoNome = $temNome ? "nome" : "email";
            $campoEscala = $temEscala ? "escala" : "'5x2' AS escala";
            $campoHorario = $temHorarioPadrao ? "horario_padrao" : "'08:00:00' AS horario_padrao";

            $where = "
                WHERE email IS NOT NULL
                AND email <> ''
            ";

            if ($temStatus) {
                $where .= "
                    AND status = 'ativo'
                ";
            }

            if ($temTipo) {
                $where .= "
                    AND tipo = 'funcionario'
                ";
            }

            $sqlUsuarios = "
                SELECT 
                    $campoNome AS nome,
                    email,
                    $campoEscala,
                    $campoHorario
                FROM usuarios
                $where
                ORDER BY nome ASC
            ";

            $stmtUsuarios = $conUsuarios->prepare($sqlUsuarios);

            if (!$stmtUsuarios) {
                die("Erro ao buscar usuários: " . $conUsuarios->error);
            }

            $stmtUsuarios->execute();
            $usuarios = $stmtUsuarios->get_result();

            if ($usuarios->num_rows === 0) {

                $erro = 'Nenhum funcionário ativo encontrado em db_mpd.usuarios.';

            } else {

                $inicio = new DateTime($dataInicio);
                $fim = new DateTime($dataFim);
                $fim->modify('+1 day');

                $periodo = new DatePeriod($inicio, new DateInterval('P1D'), $fim);

                while ($usuario = $usuarios->fetch_assoc()) {

                    $email = $usuario['email'];
                    $escala = $usuario['escala'] ?? '5x2';
                    $horarioPadrao = $usuario['horario_padrao'] ?? '08:00:00';

                    foreach ($periodo as $dia) {

                        $data = $dia->format('Y-m-d');

                        if (!deveTrabalharNoDia($data, $escala)) {
                            $totalPulados++;
                            continue;
                        }

                        /*
                            5% de chance de falta para ficar mais realista
                        */

                        $chanceFalta = rand(1, 100);

                        if ($chanceFalta <= 5) {
                            $totalPulados++;
                            continue;
                        }

                        $entrada = gerarHorarioEntrada($horarioPadrao);
                        $saidaIntervalo = gerarSaidaIntervalo();
                        $retornoIntervalo = gerarRetornoIntervalo();
                        $saida = gerarSaida($entrada);

                        $stmtVerifica = $conPonto->prepare("
                            SELECT id
                            FROM registros_ponto
                            WHERE email = ?
                            AND data = ?
                            LIMIT 1
                        ");

                        if (!$stmtVerifica) {
                            die("Erro ao verificar registro: " . $conPonto->error);
                        }

                        $stmtVerifica->bind_param("ss", $email, $data);
                        $stmtVerifica->execute();

                        $registroExistente = $stmtVerifica->get_result()->fetch_assoc();

                        if ($registroExistente) {

                            if ($sobrescrever) {

                                $idRegistro = $registroExistente['id'];

                                $stmtUpdate = $conPonto->prepare("
                                    UPDATE registros_ponto
                                    SET 
                                        entrada = ?,
                                        saida_intervalo = ?,
                                        retorno_intervalo = ?,
                                        saida = ?
                                    WHERE id = ?
                                ");

                                if (!$stmtUpdate) {
                                    die("Erro ao atualizar ponto: " . $conPonto->error);
                                }

                                $stmtUpdate->bind_param(
                                    "ssssi",
                                    $entrada,
                                    $saidaIntervalo,
                                    $retornoIntervalo,
                                    $saida,
                                    $idRegistro
                                );

                                if ($stmtUpdate->execute()) {
                                    $totalAtualizados++;
                                }

                            } else {

                                $totalPulados++;
                            }

                        } else {

                            $stmtInsert = $conPonto->prepare("
                                INSERT INTO registros_ponto (
                                    email,
                                    data,
                                    entrada,
                                    saida_intervalo,
                                    retorno_intervalo,
                                    saida
                                )
                                VALUES (?, ?, ?, ?, ?, ?)
                            ");

                            if (!$stmtInsert) {
                                die("Erro ao inserir ponto: " . $conPonto->error);
                            }

                            $stmtInsert->bind_param(
                                "ssssss",
                                $email,
                                $data,
                                $entrada,
                                $saidaIntervalo,
                                $retornoIntervalo,
                                $saida
                            );

                            if ($stmtInsert->execute()) {
                                $totalCriados++;
                            }
                        }
                    }
                }

                $mensagem = "Pontos gerados com sucesso. Criados: {$totalCriados}. Atualizados: {$totalAtualizados}. Pulados: {$totalPulados}.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Gerar Pontos Demo</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(37, 99, 235, .18), transparent 35%),
        linear-gradient(135deg, #eff6ff, #f8fafc);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: Arial, sans-serif;
    padding: 30px;
}

.card-demo {
    width: 100%;
    max-width: 820px;
    background: #ffffff;
    border-radius: 24px;
    padding: 34px;
    box-shadow: 0 25px 70px rgba(15, 23, 42, .12);
    border: 1px solid rgba(37, 99, 235, .12);
}

.icon-box {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    margin-bottom: 18px;
}

h1 {
    color: #102a56;
    font-weight: 800;
}

.text-muted-custom {
    color: #64748b;
}

.form-control {
    border-radius: 14px;
    padding: 13px;
}

.btn-gerar {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: white;
    border: none;
    border-radius: 14px;
    padding: 14px 22px;
    font-weight: 700;
}

.btn-gerar:hover {
    color: white;
    background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
}

.info-box {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e40af;
    border-radius: 18px;
    padding: 16px;
    font-size: 14px;
}

.alert {
    border-radius: 16px;
}

.badge-db {
    background: #e0f2fe;
    color: #075985;
    border-radius: 999px;
    padding: 7px 12px;
    font-weight: 700;
    font-size: 13px;
}
</style>
</head>

<body>

<div class="card-demo">

    <div class="icon-box">
        <i class="bi bi-clock-history"></i>
    </div>

    <h1>Gerar pontos automáticos</h1>

    <p class="text-muted-custom mb-4">
        Essa tela busca funcionários em <strong>db_mpd.usuarios</strong> e salva os pontos em
        <strong>db_ponto.registros_ponto</strong>.
    </p>

    <div class="d-flex gap-2 flex-wrap mb-4">
        <span class="badge-db">
            Origem: db_mpd.usuarios
        </span>

        <span class="badge-db">
            Destino: db_ponto.registros_ponto
        </span>

        <span class="badge-db">
            Tipo: funcionario
        </span>
    </div>

    <?php if ($mensagem): ?>
        <div class="alert alert-success fw-bold">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <?php if ($erro): ?>
        <div class="alert alert-danger fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <div class="info-box mb-4">
        <strong>O que será gerado?</strong><br>
        Para cada funcionário ativo, serão criados registros com:
        <strong>entrada</strong>, <strong>saida_intervalo</strong>,
        <strong>retorno_intervalo</strong> e <strong>saida</strong>.
        O sistema também cria alguns atrasos e faltas aleatórias para parecer mais realista.
    </div>

    <form method="POST">

        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label fw-bold">Data inicial</label>
                <input 
                    type="date" 
                    name="data_inicio" 
                    class="form-control"
                    value="<?= date('Y-m-01') ?>"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Data final</label>
                <input 
                    type="date" 
                    name="data_fim" 
                    class="form-control"
                    value="<?= date('Y-m-d') ?>"
                    required
                >
            </div>

        </div>

        <div class="form-check mt-4">
            <input 
                class="form-check-input" 
                type="checkbox" 
                name="sobrescrever" 
                id="sobrescrever"
            >

            <label class="form-check-label fw-semibold" for="sobrescrever">
                Sobrescrever registros já existentes nesse período
            </label>
        </div>

        <div class="d-flex gap-3 mt-4 flex-wrap">

            <button type="submit" class="btn btn-gerar">
                <i class="bi bi-magic me-2"></i>
                Gerar pontos
            </button>

            <a href="index.php" class="btn btn-outline-primary rounded-4 px-4">
                Ver registros
            </a>

            <a href="registrar_ponto.php" class="btn btn-outline-secondary rounded-4 px-4">
                Voltar
            </a>

        </div>

    </form>

</div>

</body>
</html>