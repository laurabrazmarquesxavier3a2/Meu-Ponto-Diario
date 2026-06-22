<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('America/Sao_Paulo');

require_once '../auth.php';
require_once '../config/database.php';
require_once '../lang.php';

/* =========================================================
   SESSÃO
========================================================= */

$idEmpresa = (int) ($_SESSION['id_empresa'] ?? 0);
$idUsuario = (int) ($_SESSION['id_usuario'] ?? 0);
$idFuncionario = (int) ($_SESSION['id_funcionario'] ?? 0);

if ($idEmpresa <= 0 || $idUsuario <= 0) {
    die('Sessão inválida. Faça login novamente.');
}

/* =========================================================
   FUNCIONÁRIO DO USUÁRIO LOGADO
========================================================= */

if ($idFuncionario <= 0) {

    $stmtUsuario = $con->prepare("
        SELECT id_funcionario
        FROM usuarios
        WHERE id_usuario = ?
          AND id_empresa = ?
        LIMIT 1
    ");

    if (!$stmtUsuario) {
        die('Erro ao preparar usuário: ' . $con->error);
    }

    $stmtUsuario->bind_param(
        'ii',
        $idUsuario,
        $idEmpresa
    );

    $stmtUsuario->execute();

    $usuario = $stmtUsuario
        ->get_result()
        ->fetch_assoc();

    $stmtUsuario->close();

    if (!empty($usuario['id_funcionario'])) {
        $idFuncionario = (int) $usuario['id_funcionario'];
        $_SESSION['id_funcionario'] = $idFuncionario;
    }
}

if ($idFuncionario <= 0) {
    die('Funcionário não encontrado para este usuário.');
}

/* =========================================================
   FUNÇÕES
========================================================= */

function formatarHora($hora): string
{
    if (
        empty($hora) ||
        $hora === '00:00:00' ||
        $hora === '--:--'
    ) {
        return '--:--';
    }

    return substr((string) $hora, 0, 5);
}

function formatarData($data): string
{
    if (empty($data)) {
        return '--/--/----';
    }

    return date('d/m/Y', strtotime($data));
}

function diaSemana($data): string
{
    $dias = [
        1 => 'Segunda-feira',
        2 => 'Terça-feira',
        3 => 'Quarta-feira',
        4 => 'Quinta-feira',
        5 => 'Sexta-feira',
        6 => 'Sábado',
        7 => 'Domingo'
    ];

    return $dias[(int) date('N', strtotime($data))] ?? '';
}

function formatarTotalHoras($total): string
{
    return number_format(
        (float) ($total ?? 0),
        2,
        '.',
        ''
    );
}

function calcularTotalHoras(
    $entrada,
    $saidaIntervalo,
    $retornoIntervalo,
    $saida
): float {

    if (empty($entrada) || empty($saida)) {
        return 0.00;
    }

    $inicio = strtotime($entrada);
    $fim = strtotime($saida);

    if ($inicio === false || $fim === false) {
        return 0.00;
    }

    $segundos = $fim - $inicio;

    if (
        !empty($saidaIntervalo) &&
        !empty($retornoIntervalo)
    ) {
        $inicioIntervalo = strtotime($saidaIntervalo);
        $fimIntervalo = strtotime($retornoIntervalo);

        if (
            $inicioIntervalo !== false &&
            $fimIntervalo !== false
        ) {
            $segundos -= $fimIntervalo - $inicioIntervalo;
        }
    }

    return max(0, round($segundos / 3600, 2));
}

function determinarStatusPonto($entrada, $saida): string
{
    if (empty($entrada)) {
        return 'Sem registro';
    }

    if (empty($saida)) {
        return 'Em andamento';
    }

    return 'Completo';
}

function adicionarPeriodoAoCalendario(
    array &$eventos,
    string $inicioPeriodo,
    string $fimPeriodo,
    string $inicioLimite,
    string $fimLimite,
    array $dados
): void {

    $inicioReal = max($inicioPeriodo, $inicioLimite);
    $fimReal = min($fimPeriodo, $fimLimite);

    if ($inicioReal > $fimReal) {
        return;
    }

    $data = new DateTime($inicioReal);
    $fim = new DateTime($fimReal);

    while ($data <= $fim) {

        $dataBanco = $data->format('Y-m-d');

        $eventos[$dataBanco] = array_merge(
            $dados,
            [
                'data' => $dataBanco,
                'data_inicio' => $inicioPeriodo,
                'data_fim' => $fimPeriodo
            ]
        );

        $data->modify('+1 day');
    }
}

/* =========================================================
   DADOS DO FUNCIONÁRIO
========================================================= */

$stmtFuncionario = $con->prepare("
    SELECT
        f.nome,
        f.cargo,
        f.departamento,
        f.escala,
        u.email
    FROM funcionarios AS f

    LEFT JOIN usuarios AS u
        ON u.id_funcionario = f.id_funcionario
       AND u.id_empresa = f.id_empresa

    WHERE f.id_funcionario = ?
      AND f.id_empresa = ?

    LIMIT 1
");

if (!$stmtFuncionario) {
    die('Erro ao preparar funcionário: ' . $con->error);
}

$stmtFuncionario->bind_param(
    'ii',
    $idFuncionario,
    $idEmpresa
);

$stmtFuncionario->execute();

$funcionario = $stmtFuncionario
    ->get_result()
    ->fetch_assoc();

$stmtFuncionario->close();

if (!$funcionario) {
    die('Funcionário não encontrado.');
}

$emailFuncionario = trim(
    $funcionario['email'] ?? ''
);

/* =========================================================
   DATAS
========================================================= */

$fuso = new DateTimeZone('America/Sao_Paulo');
$dataAtual = new DateTime('now', $fuso);

$hoje = $dataAtual->format('Y-m-d');

$numeroDiaSemana = (int) $dataAtual->format('N');

$inicioSemana = clone $dataAtual;
$inicioSemana->modify(
    '-' . ($numeroDiaSemana - 1) . ' days'
);
$inicioSemana->setTime(0, 0, 0);

$fimSemana = clone $inicioSemana;
$fimSemana->modify('+6 days');
$fimSemana->setTime(23, 59, 59);

$dataInicioSemana = $inicioSemana->format('Y-m-d');
$dataFimSemana = $fimSemana->format('Y-m-d');

$inicioMes = $dataAtual->format('Y-m-01');
$fimMes = $dataAtual->format('Y-m-t');

/* =========================================================
   CONEXÃO COM db_ponto
========================================================= */

mysqli_report(MYSQLI_REPORT_OFF);

$conPonto = @new mysqli(
    'localhost',
    'root',
    'usbw',
    'db_ponto'
);

$dbPontoDisponivel = !$conPonto->connect_error;

if ($dbPontoDisponivel) {
    $conPonto->set_charset('utf8mb4');
}

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

/* =========================================================
   PONTOS DO db_mpd
========================================================= */

$registrosPrincipais = [];

$stmtPontos = $con->prepare("
    SELECT
        id_ponto,
        data,
        hora_entrada,
        saida_intervalo,
        retorno_intervalo,
        hora_saida,
        total_horas,
        status
    FROM pontos
    WHERE id_funcionario = ?
      AND id_empresa = ?
      AND data BETWEEN ? AND ?
    ORDER BY data ASC, id_ponto ASC
");

if (!$stmtPontos) {
    die('Erro ao preparar pontos: ' . $con->error);
}

$stmtPontos->bind_param(
    'iiss',
    $idFuncionario,
    $idEmpresa,
    $inicioMes,
    $fimMes
);

$stmtPontos->execute();

$resultadoPontos = $stmtPontos->get_result();

while ($ponto = $resultadoPontos->fetch_assoc()) {

    $registrosPrincipais[$ponto['data']] = [
        'tipo_evento' => 'ponto',
        'data' => $ponto['data'],
        'hora_entrada' => $ponto['hora_entrada'],
        'saida_intervalo' => $ponto['saida_intervalo'],
        'retorno_intervalo' => $ponto['retorno_intervalo'],
        'hora_saida' => $ponto['hora_saida'],
        'total_horas' => $ponto['total_horas'],
        'status' => $ponto['status'] ?: 'Completo',
        'origem' => 'db_mpd'
    ];
}

$stmtPontos->close();

/* =========================================================
   PONTOS DO db_ponto
========================================================= */

$registrosExternos = [];

if (
    $dbPontoDisponivel &&
    $emailFuncionario !== ''
) {

    $stmtExternos = $conPonto->prepare("
        SELECT
            id,
            data,
            entrada,
            saida_intervalo,
            retorno_intervalo,
            saida
        FROM registros_ponto
        WHERE email = ?
          AND data BETWEEN ? AND ?
        ORDER BY data ASC, id ASC
    ");

    if ($stmtExternos) {

        $stmtExternos->bind_param(
            'sss',
            $emailFuncionario,
            $inicioMes,
            $fimMes
        );

        $stmtExternos->execute();

        $resultadoExternos =
            $stmtExternos->get_result();

        while ($externo = $resultadoExternos->fetch_assoc()) {

            $registrosExternos[$externo['data']] = [
                'tipo_evento' => 'ponto',
                'data' => $externo['data'],
                'hora_entrada' => $externo['entrada'],
                'saida_intervalo' => $externo['saida_intervalo'],
                'retorno_intervalo' => $externo['retorno_intervalo'],
                'hora_saida' => $externo['saida'],
                'total_horas' => calcularTotalHoras(
                    $externo['entrada'],
                    $externo['saida_intervalo'],
                    $externo['retorno_intervalo'],
                    $externo['saida']
                ),
                'status' => determinarStatusPonto(
                    $externo['entrada'],
                    $externo['saida']
                ),
                'origem' => 'db_ponto'
            ];
        }

        $stmtExternos->close();
    }
}

/* =========================================================
   AUSÊNCIAS
========================================================= */

$ausenciasMes = [];

/* =========================================================
   FÉRIAS APROVADAS
========================================================= */

$stmtFerias = $con->prepare("
    SELECT
        id_ferias,
        data_inicio,
        data_fim,
        dias,
        status,
        mensagem_colaborador
    FROM ferias
    WHERE id_funcionario = ?
      AND id_empresa = ?
      AND status = 'aprovado'
      AND data_inicio <= ?
      AND data_fim >= ?
    ORDER BY data_inicio ASC
");

if (!$stmtFerias) {
    die('Erro ao consultar férias: ' . $con->error);
}

$stmtFerias->bind_param(
    'iiss',
    $idFuncionario,
    $idEmpresa,
    $fimMes,
    $inicioMes
);

$stmtFerias->execute();

$resultadoFerias = $stmtFerias->get_result();

while ($ferias = $resultadoFerias->fetch_assoc()) {

    adicionarPeriodoAoCalendario(
        $ausenciasMes,
        $ferias['data_inicio'],
        $ferias['data_fim'],
        $inicioMes,
        $fimMes,
        [
            'tipo_evento' => 'ferias',
            'status' => 'Férias',
            'titulo' => 'Férias aprovadas',
            'motivo' => $ferias['mensagem_colaborador']
                ?: 'Período de férias aprovado pelo RH.',
            'observacao' => '',
            'dias' => (int) $ferias['dias'],
            'origem' => 'ferias'
        ]
    );
}

$stmtFerias->close();

/* =========================================================
   LICENÇAS MÉDICAS
========================================================= */

$stmtLicencas = $con->prepare("
    SELECT
        id,
        motivo,
        data_inicio,
        data_fim,
        dias,
        observacao,
        status,
        mensagem_colaborador
    FROM licencas_medicas
    WHERE id_funcionario = ?
      AND id_empresa = ?
      AND status IN ('visto', 'aprovado')
      AND data_inicio <= ?
      AND data_fim >= ?
    ORDER BY data_inicio ASC
");

if (!$stmtLicencas) {
    die('Erro ao consultar licenças: ' . $con->error);
}

$stmtLicencas->bind_param(
    'iiss',
    $idFuncionario,
    $idEmpresa,
    $fimMes,
    $inicioMes
);

$stmtLicencas->execute();

$resultadoLicencas = $stmtLicencas->get_result();

while ($licenca = $resultadoLicencas->fetch_assoc()) {

    adicionarPeriodoAoCalendario(
        $ausenciasMes,
        $licenca['data_inicio'],
        $licenca['data_fim'],
        $inicioMes,
        $fimMes,
        [
            'tipo_evento' => 'licenca',
            'status' => 'Licença médica',
            'titulo' => 'Licença médica',
            'motivo' => $licenca['motivo']
                ?: 'Licença médica registrada.',
            'observacao' => $licenca['observacao'] ?? '',
            'mensagem' => $licenca['mensagem_colaborador'] ?? '',
            'dias' => (int) ($licenca['dias'] ?? 0),
            'origem' => 'licencas_medicas'
        ]
    );
}

$stmtLicencas->close();

/* =========================================================
   AFASTAMENTOS
========================================================= */

$tabelaAfastamentos = $con->query("
    SHOW TABLES LIKE 'afastamentos'
");

if (
    $tabelaAfastamentos &&
    $tabelaAfastamentos->num_rows > 0
) {

    $stmtAfastamentos = $con->prepare("
        SELECT
            id_afastamento,
            tipo,
            motivo,
            data_inicio,
            data_fim,
            dias,
            status,
            observacao
        FROM afastamentos
        WHERE id_funcionario = ?
          AND id_empresa = ?
          AND status = 'aprovado'
          AND data_inicio <= ?
          AND data_fim >= ?
        ORDER BY data_inicio ASC
    ");

    if (!$stmtAfastamentos) {
        die(
            'Erro ao consultar afastamentos: ' .
            $con->error
        );
    }

    $stmtAfastamentos->bind_param(
        'iiss',
        $idFuncionario,
        $idEmpresa,
        $fimMes,
        $inicioMes
    );

    $stmtAfastamentos->execute();

    $resultadoAfastamentos =
        $stmtAfastamentos->get_result();

    while (
        $afastamento =
        $resultadoAfastamentos->fetch_assoc()
    ) {

        adicionarPeriodoAoCalendario(
            $ausenciasMes,
            $afastamento['data_inicio'],
            $afastamento['data_fim'],
            $inicioMes,
            $fimMes,
            [
                'tipo_evento' => 'afastamento',
                'status' => 'Afastado',
                'titulo' => $afastamento['tipo']
                    ?: 'Afastamento',
                'motivo' => $afastamento['motivo']
                    ?: 'Afastamento aprovado pelo RH.',
                'observacao' =>
                    $afastamento['observacao'] ?? '',
                'dias' => (int) $afastamento['dias'],
                'origem' => 'afastamentos'
            ]
        );
    }

    $stmtAfastamentos->close();
}

/* =========================================================
   JUNTA PONTOS E AUSÊNCIAS
========================================================= */

$registrosCompletos = $registrosPrincipais;

/*
 * O registro externo substitui o importado
 * quando houver o mesmo dia.
 */
foreach (
    $registrosExternos as
    $dataRegistro => $registroExterno
) {
    $registrosCompletos[$dataRegistro] =
        $registroExterno;
}

/*
 * Primeiro adiciona férias, licenças e afastamentos.
 */
$eventosCalendario = $ausenciasMes;

/*
 * Caso exista ponto no mesmo dia, o ponto tem prioridade.
 */
foreach (
    $registrosCompletos as
    $dataRegistro => $registroPonto
) {
    $eventosCalendario[$dataRegistro] =
        $registroPonto;
}

/* =========================================================
   SEMANA
========================================================= */

$eventosSemana = [];

foreach (
    $eventosCalendario as
    $dataRegistro => $evento
) {
    if (
        $dataRegistro >= $dataInicioSemana &&
        $dataRegistro <= $dataFimSemana
    ) {
        $eventosSemana[$dataRegistro] = $evento;
    }
}

ksort($eventosSemana);

/* =========================================================
   MÊS
========================================================= */

$eventosMes = $eventosCalendario;
krsort($eventosMes);

/* =========================================================
   CARDS SUPERIORES
========================================================= */

if (isset($registrosCompletos[$hoje])) {

    $registroAtual = $registrosCompletos[$hoje];

    $entradaHoje =
        $registroAtual['hora_entrada'] ?? null;

    $saidaIntervalo =
        $registroAtual['saida_intervalo'] ?? null;

    $retornoIntervalo =
        $registroAtual['retorno_intervalo'] ?? null;

    $saidaHoje =
        $registroAtual['hora_saida'] ?? null;

    $totalHoje =
        $registroAtual['total_horas'] ?? 0;

    $statusHoje =
        ucfirst($registroAtual['status'] ?? 'Sem registro');

} elseif (isset($ausenciasMes[$hoje])) {

    $ausenciaHoje = $ausenciasMes[$hoje];

    $entradaHoje = null;
    $saidaIntervalo = null;
    $retornoIntervalo = null;
    $saidaHoje = null;
    $totalHoje = 0;

    $statusHoje =
        $ausenciaHoje['status'] ?? 'Ausente';

} elseif (!empty($registrosCompletos)) {

    $ultimosRegistros = $registrosCompletos;
    krsort($ultimosRegistros);

    $ultimoRegistro = reset($ultimosRegistros);

    $entradaHoje =
        $ultimoRegistro['hora_entrada'] ?? null;

    $saidaIntervalo =
        $ultimoRegistro['saida_intervalo'] ?? null;

    $retornoIntervalo =
        $ultimoRegistro['retorno_intervalo'] ?? null;

    $saidaHoje =
        $ultimoRegistro['hora_saida'] ?? null;

    $totalHoje =
        $ultimoRegistro['total_horas'] ?? 0;

    $statusHoje = 'Último registro';

} else {

    $entradaHoje = null;
    $saidaIntervalo = null;
    $retornoIntervalo = null;
    $saidaHoje = null;
    $totalHoje = 0;
    $statusHoje = 'Sem registro';
}

/* =========================================================
   CALENDÁRIO
========================================================= */

$primeiroDiaMes = new DateTime($inicioMes, $fuso);
$ultimoDiaMes = new DateTime($fimMes, $fuso);

$numeroPrimeiroDia =
    (int) $primeiroDiaMes->format('N');

$quantidadeDias =
    (int) $ultimoDiaMes->format('d');

$numeroMes =
    (int) $primeiroDiaMes->format('n');

$anoMes =
    $primeiroDiaMes->format('Y');

$nomesMeses = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ponto do Funcionário</title>

    <link
        rel="stylesheet"
        href="../css/sidebarfunc.css"
    >

    <link
        rel="stylesheet"
        href="../css/pontof.css"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap"
        rel="stylesheet"
    >

    <style>

        .calendar-section {
            display: grid;
            grid-template-columns:
                minmax(0, 1fr)
                minmax(0, 1fr);
            gap: 24px;
            align-items: start;
            margin-top: 24px;
        }

        .calendar-box {
            min-width: 0;
            overflow: hidden;
        }

        .week-history {
            display: flex;
            flex-direction: column;
        }

        .week-day-item {
            border-bottom: 1px solid #e5e7eb;
        }

        .week-day-item:last-child {
            border-bottom: 0;
        }

        .week-day-button {
            width: 100%;
            border: 0;
            background: transparent;
            padding: 15px 4px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;

            text-align: left;
        }

        button.week-day-button {
            cursor: pointer;
        }

        button.week-day-button:hover {
            background: #f8fafc;
        }

        .week-day-button.no-record {
            cursor: default;
        }

        .week-day-info {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .week-day-name {
            color: #111827;
            font-weight: 700;
        }

        .week-day-info small {
            color: #64748b;
        }

        .week-day-status {
            display: flex;
            align-items: center;
            gap: 7px;

            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .week-day-status i {
            margin-left: 4px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            flex: 0 0 8px;
            border-radius: 50%;
        }

        .status-ponto {
            color: #1d4ed8;
        }

        .status-ponto .status-dot {
            background: #2563eb;
        }

        .status-ferias {
            color: #7e22ce;
        }

        .status-ferias .status-dot {
            background: #a855f7;
        }

        .status-licenca {
            color: #854d0e;
        }

        .status-licenca .status-dot {
            background: #eab308;
        }

        .status-afastamento {
            color: #c2410c;
        }

        .status-afastamento .status-dot {
            background: #f97316;
        }

        .week-day-empty {
            color: #94a3b8;
            font-size: 13px;
        }

        .week-day-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px;
            margin: 0 4px 14px;

            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .compact-detail {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            padding: 9px;
            min-width: 0;
        }

        .compact-detail span {
            display: block;
            color: #64748b;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .compact-detail strong {
            display: block;
            color: #111827;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .absence-ferias {
            background: #faf5ff;
            border-radius: 10px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .absence-licenca {
            background: #fefce8;
            border-radius: 10px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .absence-afastamento {
            background: #fff7ed;
            border-radius: 10px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .month-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .month-header strong {
            color: #111827;
            font-size: 17px;
        }

        .month-header span {
            color: #64748b;
            font-size: 12px;
        }

        .month-calendar {
            display: grid;
            grid-template-columns:
                repeat(7, minmax(0, 1fr));
            gap: 7px;
        }

        .calendar-week-name {
            text-align: center;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 0 8px;
        }

        .calendar-day {
            position: relative;
            min-width: 0;
            aspect-ratio: 1 / 1;

            border: 1px solid transparent;
            border-radius: 11px;
            background: #f8fafc;
            color: #334155;

            display: flex;
            align-items: center;
            justify-content: center;

            font-family: inherit;
            font-size: 13px;
        }

        button.calendar-day {
            cursor: pointer;
            font-weight: 700;

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        button.calendar-day:hover {
            transform: translateY(-2px);
            box-shadow:
                0 8px 18px
                rgba(15, 23, 42, .14);
        }

        .event-ponto {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1d4ed8;
        }

        .event-ferias {
            background: #f3e8ff;
            border-color: #c084fc;
            color: #7e22ce;
        }

        .event-licenca {
            background: #fef9c3;
            border-color: #facc15;
            color: #854d0e;
        }

        .event-afastamento {
            background: #ffedd5;
            border-color: #fb923c;
            color: #c2410c;
        }

        .calendar-day.today {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }

        .calendar-day.empty-day {
            background: transparent;
        }

        .record-indicator {
            position: absolute;
            bottom: 6px;

            width: 5px;
            height: 5px;

            border-radius: 50%;
            background: currentColor;
        }

        .calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 18px;

            color: #64748b;
            font-size: 11px;
        }

        .calendar-legend span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
        }

        .legend-ponto {
            background: #2563eb;
        }

        .legend-ferias {
            background: #a855f7;
        }

        .legend-licenca {
            background: #eab308;
        }

        .legend-afastamento {
            background: #f97316;
        }

        .legend-hoje {
            background: transparent;
            border: 2px solid #2563eb;
        }

        .modal-record-date {
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 11px;
            padding: 12px 14px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .modal-record-grid {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .modal-record-item {
            background: #f8fafc;
            border-radius: 11px;
            padding: 12px;
            min-width: 0;
        }

        .modal-record-item span {
            display: block;
            color: #64748b;
            font-size: 11px;
            margin-bottom: 4px;
        }

        .modal-record-item strong {
            display: block;
            color: #111827;
            font-size: 15px;
            overflow-wrap: anywhere;
        }

        .modal-record-item.full {
            grid-column: 1 / -1;
        }

        .modal-record-origin {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            margin-top: 14px;
            padding: 7px 10px;

            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;

            font-size: 11px;
            font-weight: 700;
        }

        @media (max-width: 1100px) {

            .calendar-section {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {

            .week-day-details {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .week-day-button {
                align-items: flex-start;
            }

            .week-day-status,
            .week-day-empty {
                white-space: normal;
                text-align: right;
            }

            .month-calendar {
                gap: 4px;
            }

            .calendar-day {
                border-radius: 8px;
                font-size: 11px;
            }

            .modal-record-grid {
                grid-template-columns: 1fr;
            }

            .modal-record-item.full {
                grid-column: auto;
            }

            .month-header {
                align-items: flex-start;
                flex-direction: column;
            }
        }

    </style>

</head>

<body>

<?php include 'sidebarfunc.php'; ?>

<div class="content">

    <div class="title">

        <h2>Ponto do Funcionário</h2>

        <p>
            <?= htmlspecialchars(
                $funcionario['nome'] ?? 'Funcionário'
            ) ?>

            —

            Empresa #<?= $idEmpresa ?>
        </p>

        <small class="text-muted">

            <?= htmlspecialchars(
                $funcionario['cargo'] ?? '-'
            ) ?>

            |

            <?= htmlspecialchars(
                $funcionario['departamento'] ?? '-'
            ) ?>

            |

            Escala:

            <?= htmlspecialchars(
                $funcionario['escala'] ?? '-'
            ) ?>

        </small>

    </div>

    <div class="top-content">

        <div class="left-side">

            <div class="cards">

                <div class="card-box">
                    <div>
                        <span>Entrada</span>
                        <strong>
                            <?= formatarHora($entradaHoje) ?>
                        </strong>
                    </div>
                    <i class="fa-solid fa-right-to-bracket"></i>
                </div>

                <div class="card-box">
                    <div>
                        <span>Saída Intervalo</span>
                        <strong>
                            <?= formatarHora($saidaIntervalo) ?>
                        </strong>
                    </div>
                    <i class="fa-solid fa-utensils"></i>
                </div>

                <div class="card-box">
                    <div>
                        <span>Retorno Intervalo</span>
                        <strong>
                            <?= formatarHora($retornoIntervalo) ?>
                        </strong>
                    </div>
                    <i class="fa-solid fa-arrow-rotate-left"></i>
                </div>

                <div class="card-box">
                    <div>
                        <span>Saída</span>
                        <strong>
                            <?= formatarHora($saidaHoje) ?>
                        </strong>
                    </div>
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>

                <div class="card-box">
                    <div>
                        <span>Status</span>
                        <strong>
                            <?= htmlspecialchars($statusHoje) ?>
                        </strong>
                    </div>
                    <i class="fa-solid fa-circle-info"></i>
                </div>

                <div class="card-box">
                    <div>
                        <span>Horas Hoje</span>
                        <strong>
                            <?= formatarTotalHoras($totalHoje) ?>h
                        </strong>
                    </div>
                    <i class="fa-solid fa-business-time"></i>
                </div>

            </div>

            <div class="calendar-section">

                <!-- SEMANA -->

                <div class="calendar-box">

                    <div class="calendar-title">
                        <i class="fa-regular fa-calendar"></i>
                        Histórico da Semana
                    </div>

                    <div class="small text-muted mb-3">

                        <?= $inicioSemana->format('d/m/Y') ?>

                        até

                        <?= $fimSemana->format('d/m/Y') ?>

                    </div>

                    <div class="week-history">

                        <?php

                        $dataLoop = clone $inicioSemana;

                        while ($dataLoop <= $fimSemana):

                            $dataBanco =
                                $dataLoop->format('Y-m-d');

                            $evento =
                                $eventosSemana[$dataBanco]
                                ?? null;

                            $collapseId =
                                'semana-' .
                                str_replace('-', '', $dataBanco);

                        ?>

                            <div class="week-day-item">

                                <?php if (
                                    $evento &&
                                    ($evento['tipo_evento'] ?? '') === 'ponto'
                                ): ?>

                                    <button
                                        type="button"
                                        class="week-day-button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#<?= $collapseId ?>"
                                        aria-expanded="false"
                                    >

                                        <div class="week-day-info">

                                            <span class="week-day-name">
                                                <?= htmlspecialchars(
                                                    diaSemana($dataBanco)
                                                ) ?>
                                            </span>

                                            <small>
                                                <?= formatarData($dataBanco) ?>
                                            </small>

                                        </div>

                                        <div class="week-day-status status-ponto">

                                            <span class="status-dot"></span>

                                            <?= htmlspecialchars(
                                                ucfirst(
                                                    $evento['status']
                                                    ?? 'Completo'
                                                )
                                            ) ?>

                                            <i class="fa-solid fa-chevron-down"></i>

                                        </div>

                                    </button>

                                    <div
                                        class="collapse"
                                        id="<?= $collapseId ?>"
                                    >

                                        <div class="week-day-details">

                                            <div class="compact-detail">
                                                <span>Entrada</span>
                                                <strong>
                                                    <?= formatarHora(
                                                        $evento['hora_entrada']
                                                        ?? null
                                                    ) ?>
                                                </strong>
                                            </div>

                                            <div class="compact-detail">
                                                <span>Saída intervalo</span>
                                                <strong>
                                                    <?= formatarHora(
                                                        $evento['saida_intervalo']
                                                        ?? null
                                                    ) ?>
                                                </strong>
                                            </div>

                                            <div class="compact-detail">
                                                <span>Retorno</span>
                                                <strong>
                                                    <?= formatarHora(
                                                        $evento['retorno_intervalo']
                                                        ?? null
                                                    ) ?>
                                                </strong>
                                            </div>

                                            <div class="compact-detail">
                                                <span>Saída</span>
                                                <strong>
                                                    <?= formatarHora(
                                                        $evento['hora_saida']
                                                        ?? null
                                                    ) ?>
                                                </strong>
                                            </div>

                                            <div class="compact-detail">
                                                <span>Total</span>
                                                <strong>
                                                    <?= formatarTotalHoras(
                                                        $evento['total_horas']
                                                        ?? 0
                                                    ) ?>h
                                                </strong>
                                            </div>

                                            <div class="compact-detail">
                                                <span>Origem</span>
                                                <strong>
                                                    <?= (
                                                        $evento['origem']
                                                        ?? ''
                                                    ) === 'db_ponto'
                                                        ? 'Externo'
                                                        : 'Importado' ?>
                                                </strong>
                                            </div>

                                        </div>

                                    </div>

                                <?php elseif ($evento): ?>

                                    <?php
                                    $tipoEvento =
                                        $evento['tipo_evento']
                                        ?? 'ausencia';
                                    ?>

                                    <button
                                        type="button"
                                        class="
                                            week-day-button
                                            absence-<?= htmlspecialchars($tipoEvento) ?>
                                            botao-ausencia
                                        "
                                        data-event-date="<?= htmlspecialchars(
                                            $dataBanco
                                        ) ?>"
                                    >

                                        <div class="week-day-info">

                                            <span class="week-day-name">
                                                <?= htmlspecialchars(
                                                    diaSemana($dataBanco)
                                                ) ?>
                                            </span>

                                            <small>
                                                <?= formatarData($dataBanco) ?>
                                            </small>

                                        </div>

                                        <div
                                            class="
                                                week-day-status
                                                status-<?= htmlspecialchars(
                                                    $tipoEvento
                                                ) ?>
                                            "
                                        >

                                            <span class="status-dot"></span>

                                            <?= htmlspecialchars(
                                                $evento['status']
                                                ?? 'Ausência'
                                            ) ?>

                                            <i class="fa-solid fa-circle-info"></i>

                                        </div>

                                    </button>

                                <?php else: ?>

                                    <div class="week-day-button no-record">

                                        <div class="week-day-info">

                                            <span class="week-day-name">
                                                <?= htmlspecialchars(
                                                    diaSemana($dataBanco)
                                                ) ?>
                                            </span>

                                            <small>
                                                <?= formatarData($dataBanco) ?>
                                            </small>

                                        </div>

                                        <div class="week-day-empty">
                                            Sem registro
                                        </div>

                                    </div>

                                <?php endif; ?>

                            </div>

                        <?php

                            $dataLoop->modify('+1 day');

                        endwhile;

                        ?>

                    </div>

                </div>

                <!-- MÊS -->

                <div class="calendar-box">

                    <div class="calendar-title">
                        <i class="fa-regular fa-calendar-days"></i>
                        Histórico do Mês
                    </div>

                    <div class="month-header">

                        <strong>
                            <?= $nomesMeses[$numeroMes] ?>
                            de
                            <?= $anoMes ?>
                        </strong>

                        <span>
                            Clique em um dia colorido
                        </span>

                    </div>

                    <div class="month-calendar">

                        <div class="calendar-week-name">Seg</div>
                        <div class="calendar-week-name">Ter</div>
                        <div class="calendar-week-name">Qua</div>
                        <div class="calendar-week-name">Qui</div>
                        <div class="calendar-week-name">Sex</div>
                        <div class="calendar-week-name">Sáb</div>
                        <div class="calendar-week-name">Dom</div>

                        <?php

                        for (
                            $espaco = 1;
                            $espaco < $numeroPrimeiroDia;
                            $espaco++
                        ):

                        ?>

                            <div class="calendar-day empty-day"></div>

                        <?php endfor; ?>

                        <?php

                        for (
                            $dia = 1;
                            $dia <= $quantidadeDias;
                            $dia++
                        ):

                            $dataCalendario = sprintf(
                                '%s-%02d-%02d',
                                $anoMes,
                                $numeroMes,
                                $dia
                            );

                            $eventoDia =
                                $eventosMes[$dataCalendario]
                                ?? null;

                            $ehHoje =
                                $dataCalendario === $hoje;

                        ?>

                            <?php if ($eventoDia): ?>

                                <?php
                                $tipoEvento =
                                    $eventoDia['tipo_evento']
                                    ?? 'ponto';
                                ?>

                                <button
                                    type="button"
                                    class="
                                        calendar-day
                                        event-<?= htmlspecialchars(
                                            $tipoEvento
                                        ) ?>
                                        <?= $ehHoje ? 'today' : '' ?>
                                        botao-evento-calendario
                                    "
                                    data-event-date="<?= htmlspecialchars(
                                        $dataCalendario
                                    ) ?>"
                                    title="<?= htmlspecialchars(
                                        $eventoDia['status']
                                        ?? 'Ver detalhes'
                                    ) ?>"
                                >

                                    <span class="day-number">
                                        <?= $dia ?>
                                    </span>

                                    <span class="record-indicator"></span>

                                </button>

                            <?php else: ?>

                                <div
                                    class="
                                        calendar-day
                                        <?= $ehHoje ? 'today' : '' ?>
                                    "
                                >
                                    <span class="day-number">
                                        <?= $dia ?>
                                    </span>
                                </div>

                            <?php endif; ?>

                        <?php endfor; ?>

                    </div>

                    <div class="calendar-legend">

                        <span>
                            <i class="legend-dot legend-ponto"></i>
                            Ponto
                        </span>

                        <span>
                            <i class="legend-dot legend-ferias"></i>
                            Férias
                        </span>

                        <span>
                            <i class="legend-dot legend-licenca"></i>
                            Licença
                        </span>

                        <span>
                            <i class="legend-dot legend-afastamento"></i>
                            Afastamento
                        </span>

                        <span>
                            <i class="legend-dot legend-hoje"></i>
                            Hoje
                        </span>

                    </div>

                </div>

            </div>

        </div>

        <div class="clock-box">

            <i class="fa-regular fa-clock"></i>

            <div id="clock" class="clock"></div>

            <div id="date" class="date"></div>

            <div class="mt-3 text-muted small">

                ID Funcionário:
                <?= $idFuncionario ?>

                <br>

                ID Empresa:
                <?= $idEmpresa ?>

            </div>

        </div>

    </div>

</div>

<!-- MODAL -->

<div
    class="modal fade"
    id="modalEvento"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 rounded-4 shadow">

            <div class="modal-header border-0 pb-0">

                <div>

                    <small
                        class="text-muted"
                        id="modalSubtitulo"
                    >
                        Histórico
                    </small>

                    <h5
                        class="modal-title fw-bold text-primary"
                        id="modalTitulo"
                    >
                        Detalhes do dia
                    </h5>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>

            </div>

            <div class="modal-body pt-3">

                <div
                    class="modal-record-date"
                    id="modalData"
                ></div>

                <div
                    class="modal-record-grid"
                    id="modalConteudo"
                ></div>

                <div class="modal-record-origin">

                    <i class="fa-solid fa-database"></i>

                    <span id="modalOrigem">
                        Registro
                    </span>

                </div>

            </div>

            <div class="modal-footer border-0 pt-0">

                <button
                    type="button"
                    class="btn btn-primary px-4 rounded-3"
                    data-bs-dismiss="modal"
                >
                    Fechar
                </button>

            </div>

        </div>

    </div>

</div>

<script>

const eventosDoMes = <?= json_encode(
    $eventosMes,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
) ?>;

function escaparHtml(valor) {

    const elemento =
        document.createElement('div');

    elemento.textContent =
        valor === null ||
        valor === undefined
            ? ''
            : String(valor);

    return elemento.innerHTML;
}

function horaCurta(hora) {

    if (
        !hora ||
        hora === '00:00:00'
    ) {
        return '--:--';
    }

    return String(hora).substring(0, 5);
}

function dataBrasil(data) {

    if (!data) {
        return '-';
    }

    const partes = String(data).split('-');

    if (partes.length !== 3) {
        return data;
    }

    return (
        partes[2] +
        '/' +
        partes[1] +
        '/' +
        partes[0]
    );
}

function primeiraMaiuscula(texto) {

    if (!texto) {
        return '';
    }

    texto = String(texto);

    return (
        texto.charAt(0).toUpperCase() +
        texto.slice(1)
    );
}

function criarCampo(titulo, valor, classeExtra = '') {

    return `
        <div class="modal-record-item ${classeExtra}">
            <span>${escaparHtml(titulo)}</span>
            <strong>${escaparHtml(valor)}</strong>
        </div>
    `;
}

function abrirModalEvento(dataEvento) {

    const evento =
        eventosDoMes[dataEvento];

    if (!evento) {
        return;
    }

    const tipo =
        evento.tipo_evento || 'ponto';

    const titulo =
        document.getElementById('modalTitulo');

    const subtitulo =
        document.getElementById('modalSubtitulo');

    const data =
        document.getElementById('modalData');

    const conteudo =
        document.getElementById('modalConteudo');

    const origem =
        document.getElementById('modalOrigem');

    data.textContent =
        dataBrasil(dataEvento);

    let html = '';

    if (tipo === 'ponto') {

        subtitulo.textContent =
            'Histórico de ponto';

        titulo.textContent =
            'Registro do dia';

        html += criarCampo(
            'Entrada',
            horaCurta(evento.hora_entrada)
        );

        html += criarCampo(
            'Saída do intervalo',
            horaCurta(evento.saida_intervalo)
        );

        html += criarCampo(
            'Retorno do intervalo',
            horaCurta(evento.retorno_intervalo)
        );

        html += criarCampo(
            'Saída',
            horaCurta(evento.hora_saida)
        );

        html += criarCampo(
            'Total trabalhado',
            Number(
                evento.total_horas || 0
            ).toFixed(2) + 'h'
        );

        html += criarCampo(
            'Status',
            primeiraMaiuscula(
                evento.status || 'Sem status'
            )
        );

        origem.textContent =
            evento.origem === 'db_ponto'
                ? 'Registro externo'
                : 'Registro importado';

    } else {

        subtitulo.textContent =
            'Ausência justificada';

        titulo.textContent =
            evento.titulo ||
            evento.status ||
            'Ausência';

        html += criarCampo(
            'Situação',
            evento.status || 'Ausência'
        );

        html += criarCampo(
            'Período',
            dataBrasil(evento.data_inicio) +
            ' até ' +
            dataBrasil(evento.data_fim)
        );

        html += criarCampo(
            'Quantidade de dias',
            String(evento.dias || 0)
        );

        html += criarCampo(
            'Motivo',
            evento.motivo ||
            'Não informado',
            'full'
        );

        if (evento.observacao) {

            html += criarCampo(
                'Observação',
                evento.observacao,
                'full'
            );
        }

        origem.textContent =
            tipo === 'ferias'
                ? 'Férias aprovadas'
                : (
                    tipo === 'licenca'
                        ? 'Licença médica'
                        : 'Afastamento'
                );
    }

    conteudo.innerHTML = html;

    const modal =
        bootstrap.Modal.getOrCreateInstance(
            document.getElementById(
                'modalEvento'
            )
        );

    modal.show();
}

function updateClock() {

    const now = new Date();

    document.getElementById('clock').innerText =
        now.toLocaleTimeString('pt-BR');

    document.getElementById('date').innerText =
        now.toLocaleDateString(
            'pt-BR',
            {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }
        );
}

setInterval(updateClock, 1000);
updateClock();

document.addEventListener(
    'DOMContentLoaded',
    function () {

        document
            .querySelectorAll(
                '.botao-evento-calendario, .botao-ausencia'
            )
            .forEach(function (botao) {

                botao.addEventListener(
                    'click',
                    function () {

                        abrirModalEvento(
                            this.dataset.eventDate
                        );
                    }
                );
            });
    }
);

</script>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

<script src="../js/theme.js"></script>
<script src="../js/translate.js"></script>

</body>

</html>