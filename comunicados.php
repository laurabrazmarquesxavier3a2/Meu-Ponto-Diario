<?php
$comunicados = [];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Comunicados</title>

<link rel="stylesheet" href="css/style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>

/* CARDS */
.card-dashboard {
    border-radius: 14px;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* FIXADOS */
.card-fixado {
    background: #fef9e7;
    border-left: 5px solid #f59e0b;
}

/* BADGES */
.badge-soft {
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
}

/* CORES */
.badge-politica {
    background: #e0e7ff;
    color: #3730a3;
}

.badge-evento {
    background: #f3e8ff;
    color: #7e22ce;
}

.badge-comemoracao {
    background: #ffe4e6;
    color: #be123c;
}

.badge-urgente {
    background: #fee2e2;
    color: #b91c1c;
}

.badge-geral {
    background: #e0f2fe;
    color: #0369a1;
}

</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="fw-bold">
                Comunicados
            </h1>

            <h5 class="text-muted mb-4">
                Avisos importantes para os funcionários
            </h5>
        </div>

        <!-- BOTÃO -->
        <button class="btn btn-primary px-4 py-2"
                data-bs-toggle="modal"
                data-bs-target="#modalComunicado">

            <i class="bi bi-bell me-2"></i>
            Novo Comunicado

        </button>

    </div>

    <!-- CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="card card-dashboard p-3">

                <small>Total de Comunicados</small>

                <h2 class="fw-bold d-flex justify-content-between">

                    <span id="totalComunicados">0</span>

                    <i class="bi bi-bell"></i>

                </h2>

            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-dashboard p-3">

                <small>Fixados</small>

                <h2 class="fw-bold d-flex justify-content-between">

                    <span id="totalFixados">0</span>

                    <i class="bi bi-pin"></i>

                </h2>

            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-dashboard p-3">

                <small>Este mês</small>

                <h2 class="fw-bold d-flex justify-content-between">

                    <span id="mesComunicados">0</span>

                    <i class="bi bi-calendar"></i>

                </h2>

            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-dashboard p-3">

                <small>Alcance</small>

                <h2 class="fw-bold d-flex justify-content-between">

                    248

                    <i class="bi bi-people"></i>

                </h2>

            </div>
        </div>

    </div>

    <!-- FIXADOS -->
    <div id="comunicadosFixados"></div>

    <!-- TODOS -->
    <h5 class="fw-bold mt-4 mb-3">
        Todos os Comunicados
    </h5>

    <div id="listaComunicados"></div>

</div>

<!-- MODAL -->
<div class="modal fade"
     id="modalComunicado"
     tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Novo Comunicado
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <div class="mb-3">

                    <label>Título</label>

                    <input type="text"
                           id="titulo"
                           class="form-control">

                </div>

                <div class="mb-3">

                    <label>Conteúdo</label>

                    <textarea id="conteudo"
                              class="form-control"
                              rows="5"></textarea>

                </div>

                <div class="mb-3">

                    <label>Categoria</label>

                    <select id="categoria"
                            class="form-select">

                        <option>Política</option>
                        <option>Evento</option>
                        <option>Comemoração</option>
                        <option>Urgente</option>

                    </select>

                </div>

                <div class="form-check">

                    <input type="checkbox"
                           id="fixado"
                           class="form-check-input">

                    <label class="form-check-label">
                        Fixar comunicado
                    </label>

                </div>

            </div>

            <div class="modal-footer">

                <button class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    Cancelar

                </button>

                <button class="btn btn-primary"
                        onclick="salvarComunicado()">

                    Publicar

                </button>

            </div>

        </div>

    </div>

</div>

<!-- BOOTSTRAP -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- THEME -->
<script src="js/theme.js"></script>

<!-- SCRIPT -->
<script>

function carregarComunicados() {

    const lista =
        document.getElementById("listaComunicados");

    const fixadosDiv =
        document.getElementById("comunicadosFixados");

    let comunicados =
        JSON.parse(localStorage.getItem("comunicados")) || [];

    lista.innerHTML = "";
    fixadosDiv.innerHTML = "";

    let totalFixados = 0;

    comunicados.forEach(c => {

        const card = `
        <div class="card card-dashboard p-4 mb-3 ${c.fixado ? 'card-fixado' : ''}">

            <div class="d-flex justify-content-between mb-2">

                <span class="badge bg-primary">
                    ${c.categoria}
                </span>

                <small class="text-muted">
                    ${c.data}
                </small>

            </div>

            <h5 class="fw-bold">
                ${c.titulo}
            </h5>

            <p class="text-muted">
                ${c.conteudo}
            </p>

            <div class="small text-muted">
                Por Administrador
            </div>

        </div>
        `;

        if(c.fixado){

            totalFixados++;

            fixadosDiv.innerHTML += card;

        } else {

            lista.innerHTML += card;
        }

    });

    // ESTATÍSTICAS
    document.getElementById("totalComunicados")
        .innerText = comunicados.length;

    document.getElementById("totalFixados")
        .innerText = totalFixados;

    document.getElementById("mesComunicados")
        .innerText = comunicados.length;
}

// SALVAR
function salvarComunicado() {

    const titulo =
        document.getElementById("titulo").value;

    const conteudo =
        document.getElementById("conteudo").value;

    const categoria =
        document.getElementById("categoria").value;

    const fixado =
        document.getElementById("fixado").checked;

    if(titulo.trim() === "" || conteudo.trim() === ""){

        alert("Preencha todos os campos.");

        return;
    }

    let comunicados =
        JSON.parse(localStorage.getItem("comunicados")) || [];

    comunicados.unshift({

        titulo,
        conteudo,
        categoria,
        fixado,

        data: new Date().toLocaleDateString("pt-BR")

    });

    localStorage.setItem(
        "comunicados",
        JSON.stringify(comunicados)
    );

    carregarComunicados();

    // LIMPAR CAMPOS
    document.getElementById("titulo").value = "";
    document.getElementById("conteudo").value = "";

    // FECHAR MODAL
    bootstrap.Modal
        .getInstance(
            document.getElementById("modalComunicado")
        )
        .hide();
}

// INICIAR
carregarComunicados();

</script>

</body>
</html>