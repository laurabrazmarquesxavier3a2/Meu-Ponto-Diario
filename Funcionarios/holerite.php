<?php

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';
require_once '../lang.php';

/*  VALIDAÇÃO DO LOGIN */
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.php');
    exit;
}

$id_usuario = (int) $_SESSION['id_usuario'];

if ($id_usuario <= 0) {
    session_destroy();

    header('Location: ../login.php');
    exit;
}

/*BUSCAR FUNCIONÁRIO E EMPRESA */
$sqlUsuario = "
    SELECT
        id_funcionario,
        id_empresa

    FROM usuarios

    WHERE id_usuario = ?

    LIMIT 1
";

$stmtUsuario = $con->prepare($sqlUsuario);

if (!$stmtUsuario) {
    die(
        'Erro ao preparar busca do usuário: ' .
        htmlspecialchars($con->error)
    );
}

$stmtUsuario->bind_param(
    'i',
    $id_usuario
);

if (!$stmtUsuario->execute()) {
    die(
        'Erro ao buscar usuário: ' .
        htmlspecialchars($stmtUsuario->error)
    );
}

$usuario = $stmtUsuario
    ->get_result()
    ->fetch_assoc();

$stmtUsuario->close();

if (
    !$usuario ||
    empty($usuario['id_funcionario'])
) {
    die('Funcionário não encontrado.');
}

$id_funcionario = (int) $usuario['id_funcionario'];
$id_empresa = (int) ($usuario['id_empresa'] ?? 0);

if ($id_funcionario <= 0) {
    die('Funcionário não identificado.');
}

if ($id_empresa <= 0) {
    die('Empresa não identificada.');
}


$meses = [
    '01' => 'Janeiro',
    '02' => 'Fevereiro',
    '03' => 'Março',
    '04' => 'Abril',
    '05' => 'Maio',
    '06' => 'Junho',
    '07' => 'Julho',
    '08' => 'Agosto',
    '09' => 'Setembro',
    '10' => 'Outubro',
    '11' => 'Novembro',
    '12' => 'Dezembro'
];


$sqlAnos = "
    SELECT DISTINCT

        CAST(
            TRIM(
                SUBSTRING_INDEX(periodo, '/', -1)
            )
            AS UNSIGNED
        ) AS ano

    FROM holerites

    WHERE funcionario_id = ?
      AND id_empresa = ?
      AND status = 'enviado'
      AND periodo IS NOT NULL
      AND TRIM(periodo) <> ''
      AND periodo LIKE '%/%'

    ORDER BY ano DESC
";

$stmtAnos = $con->prepare($sqlAnos);

if (!$stmtAnos) {
    die(
        'Erro ao preparar consulta dos anos: ' .
        htmlspecialchars($con->error)
    );
}

$stmtAnos->bind_param(
    'ii',
    $id_funcionario,
    $id_empresa
);

if (!$stmtAnos->execute()) {
    die(
        'Erro ao consultar anos disponíveis: ' .
        htmlspecialchars($stmtAnos->error)
    );
}

$resultadoAnos = $stmtAnos->get_result();

$anosDisponiveis = [];

while ($linhaAno = $resultadoAnos->fetch_assoc()) {
    $anoDisponivel = (int) ($linhaAno['ano'] ?? 0);

    if (
        $anoDisponivel >= 2000 &&
        $anoDisponivel <= 2100
    ) {
        $anosDisponiveis[] = $anoDisponivel;
    }
}

$stmtAnos->close();

if (count($anosDisponiveis) === 0) {
    $anosDisponiveis[] = (int) date('Y');
}

$ano = isset($_GET['ano'])
    ? (int) $_GET['ano']
    : $anosDisponiveis[0];

$mesRecebido = trim(
    (string) ($_GET['mes'] ?? '')
);

if ($mesRecebido === '') {
    $mes = '';
} else {
    $numeroMes = (int) $mesRecebido;

    $mes = str_pad(
        (string) $numeroMes,
        2,
        '0',
        STR_PAD_LEFT
    );
}

if (!array_key_exists($mes, $meses)) {
    $mes = '';
}

if ($ano < 2000 || $ano > 2100) {
    $ano = $anosDisponiveis[0];
}

$sql = "
    SELECT
        h.id,
        h.funcionario_id,
        h.arquivo,
        h.periodo,
        h.data_envio,
        h.status,
        h.id_empresa

    FROM holerites h

    WHERE h.funcionario_id = ?
      AND h.id_empresa = ?
      AND h.status = 'enviado'

      AND CAST(
            TRIM(
                SUBSTRING_INDEX(h.periodo, '/', -1)
            )
            AS UNSIGNED
          ) = ?
";

$tipos = 'iii';

$parametros = [
    $id_funcionario,
    $id_empresa,
    $ano
];

if ($mes !== '') {
    $nomeMesSelecionado = $meses[$mes];

    $sql .= "
        AND LOWER(
            TRIM(
                SUBSTRING_INDEX(h.periodo, '/', 1)
            )
        ) = LOWER(?)
    ";

    $tipos .= 's';
    $parametros[] = $nomeMesSelecionado;
}


$sql .= "
    ORDER BY

        CAST(
            TRIM(
                SUBSTRING_INDEX(h.periodo, '/', -1)
            )
            AS UNSIGNED
        ) DESC,

        CASE LOWER(
            TRIM(
                SUBSTRING_INDEX(h.periodo, '/', 1)
            )
        )
            WHEN 'janeiro' THEN 1
            WHEN 'fevereiro' THEN 2
            WHEN 'março' THEN 3
            WHEN 'marco' THEN 3
            WHEN 'abril' THEN 4
            WHEN 'maio' THEN 5
            WHEN 'junho' THEN 6
            WHEN 'julho' THEN 7
            WHEN 'agosto' THEN 8
            WHEN 'setembro' THEN 9
            WHEN 'outubro' THEN 10
            WHEN 'novembro' THEN 11
            WHEN 'dezembro' THEN 12
            ELSE 0
        END DESC,

        h.data_envio DESC,
        h.id DESC
";

$stmt = $con->prepare($sql);

if (!$stmt) {
    die(
        'Erro SQL holerites: ' .
        htmlspecialchars($con->error)
    );
}

$stmt->bind_param(
    $tipos,
    ...$parametros
);

if (!$stmt->execute()) {
    die(
        'Erro ao consultar holerites: ' .
        htmlspecialchars($stmt->error)
    );
}

$resultado = $stmt->get_result();

$holerites = [];

while ($row = $resultado->fetch_assoc()) {
    $holerites[] = $row;
}

$stmt->close();

$total = count($holerites);

function separarPeriodo(?string $periodo): array
{
    $periodo = trim((string) $periodo);

    if ($periodo === '') {
        return [
            'mes' => 'Mês',
            'ano' => date('Y')
        ];
    }

    $partes = array_map(
        'trim',
        explode('/', $periodo, 2)
    );

    $nomeMes = !empty($partes[0])
        ? $partes[0]
        : 'Mês';

    $anoPeriodo = !empty($partes[1])
        ? $partes[1]
        : date('Y');

    return [
        'mes' => $nomeMes,
        'ano' => $anoPeriodo
    ];
}


function montarCaminhoArquivo(?string $arquivo): string
{
    $arquivo = trim((string) $arquivo);

    if ($arquivo === '') {
        return '';
    }

    if (
        str_starts_with($arquivo, 'http://') ||
        str_starts_with($arquivo, 'https://')
    ) {
        return $arquivo;
    }

    $arquivo = preg_replace(
        '#^(\.\./)+#',
        '',
        $arquivo
    );

    $arquivo = ltrim(
        (string) $arquivo,
        '/\\'
    );

    return '../' . $arquivo;
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Seus Holerites</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebarfunc.css">
    <link rel="stylesheet" href="../css/global.css">

    <style>
        :root {
            --page-bg: #f4f6f9;
            --card-bg: #ffffff;
            --text-main: #1f2937;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --primary: #0d6efd;
            --primary-soft: #eff6ff;
            --shadow:
                0 10px 25px rgba(15, 23, 42, .08);
        }

        body {
            background: var(--page-bg);
            color: var(--text-main);
        }

        .content {
            min-height: 100vh;

            margin-left: 270px;
            padding: 30px;
        }

        .page-title {
            margin-bottom: 6px;

            color: var(--text-main);

            font-weight: 700;
        }

        .page-subtitle {
            margin-bottom: 0;
            color: var(--text-muted);
        }

        .filter-form .form-select {
            min-width: 190px;
            min-height: 48px;

            background: var(--card-bg);

            border: 1px solid #bfdbfe;
            border-radius: 16px;

            color: var(--text-main);
        }

        .filter-form .form-select:focus {
            border-color: #60a5fa;

            box-shadow:
                0 0 0 .22rem rgba(37, 99, 235, .12);
        }

        .card-holerite {
            height: 100%;

            background: var(--card-bg);

            border:
                1px solid rgba(226, 232, 240, .8);

            border-radius: 20px;

            box-shadow: var(--shadow);

            transition:
                box-shadow .25s ease,
                border-color .25s ease;
        }

        .card-holerite:hover {
            border-color: #bfdbfe;

            box-shadow:
                0 16px 36px rgba(15, 23, 42, .12);
        }

        .badge-mes {
            padding: 8px 12px;

            background: var(--primary);

            border-radius: 10px;

            color: #ffffff;

            font-size: 13px;
            white-space: nowrap;
        }

        .empty-box {
            padding: 40px;

            background: var(--card-bg);

            border: 1px solid var(--border);
            border-radius: 20px;

            box-shadow: var(--shadow);

            text-align: center;
        }

        .btn {
            border-radius: 8px;
        }

        .arquivo-indisponivel {
            padding: 12px 14px;

            background: var(--primary-soft);

            border-radius: 10px;

            color: var(--text-muted);
            text-align: center;
        }

        body.dark,
        body.dark-mode {
            --page-bg: #0f172a;
            --card-bg: #111c2f;
            --text-main: #f8fafc;
            --text-muted: #cbd5e1;
            --border: #334155;
            --primary-soft: #17233a;
            --shadow:
                0 10px 25px rgba(0, 0, 0, .24);
        }

        body.dark .text-secondary,
        body.dark-mode .text-secondary {
            color: var(--text-muted) !important;
        }

        body.dark .filter-form .form-select,
        body.dark-mode .filter-form .form-select {
            background: #0b1220;
            border-color: #334155;
            color: #f8fafc;
        }

        body.dark .filter-form option,
        body.dark-mode .filter-form option {
            background: #0b1220;
            color: #f8fafc;
        }

        body.dark .card-holerite,
        body.dark-mode .card-holerite {
            border-color: #2b3a55;
        }

        body.dark .card-holerite:hover,
        body.dark-mode .card-holerite:hover {
            border-color: #3b82f6;
        }

        @media (max-width: 991px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }

            .filter-form {
                width: 100%;
            }

            .filter-form .form-select {
                min-width: 0;
                flex: 1;
            }
        }

        @media (max-width: 575px) {
            .filter-form {
                flex-direction: column;
            }

            .filter-form .form-select {
                width: 100%;
            }

            .acoes-holerite {
                flex-direction: column;
            }

            .acoes-holerite .btn {
                width: 100% !important;
            }
        }
    </style>

</head>

<body>

<?php include 'sidebarfunc.php'; ?>

<div class="content">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>

            <h2 class="page-title">
                Seus Holerites
            </h2>

            <p class="page-subtitle">

                Total encontrado:

                <strong>
                    <?= $total ?>
                </strong>

            </p>

        </div>

        <form
            method="GET"
            action=""
            class="filter-form d-flex gap-2 flex-wrap"
            id="formFiltroHolerites"
        >

            <select
                name="ano"
                id="filtroAno"
                class="form-select"
                aria-label="Filtrar por ano"
            >

                <?php foreach (
                    $anosDisponiveis as $anoDisponivel
                ): ?>

                    <option
                        value="<?= $anoDisponivel ?>"
                        <?= $anoDisponivel === $ano
                            ? 'selected'
                            : ''
                        ?>
                    >

                        <?= $anoDisponivel ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <select
                name="mes"
                id="filtroMes"
                class="form-select"
                aria-label="Filtrar por mês"
            >

                <option value="">
                    Todos
                </option>

                <?php foreach (
                    $meses as $numeroMes => $nomeMes
                ): ?>

                    <option
                        value="<?= htmlspecialchars(
                            $numeroMes,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        <?= $mes === $numeroMes
                            ? 'selected'
                            : ''
                        ?>
                    >

                        <?= htmlspecialchars(
                            $nomeMes,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </form>

    </div>

    <div class="row g-4">

        <?php if ($total > 0): ?>

            <?php foreach ($holerites as $row): ?>

                <?php

                $dadosPeriodo = separarPeriodo(
                    $row['periodo'] ?? ''
                );

                $nomeMes = $dadosPeriodo['mes'];
                $anoPeriodo = $dadosPeriodo['ano'];

                $arquivo = trim(
                    (string) ($row['arquivo'] ?? '')
                );

                $caminhoArquivo = montarCaminhoArquivo(
                    $arquivo
                );

                ?>

                <div class="col-12 col-md-6 col-lg-4">

                    <div class="card card-holerite">

                        <div class="card-body p-4 d-flex flex-column">

                            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">

                                <h5 class="mb-0 fw-bold">
                                    Holerite
                                </h5>

                                <span class="badge-mes">

                                    <?= htmlspecialchars(
                                        $nomeMes,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                    /

                                    <?= htmlspecialchars(
                                        $anoPeriodo,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </span>

                            </div>

                            <p class="text-secondary flex-grow-1">

                                Comprovante referente ao mês de

                                <strong>

                                    <?= htmlspecialchars(
                                        $nomeMes,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>.

                            </p>

                            <?php if (
                                $arquivo !== '' &&
                                $caminhoArquivo !== ''
                            ): ?>

                                <div class="acoes-holerite d-flex gap-2 mt-3">

                                    <a
                                        href="<?= htmlspecialchars(
                                            $caminhoArquivo,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-primary w-50"
                                    >

                                        <i class="fa-solid fa-eye me-1"></i>

                                        Ver

                                    </a>

                                    <a
                                        href="<?= htmlspecialchars(
                                            $caminhoArquivo,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        download
                                        class="btn btn-outline-primary w-50"
                                    >

                                        <i class="fa-solid fa-download me-1"></i>

                                        Baixar

                                    </a>

                                </div>

                            <?php else: ?>

                                <div class="arquivo-indisponivel mt-3">

                                    <i class="fa-solid fa-file-circle-xmark me-1"></i>

                                    Arquivo não disponível.

                                </div>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="empty-box">

                    <i class="fa-solid fa-file-circle-xmark fa-3x text-secondary mb-3"></i>

                    <h4>
                        Nenhum holerite encontrado
                    </h4>

                    <p class="text-secondary mb-0">

                        Não existem holerites enviados para

                        <?php if ($mes !== ''): ?>

                            <strong>

                                <?= htmlspecialchars(
                                    $meses[$mes],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                de

                                <?= $ano ?>

                            </strong>.

                        <?php else: ?>

                            o ano de

                            <strong>
                                <?= $ano ?>
                            </strong>.

                        <?php endif; ?>

                    </p>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../js/theme.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const formulario = document.getElementById(
        'formFiltroHolerites'
    );

    const filtroAno = document.getElementById(
        'filtroAno'
    );

    const filtroMes = document.getElementById(
        'filtroMes'
    );

    function enviarFormulario() {

        if (!formulario) {
            return;
        }

        formulario.submit();

    }

    if (filtroAno) {

        filtroAno.addEventListener(
            'change',
            enviarFormulario
        );

    }

    if (filtroMes) {

        filtroMes.addEventListener(
            'change',
            enviarFormulario
        );

    }

});
</script>
</body>
</html>

