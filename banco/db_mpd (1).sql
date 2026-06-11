-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 11-Jun-2026 às 21:04
-- Versão do servidor: 5.7.36
-- versão do PHP: 8.1.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `db_mpd`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `assinaturas`
--

CREATE TABLE `assinaturas` (
  `id_assinatura` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `plano` enum('pequeno','medio') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` enum('pendente','ativo','cancelado') DEFAULT 'pendente',
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `atividades`
--

CREATE TABLE `atividades` (
  `id_atividade` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `tipo` enum('success','primary','warning','danger') DEFAULT 'primary',
  `data_atividade` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `atividades`
--

INSERT INTO `atividades` (`id_atividade`, `id_usuario`, `descricao`, `tipo`, `data_atividade`) VALUES
(1, 21, 'Enviou solicitação de licença médica', 'warning', '2026-06-09 15:54:00'),
(2, 16, 'Enviou solicitação de licença médica', 'warning', '2026-06-09 16:17:59'),
(3, 12, 'Aprovou uma solicitação de férias', 'success', '2026-06-09 16:45:17'),
(4, 12, 'Aprovou uma solicitação de férias', 'success', '2026-06-09 16:45:19'),
(5, 21, 'Registrou uma ocorrência de segurança', 'danger', '2026-06-09 16:46:13'),
(6, 21, 'Registrou uma ocorrência de segurança', 'danger', '2026-06-09 16:49:26'),
(7, 12, 'Rejeitou uma solicitação de férias', 'danger', '2026-06-09 17:22:24');

-- --------------------------------------------------------

--
-- Estrutura da tabela `banco_horas`
--

CREATE TABLE `banco_horas` (
  `id_banco` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `mes` varchar(7) NOT NULL,
  `saldo_total` decimal(6,2) DEFAULT '0.00',
  `saldo_mes` decimal(6,2) DEFAULT '0.00',
  `horas_extras_mes` decimal(6,2) DEFAULT '0.00',
  `horas_debito_mes` decimal(6,2) DEFAULT '0.00',
  `data_atualizacao` date DEFAULT NULL,
  `status` enum('positivo','negativo','neutro') DEFAULT 'neutro',
  `id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `banco_horas`
--

INSERT INTO `banco_horas` (`id_banco`, `id_funcionario`, `mes`, `saldo_total`, `saldo_mes`, `horas_extras_mes`, `horas_debito_mes`, `data_atualizacao`, `status`, `id_empresa`) VALUES
(11, 11, '2026-06', '0.00', '0.00', '0.00', '0.00', '2026-06-09', 'neutro', 4),
(12, 12, '2026-06', '0.00', '0.00', '0.00', '0.00', '2026-06-09', 'neutro', 4),
(13, 13, '2026-06', '0.00', '0.00', '0.00', '0.00', '2026-06-09', 'neutro', 4),
(14, 14, '2026-06', '0.00', '0.00', '0.00', '0.00', '2026-06-09', 'neutro', 4),
(15, 15, '2026-06', '0.00', '0.00', '0.00', '0.00', '2026-06-09', 'neutro', 4),
(16, 16, '2026-06', '0.00', '0.00', '0.00', '0.00', '2026-06-09', 'neutro', 4),
(17, 17, '2026-06', '0.00', '0.00', '0.00', '0.00', '2026-06-09', 'neutro', 4),
(18, 18, '2026-06', '0.00', '0.00', '0.00', '0.00', '2026-06-09', 'neutro', 4),
(19, 19, '2026-06', '0.00', '0.00', '0.00', '0.00', '2026-06-09', 'neutro', 4),
(20, 20, '2026-06', '0.00', '0.00', '0.00', '0.00', '2026-06-09', 'neutro', 4);

-- --------------------------------------------------------

--
-- Estrutura da tabela `banco_horas_movimentacao`
--

CREATE TABLE `banco_horas_movimentacao` (
  `id_mov` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `data` date NOT NULL,
  `tipo` enum('extra','debito') NOT NULL,
  `horas` decimal(5,2) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `id_empresa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `comunicados`
--

CREATE TABLE `comunicados` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `fixado` tinyint(1) DEFAULT '0',
  `autor` varchar(150) DEFAULT NULL,
  `publico` varchar(150) DEFAULT NULL,
  `data_publicacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_empresa` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `config_notificacoes`
--

CREATE TABLE `config_notificacoes` (
  `id_config` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `novas_solicitacoes` tinyint(1) NOT NULL DEFAULT '1',
  `aprovacoes_pendentes` tinyint(1) NOT NULL DEFAULT '1',
  `alertas_emergencia` tinyint(1) NOT NULL DEFAULT '1',
  `resumo_semanal` tinyint(1) NOT NULL DEFAULT '0',
  `data_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `config_notificacoes`
--

INSERT INTO `config_notificacoes` (`id_config`, `id_usuario`, `id_empresa`, `novas_solicitacoes`, `aprovacoes_pendentes`, `alertas_emergencia`, `resumo_semanal`, `data_atualizacao`) VALUES
(1, 21, 4, 1, 1, 1, 0, '2026-06-09 15:51:34'),
(3, 12, 4, 1, 1, 1, 0, '2026-06-09 17:22:00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `duvidas`
--

CREATE TABLE `duvidas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `duvida` text NOT NULL,
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `empresas`
--

CREATE TABLE `empresas` (
  `id_empresa` int(11) NOT NULL,
  `razao_social` varchar(255) NOT NULL,
  `nome_fantasia` varchar(255) NOT NULL,
  `cnpj` varchar(20) NOT NULL,
  `segmento` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `responsavel` varchar(150) DEFAULT NULL,
  `cargo_responsavel` varchar(100) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `cep` varchar(15) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `plano` enum('pequeno','medio') NOT NULL,
  `status` enum('ativa','inativa','teste') DEFAULT 'teste',
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `empresas`
--

INSERT INTO `empresas` (`id_empresa`, `razao_social`, `nome_fantasia`, `cnpj`, `segmento`, `email`, `telefone`, `responsavel`, `cargo_responsavel`, `endereco`, `cidade`, `estado`, `cep`, `logo`, `plano`, `status`, `data_cadastro`) VALUES
(4, 'MEU PONTO DIÁRIO DEMONSTRAÇÃO LTDA', 'Empresa Demonstração', '12345678000195', '', 'demo@meupontodiario.com.br', '(11) 99999-0000', 'ACGLL', 'Dono', 'Avenida Tecnologia, 100 - Centro', 'São Paulo', 'SP', '01000000', NULL, 'pequeno', 'ativa', '2026-06-09 02:52:57');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ferias`
--

CREATE TABLE `ferias` (
  `id_ferias` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `dias` int(11) NOT NULL,
  `data_solicitacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pendente','visto','aprovado','rejeitado') DEFAULT 'pendente',
  `data_visto` datetime DEFAULT NULL,
  `mensagem_colaborador` varchar(255) DEFAULT NULL,
  `alteracoes_restantes` int(11) DEFAULT '2',
  `motivo_rejeicao` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `ferias`
--

INSERT INTO `ferias` (`id_ferias`, `id_funcionario`, `id_empresa`, `data_inicio`, `data_fim`, `dias`, `data_solicitacao`, `status`, `data_visto`, `mensagem_colaborador`, `alteracoes_restantes`, `motivo_rejeicao`) VALUES
(1, 19, 4, '2027-05-01', '2027-05-30', 30, '2026-06-09 15:48:42', 'aprovado', '2026-06-09 16:45:19', 'Sua solicitação de férias foi aprovada pelo RH.', 0, NULL),
(2, 14, 4, '2026-07-01', '2026-07-30', 30, '2026-06-09 16:17:45', 'aprovado', '2026-06-09 16:45:17', 'Sua solicitação de férias foi aprovada pelo RH.', 1, NULL),
(3, 19, 4, '2027-03-01', '2027-03-30', 30, '2026-06-09 17:06:37', 'rejeitado', '2026-06-09 17:22:24', 'Sua solicitação de férias foi rejeitada pelo RH.', 2, 'ja foi aprovado um pedido anteriormente');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ferias_meses_disponiveis`
--

CREATE TABLE `ferias_meses_disponiveis` (
  `id` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `mes` tinyint(4) NOT NULL,
  `disponivel` tinyint(4) NOT NULL DEFAULT '1',
  `limite_pedidos` int(11) NOT NULL DEFAULT '0',
  `atualizado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `ferias_meses_disponiveis`
--

INSERT INTO `ferias_meses_disponiveis` (`id`, `id_empresa`, `mes`, `disponivel`, `limite_pedidos`, `atualizado_em`) VALUES
(1, 1, 1, 1, 0, '2026-06-08 19:33:18'),
(2, 1, 2, 1, 0, '2026-06-08 19:33:18'),
(3, 1, 3, 1, 0, '2026-06-08 19:33:18'),
(4, 1, 4, 1, 0, '2026-06-08 19:33:18'),
(5, 1, 5, 1, 0, '2026-06-08 19:33:18'),
(6, 1, 6, 1, 0, '2026-06-08 19:33:18'),
(7, 1, 7, 1, 0, '2026-06-08 19:33:18'),
(8, 1, 8, 1, 0, '2026-06-08 19:33:18'),
(9, 1, 9, 1, 0, '2026-06-08 19:33:18'),
(10, 1, 10, 1, 0, '2026-06-08 19:33:18'),
(11, 1, 11, 1, 0, '2026-06-08 19:33:18'),
(12, 1, 12, 1, 0, '2026-06-08 19:33:18'),
(193, 4, 1, 1, 0, '2026-06-09 00:04:24'),
(194, 4, 2, 1, 0, '2026-06-09 00:04:24'),
(195, 4, 3, 1, 0, '2026-06-09 00:04:24'),
(196, 4, 4, 1, 0, '2026-06-09 00:04:24'),
(197, 4, 5, 1, 0, '2026-06-09 00:04:24'),
(198, 4, 6, 1, 0, '2026-06-09 00:04:24'),
(199, 4, 7, 1, 0, '2026-06-09 00:04:24'),
(200, 4, 8, 1, 0, '2026-06-09 00:04:24'),
(201, 4, 9, 1, 0, '2026-06-09 00:04:24'),
(202, 4, 10, 1, 0, '2026-06-09 00:04:24'),
(203, 4, 11, 1, 0, '2026-06-09 00:04:24'),
(204, 4, 12, 1, 0, '2026-06-09 00:04:24');

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcionarios`
--

CREATE TABLE `funcionarios` (
  `id_funcionario` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `horario_padrao` time DEFAULT '09:00:00',
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_empresa` int(11) NOT NULL,
  `escala` varchar(50) DEFAULT NULL,
  `supervisor` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `funcionarios`
--

INSERT INTO `funcionarios` (`id_funcionario`, `nome`, `cargo`, `departamento`, `horario_padrao`, `ativo`, `created_at`, `id_empresa`, `escala`, `supervisor`) VALUES
(11, 'João Silva', 'Analista de TI', 'TI', '08:00:00', 1, '2026-06-09 03:05:12', 4, '5x2', 'Carlos Mendes'),
(12, 'Maria Souza', 'Auxiliar RH', 'RH', '08:00:00', 1, '2026-06-09 03:05:12', 4, '5x2', 'Ana Costa'),
(13, 'Pedro Santos', 'Desenvolvedor Full Stack', 'TI', '08:00:00', 1, '2026-06-09 03:05:12', 4, '12x36', 'Carlos Mendes'),
(14, 'Ana Oliveira', 'Analista Financeiro', 'Financeiro', '09:00:00', 1, '2026-06-09 03:05:12', 4, '5x2', 'Roberto Lima'),
(15, 'Lucas Pereira', 'Analista de Marketing', 'Marketing', '08:30:00', 1, '2026-06-09 03:05:12', 4, '5x2', 'Mariana Rocha'),
(16, 'Fernanda Costa', 'Designer UX/UI', 'Design', '09:00:00', 1, '2026-06-09 03:05:12', 4, 'Home Office', 'Carlos Mendes'),
(17, 'Ricardo Lima', 'Supervisor de Produção', 'Produção', '07:00:00', 1, '2026-06-09 03:05:12', 4, '6x1', 'Diretoria'),
(18, 'Juliana Alves', 'Analista Comercial', 'Comercial', '08:00:00', 1, '2026-06-09 03:05:12', 4, '5x2', 'Patrícia Gomes'),
(19, 'Bruno Martins', 'Técnico de Suporte', 'TI', '08:00:00', 1, '2026-06-09 03:05:12', 4, '12x36', 'Carlos Mendes'),
(20, 'Carla Rocha', 'Coordenadora RH', 'RH', '08:00:00', 1, '2026-06-09 03:05:12', 4, '5x2', 'Diretoria');

-- --------------------------------------------------------

--
-- Estrutura da tabela `holerites`
--

CREATE TABLE `holerites` (
  `id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `arquivo` varchar(255) DEFAULT NULL,
  `periodo` varchar(20) NOT NULL,
  `data_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pendente','enviado') DEFAULT 'pendente',
  `id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `licencas_medicas`
--

CREATE TABLE `licencas_medicas` (
  `id` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `arquivo_atestado` varchar(255) NOT NULL,
  `tipo_arquivo` varchar(20) DEFAULT NULL,
  `motivo` varchar(150) DEFAULT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `dias` int(11) DEFAULT NULL,
  `observacao` varchar(255) DEFAULT NULL,
  `data_envio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_empresa` int(11) NOT NULL,
  `status` varchar(30) DEFAULT 'pendente',
  `data_visto` datetime DEFAULT NULL,
  `mensagem_colaborador` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `licencas_medicas`
--

INSERT INTO `licencas_medicas` (`id`, `id_funcionario`, `arquivo_atestado`, `tipo_arquivo`, `motivo`, `data_inicio`, `data_fim`, `dias`, `observacao`, `data_envio`, `id_empresa`, `status`, `data_visto`, `mensagem_colaborador`) VALUES
(1, 19, 'uploads/licencas/licenca_6a28614867b88.jpg', 'jpg', 'Gripe', '2026-06-09', '2026-06-12', 4, '', '2026-06-09 18:54:00', 4, 'visto', '2026-06-09 16:45:06', 'Sua licença médica foi visualizada pelo RH.'),
(2, 14, 'uploads/licencas/licenca_6a2866e7dd8f0.jpg', 'jpg', 'dor de cabeça', '2026-06-09', '2026-06-10', 2, '', '2026-06-09 19:17:59', 4, 'visto', '2026-06-09 16:45:04', 'Sua licença médica foi visualizada pelo RH.');

-- --------------------------------------------------------

--
-- Estrutura da tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id_notificacao` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_usuario_destino` int(11) NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sistema',
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lida` tinyint(1) NOT NULL DEFAULT '0',
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `notificacoes`
--

INSERT INTO `notificacoes` (`id_notificacao`, `id_empresa`, `id_usuario_destino`, `tipo`, `titulo`, `mensagem`, `link`, `lida`, `data_criacao`) VALUES
(1, 1, 1, 'sistema', 'Teste de notificação', 'Essa é uma notificação de teste aparecendo no sininho.', 'configuracoes.php', 0, '2026-06-09 15:50:22'),
(2, 1, 1, 'solicitacao', 'Nova solicitação', 'Teste de solicitação.', 'funcionarios.php', 0, '2026-06-09 15:52:26'),
(3, 1, 1, 'emergencia', 'Alerta de emergência', 'Teste de emergência.', 'configuracoes.php', 0, '2026-06-09 15:52:26'),
(4, 1, 1, 'resumo', 'Resumo semanal', 'Teste de resumo semanal.', 'perfil.php', 0, '2026-06-09 15:52:26'),
(5, 4, 12, 'solicitacao', 'Novo atestado enviado', 'Ana Oliveira enviou um novo atestado médico.', 'licenca.php', 1, '2026-06-09 16:17:59'),
(6, 4, 12, 'solicitacao', 'Solicitação de férias alterada', 'Ana Oliveira alterou uma solicitação de férias para Julho.', 'ferias.php', 1, '2026-06-09 16:22:28'),
(7, 4, 12, 'emergencia', 'Nova ocorrência registrada', 'Uma nova ocorrência de segurança foi registrada: Agressão', 'emergencias.php', 1, '2026-06-09 16:31:01'),
(8, 4, 21, 'emergencia', 'Status da sua ocorrência atualizado', 'Sua ocorrência de categoria \"Agressão\" foi atualizada para: Em análise.', 'seguranca.php', 0, '2026-06-09 16:44:34'),
(9, 4, 16, 'emergencia', 'Status da sua ocorrência atualizado', 'Sua ocorrência de categoria \"Outro\" foi atualizada para: Resolvida.', 'seguranca.php', 0, '2026-06-09 16:44:41'),
(10, 4, 12, 'emergencia', 'Nova ocorrência registrada', 'Uma nova ocorrência de segurança foi registrada na categoria: Agressão.', 'emergencias.php', 0, '2026-06-09 16:46:13'),
(11, 4, 21, 'emergencia', 'Status da sua ocorrência atualizado', 'Sua ocorrência de categoria \"Agressão\" foi atualizada para: Resolvida.', 'seguranca.php', 0, '2026-06-09 16:46:35'),
(12, 4, 12, 'emergencia', 'Nova ocorrência registrada', 'Uma nova ocorrência de segurança foi registrada na categoria: Assédio.', 'emergencias.php', 0, '2026-06-09 16:49:26'),
(13, 4, 21, 'emergencia', 'Status da sua ocorrência atualizado', 'Sua ocorrência de categoria \"Assédio\" foi atualizada para: Resolvida.', 'seguranca.php', 0, '2026-06-09 16:49:40'),
(14, 4, 12, 'solicitacao', 'Nova solicitação de férias', 'Bruno Martins enviou uma nova solicitação de férias para Março.', 'ferias.php', 0, '2026-06-09 17:06:37');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ocorrencias`
--

CREATE TABLE `ocorrencias` (
  `id_ocorrencia` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `tipo_reporte` varchar(30) DEFAULT NULL,
  `nome` varchar(150) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `andar` varchar(50) DEFAULT NULL,
  `sala` varchar(100) DEFAULT NULL,
  `local_especifico` varchar(255) DEFAULT NULL,
  `descricao` text,
  `testemunhas` varchar(255) DEFAULT NULL,
  `evidencia` varchar(255) DEFAULT NULL,
  `status` enum('aberta','em_analise','resolvida') DEFAULT 'aberta',
  `data_ocorrencia` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `ocorrencias`
--

INSERT INTO `ocorrencias` (`id_ocorrencia`, `id_empresa`, `id_usuario`, `tipo_reporte`, `nome`, `categoria`, `andar`, `sala`, `local_especifico`, `descricao`, `testemunhas`, `evidencia`, `status`, `data_ocorrencia`) VALUES
(1, 4, 21, 'Identificado', 'Bruno Martins', 'Equipamento danificado', '2º Andar', 'Sala 4', 'lab 4', 'computador quebrado', '', 'uploads/ocorrencias/ocorrencia_6a28648f5ec3b4.24458022.jpg', 'aberta', '2026-06-09 16:07:59'),
(2, 4, 16, 'Identificado', 'Ana Oliveira', 'Outro', '1º Andar', 'Sala 2', 'banheiro', 'pia quebrada', 'carlos antonio e guilherme alves', 'uploads/ocorrencias/ocorrencia_6a286836208005.91593107.webp', 'resolvida', '2026-06-09 16:23:34'),
(3, 4, 21, 'Anônimo', 'Anônimo', 'Agressão', 'Térreo', '', 'entrada', 'guilherme e carlos se pegando na porrada', '', 'uploads/ocorrencias/ocorrencia_6a2869f5a085d0.13672972.jpg', 'em_analise', '2026-06-09 16:31:01'),
(4, 4, 21, 'Anônimo', 'Anônimo', 'Agressão', '1º Andar', 'adsa', '', 'asdasd', 'adasda', NULL, 'resolvida', '2026-06-09 16:46:13'),
(5, 4, 21, 'Anônimo', 'Anônimo', 'Assédio', 'Térreo', 'adads', 'asdsad', 'asdsad', '', NULL, 'resolvida', '2026-06-09 16:49:26');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pontos`
--

CREATE TABLE `pontos` (
  `id_ponto` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `data` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_saida` time DEFAULT NULL,
  `total_horas` decimal(5,2) DEFAULT NULL,
  `status` enum('completo','atraso','em andamento','ausente') DEFAULT 'em andamento',
  `justificativa` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_empresa` int(11) NOT NULL,
  `saida_almoco` time DEFAULT NULL,
  `retorno_almoco` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_funcionario` int(11) DEFAULT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('empresa','rh','funcionario') NOT NULL DEFAULT 'funcionario',
  `status` enum('ativo','inativo','ferias','licenca','afastado') DEFAULT 'ativo',
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `telefone` varchar(20) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_funcionario`, `nome`, `email`, `senha`, `tipo`, `status`, `ultimo_login`, `created_at`, `telefone`, `cidade`, `foto`, `cargo`, `departamento`, `id_empresa`) VALUES
(12, NULL, 'ACGLL', 'demo@meupontodiario.com.br', '$2y$10$eFyK7aB2SteZUMVlYsjUkuWK3YOYGU7pSnbXIXDhbZtsko7NsCCe.', 'rh', 'ativo', '2026-06-09 18:16:10', '2026-06-09 02:52:57', '(11) 99999-0000', 'São Paulo', NULL, 'Dono', NULL, 4),
(13, 11, 'João Silva', 'joao@empresa.com', '$2y$10$Pop9sVCvE4Yx6sWqppufXOCFt.QPkXl2/vpa36a0rfmA4fpQ/MNN6', 'funcionario', 'ativo', NULL, '2026-06-09 03:05:12', '11999991111', 'São Paulo', NULL, 'Analista de TI', 'TI', 4),
(14, 12, 'Maria Souza', 'maria@empresa.com', '$2y$10$lDY2XUzi8RVvYw1xCFzLeuSJ/ZFkhM.LyttKcwFlM4ItAJI3N7R7u', 'funcionario', 'ferias', NULL, '2026-06-09 03:05:12', '11999992222', 'Campinas', NULL, 'Auxiliar RH', 'RH', 4),
(15, 13, 'Pedro Santos', 'pedro@empresa.com', '$2y$10$gZkgW/PaFSOFxT5ixlqfr.vVozjXElNK7bRCXudmqekKWNxWdiHLO', 'funcionario', 'licenca', NULL, '2026-06-09 03:05:12', '11999993333', 'Santos', NULL, 'Desenvolvedor Full Stack', 'TI', 4),
(16, 14, 'Ana Oliveira', 'ana@empresa.com', '$2y$10$npTxMYq8a7b1ajLtGnkaGuebYJHUB2My2NQR6.Sv9GoqfD5lDahbS', 'funcionario', 'ativo', '2026-06-09 16:22:00', '2026-06-09 03:05:12', '11999994444', 'São Paulo', NULL, 'Analista Financeiro', 'Financeiro', 4),
(17, 15, 'Lucas Pereira', 'lucas@empresa.com', '$2y$10$VC/FGORnRiL9dp5Ry087OudYMcooq/4Yjnwp8vUvg8NshJSNja1US', 'funcionario', 'afastado', NULL, '2026-06-09 03:05:12', '11999995555', 'Guarulhos', NULL, 'Analista de Marketing', 'Marketing', 4),
(18, 16, 'Fernanda Costa', 'fernanda@empresa.com', '$2y$10$ytnuA8ZT7s8WgEozrruBEO/Bu.CnNeKOg6d3OeDaAjTvKKIj/o5Vy', 'funcionario', 'ativo', NULL, '2026-06-09 03:05:12', '11999996666', 'Osasco', NULL, 'Designer UX/UI', 'Design', 4),
(19, 17, 'Ricardo Lima', 'ricardo@empresa.com', '$2y$10$K9aExqxZenvKLfVfj7N8FOAqeJJFlET0gZU43dDK0VDxBEliCBFSC', 'funcionario', 'ativo', NULL, '2026-06-09 03:05:12', '11999997777', 'Sorocaba', NULL, 'Supervisor de Produção', 'Produção', 4),
(20, 18, 'Juliana Alves', 'juliana@empresa.com', '$2y$10$rA77xAwSJp7A39/oZ8rVp.vVAKAv2XeD/Jf.mXM7RPROmz2t116FS', 'funcionario', 'ferias', NULL, '2026-06-09 03:05:12', '11999998888', 'São Paulo', NULL, 'Analista Comercial', 'Comercial', 4),
(21, 19, 'Bruno Martins', 'bruno@empresa.com', '$2y$10$FI84T4iaHtpPdZPkA79ueO3.Yb2i5gcbjHFd7ZE7KWGIEdRXY80HC', 'funcionario', 'ativo', '2026-06-09 18:12:11', '2026-06-09 03:05:12', '11999999999', 'São Bernardo', NULL, 'Técnico de Suporte', 'TI', 4),
(22, 20, 'Carla Rocha', 'carla@empresa.com', '$2y$10$iYiNGRoSO6VyfVGVwQeq.eAGS5J9vtN29s7hOgIRyPPq5XJnrIMSS', 'funcionario', 'ativo', NULL, '2026-06-09 03:05:12', '11988880000', 'São Paulo', NULL, 'Coordenadora RH', 'RH', 4);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  ADD PRIMARY KEY (`id_assinatura`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Índices para tabela `atividades`
--
ALTER TABLE `atividades`
  ADD PRIMARY KEY (`id_atividade`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices para tabela `banco_horas`
--
ALTER TABLE `banco_horas`
  ADD PRIMARY KEY (`id_banco`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Índices para tabela `banco_horas_movimentacao`
--
ALTER TABLE `banco_horas_movimentacao`
  ADD PRIMARY KEY (`id_mov`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Índices para tabela `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `config_notificacoes`
--
ALTER TABLE `config_notificacoes`
  ADD PRIMARY KEY (`id_config`),
  ADD UNIQUE KEY `unico_usuario_empresa` (`id_usuario`,`id_empresa`);

--
-- Índices para tabela `duvidas`
--
ALTER TABLE `duvidas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `cnpj` (`cnpj`);

--
-- Índices para tabela `ferias`
--
ALTER TABLE `ferias`
  ADD PRIMARY KEY (`id_ferias`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Índices para tabela `ferias_meses_disponiveis`
--
ALTER TABLE `ferias_meses_disponiveis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `empresa_mes` (`id_empresa`,`mes`);

--
-- Índices para tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id_funcionario`);

--
-- Índices para tabela `holerites`
--
ALTER TABLE `holerites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `funcionario_id` (`funcionario_id`);

--
-- Índices para tabela `licencas_medicas`
--
ALTER TABLE `licencas_medicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Índices para tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id_notificacao`),
  ADD KEY `idx_usuario_empresa` (`id_usuario_destino`,`id_empresa`),
  ADD KEY `idx_lida` (`lida`);

--
-- Índices para tabela `ocorrencias`
--
ALTER TABLE `ocorrencias`
  ADD PRIMARY KEY (`id_ocorrencia`);

--
-- Índices para tabela `pontos`
--
ALTER TABLE `pontos`
  ADD PRIMARY KEY (`id_ponto`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  MODIFY `id_assinatura` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `atividades`
--
ALTER TABLE `atividades`
  MODIFY `id_atividade` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `banco_horas`
--
ALTER TABLE `banco_horas`
  MODIFY `id_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `banco_horas_movimentacao`
--
ALTER TABLE `banco_horas_movimentacao`
  MODIFY `id_mov` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `config_notificacoes`
--
ALTER TABLE `config_notificacoes`
  MODIFY `id_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `duvidas`
--
ALTER TABLE `duvidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `ferias`
--
ALTER TABLE `ferias`
  MODIFY `id_ferias` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `ferias_meses_disponiveis`
--
ALTER TABLE `ferias_meses_disponiveis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `holerites`
--
ALTER TABLE `holerites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `licencas_medicas`
--
ALTER TABLE `licencas_medicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id_notificacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `ocorrencias`
--
ALTER TABLE `ocorrencias`
  MODIFY `id_ocorrencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `pontos`
--
ALTER TABLE `pontos`
  MODIFY `id_ponto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  ADD CONSTRAINT `assinaturas_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`);

--
-- Limitadores para a tabela `atividades`
--
ALTER TABLE `atividades`
  ADD CONSTRAINT `atividades_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `banco_horas`
--
ALTER TABLE `banco_horas`
  ADD CONSTRAINT `banco_horas_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `banco_horas_movimentacao`
--
ALTER TABLE `banco_horas_movimentacao`
  ADD CONSTRAINT `banco_horas_movimentacao_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `ferias`
--
ALTER TABLE `ferias`
  ADD CONSTRAINT `ferias_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `holerites`
--
ALTER TABLE `holerites`
  ADD CONSTRAINT `holerites_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id_funcionario`);

--
-- Limitadores para a tabela `licencas_medicas`
--
ALTER TABLE `licencas_medicas`
  ADD CONSTRAINT `licencas_medicas_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `pontos`
--
ALTER TABLE `pontos`
  ADD CONSTRAINT `pontos_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
