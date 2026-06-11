<?php
require_once 'notific.php';

$idUsuarioNotific = $_SESSION['id_usuario'] ?? 0;
$idEmpresaNotific = $_SESSION['id_empresa'] ?? 0;

$totalNotificacoesNaoLidas = 0;
$listaNotificacoesPopup = [];

if ($idUsuarioNotific && $idEmpresaNotific) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_notificacao_lida'])) {

        $idNotificacaoLida = intval($_POST['id_notificacao'] ?? 0);

        $stmtLida = $con->prepare("
            UPDATE notificacoes
            SET lida = 1
            WHERE id_notificacao = ?
            AND id_usuario_destino = ?
            AND id_empresa = ?
        ");

        if ($stmtLida) {

            $stmtLida->bind_param(
                "iii",
                $idNotificacaoLida,
                $idUsuarioNotific,
                $idEmpresaNotific
            );

            $stmtLida->execute();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_todas_notificacoes_lidas'])) {

        $stmtTodasLidas = $con->prepare("
            UPDATE notificacoes
            SET lida = 1
            WHERE id_usuario_destino = ?
            AND id_empresa = ?
        ");

        if ($stmtTodasLidas) {

            $stmtTodasLidas->bind_param(
                "ii",
                $idUsuarioNotific,
                $idEmpresaNotific
            );

            $stmtTodasLidas->execute();
        }
    }

    $totalNotificacoesNaoLidas = contarNotificacoesNaoLidas(
        $con,
        $idUsuarioNotific,
        $idEmpresaNotific
    );

    $stmtNotificacoesPopup = $con->prepare("
        SELECT
            id_notificacao,
            tipo,
            titulo,
            mensagem,
            link,
            lida,
            data_criacao
        FROM notificacoes
        WHERE id_usuario_destino = ?
        AND id_empresa = ?
        ORDER BY lida ASC, data_criacao DESC
        LIMIT 5
    ");

    if ($stmtNotificacoesPopup) {

        $stmtNotificacoesPopup->bind_param(
            "ii",
            $idUsuarioNotific,
            $idEmpresaNotific
        );

        $stmtNotificacoesPopup->execute();

        $resultadoNotificacoesPopup = $stmtNotificacoesPopup->get_result();

        while ($notificacaoPopup = $resultadoNotificacoesPopup->fetch_assoc()) {
            $listaNotificacoesPopup[] = $notificacaoPopup;
        }
    }
}

function iconeNotificacaoPopup($tipo) {

    if ($tipo == 'emergencia') {
        return 'bi-exclamation-triangle-fill text-danger';
    }

    if ($tipo == 'solicitacao') {
        return 'bi-inbox-fill text-primary';
    }

    if ($tipo == 'aprovacao') {
        return 'bi-check-circle-fill text-success';
    }

    if ($tipo == 'resumo') {
        return 'bi-calendar-week-fill text-info';
    }

    return 'bi-bell-fill text-primary';
}
?>

<style>
.sinho-wrapper{
    position:fixed;
    top:10px;
    right:10px;
    z-index:99997;
}

.sinho-btn{
    width:38px;
    height:38px;
    border-radius:50%;
    border:none;
    background:#0d6efd;
    color:white;
    box-shadow:0 8px 18px rgba(13,110,253,.30);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:17px;
    position:relative;
}

.sinho-btn:hover{
    background:#0b5ed7;
}

.sinho-badge{
    position:absolute;
    top:-4px;
    right:-4px;
    min-width:21px;
    height:21px;
    border-radius:999px;
    background:#dc3545;
    color:white;
    font-size:12px;
    font-weight:700;
    display:flex;
    align-items:center;
    justify-content:center;
    border:2px solid white;
}

.sinho-box{
    position:absolute;
    right:0;
    top:58px;
    width:360px;
    max-width:calc(100vw - 32px);
    background:white;
    border-radius:18px;
    box-shadow:0 18px 45px rgba(15,23,42,.18);
    border:1px solid #e5e7eb;
    overflow:hidden;
    display:none;
}

.sinho-box.ativo{
    display:block;
}

.sinho-header{
    padding:16px;
    background:#0d6efd;
    color:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.sinho-lista{
    max-height:380px;
    overflow:auto;
}

.sinho-item{
    padding:14px 16px;
    border-bottom:1px solid #e5e7eb;
    display:flex;
    gap:12px;
}

.sinho-item:last-child{
    border-bottom:none;
}

.sinho-item.nao-lida{
    background:#f8fbff;
}

.sinho-icon{
    width:38px;
    height:38px;
    border-radius:12px;
    background:#f1f5f9;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
    font-size:18px;
}

.sinho-titulo{
    font-weight:700;
    font-size:14px;
    margin-bottom:3px;
}

.sinho-msg{
    font-size:13px;
    color:#64748b;
    margin-bottom:6px;
}

.sinho-data{
    font-size:12px;
    color:#94a3b8;
}

.sinho-actions{
    display:flex;
    gap:6px;
    margin-top:8px;
}

.sinho-empty{
    padding:28px 16px;
    text-align:center;
    color:#64748b;
}

.sinho-footer{
    padding:12px 16px;
    background:#f8fafc;
    text-align:center;
}

body.dark-mode .sinho-box{
    background:#0f172a;
    border-color:#334155;
    color:#f8fafc;
}

body.dark-mode .sinho-item{
    border-color:#334155;
}

body.dark-mode .sinho-item.nao-lida{
    background:#111827;
}

body.dark-mode .sinho-msg,
body.dark-mode .sinho-data{
    color:#cbd5e1;
}

body.dark-mode .sinho-footer{
    background:#111827;
}
</style>

<div class="sinho-wrapper">

    <button type="button" class="sinho-btn" id="btnSinho">
        <i class="bi bi-bell-fill"></i>

        <?php if($totalNotificacoesNaoLidas > 0): ?>
            <span class="sinho-badge">
                <?= $totalNotificacoesNaoLidas ?>
            </span>
        <?php endif; ?>
    </button>

    <div class="sinho-box" id="boxSinho">

        <div class="sinho-header">

            <div>
                <strong>Notificações</strong><br>
                <small><?= $totalNotificacoesNaoLidas ?> não lidas</small>
            </div>

            <?php if($totalNotificacoesNaoLidas > 0): ?>
                <form method="POST" class="m-0">
                    <button
                        type="submit"
                        name="marcar_todas_notificacoes_lidas"
                        class="btn btn-sm btn-light"
                    >
                        Limpar
                    </button>
                </form>
            <?php endif; ?>

        </div>

        <div class="sinho-lista">

            <?php if(count($listaNotificacoesPopup) > 0): ?>

                <?php foreach($listaNotificacoesPopup as $n): ?>

                    <div class="sinho-item <?= $n['lida'] == 0 ? 'nao-lida' : '' ?>">

                        <div class="sinho-icon">
                            <i class="bi <?= iconeNotificacaoPopup($n['tipo']) ?>"></i>
                        </div>

                        <div class="flex-grow-1">

                            <div class="sinho-titulo">
                                <?= htmlspecialchars($n['titulo']) ?>
                            </div>

                            <div class="sinho-msg">
                                <?= htmlspecialchars($n['mensagem']) ?>
                            </div>

                            <div class="sinho-data">
                                <?= date('d/m/Y H:i', strtotime($n['data_criacao'])) ?>
                            </div>

                            <div class="sinho-actions">

                                <?php if(!empty($n['link'])): ?>
                                    <a href="<?= htmlspecialchars($n['link']) ?>" class="btn btn-sm btn-primary">
                                        Abrir
                                    </a>
                                <?php endif; ?>

                                <?php if($n['lida'] == 0): ?>
                                    <form method="POST" class="m-0">
                                        <input
                                            type="hidden"
                                            name="id_notificacao"
                                            value="<?= intval($n['id_notificacao']) ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="marcar_notificacao_lida"
                                            class="btn btn-sm btn-outline-secondary"
                                        >
                                            Lida
                                        </button>
                                    </form>
                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="sinho-empty">
                    <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                    Nenhuma notificação no momento.
                </div>

            <?php endif; ?>

        </div>

        <div class="sinho-footer">
            <small class="text-muted">
                As últimas 5 notificações aparecem aqui.
            </small>
        </div>

    </div>

</div>

<script>
const btnSinho = document.getElementById('btnSinho');
const boxSinho = document.getElementById('boxSinho');

if(btnSinho && boxSinho){

    btnSinho.addEventListener('click', function(event){
        event.stopPropagation();
        boxSinho.classList.toggle('ativo');
    });

    boxSinho.addEventListener('click', function(event){
        event.stopPropagation();
    });

    document.addEventListener('click', function(){
        boxSinho.classList.remove('ativo');
    });
}
</script>