Substitua esse arquivo inteiro por este. Ele pega `id_empresa` da sessão, filtra tudo pela empresa logada e protege o link do arquivo:

```php
<?php
require_once 'auth.php';
require_once 'config/database.php';

$idEmpresa = $_SESSION['id_empresa'] ?? null;

if (!$idEmpresa) {
    die("Empresa não identificada. Faça login novamente.");
}

/* BUSCAR LICENÇAS DA EMPRESA */
$sql = "
SELECT
    lm.*,
    f.nome
FROM licencas_medicas lm
INNER JOIN funcionarios f
ON lm.id_funcionario = f.id_funcionario
AND lm.id_empresa = f.id_empresa
WHERE lm.id_empresa = ?
ORDER BY lm.data_envio DESC
";

$stmt = $con->prepare($sql);

if (!$stmt) {
    die("Erro SQL licenças: " . $con->error);
}

$stmt->bind_param("i", $idEmpresa);
$stmt->execute();

$result = $stmt->get_result();

/* TOTAL DE SUBMISSÕES DA EMPRESA */
$stmtTotal = $con->prepare("
    SELECT COUNT(*) AS total
    FROM licencas_medicas
    WHERE id_empresa = ?
");

$stmtTotal->bind_param("i", $idEmpresa);
$stmtTotal->execute();

$totalSubmissoes = $stmtTotal
    ->get_result()
    ->fetch_assoc()['total'];

/* LICENÇAS ATIVAS DA EMPRESA */
$stmtAtivas = $con->prepare("
    SELECT COUNT(*) AS total
    FROM licencas_medicas
    WHERE id_empresa = ?
    AND CURDATE() BETWEEN data_inicio AND data_fim
");

$stmtAtivas->bind_param("i", $idEmpresa);
$stmtAtivas->execute();

$licencasAtivas = $stmtAtivas
    ->get_result()
    ->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Licença Médica</title>

    <link rel="stylesheet" href="css/style.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <div class="container-fluid">

        <h1 class="fw-bold">Licenças Médicas</h1>

        <h5 class="text-muted mb-4">
            Gerencie envios de atestados e licenças médicas da sua empresa
        </h5>

        <div class="row g-4 mb-4">

            <div class="col-12 col-md-6">
                <div class="card card-dashboard p-3 text-start">
                    <h5>Licenças Ativas</h5>

                    <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                        <?= $licencasAtivas ?>
                        <i class="bi bi-heart-pulse"></i>
                    </h1>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card card-dashboard p-3 text-start">
                    <h5>Total de Submissões</h5>

                    <h1 class="fw-bolder d-flex justify-content-between align-items-center">
                        <?= $totalSubmissoes ?>
                        <i class="bi bi-file-earmark-medical"></i>
                    </h1>
                </div>
            </div>

        </div>

        <div class="card card-dashboard p-3">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <h5 class="mb-0">Atestados e Licenças</h5>

                <input
                    type="text"
                    class="form-control"
                    id="pesquisarLicenca"
                    placeholder="Pesquisar funcionário, motivo ou período..."
                    style="max-width:320px;"
                >

            </div>

            <div class="table-responsive">

                <table class="table table-hover mt-3 align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>Funcionário</th>
                            <th>Período</th>
                            <th>Dias</th>
                            <th>Motivo</th>
                            <th>Data Envio</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody id="tabelaLicencas">

                    <?php if($result->num_rows > 0){ ?>

                        <?php while($licenca = $result->fetch_assoc()){ ?>

                            <?php
                            $arquivo = $licenca['arquivo_atestado'];

                            if (!empty($arquivo) && strpos($arquivo, '../') !== 0) {
                                $arquivoLink = $arquivo;
                            } else {
                                $arquivoLink = str_replace('../', '', $arquivo);
                            }
                            ?>

                            <tr>

                                <td>
                                    <i class="bi bi-person me-2"></i>
                                    <?= htmlspecialchars($licenca['nome']) ?>
                                </td>

                                <td>
                                    <?= date('d/m/Y', strtotime($licenca['data_inicio'])) ?>
                                    -
                                    <?= date('d/m/Y', strtotime($licenca['data_fim'])) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($licenca['dias']) ?> dias
                                </td>

                                <td>
                                    <?= htmlspecialchars($licenca['motivo']) ?>
                                </td>

                                <td>
                                    <?= date('d/m/Y H:i', strtotime($licenca['data_envio'])) ?>
                                </td>

                                <td>
                                    <?php if(!empty($arquivoLink)): ?>

                                        <a
                                            href="<?= htmlspecialchars($arquivoLink) ?>"
                                            target="_blank"
                                            class="btn btn-outline-primary btn-sm"
                                        >
                                            <i class="bi bi-eye me-1"></i>
                                            Ver atestado
                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Sem arquivo
                                        </span>

                                    <?php endif; ?>
                                </td>

                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Nenhuma licença enviada para esta empresa
                            </td>
                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>
        </div>

    </div>

</div>

<script>
const pesquisa = document.getElementById('pesquisarLicenca');
const linhas = document.querySelectorAll('#tabelaLicencas tr');

pesquisa.addEventListener('keyup', function(){

    const termo = this.value.toLowerCase();

    linhas.forEach(function(linha){

        const texto = linha.innerText.toLowerCase();

        linha.style.display =
            texto.includes(termo) ? '' : 'none';

    });

});
</script>

<script src="js/theme.js"></script>

</body>
</html>
```
