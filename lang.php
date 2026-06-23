<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['idioma'])) {
    $_SESSION['idioma'] = $_POST['idioma'];
}

$idioma = $_SESSION['idioma'] ?? 'pt-BR';

$txt = [

    'pt-BR' => [
        'gestao' => 'Gestão',
        'administracao' => 'Administração',
        'minha_area' => 'Minha área',
        'colaborador' => 'Colaborador',

        'ponto' => 'Ponto',
        'banco_horas' => 'Banco de horas',
        'ferias' => 'Férias',
        'holerite' => 'Holerite',
        'licencas' => 'Licenças médicas',
        'emergencias' => 'Emergências',
        'comunicados' => 'Comunicados',
        'funcionarios' => 'Funcionários',

        'importar_banco' => 'Importar banco',
        'importar_pontos' => 'Importar pontos',
        'importar_funcionarios' => 'Importar funcionários',
        'cadastrar_usuario' => 'Cadastrar usuário',

        'historico_ponto' => 'Histórico de ponto',
        'pedidos' => 'Pedidos',
        'solicitacoes' => 'Solicitações e licenças',

        'perfil' => 'Meu perfil',
        'configuracoes' => 'Configurações',
        'seguranca' => 'Segurança',
        'sair' => 'Sair',

        'salvar' => 'Salvar Alterações',
        'cancelar' => 'Cancelar',
        'idioma' => 'Idioma',

        'confirmar_sair' => 'Deseja realmente sair?'
    ],

    'en' => [
        'gestao' => 'Management',
        'administracao' => 'Administration',
        'minha_area' => 'My area',
        'colaborador' => 'Employee',

        'ponto' => 'Time Clock',
        'banco_horas' => 'Hour Bank',
        'ferias' => 'Vacations',
        'holerite' => 'Payslip',
        'licencas' => 'Medical Leaves',
        'emergencias' => 'Emergencies',
        'comunicados' => 'Announcements',
        'funcionarios' => 'Employees',

        'importar_banco' => 'Import hour bank',
        'importar_pontos' => 'Import time records',
        'importar_funcionarios' => 'Import employees',
        'cadastrar_usuario' => 'Create user',

        'historico_ponto' => 'Time history',
        'pedidos' => 'Requests',
        'solicitacoes' => 'Requests and leaves',

        'perfil' => 'My profile',
        'configuracoes' => 'Settings',
        'seguranca' => 'Security',
        'sair' => 'Logout',

        'salvar' => 'Save Changes',
        'cancelar' => 'Cancel',
        'idioma' => 'Language',

        'confirmar_sair' => 'Do you really want to logout?'
    ]

];

function t($key) {
    global $txt, $idioma;

    return $txt[$idioma][$key] ?? $key;
}