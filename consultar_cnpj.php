<?php

header('Content-Type: application/json; charset=utf-8');

function responder($sucesso, $mensagem, $extras = []) {
    echo json_encode(array_merge([
        'sucesso' => $sucesso,
        'mensagem' => $mensagem
    ], $extras), JSON_UNESCAPED_UNICODE);
    exit;
}

$cnpj = $_GET['cnpj'] ?? '';
$cnpj = preg_replace('/[^0-9]/', '', $cnpj);

/* CNPJ FICTÍCIO PARA DEMONSTRAÇÃO DO TCC */
if ($cnpj === '12345678000195') {
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'CNPJ de demonstração aceito.',

        'razao_social' => 'MEU PONTO DIÁRIO DEMONSTRAÇÃO LTDA',
        'nome_fantasia' => 'Empresa Demonstração',
        'email' => 'demo@meupontodiario.com.br',
        'telefone' => '(11) 99999-0000',
        'endereco' => 'Avenida Tecnologia, 100 - Centro',
        'cidade' => 'São Paulo',
        'estado' => 'SP',
        'cep' => '01000000',
        'situacao' => 'Ativa'
    ]);
    exit;
}

function validarCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

    if (strlen($cnpj) != 14) return false;
    if (preg_match('/(\d)\1{13}/', $cnpj)) return false;

    for ($t = 12; $t < 14; $t++) {
        $soma = 0;
        $pos = $t - 7;

        for ($i = 0; $i < $t; $i++) {
            $soma += intval($cnpj[$i]) * $pos;
            $pos--;
            if ($pos < 2) $pos = 9;
        }

        $digito = ((10 * $soma) % 11) % 10;

        if (intval($cnpj[$t]) !== $digito) {
            return false;
        }
    }

    return true;
}

$cnpj = $_GET['cnpj'] ?? '';
$cnpj = preg_replace('/[^0-9]/', '', $cnpj);

if (!validarCNPJ($cnpj)) {
    responder(false, 'CNPJ inválido.');
}

$url = "https://publica.cnpj.ws/cnpj/" . $cnpj;

$contexto = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 15,
        'header' => "Accept: application/json\r\nUser-Agent: MeuPontoDiario/1.0\r\n"
    ]
]);

$resposta = @file_get_contents($url, false, $contexto);

if ($resposta === false) {
    responder(false, 'Não foi possível consultar o CNPJ. Verifique sua internet, SSL ou limite da API.');
}

$dados = json_decode($resposta, true);

if (!$dados) {
    responder(false, 'A API retornou uma resposta inválida.');
}

if (isset($dados['message'])) {
    responder(false, $dados['message']);
}

if (!isset($dados['razao_social'])) {
    responder(false, 'Empresa não encontrada.');
}

$est = $dados['estabelecimento'] ?? [];

$situacao = $est['situacao_cadastral'] ?? '';

if (strtoupper($situacao) !== 'ATIVA') {
    responder(false, 'Este CNPJ existe, mas não está ativo.');
}

$endereco = trim(
    ($est['tipo_logradouro'] ?? '') . ' ' .
    ($est['logradouro'] ?? '') . ', ' .
    ($est['numero'] ?? '') . ' - ' .
    ($est['bairro'] ?? '')
);

responder(true, 'CNPJ encontrado e ativo.', [
    'razao_social' => $dados['razao_social'] ?? '',
    'nome_fantasia' => $est['nome_fantasia'] ?? '',
    'email' => $est['email'] ?? '',
    'telefone' => $est['telefone1'] ?? '',
    'endereco' => $endereco,
    'cidade' => $est['cidade']['nome'] ?? '',
    'estado' => $est['estado']['sigla'] ?? '',
    'cep' => $est['cep'] ?? '',
    'situacao' => $situacao
]);