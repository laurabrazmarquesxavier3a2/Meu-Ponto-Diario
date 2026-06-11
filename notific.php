<?php

function buscarConfigNotificacoes($con, $idUsuario, $idEmpresa) {
    $padrao = [
        'novas_solicitacoes' => 1,
        'aprovacoes_pendentes' => 1,
        'alertas_emergencia' => 1,
        'resumo_semanal' => 0
    ];

    $stmt = $con->prepare("
        SELECT 
            novas_solicitacoes,
            aprovacoes_pendentes,
            alertas_emergencia,
            resumo_semanal
        FROM config_notificacoes
        WHERE id_usuario = ?
        AND id_empresa = ?
        LIMIT 1
    ");

    if (!$stmt) {
        return $padrao;
    }

    $stmt->bind_param("ii", $idUsuario, $idEmpresa);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        return $resultado->fetch_assoc();
    }

    $stmtInsert = $con->prepare("
        INSERT INTO config_notificacoes (
            id_usuario,
            id_empresa,
            novas_solicitacoes,
            aprovacoes_pendentes,
            alertas_emergencia,
            resumo_semanal
        )
        VALUES (?, ?, 1, 1, 1, 0)
    ");

    if ($stmtInsert) {
        $stmtInsert->bind_param("ii", $idUsuario, $idEmpresa);
        $stmtInsert->execute();
    }

    return $padrao;
}

function salvarConfigNotificacoes($con, $idUsuario, $idEmpresa, $dados) {
    $novasSolicitacoes = !empty($dados['novas_solicitacoes']) ? 1 : 0;
    $aprovacoesPendentes = !empty($dados['aprovacoes_pendentes']) ? 1 : 0;
    $alertasEmergencia = !empty($dados['alertas_emergencia']) ? 1 : 0;
    $resumoSemanal = !empty($dados['resumo_semanal']) ? 1 : 0;

    $stmt = $con->prepare("
        INSERT INTO config_notificacoes (
            id_usuario,
            id_empresa,
            novas_solicitacoes,
            aprovacoes_pendentes,
            alertas_emergencia,
            resumo_semanal
        )
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            novas_solicitacoes = VALUES(novas_solicitacoes),
            aprovacoes_pendentes = VALUES(aprovacoes_pendentes),
            alertas_emergencia = VALUES(alertas_emergencia),
            resumo_semanal = VALUES(resumo_semanal)
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "iiiiii",
        $idUsuario,
        $idEmpresa,
        $novasSolicitacoes,
        $aprovacoesPendentes,
        $alertasEmergencia,
        $resumoSemanal
    );

    return $stmt->execute();
}

function criarNotificacao($con, $idEmpresa, $idUsuarioDestino, $tipo, $titulo, $mensagem, $link = null) {
    $stmt = $con->prepare("
        INSERT INTO notificacoes (
            id_empresa,
            id_usuario_destino,
            tipo,
            titulo,
            mensagem,
            link,
            lida
        )
        VALUES (?, ?, ?, ?, ?, ?, 0)
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "iissss",
        $idEmpresa,
        $idUsuarioDestino,
        $tipo,
        $titulo,
        $mensagem,
        $link
    );

    return $stmt->execute();
}

/*
    FUNÇÃO NORMAL:
    respeita as configurações da tela configuracao.php
*/
function criarNotificacaoParaRH($con, $idEmpresa, $tipoConfig, $titulo, $mensagem, $link = null, $tipo = 'sistema') {
    $stmt = $con->prepare("
        SELECT id_usuario
        FROM usuarios
        WHERE id_empresa = ?
        AND status = 'ativo'
        AND tipo IN ('rh', 'RH', 'admin', 'administrador')
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $idEmpresa);
    $stmt->execute();

    $rhs = $stmt->get_result();

    while ($rh = $rhs->fetch_assoc()) {
        $idRh = $rh['id_usuario'];

        $config = buscarConfigNotificacoes($con, $idRh, $idEmpresa);

        if (!empty($config[$tipoConfig])) {
            criarNotificacao(
                $con,
                $idEmpresa,
                $idRh,
                $tipo,
                $titulo,
                $mensagem,
                $link
            );
        }
    }

    return true;
}

/*
    FUNÇÃO FORÇADA:
    envia para RH/Admin mesmo se alguma configuração estiver desligada.
    Use para férias, atestado e emergência.
*/
function criarNotificacaoParaRHForcada($con, $idEmpresa, $titulo, $mensagem, $link = null, $tipo = 'sistema') {
    $stmt = $con->prepare("
        SELECT id_usuario
        FROM usuarios
        WHERE id_empresa = ?
        AND status = 'ativo'
        AND tipo IN ('rh', 'RH', 'admin', 'administrador')
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $idEmpresa);
    $stmt->execute();

    $resultado = $stmt->get_result();

    while ($usuario = $resultado->fetch_assoc()) {

        $idUsuarioDestino = $usuario['id_usuario'];

        criarNotificacao(
            $con,
            $idEmpresa,
            $idUsuarioDestino,
            $tipo,
            $titulo,
            $mensagem,
            $link
        );
    }

    return true;
}

function contarNotificacoesNaoLidas($con, $idUsuario, $idEmpresa) {
    $stmt = $con->prepare("
        SELECT COUNT(*) AS total
        FROM notificacoes
        WHERE id_usuario_destino = ?
        AND id_empresa = ?
        AND lida = 0
    ");

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("ii", $idUsuario, $idEmpresa);
    $stmt->execute();

    $resultado = $stmt->get_result()->fetch_assoc();

    return intval($resultado['total'] ?? 0);
}