<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config/database.php';
require_once 'lang.php';
require_once 'notific.php';

$id_empresa = (int) ($_SESSION['id_empresa'] ?? 0);

if ($id_empresa <= 0) {
    die('Erro: empresa não identificada. Faça login novamente.');
}

$mesAtual = date('Y-m');

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

$mesNome = $meses[date('m')] ?? date('m');

function formatarMinutos(
    int|float|string|null $valorMinutos,
    bool $mostrarSinalPositivo = true
): string {
    $totalMinutos = (int) round((float) $valorMinutos);

    if ($totalMinutos === 0) {
        return '0h';
    }

    if ($totalMinutos > 0) {
        $sinal = $mostrarSinalPositivo ? '+' : '';
    } else {
        $sinal = '-';
    }

    $valorAbsoluto = abs($totalMinutos);

    $horas = intdiv($valorAbsoluto, 60);
    $minutos = $valorAbsoluto % 60;

    if ($horas === 0) {
        return $sinal . $minutos . 'min';
    }

    if ($minutos === 0) {
        return $sinal . $horas . 'h';
    }

    return $sinal .
        $horas .
        'h' .
        str_pad(
            (string) $minutos,
            2,
            '0',
            STR_PAD_LEFT
        ) .
        'min';
}

function classeSaldo(int|float|string|null $minutos): string
{
    $valor = (int) round((float) $minutos);

    if ($valor > 0) {
        return 'saldo-positivo';
    }

    if ($valor < 0) {
        return 'saldo-negativo';
    }

    return 'saldo-neutro';
}

$registros = [];

$sql = "
    SELECT
        f.id_funcionario,
        f.nome,

        CAST(
            ROUND(
                COALESCE(
                    SUM(
                        CASE
                            WHEN p.total_horas IS NOT NULL
                            THEN (p.total_horas - 8) * 60
                            ELSE 0
                        END
                    ),
                    0
                ),
                0
            )
            AS SIGNED
        ) AS saldo_total_minutos,

        CAST(
            ROUND(
                COALESCE(
                    SUM(
                        CASE
                            WHEN MONTH(p.data) = MONTH(CURDATE())
                             AND YEAR(p.data) = YEAR(CURDATE())
                             AND p.total_horas IS NOT NULL
                            THEN (p.total_horas - 8) * 60
                            ELSE 0
                        END
                    ),
                    0
                ),
                0
            )
            AS SIGNED
        ) AS saldo_mes_minutos,

        CAST(
            ROUND(
                COALESCE(
                    SUM(
                        CASE
                            WHEN MONTH(p.data) = MONTH(CURDATE())
                             AND YEAR(p.data) = YEAR(CURDATE())
                             AND p.total_horas > 8
                            THEN (p.total_horas - 8) * 60
                            ELSE 0
                        END
                    ),
                    0
                ),
                0
            )
            AS SIGNED
        ) AS extras_mes_minutos,

        MAX(p.data) AS data_atualizacao

    FROM funcionarios f

    LEFT JOIN pontos p
        ON p.id_funcionario = f.id_funcionario
       AND p.id_empresa = f.id_empresa

    WHERE f.id_empresa = ?
      AND f.ativo = 1

    GROUP BY
        f.id_funcionario,
        f.nome

    ORDER BY
        f.nome ASC
";

$stmt = $con->prepare($sql);

if (!$stmt) {
    die(
        'Erro no prepare da tabela: ' .
        htmlspecialchars($con->error)
    );
}

$stmt->bind_param('i', $id_empresa);

if (!$stmt->execute()) {
    die(
        'Erro ao consultar banco de horas: ' .
        htmlspecialchars($stmt->error)
    );
}

$resultado = $stmt->get_result();

while ($row = $resultado->fetch_assoc()) {
    $row['saldo_total_minutos'] =
        (int) $row['saldo_total_minutos'];

    $row['saldo_mes_minutos'] =
        (int) $row['saldo_mes_minutos'];

    $row['extras_mes_minutos'] =
        (int) $row['extras_mes_minutos'];

    if ($row['saldo_total_minutos'] > 0) {
        $row['status'] = 'positivo';
    } elseif ($row['saldo_total_minutos'] < 0) {
        $row['status'] = 'negativo';
    } else {
        $row['status'] = 'neutro';
    }

    $registros[] = $row;
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| INDICADORES
|--------------------------------------------------------------------------
*/

$positivos = 0;
$negativos = 0;

$totalExtrasMinutos = 0;
$totalFuncionarios = count($registros);

$maiorPositivo = null;
$maiorNegativo = null;

$alertasNegativos = [];

foreach ($registros as $registro) {
    $saldoTotalMinutos =
        (int) $registro['saldo_total_minutos'];

    $extrasMesMinutos =
        (int) $registro['extras_mes_minutos'];

    if ($saldoTotalMinutos > 0) {
        $positivos++;

        if (
            $maiorPositivo === null ||
            $saldoTotalMinutos >
            (int) $maiorPositivo['saldo_total_minutos']
        ) {
            $maiorPositivo = $registro;
        }
    }

    if ($saldoTotalMinutos < 0) {
        $negativos++;

        $alertasNegativos[] = $registro;

        if (
            $maiorNegativo === null ||
            $saldoTotalMinutos <
            (int) $maiorNegativo['saldo_total_minutos']
        ) {
            $maiorNegativo = $registro;
        }
    }

    $totalExtrasMinutos += $extrasMesMinutos;
}

usort(
    $alertasNegativos,
    function (array $a, array $b): int {
        return
            (int) $a['saldo_total_minutos']
            <=>
            (int) $b['saldo_total_minutos'];
    }
);

$mediaExtrasMinutos = $totalFuncionarios > 0
    ? (int) round($totalExtrasMinutos / $totalFuncionarios)
    : 0;

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Banco de Horas</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/banco-horas.css">
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid">

        <div class="dashboard-header">

            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">

                <div>

                    <h1 class="dashboard-title">
                        Banco de Horas
                    </h1>

                    <p class="dashboard-subtitle">
                        Acompanhe saldos, horas extras e pendências de
                        compensação.
                    </p>

                </div>

                <div class="d-flex align-items-start">

                    <span class="month-pill">

                        <i class="bi bi-calendar3 me-1"></i>

                        <?= htmlspecialchars($mesNome) ?>
                        /
                        <?= date('Y') ?>

                    </span>

                </div>

            </div>

        </div>

        <div class="row g-3 mb-4">

            <div class="col-12 col-md-6 col-xl-3">

                <div class="kpi-card">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="kpi-label">
                                Saldos positivos
                            </div>

                            <h2 class="kpi-value">
                                <?= $positivos ?>
                            </h2>

                            <div class="kpi-small">
                                Funcionários com saldo positivo
                            </div>

                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-arrow-up-right"></i>
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-12 col-md-6 col-xl-3">

                <div class="kpi-card">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="kpi-label">
                                Saldos negativos
                            </div>

                            <h2 class="kpi-value">
                                <?= $negativos ?>
                            </h2>

                            <div class="kpi-small">
                                Funcionários em atenção
                            </div>

                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-exclamation-circle"></i>
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-12 col-md-6 col-xl-3">

                <div class="kpi-card">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="kpi-label">
                                Horas extras
                            </div>

                            <h2 class="kpi-value">
                                <?= formatarMinutos(
                                    $totalExtrasMinutos,
                                    false
                                ) ?>
                            </h2>

                            <div class="kpi-small">
                                Total positivo no mês
                            </div>

                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-clock"></i>
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-12 col-md-6 col-xl-3">

                <div class="kpi-card">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="kpi-label">
                                Monitorados
                            </div>

                            <h2 class="kpi-value">
                                <?= $totalFuncionarios ?>
                            </h2>

                            <div class="kpi-small">
                                Funcionários ativos
                            </div>

                        </div>

                        <div class="kpi-icon">
                            <i class="bi bi-people"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="row g-3 mb-4">

            <div class="col-12 col-xl-7">

                <div class="panel-card">

                    <div class="panel-header">

                        <h5>Funcionários em alerta</h5>

                        <p>
                            Maiores saldos negativos para acompanhamento do RH.
                        </p>

                    </div>

                    <div class="panel-body-custom">

                        <?php if (count($alertasNegativos) > 0): ?>

                            <?php foreach (
                                array_slice($alertasNegativos, 0, 5)
                                as $alerta
                            ): ?>

                                <?php

                                $inicialAlerta = mb_strtoupper(
                                    mb_substr(
                                        $alerta['nome'],
                                        0,
                                        1,
                                        'UTF-8'
                                    ),
                                    'UTF-8'
                                );

                                ?>

                                <div class="alert-user">

                                    <div class="d-flex align-items-center gap-2">

                                        <div class="employee-avatar">

                                            <?= htmlspecialchars(
                                                $inicialAlerta
                                            ) ?>

                                        </div>

                                        <div>

                                            <div class="employee-name">

                                                <?= htmlspecialchars(
                                                    $alerta['nome']
                                                ) ?>

                                            </div>

                                            <div class="text-muted small">
                                                Saldo total negativo
                                            </div>

                                        </div>

                                    </div>

                                    <span class="saldo-negativo">

                                        <?= formatarMinutos(
                                            $alerta[
                                                'saldo_total_minutos'
                                            ]
                                        ) ?>

                                    </span>

                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="empty-state">

                                <i class="bi bi-check-circle"></i>

                                Nenhum funcionário com saldo negativo.

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <div class="col-12 col-xl-5">

                <div class="panel-card">

                    <div class="panel-header">

                        <h5>Resumo do mês</h5>

                        <p>
                            Indicadores rápidos do período atual.
                        </p>

                    </div>

                    <div class="panel-body-custom">

                        <div class="metric-line">

                            <span>Maior saldo positivo</span>

                            <strong class="saldo-positivo">

                                <?php if ($maiorPositivo): ?>

                                    <?= htmlspecialchars(
                                        $maiorPositivo['nome']
                                    ) ?>

                                    <?= formatarMinutos(
                                        $maiorPositivo[
                                            'saldo_total_minutos'
                                        ]
                                    ) ?>

                                <?php else: ?>

                                    -

                                <?php endif; ?>

                            </strong>

                        </div>

                        <div class="metric-line">

                            <span>Maior saldo negativo</span>

                            <strong class="saldo-negativo">

                                <?php if ($maiorNegativo): ?>

                                    <?= htmlspecialchars(
                                        $maiorNegativo['nome']
                                    ) ?>

                                    <?= formatarMinutos(
                                        $maiorNegativo[
                                            'saldo_total_minutos'
                                        ]
                                    ) ?>

                                <?php else: ?>

                                    -

                                <?php endif; ?>

                            </strong>

                        </div>

                        <div class="metric-line">

                            <span>Média de extras</span>

                            <strong>

                                <?= formatarMinutos(
                                    $mediaExtrasMinutos,
                                    false
                                ) ?>

                            </strong>

                        </div>

                        <div class="metric-line">

                            <span>Total de funcionários</span>

                            <strong>
                                <?= $totalFuncionarios ?>
                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="table-card">

            <div class="table-card-header">

                <div class="row align-items-center g-3">

                    <div class="col-lg-6">

                        <h5>Saldos dos Funcionários</h5>

                        <p>
                            Consulte o saldo total e mensal de cada funcionário.
                        </p>

                    </div>

                    <div class="col-lg-3">

                        <input
                            type="text"
                            id="pesquisaTabela"
                            class="form-control search-input"
                            placeholder="Pesquisar funcionário"
                            autocomplete="off"
                        >

                    </div>

                    <div class="col-lg-3">

                        <select
                            id="filtroStatus"
                            class="form-select status-filter"
                        >

                            <option value="todos">
                                Todos
                            </option>

                            <option value="positivo">
                                Positivo
                            </option>

                            <option value="negativo">
                                Negativo
                            </option>

                            <option value="neutro">
                                Neutro
                            </option>

                        </select>

                    </div>

                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>Funcionário</th>

                            <th>Saldo Total</th>

                            <th>Este Mês</th>

                            <th>Última Atualização</th>

                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody id="corpoTabela">

                    <?php if (count($registros) > 0): ?>

                        <?php foreach ($registros as $row): ?>

                            <?php

                            $statusLinha =
                                $row['status'] ?? 'neutro';

                            if ($statusLinha === 'positivo') {
                                $badge = '
                                    <span class="badge-status badge-positivo">
                                        Positivo
                                    </span>
                                ';
                            } elseif ($statusLinha === 'negativo') {
                                $badge = '
                                    <span class="badge-status badge-negativo">
                                        Negativo
                                    </span>
                                ';
                            } else {
                                $statusLinha = 'neutro';

                                $badge = '
                                    <span class="badge-status badge-neutro">
                                        Neutro
                                    </span>
                                ';
                            }

                            $dataAtualizacao =
                                !empty($row['data_atualizacao'])
                                    ? date(
                                        'd/m/Y',
                                        strtotime(
                                            $row['data_atualizacao']
                                        )
                                    )
                                    : 'Sem registro';

                            $saldoTotalClass = classeSaldo(
                                $row['saldo_total_minutos']
                            );

                            $saldoMesClass = classeSaldo(
                                $row['saldo_mes_minutos']
                            );

                            $inicial = mb_strtoupper(
                                mb_substr(
                                    $row['nome'],
                                    0,
                                    1,
                                    'UTF-8'
                                ),
                                'UTF-8'
                            );

                            ?>

                            <tr
                                data-nome="<?= htmlspecialchars(
                                    mb_strtolower(
                                        $row['nome'],
                                        'UTF-8'
                                    )
                                ) ?>"
                                data-status="<?= htmlspecialchars(
                                    $statusLinha
                                ) ?>"
                            >

                                <td>

                                    <div class="d-flex align-items-center gap-2">

                                        <div class="employee-avatar">

                                            <?= htmlspecialchars($inicial) ?>

                                        </div>

                                        <span class="employee-name">

                                            <?= htmlspecialchars(
                                                $row['nome']
                                            ) ?>

                                        </span>

                                    </div>

                                </td>

                                <td>

                                    <span class="<?= htmlspecialchars(
                                        $saldoTotalClass
                                    ) ?>">

                                        <?= formatarMinutos(
                                            $row[
                                                'saldo_total_minutos'
                                            ]
                                        ) ?>

                                    </span>

                                </td>

                                <td>

                                    <span class="<?= htmlspecialchars(
                                        $saldoMesClass
                                    ) ?>">

                                        <?= formatarMinutos(
                                            $row[
                                                'saldo_mes_minutos'
                                            ]
                                        ) ?>

                                    </span>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        $dataAtualizacao
                                    ) ?>

                                </td>

                                <td>
                                    <?= $badge ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="5">

                                <div class="empty-state">

                                    <i class="bi bi-inbox"></i>

                                    Nenhum saldo encontrado.

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <div
                id="semResultado"
                class="empty-state d-none"
            >

                <i class="bi bi-search"></i>

                Nenhum funcionário encontrado.

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/theme.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const pesquisaTabela = document.getElementById(
        'pesquisaTabela'
    );

    const filtroStatus = document.getElementById(
        'filtroStatus'
    );

    const linhas = document.querySelectorAll(
        '#corpoTabela tr[data-nome]'
    );

    const semResultado = document.getElementById(
        'semResultado'
    );

    function removerAcentos(texto) {
        return String(texto || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function filtrarTabela() {
        const pesquisa = removerAcentos(
            pesquisaTabela.value
                .toLowerCase()
                .trim()
        );

        const statusSelecionado = filtroStatus.value;

        let quantidadeVisivel = 0;

        linhas.forEach(function (linha) {
            const nome = removerAcentos(
                linha.dataset.nome || ''
            );

            const statusLinha =
                linha.dataset.status || 'neutro';

            const combinaNome =
                nome.includes(pesquisa);

            const combinaStatus =
                statusSelecionado === 'todos' ||
                statusSelecionado === statusLinha;

            const deveMostrar =
                combinaNome && combinaStatus;

            linha.style.display =
                deveMostrar ? '' : 'none';

            if (deveMostrar) {
                quantidadeVisivel++;
            }
        });

        if (linhas.length > 0) {
            semResultado.classList.toggle(
                'd-none',
                quantidadeVisivel > 0
            );
        }
    }

    if (pesquisaTabela) {
        pesquisaTabela.addEventListener(
            'input',
            filtrarTabela
        );
    }

    if (filtroStatus) {
        filtroStatus.addEventListener(
            'change',
            filtrarTabela
        );
    }

});
</script>
</body>
</html>