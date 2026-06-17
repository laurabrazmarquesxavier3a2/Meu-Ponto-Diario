-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/06/2026 às 03:29
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

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
-- Estrutura para tabela `assinaturas`
--

CREATE TABLE `assinaturas` (
  `id_assinatura` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `plano` enum('pequeno','medio') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` enum('pendente','ativo','cancelado') DEFAULT 'pendente',
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `atividades`
--

CREATE TABLE `atividades` (
  `id_atividade` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `tipo` enum('success','primary','warning','danger') DEFAULT 'primary',
  `data_atividade` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `atividades`
--

INSERT INTO `atividades` (`id_atividade`, `id_usuario`, `descricao`, `tipo`, `data_atividade`) VALUES
(1, 21, 'Enviou solicitação de licença médica', 'warning', '2026-06-09 15:54:00'),
(2, 16, 'Enviou solicitação de licença médica', 'warning', '2026-06-09 16:17:59'),
(3, 12, 'Aprovou uma solicitação de férias', 'success', '2026-06-09 16:45:17'),
(4, 12, 'Aprovou uma solicitação de férias', 'success', '2026-06-09 16:45:19'),
(5, 21, 'Registrou uma ocorrência de segurança', 'danger', '2026-06-09 16:46:13'),
(6, 21, 'Registrou uma ocorrência de segurança', 'danger', '2026-06-09 16:49:26'),
(7, 12, 'Rejeitou uma solicitação de férias', 'danger', '2026-06-09 17:22:24'),
(8, 23, 'Bloqueou solicitações de férias para Janeiro', 'warning', '2026-06-11 21:50:53'),
(9, 23, 'Liberou solicitações de férias para Janeiro', 'success', '2026-06-11 21:51:02'),
(10, 23, 'Liberou solicitações de férias para Fevereiro', 'success', '2026-06-11 21:51:06'),
(11, 23, 'Liberou solicitações de férias para Março', 'success', '2026-06-11 21:51:07'),
(12, 23, 'Liberou solicitações de férias para Abril', 'success', '2026-06-11 21:51:10'),
(13, 23, 'Liberou solicitações de férias para Abril', 'success', '2026-06-11 21:56:38'),
(14, 23, 'Liberou solicitações de férias para Fevereiro', 'success', '2026-06-11 21:56:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `banco_horas`
--

CREATE TABLE `banco_horas` (
  `id_banco` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `mes` varchar(7) NOT NULL,
  `saldo_total` decimal(6,2) DEFAULT 0.00,
  `saldo_mes` decimal(6,2) DEFAULT 0.00,
  `horas_extras_mes` decimal(6,2) DEFAULT 0.00,
  `horas_debito_mes` decimal(6,2) DEFAULT 0.00,
  `data_atualizacao` date DEFAULT NULL,
  `status` enum('positivo','negativo','neutro') DEFAULT 'neutro',
  `id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `banco_horas`
--

INSERT INTO `banco_horas` (`id_banco`, `id_funcionario`, `mes`, `saldo_total`, `saldo_mes`, `horas_extras_mes`, `horas_debito_mes`, `data_atualizacao`, `status`, `id_empresa`) VALUES
(11, 11, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-09', 'neutro', 4),
(12, 12, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-09', 'neutro', 4),
(13, 13, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-09', 'neutro', 4),
(14, 14, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-09', 'neutro', 4),
(15, 15, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-09', 'neutro', 4),
(16, 16, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-09', 'neutro', 4),
(17, 17, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-09', 'neutro', 4),
(18, 18, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-09', 'neutro', 4),
(19, 19, '2026-06', 1.29, 1.29, 1.34, 0.05, '2026-06-16', 'positivo', 4),
(20, 20, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-09', 'neutro', 4),
(21, 21, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(22, 22, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(23, 23, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(24, 24, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(25, 25, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(26, 26, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(27, 27, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(28, 28, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(29, 29, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(30, 30, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(31, 31, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(32, 32, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(33, 33, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(34, 34, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(35, 35, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(36, 36, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(37, 37, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(38, 38, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(39, 39, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(40, 40, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(41, 41, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(42, 42, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(43, 43, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(44, 44, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(45, 45, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(46, 46, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(47, 47, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(48, 48, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(49, 49, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5),
(50, 50, '2026-06', 0.00, 0.00, 0.00, 0.00, '2026-06-11', 'neutro', 5);

-- --------------------------------------------------------

--
-- Estrutura para tabela `banco_horas_movimentacao`
--

CREATE TABLE `banco_horas_movimentacao` (
  `id_mov` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `data` date NOT NULL,
  `tipo` enum('extra','debito') NOT NULL,
  `horas` decimal(5,2) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `id_empresa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comunicados`
--

CREATE TABLE `comunicados` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `fixado` tinyint(1) DEFAULT 0,
  `autor` varchar(150) DEFAULT NULL,
  `publico` varchar(150) DEFAULT NULL,
  `data_publicacao` datetime DEFAULT current_timestamp(),
  `id_empresa` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `comunicados`
--

INSERT INTO `comunicados` (`id`, `titulo`, `conteudo`, `categoria`, `fixado`, `autor`, `publico`, `data_publicacao`, `id_empresa`) VALUES
(1, 'Atenção!', 'Banheiro do segundo andar interditado, pedimos para que não entrem até segunda ordem para sua segurança e bem estar.', 'Aviso', 1, 'Mariana Alves', 'Todos', '2026-06-11 22:01:24', 5),
(2, 'Atualização da política de férias', 'Informamos que a solicitação de férias deve ser realizada com antecedência mínima de 30 dias. Em caso de dúvidas, entre em contato com o RH.', 'Política', 1, 'Mariana Alves', 'Funcionários', '2026-06-11 22:15:36', 5),
(3, 'Café especial da equipe', 'Nesta sexta-feira teremos um café especial para promover a integração entre os colaboradores. A participação de todos é muito bem-vinda.', 'Comemoração', 0, 'Mariana Alves', 'Funcionários', '2026-06-11 22:16:41', 5),
(4, 'Aniversariantes do mês', 'Vamos comemorar os aniversariantes do mês com um momento especial de confraternização. Contamos com a presença de todos para celebrar junto com a equipe.', 'Comemoração', 0, 'Mariana Alves', 'Todos', '2026-06-11 22:17:20', 5),
(6, 'Festa junina da empresa', 'A empresa realizará uma festa junina para os colaboradores, com comidas típicas, música e atividades de integração. Em breve divulgaremos mais detalhes sobre data e horário.', 'Evento', 0, 'Mariana Alves', 'Todos', '2026-06-11 22:18:55', 5);

-- --------------------------------------------------------

--
-- Estrutura para tabela `config_notificacoes`
--

CREATE TABLE `config_notificacoes` (
  `id_config` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `novas_solicitacoes` tinyint(1) NOT NULL DEFAULT 1,
  `aprovacoes_pendentes` tinyint(1) NOT NULL DEFAULT 1,
  `alertas_emergencia` tinyint(1) NOT NULL DEFAULT 1,
  `resumo_semanal` tinyint(1) NOT NULL DEFAULT 0,
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `config_notificacoes`
--

INSERT INTO `config_notificacoes` (`id_config`, `id_usuario`, `id_empresa`, `novas_solicitacoes`, `aprovacoes_pendentes`, `alertas_emergencia`, `resumo_semanal`, `data_atualizacao`) VALUES
(1, 21, 4, 1, 1, 1, 0, '2026-06-09 15:51:34'),
(3, 12, 4, 1, 1, 1, 0, '2026-06-09 17:22:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `duvidas`
--

CREATE TABLE `duvidas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `duvida` text NOT NULL,
  `data_envio` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresas`
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
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresas`
--

INSERT INTO `empresas` (`id_empresa`, `razao_social`, `nome_fantasia`, `cnpj`, `segmento`, `email`, `telefone`, `responsavel`, `cargo_responsavel`, `endereco`, `cidade`, `estado`, `cep`, `logo`, `plano`, `status`, `data_cadastro`) VALUES
(4, 'MEU PONTO DIÁRIO DEMONSTRAÇÃO LTDA', 'Empresa Demonstração', '12345678000195', '', 'demo@meupontodiario.com.br', '(11) 99999-0000', 'ACGLL', 'Dono', 'Avenida Tecnologia, 100 - Centro', 'São Paulo', 'SP', '01000000', NULL, 'pequeno', 'ativa', '2026-06-09 02:52:57'),
(5, 'TechNova Sistemas Corporativos LTDA', 'TechNova Sistemas', '74291836000129', 'Tecnologia', 'Alves@technova.com.br', '(11) 97777-2020', 'Mariana Alves', 'Gerente de RH', 'Avenida Paulista, 1500 - Bela Vista', 'São Paulo', 'SP', '01310-200', NULL, 'pequeno', 'ativa', '2026-06-11 23:26:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ferias`
--

CREATE TABLE `ferias` (
  `id_ferias` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `dias` int(11) NOT NULL,
  `data_solicitacao` datetime DEFAULT current_timestamp(),
  `status` enum('pendente','visto','aprovado','rejeitado') DEFAULT 'pendente',
  `data_visto` datetime DEFAULT NULL,
  `mensagem_colaborador` varchar(255) DEFAULT NULL,
  `alteracoes_restantes` int(11) DEFAULT 2,
  `motivo_rejeicao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ferias`
--

INSERT INTO `ferias` (`id_ferias`, `id_funcionario`, `id_empresa`, `data_inicio`, `data_fim`, `dias`, `data_solicitacao`, `status`, `data_visto`, `mensagem_colaborador`, `alteracoes_restantes`, `motivo_rejeicao`) VALUES
(1, 19, 4, '2027-05-01', '2027-05-30', 30, '2026-06-09 15:48:42', 'aprovado', '2026-06-09 16:45:19', 'Sua solicitação de férias foi aprovada pelo RH.', 0, NULL),
(2, 14, 4, '2026-07-01', '2026-07-30', 30, '2026-06-09 16:17:45', 'aprovado', '2026-06-09 16:45:17', 'Sua solicitação de férias foi aprovada pelo RH.', 1, NULL),
(3, 19, 4, '2027-03-01', '2027-03-30', 30, '2026-06-09 17:06:37', 'rejeitado', '2026-06-09 17:22:24', 'Sua solicitação de férias foi rejeitada pelo RH.', 2, 'ja foi aprovado um pedido anteriormente'),
(4, 19, 4, '2026-07-01', '2026-07-30', 30, '2026-06-15 21:47:36', 'pendente', NULL, 'Solicitação alterada. Aguardando visualização do RH.', 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `ferias_meses_disponiveis`
--

CREATE TABLE `ferias_meses_disponiveis` (
  `id` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `mes` tinyint(4) NOT NULL,
  `disponivel` tinyint(4) NOT NULL DEFAULT 1,
  `limite_pedidos` int(11) DEFAULT NULL,
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `ferias_meses_disponiveis`
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
(204, 4, 12, 1, 0, '2026-06-09 00:04:24'),
(205, 5, 1, 1, NULL, '2026-06-11 21:51:02'),
(206, 5, 2, 1, NULL, '2026-06-11 21:56:48'),
(207, 5, 3, 1, NULL, '2026-06-11 21:51:07'),
(208, 5, 4, 1, NULL, '2026-06-11 21:56:38'),
(209, 5, 5, 1, 0, '2026-06-11 20:52:55'),
(210, 5, 6, 1, 0, '2026-06-11 20:52:55'),
(211, 5, 7, 1, 0, '2026-06-11 20:52:55'),
(212, 5, 8, 1, 0, '2026-06-11 20:52:55'),
(213, 5, 9, 1, 0, '2026-06-11 20:52:55'),
(214, 5, 10, 1, 0, '2026-06-11 20:52:55'),
(215, 5, 11, 1, 0, '2026-06-11 20:52:55'),
(216, 5, 12, 1, 0, '2026-06-11 20:52:55');

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionarios`
--

CREATE TABLE `funcionarios` (
  `id_funcionario` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `horario_padrao` time DEFAULT '09:00:00',
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_empresa` int(11) NOT NULL,
  `escala` varchar(50) DEFAULT NULL,
  `supervisor` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `funcionarios`
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
(20, 'Carla Rocha', 'Coordenadora RH', 'RH', '08:00:00', 1, '2026-06-09 03:05:12', 4, '5x2', 'Diretoria'),
(21, 'João Silva', 'Analista de TI', 'TI', '08:00:00', 1, '2026-06-11 23:27:03', 5, '5x2', 'Carlos Mendes'),
(22, 'Maria Oliveira', 'Desenvolvedora Back-end', 'TI', '08:00:00', 1, '2026-06-11 23:27:03', 5, '5x2', 'Carlos Mendes'),
(23, 'Pedro Santos', 'Desenvolvedor Front-end', 'TI', '08:00:00', 1, '2026-06-11 23:27:03', 5, '5x2', 'Carlos Mendes'),
(24, 'Ana Costa', 'Analista de RH', 'RH', '08:00:00', 1, '2026-06-11 23:27:03', 5, '5x2', 'Mariana Alves'),
(25, 'Lucas Pereira', 'Designer UX UI', 'Design', '09:00:00', 1, '2026-06-11 23:27:03', 5, '5x2', 'Fernanda Lima'),
(26, 'Beatriz Souza', 'Analista Financeira', 'Financeiro', '08:00:00', 1, '2026-06-11 23:27:03', 5, '5x2', 'Roberto Lima'),
(27, 'Rafael Almeida', 'Coordenador de Suporte', 'Suporte', '10:00:00', 1, '2026-06-11 23:27:03', 5, '6x1', 'Juliana Rocha'),
(28, 'Camila Ferreira', 'Técnica de Suporte', 'Suporte', '10:00:00', 1, '2026-06-11 23:27:03', 5, '6x1', 'Juliana Rocha'),
(29, 'Gustavo Martins', 'Administrador de Redes', 'Infraestrutura', '08:00:00', 1, '2026-06-11 23:27:03', 5, '5x2', 'Carlos Mendes'),
(30, 'Larissa Gomes', 'Product Owner', 'Produto', '09:00:00', 1, '2026-06-11 23:27:03', 5, '5x2', 'Patricia Nunes'),
(31, 'Felipe Barbosa', 'Scrum Master', 'Produto', '09:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Patricia Nunes'),
(32, 'Juliana Rocha', 'Gerente de Suporte', 'Suporte', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Mariana Alves'),
(33, 'Carlos Mendes', 'Gerente de TI', 'TI', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Mariana Alves'),
(34, 'Fernanda Lima', 'Coordenadora de Design', 'Design', '09:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Patricia Nunes'),
(35, 'Roberto Lima', 'Gerente Financeiro', 'Financeiro', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Mariana Alves'),
(36, 'Patricia Nunes', 'Gerente de Produto', 'Produto', '09:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Mariana Alves'),
(37, 'Diego Ribeiro', 'Analista de Dados', 'BI', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Henrique Castro'),
(38, 'Isabela Cardoso', 'Cientista de Dados', 'BI', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Henrique Castro'),
(39, 'Henrique Castro', 'Coordenador de BI', 'BI', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Mariana Alves'),
(40, 'Mariana Alves', 'Gerente de RH', 'RH', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Diretoria'),
(41, 'Thiago Moreira', 'Analista de Segurança', 'Segurança da Informação', '19:00:00', 1, '2026-06-11 23:27:04', 5, '12x36', 'Carlos Mendes'),
(42, 'Natália Dias', 'DevOps Junior', 'Infraestrutura', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Carlos Mendes'),
(43, 'Bruno Teixeira', 'DevOps Pleno', 'Infraestrutura', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Carlos Mendes'),
(44, 'Amanda Reis', 'Assistente de RH', 'RH', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Mariana Alves'),
(45, 'Vinícius Duarte', 'QA Tester', 'Qualidade', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Carolina Matos'),
(46, 'Carolina Matos', 'Coordenadora de Qualidade', 'Qualidade', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Patricia Nunes'),
(47, 'Eduardo Ramos', 'QA Automatizador', 'Qualidade', '08:00:00', 1, '2026-06-11 23:27:04', 5, '5x2', 'Carolina Matos'),
(48, 'Sofia Martins', 'Analista Comercial', 'Comercial', '09:00:00', 1, '2026-06-11 23:27:05', 5, '5x2', 'Ricardo Lopes'),
(49, 'Ricardo Lopes', 'Gerente Comercial', 'Comercial', '09:00:00', 1, '2026-06-11 23:27:05', 5, '5x2', 'Mariana Alves'),
(50, 'Letícia Araujo', 'Assistente Administrativo', 'Administrativo', '08:00:00', 1, '2026-06-11 23:27:05', 5, '5x2', 'Roberto Lima'),
(51, 'Davi keterson', 'auxiliar de logistica', 'log', '08:00:00', 1, '2026-06-17 01:03:21', 5, '5x2', 'arnaldo freitas');

-- --------------------------------------------------------

--
-- Estrutura para tabela `holerites`
--

CREATE TABLE `holerites` (
  `id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `arquivo` varchar(255) DEFAULT NULL,
  `periodo` varchar(20) NOT NULL,
  `data_envio` datetime DEFAULT current_timestamp(),
  `status` enum('pendente','enviado') DEFAULT 'pendente',
  `id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `holerites`
--

INSERT INTO `holerites` (`id`, `funcionario_id`, `arquivo`, `periodo`, `data_envio`, `status`, `id_empresa`) VALUES
(1, 19, 'uploads/holerites/holerite_4_19_6a309daf5e80a.pdf', 'Janeiro/2026', '2026-06-15 21:49:51', 'enviado', 4),
(2, 19, 'uploads/holerites/holerite_4_19_6a309dbd241a0.pdf', 'Fevereiro/2026', '2026-06-15 21:50:05', 'enviado', 4),
(3, 14, 'uploads/holerites/holerite_4_14_6a309dc7b07a0.pdf', 'Junho/2026', '2026-06-15 21:50:15', 'enviado', 4),
(4, 18, 'uploads/holerites/holerite_4_18_6a309dd1f1b8b.pdf', 'Março/2026', '2026-06-15 21:50:25', 'enviado', 4),
(5, 19, 'uploads/holerites/holerite_4_19_6a309ddd3976e.pdf', 'Março/2026', '2026-06-15 21:50:37', 'enviado', 4),
(6, 19, 'uploads/holerites/holerite_4_19_6a309de75daef.pdf', 'Abril/2026', '2026-06-15 21:50:47', 'enviado', 4),
(7, 15, 'uploads/holerites/holerite_4_15_6a309df203cd0.pdf', 'Fevereiro/2026', '2026-06-15 21:50:58', 'enviado', 4),
(8, 11, 'uploads/holerites/holerite_4_11_6a309dfc20817.pdf', 'Fevereiro/2026', '2026-06-15 21:51:08', 'enviado', 4),
(9, 19, 'uploads/holerites/holerite_4_19_6a309e0d4880c.pdf', 'Maio/2026', '2026-06-15 21:51:25', 'enviado', 4),
(10, 19, 'uploads/holerites/holerite_4_19_6a309e16f2f95.pdf', 'Junho/2026', '2026-06-15 21:51:34', 'enviado', 4),
(11, 12, 'uploads/holerites/holerite_4_12_6a309e25e3672.pdf', 'Junho/2026', '2026-06-15 21:51:49', 'enviado', 4);

-- --------------------------------------------------------

--
-- Estrutura para tabela `licencas_medicas`
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
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_empresa` int(11) NOT NULL,
  `status` varchar(30) DEFAULT 'pendente',
  `data_visto` datetime DEFAULT NULL,
  `mensagem_colaborador` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `licencas_medicas`
--

INSERT INTO `licencas_medicas` (`id`, `id_funcionario`, `arquivo_atestado`, `tipo_arquivo`, `motivo`, `data_inicio`, `data_fim`, `dias`, `observacao`, `data_envio`, `id_empresa`, `status`, `data_visto`, `mensagem_colaborador`) VALUES
(1, 19, 'uploads/licencas/licenca_6a28614867b88.jpg', 'jpg', 'Gripe', '2026-06-09', '2026-06-12', 4, '', '2026-06-09 18:54:00', 4, 'visto', '2026-06-09 16:45:06', 'Sua licença médica foi visualizada pelo RH.'),
(2, 14, 'uploads/licencas/licenca_6a2866e7dd8f0.jpg', 'jpg', 'dor de cabeça', '2026-06-09', '2026-06-10', 2, '', '2026-06-09 19:17:59', 4, 'visto', '2026-06-09 16:45:04', 'Sua licença médica foi visualizada pelo RH.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id_notificacao` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_usuario_destino` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'sistema',
  `titulo` varchar(150) NOT NULL,
  `mensagem` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `lida` tinyint(1) NOT NULL DEFAULT 0,
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `notificacoes`
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
(14, 4, 12, 'solicitacao', 'Nova solicitação de férias', 'Bruno Martins enviou uma nova solicitação de férias para Março.', 'ferias.php', 0, '2026-06-09 17:06:37'),
(15, 4, 12, 'solicitacao', 'Nova solicitação de férias', 'Bruno Martins enviou uma nova solicitação de férias para Maio.', 'ferias.php', 0, '2026-06-15 21:47:36'),
(16, 4, 12, 'solicitacao', 'Solicitação de férias alterada', 'Bruno Martins alterou uma solicitação de férias para Agosto.', 'ferias.php', 0, '2026-06-15 21:47:39'),
(17, 4, 12, 'solicitacao', 'Solicitação de férias alterada', 'Bruno Martins alterou uma solicitação de férias para Julho.', 'ferias.php', 0, '2026-06-15 21:47:41');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ocorrencias`
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
  `descricao` text DEFAULT NULL,
  `testemunhas` varchar(255) DEFAULT NULL,
  `evidencia` varchar(255) DEFAULT NULL,
  `status` enum('aberta','em_analise','resolvida') DEFAULT 'aberta',
  `data_ocorrencia` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ocorrencias`
--

INSERT INTO `ocorrencias` (`id_ocorrencia`, `id_empresa`, `id_usuario`, `tipo_reporte`, `nome`, `categoria`, `andar`, `sala`, `local_especifico`, `descricao`, `testemunhas`, `evidencia`, `status`, `data_ocorrencia`) VALUES
(1, 4, 21, 'Identificado', 'Bruno Martins', 'Equipamento danificado', '2º Andar', 'Sala 4', 'lab 4', 'computador quebrado', '', 'uploads/ocorrencias/ocorrencia_6a28648f5ec3b4.24458022.jpg', 'aberta', '2026-06-09 16:07:59'),
(2, 4, 16, 'Identificado', 'Ana Oliveira', 'Outro', '1º Andar', 'Sala 2', 'banheiro', 'pia quebrada', 'carlos antonio e guilherme alves', 'uploads/ocorrencias/ocorrencia_6a286836208005.91593107.webp', 'resolvida', '2026-06-09 16:23:34'),
(3, 4, 21, 'Anônimo', 'Anônimo', 'Agressão', 'Térreo', '', 'entrada', 'guilherme e carlos se pegando na porrada', '', 'uploads/ocorrencias/ocorrencia_6a2869f5a085d0.13672972.jpg', 'em_analise', '2026-06-09 16:31:01'),
(4, 4, 21, 'Anônimo', 'Anônimo', 'Agressão', '1º Andar', 'adsa', '', 'asdasd', 'adasda', NULL, 'resolvida', '2026-06-09 16:46:13'),
(5, 4, 21, 'Anônimo', 'Anônimo', 'Assédio', 'Térreo', 'adads', 'asdsad', 'asdsad', '', NULL, 'resolvida', '2026-06-09 16:49:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pontos`
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_empresa` int(11) NOT NULL,
  `saida_intervalo` time DEFAULT NULL,
  `retorno_intervalo` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pontos`
--

INSERT INTO `pontos` (`id_ponto`, `id_funcionario`, `data`, `hora_entrada`, `hora_saida`, `total_horas`, `status`, `justificativa`, `created_at`, `id_empresa`, `saida_intervalo`, `retorno_intervalo`) VALUES
(1, 45, '2026-06-11', '08:23:00', '17:20:00', 8.18, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '12:56:00'),
(2, 41, '2026-06-11', '08:08:00', '17:23:00', 8.32, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:06:00', '13:02:00'),
(3, 48, '2026-06-11', '08:37:00', '17:35:00', 7.87, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:03:00'),
(4, 35, '2026-06-11', '08:01:00', '17:11:00', 7.98, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:08:00'),
(5, 49, '2026-06-11', '08:01:00', '17:03:00', 7.85, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:07:00'),
(6, 27, '2026-06-11', '08:03:00', '17:14:00', 8.03, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:06:00'),
(7, 23, '2026-06-11', '08:38:00', '17:42:00', 8.02, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:00:00', '13:03:00'),
(8, 36, '2026-06-11', '08:42:00', '17:57:00', 8.08, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '13:13:00'),
(9, 42, '2026-06-11', '08:44:00', '17:57:00', 8.18, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '12:58:00'),
(10, 40, '2026-06-11', '08:07:00', '17:20:00', 8.35, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:06:00', '12:58:00'),
(11, 22, '2026-06-11', '07:53:00', '16:51:00', 7.88, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:08:00', '13:13:00'),
(12, 25, '2026-06-11', '07:52:00', '17:07:00', 8.05, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '13:13:00'),
(13, 50, '2026-06-11', '07:58:00', '17:03:00', 7.92, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:06:00'),
(14, 30, '2026-06-11', '07:59:00', '17:17:00', 8.18, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '13:11:00'),
(15, 32, '2026-06-11', '07:50:00', '16:46:00', 8.10, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '13:00:00'),
(16, 21, '2026-06-11', '07:56:00', '17:06:00', 8.28, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:00:00'),
(17, 38, '2026-06-11', '08:28:00', '17:43:00', 8.45, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:08:00', '12:56:00'),
(18, 39, '2026-06-11', '07:50:00', '17:09:00', 8.20, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '13:08:00'),
(19, 29, '2026-06-11', '07:52:00', '17:05:00', 7.98, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:10:00'),
(20, 34, '2026-06-11', '08:00:00', '17:15:00', 8.13, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '13:11:00'),
(21, 31, '2026-06-11', '08:39:00', '17:59:00', 8.45, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '12:57:00'),
(22, 47, '2026-06-11', '08:25:00', '17:32:00', 8.00, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:14:00'),
(23, 37, '2026-06-11', '07:54:00', '16:56:00', 8.00, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '13:05:00'),
(24, 46, '2026-06-11', '08:35:00', '17:43:00', 7.97, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '13:05:00'),
(25, 33, '2026-06-11', '08:01:00', '17:05:00', 7.98, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:59:00', '13:04:00'),
(26, 28, '2026-06-11', '07:56:00', '16:49:00', 7.90, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:01:00'),
(27, 43, '2026-06-11', '07:51:00', '16:57:00', 8.08, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '12:57:00'),
(28, 26, '2026-06-11', '08:02:00', '17:22:00', 8.55, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '12:57:00'),
(29, 24, '2026-06-11', '07:51:00', '16:58:00', 8.12, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '12:56:00'),
(30, 44, '2026-06-11', '08:00:00', '17:11:00', 8.10, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '13:00:00'),
(31, 39, '2026-06-10', '07:50:00', '16:52:00', 7.87, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:12:00'),
(32, 31, '2026-06-10', '08:01:00', '17:05:00', 8.13, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:03:00'),
(33, 45, '2026-06-10', '07:53:00', '17:00:00', 8.00, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '13:05:00'),
(34, 41, '2026-06-10', '08:06:00', '17:12:00', 8.05, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '13:13:00'),
(35, 48, '2026-06-10', '08:08:00', '17:07:00', 7.85, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:15:00'),
(36, 35, '2026-06-10', '08:03:00', '17:12:00', 7.95, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:08:00'),
(37, 49, '2026-06-10', '08:35:00', '17:27:00', 7.93, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '12:57:00'),
(38, 27, '2026-06-10', '08:15:00', '17:05:00', 7.63, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:09:00'),
(39, 23, '2026-06-10', '07:51:00', '17:07:00', 8.25, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '13:02:00'),
(40, 36, '2026-06-10', '07:53:00', '17:08:00', 8.23, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '13:02:00'),
(41, 42, '2026-06-10', '07:53:00', '16:46:00', 7.70, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:59:00', '13:10:00'),
(42, 40, '2026-06-10', '08:02:00', '16:59:00', 7.73, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:59:00', '13:12:00'),
(43, 22, '2026-06-10', '07:54:00', '16:54:00', 7.97, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:09:00'),
(44, 25, '2026-06-10', '08:27:00', '17:34:00', 8.00, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:14:00'),
(45, 50, '2026-06-10', '07:59:00', '16:49:00', 7.88, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:04:00'),
(46, 30, '2026-06-10', '08:01:00', '17:13:00', 7.95, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '13:13:00'),
(47, 32, '2026-06-10', '07:56:00', '17:02:00', 8.27, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '13:00:00'),
(48, 21, '2026-06-10', '07:52:00', '17:10:00', 8.22, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:00:00', '13:05:00'),
(49, 38, '2026-06-10', '08:38:00', '17:43:00', 8.27, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '12:59:00'),
(50, 29, '2026-06-10', '08:07:00', '17:24:00', 8.08, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '13:10:00'),
(51, 34, '2026-06-10', '08:06:00', '17:20:00', 7.92, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '13:14:00'),
(52, 47, '2026-06-10', '08:04:00', '16:57:00', 8.05, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '12:57:00'),
(53, 37, '2026-06-10', '08:38:00', '17:46:00', 7.82, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:15:00'),
(54, 46, '2026-06-10', '08:33:00', '17:45:00', 7.90, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:15:00'),
(55, 33, '2026-06-10', '07:52:00', '16:55:00', 7.95, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:06:00', '13:12:00'),
(56, 28, '2026-06-10', '08:00:00', '17:04:00', 7.95, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:00:00', '13:07:00'),
(57, 43, '2026-06-10', '07:59:00', '17:11:00', 8.25, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:09:00', '13:06:00'),
(58, 26, '2026-06-10', '08:42:00', '17:35:00', 7.70, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:13:00'),
(59, 24, '2026-06-10', '07:52:00', '16:42:00', 7.77, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:06:00', '13:10:00'),
(60, 44, '2026-06-10', '08:38:00', '17:28:00', 7.87, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '12:56:00'),
(61, 28, '2026-06-09', '07:55:00', '16:55:00', 8.07, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:09:00', '13:05:00'),
(62, 45, '2026-06-09', '07:56:00', '16:50:00', 7.72, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:08:00'),
(63, 41, '2026-06-09', '07:53:00', '16:43:00', 7.93, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '12:57:00'),
(64, 48, '2026-06-09', '08:01:00', '17:11:00', 8.05, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:03:00'),
(65, 35, '2026-06-09', '08:45:00', '17:43:00', 7.77, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '13:10:00'),
(66, 49, '2026-06-09', '07:55:00', '17:06:00', 8.02, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:06:00'),
(67, 27, '2026-06-09', '07:50:00', '17:01:00', 8.18, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:08:00', '13:08:00'),
(68, 23, '2026-06-09', '07:57:00', '17:06:00', 8.05, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:09:00', '13:15:00'),
(69, 36, '2026-06-09', '08:41:00', '17:48:00', 8.08, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '12:58:00'),
(70, 42, '2026-06-09', '07:59:00', '16:56:00', 7.73, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:09:00'),
(71, 40, '2026-06-09', '07:57:00', '16:54:00', 7.90, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '12:58:00'),
(72, 22, '2026-06-09', '08:08:00', '16:58:00', 7.78, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '13:01:00'),
(73, 25, '2026-06-09', '08:14:00', '17:20:00', 8.03, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '12:59:00'),
(74, 50, '2026-06-09', '07:58:00', '16:53:00', 7.67, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:00:00', '13:15:00'),
(75, 30, '2026-06-09', '08:30:00', '17:44:00', 8.00, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '13:12:00'),
(76, 32, '2026-06-09', '07:53:00', '16:55:00', 8.20, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:09:00', '12:59:00'),
(77, 21, '2026-06-09', '08:08:00', '17:25:00', 8.23, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:10:00'),
(78, 38, '2026-06-09', '08:08:00', '17:24:00', 8.13, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '13:09:00'),
(79, 39, '2026-06-09', '08:23:00', '17:13:00', 7.92, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:02:00'),
(80, 29, '2026-06-09', '07:59:00', '16:56:00', 7.88, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:06:00', '13:10:00'),
(81, 34, '2026-06-09', '07:54:00', '17:00:00', 7.98, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:04:00'),
(82, 31, '2026-06-09', '07:55:00', '17:15:00', 8.38, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '13:01:00'),
(83, 47, '2026-06-09', '08:23:00', '17:22:00', 8.12, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:08:00', '13:00:00'),
(84, 37, '2026-06-09', '07:51:00', '16:55:00', 8.02, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:05:00', '13:08:00'),
(85, 46, '2026-06-09', '08:45:00', '17:41:00', 7.72, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:15:00'),
(86, 33, '2026-06-09', '08:03:00', '16:55:00', 7.93, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '12:57:00'),
(87, 43, '2026-06-09', '07:52:00', '17:00:00', 8.03, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:13:00'),
(88, 26, '2026-06-09', '08:30:00', '17:25:00', 7.85, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:59:00', '13:03:00'),
(89, 24, '2026-06-09', '08:05:00', '17:09:00', 7.97, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:13:00'),
(90, 44, '2026-06-09', '08:36:00', '17:31:00', 7.90, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:09:00', '13:10:00'),
(91, 21, '2026-06-08', '08:04:00', '17:13:00', 8.12, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '12:58:00'),
(92, 45, '2026-06-08', '08:01:00', '17:17:00', 8.25, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '13:11:00'),
(93, 41, '2026-06-08', '07:50:00', '16:54:00', 7.90, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:12:00'),
(94, 48, '2026-06-08', '08:01:00', '16:53:00', 7.88, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:09:00', '13:08:00'),
(95, 35, '2026-06-08', '08:13:00', '17:06:00', 8.03, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:06:00', '12:57:00'),
(96, 49, '2026-06-08', '08:02:00', '16:58:00', 7.65, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '13:15:00'),
(97, 27, '2026-06-08', '07:56:00', '16:51:00', 7.78, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '13:06:00'),
(98, 23, '2026-06-08', '07:59:00', '16:58:00', 7.83, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:05:00', '13:14:00'),
(99, 36, '2026-06-08', '08:02:00', '17:06:00', 8.05, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '13:05:00'),
(100, 42, '2026-06-08', '08:13:00', '17:26:00', 8.22, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '13:03:00'),
(101, 40, '2026-06-08', '08:11:00', '17:01:00', 7.55, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '13:12:00'),
(102, 22, '2026-06-08', '07:59:00', '17:06:00', 8.23, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '12:56:00'),
(103, 25, '2026-06-08', '08:21:00', '17:24:00', 8.03, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '13:04:00'),
(104, 50, '2026-06-08', '08:05:00', '17:00:00', 7.70, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:59:00', '13:12:00'),
(105, 30, '2026-06-08', '07:51:00', '16:52:00', 7.85, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '13:14:00'),
(106, 32, '2026-06-08', '08:34:00', '17:42:00', 8.00, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '13:11:00'),
(107, 38, '2026-06-08', '07:53:00', '17:07:00', 8.23, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '13:01:00'),
(108, 39, '2026-06-08', '08:08:00', '17:13:00', 7.98, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:13:00'),
(109, 29, '2026-06-08', '07:50:00', '16:42:00', 7.65, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '13:08:00'),
(110, 34, '2026-06-08', '07:59:00', '16:54:00', 7.72, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:09:00'),
(111, 31, '2026-06-08', '07:57:00', '17:17:00', 8.57, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '12:56:00'),
(112, 47, '2026-06-08', '08:00:00', '17:00:00', 8.08, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:05:00', '13:00:00'),
(113, 37, '2026-06-08', '08:07:00', '17:11:00', 8.28, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '12:57:00'),
(114, 46, '2026-06-08', '07:54:00', '17:00:00', 7.95, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '13:13:00'),
(115, 33, '2026-06-08', '08:04:00', '16:59:00', 7.87, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:08:00', '13:11:00'),
(116, 28, '2026-06-08', '07:52:00', '16:47:00', 7.92, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:02:00'),
(117, 43, '2026-06-08', '08:01:00', '17:06:00', 8.08, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:06:00', '13:06:00'),
(118, 26, '2026-06-08', '07:55:00', '16:53:00', 8.08, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:05:00', '12:58:00'),
(119, 24, '2026-06-08', '08:01:00', '17:12:00', 8.42, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:09:00', '12:55:00'),
(120, 44, '2026-06-08', '08:06:00', '17:19:00', 8.23, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '12:57:00'),
(121, 25, '2026-06-05', '08:02:00', '16:53:00', 7.70, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '13:10:00'),
(122, 46, '2026-06-05', '07:55:00', '17:15:00', 8.33, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:06:00', '13:06:00'),
(123, 45, '2026-06-05', '07:59:00', '17:04:00', 8.18, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '12:58:00'),
(124, 41, '2026-06-05', '07:56:00', '16:57:00', 7.87, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:11:00'),
(125, 48, '2026-06-05', '08:05:00', '17:12:00', 7.88, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '13:09:00'),
(126, 35, '2026-06-05', '08:03:00', '17:06:00', 8.07, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:59:00', '12:58:00'),
(127, 49, '2026-06-05', '07:57:00', '17:09:00', 7.95, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:12:00'),
(128, 27, '2026-06-05', '08:07:00', '17:10:00', 8.12, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '12:58:00'),
(129, 23, '2026-06-05', '08:12:00', '17:22:00', 8.20, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:05:00'),
(130, 36, '2026-06-05', '08:07:00', '17:08:00', 7.93, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:07:00'),
(131, 42, '2026-06-05', '08:30:00', '17:42:00', 8.05, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:06:00'),
(132, 40, '2026-06-05', '07:52:00', '16:49:00', 7.82, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:10:00'),
(133, 22, '2026-06-05', '08:36:00', '17:26:00', 7.72, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:05:00', '13:12:00'),
(134, 50, '2026-06-05', '08:43:00', '17:39:00', 7.65, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '13:12:00'),
(135, 30, '2026-06-05', '07:51:00', '17:00:00', 8.02, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:15:00'),
(136, 32, '2026-06-05', '07:56:00', '17:01:00', 8.15, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:08:00', '13:04:00'),
(137, 21, '2026-06-05', '07:55:00', '16:56:00', 7.82, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '13:13:00'),
(138, 38, '2026-06-05', '08:08:00', '17:11:00', 8.02, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '13:05:00'),
(139, 39, '2026-06-05', '08:20:00', '17:26:00', 8.07, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:09:00'),
(140, 29, '2026-06-05', '08:08:00', '17:14:00', 8.17, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '13:06:00'),
(141, 34, '2026-06-05', '07:50:00', '16:43:00', 7.83, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:00:00', '13:03:00'),
(142, 31, '2026-06-05', '08:07:00', '17:09:00', 7.97, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:01:00'),
(143, 47, '2026-06-05', '08:07:00', '17:06:00', 8.00, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:59:00', '12:58:00'),
(144, 37, '2026-06-05', '07:55:00', '16:57:00', 8.03, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '13:10:00'),
(145, 33, '2026-06-05', '07:57:00', '16:51:00', 8.05, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:05:00', '12:56:00'),
(146, 28, '2026-06-05', '08:41:00', '17:49:00', 8.15, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '12:57:00'),
(147, 43, '2026-06-05', '08:27:00', '17:20:00', 8.05, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:06:00', '12:56:00'),
(148, 26, '2026-06-05', '08:17:00', '17:18:00', 8.02, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:08:00', '13:08:00'),
(149, 24, '2026-06-05', '07:53:00', '16:45:00', 7.98, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:09:00', '13:02:00'),
(150, 44, '2026-06-05', '08:02:00', '16:56:00', 7.68, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:15:00'),
(151, 45, '2026-06-04', '07:54:00', '17:02:00', 8.20, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '13:06:00'),
(152, 41, '2026-06-04', '07:51:00', '16:49:00', 7.78, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '13:14:00'),
(153, 48, '2026-06-04', '07:55:00', '16:49:00', 7.78, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:08:00', '13:15:00'),
(154, 35, '2026-06-04', '07:58:00', '17:12:00', 8.25, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:08:00', '13:07:00'),
(155, 49, '2026-06-04', '08:06:00', '17:07:00', 7.93, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:02:00'),
(156, 27, '2026-06-04', '08:05:00', '17:15:00', 8.25, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:00:00', '12:55:00'),
(157, 23, '2026-06-04', '07:59:00', '16:54:00', 7.90, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:05:00', '13:06:00'),
(158, 36, '2026-06-04', '07:52:00', '17:12:00', 8.43, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '12:58:00'),
(159, 42, '2026-06-04', '07:51:00', '17:08:00', 8.38, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '12:55:00'),
(160, 40, '2026-06-04', '07:50:00', '16:48:00', 7.93, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:08:00', '13:10:00'),
(161, 22, '2026-06-04', '07:58:00', '17:16:00', 8.28, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:09:00', '13:10:00'),
(162, 25, '2026-06-04', '07:59:00', '17:17:00', 8.23, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:06:00'),
(163, 50, '2026-06-04', '08:29:00', '17:35:00', 8.12, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '13:02:00'),
(164, 30, '2026-06-04', '08:18:00', '17:24:00', 8.05, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '12:58:00'),
(165, 32, '2026-06-04', '08:01:00', '17:16:00', 8.00, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:11:00'),
(166, 21, '2026-06-04', '08:06:00', '17:00:00', 7.72, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '13:15:00'),
(167, 38, '2026-06-04', '08:07:00', '17:27:00', 8.12, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:00:00', '13:13:00'),
(168, 39, '2026-06-04', '08:43:00', '17:57:00', 8.15, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:06:00', '13:11:00'),
(169, 29, '2026-06-04', '08:01:00', '17:07:00', 7.93, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:00:00', '13:10:00'),
(170, 34, '2026-06-04', '07:58:00', '17:03:00', 7.95, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:59:00', '13:07:00'),
(171, 31, '2026-06-04', '08:36:00', '17:43:00', 7.93, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:07:00'),
(172, 47, '2026-06-04', '08:03:00', '17:17:00', 8.17, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:09:00', '13:13:00'),
(173, 37, '2026-06-04', '08:17:00', '17:21:00', 8.22, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '12:55:00'),
(174, 46, '2026-06-04', '07:54:00', '17:11:00', 8.13, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:59:00', '13:08:00'),
(175, 33, '2026-06-04', '07:56:00', '17:14:00', 8.13, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '13:12:00'),
(176, 28, '2026-06-04', '08:38:00', '17:28:00', 7.63, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '13:13:00'),
(177, 43, '2026-06-04', '07:53:00', '17:00:00', 8.05, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '13:05:00'),
(178, 26, '2026-06-04', '07:53:00', '17:06:00', 8.10, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:00:00', '13:07:00'),
(179, 24, '2026-06-04', '07:58:00', '17:09:00', 8.27, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:02:00', '12:57:00'),
(180, 44, '2026-06-04', '08:07:00', '16:59:00', 7.72, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:05:00'),
(181, 45, '2026-06-03', '08:38:00', '17:36:00', 7.98, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:05:00', '13:04:00'),
(182, 23, '2026-06-03', '07:51:00', '16:45:00', 7.75, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '13:04:00'),
(183, 41, '2026-06-03', '08:38:00', '17:56:00', 7.97, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:55:00', '13:15:00'),
(184, 48, '2026-06-03', '07:57:00', '17:14:00', 8.02, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:12:00'),
(185, 35, '2026-06-03', '07:51:00', '17:08:00', 8.15, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:04:00'),
(186, 49, '2026-06-03', '07:51:00', '16:42:00', 7.93, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '12:59:00'),
(187, 27, '2026-06-03', '07:55:00', '16:54:00', 7.73, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:11:00'),
(188, 36, '2026-06-03', '08:06:00', '17:16:00', 8.30, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '13:02:00'),
(189, 42, '2026-06-03', '07:50:00', '17:06:00', 8.23, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '13:05:00'),
(190, 40, '2026-06-03', '07:57:00', '17:06:00', 8.08, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '13:08:00'),
(191, 22, '2026-06-03', '07:59:00', '17:16:00', 8.17, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:00:00', '13:07:00'),
(192, 25, '2026-06-03', '07:58:00', '17:14:00', 8.22, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:57:00', '13:00:00'),
(193, 50, '2026-06-03', '08:00:00', '17:15:00', 8.13, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '13:03:00'),
(194, 30, '2026-06-03', '08:04:00', '17:23:00', 8.38, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:06:00', '13:02:00'),
(195, 32, '2026-06-03', '08:03:00', '16:59:00', 7.95, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:56:00', '12:55:00'),
(196, 21, '2026-06-03', '08:16:00', '17:11:00', 7.85, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:07:00', '13:11:00'),
(197, 38, '2026-06-03', '07:59:00', '17:19:00', 8.35, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:05:00', '13:04:00'),
(198, 39, '2026-06-03', '08:28:00', '17:47:00', 8.27, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:10:00', '13:13:00'),
(199, 29, '2026-06-03', '07:56:00', '16:55:00', 7.98, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:08:00', '13:08:00'),
(200, 34, '2026-06-03', '07:52:00', '17:10:00', 8.38, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '12:59:00'),
(201, 31, '2026-06-03', '07:53:00', '17:00:00', 8.17, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:05:00', '13:02:00'),
(202, 47, '2026-06-03', '08:02:00', '17:06:00', 8.02, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:03:00', '13:06:00'),
(203, 37, '2026-06-03', '07:57:00', '17:09:00', 8.23, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '12:59:00'),
(204, 46, '2026-06-03', '08:29:00', '17:33:00', 7.93, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:01:00', '13:09:00'),
(205, 33, '2026-06-03', '07:57:00', '17:04:00', 8.10, 'completo', NULL, '2026-06-12 00:09:04', 5, '12:04:00', '13:05:00'),
(206, 28, '2026-06-03', '08:45:00', '17:40:00', 7.75, 'completo', NULL, '2026-06-12 00:09:04', 5, '11:58:00', '13:08:00'),
(207, 43, '2026-06-03', '07:57:00', '17:17:00', 8.02, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:56:00', '13:15:00'),
(208, 26, '2026-06-03', '08:11:00', '17:06:00', 7.62, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:56:00', '13:14:00'),
(209, 24, '2026-06-03', '07:54:00', '16:58:00', 8.12, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:58:00', '12:55:00'),
(210, 44, '2026-06-03', '07:59:00', '16:49:00', 7.77, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:10:00', '13:14:00'),
(211, 41, '2026-06-02', '07:56:00', '17:16:00', 8.10, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:00:00', '13:14:00'),
(212, 25, '2026-06-02', '07:54:00', '16:52:00', 8.13, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:10:00', '13:00:00'),
(213, 43, '2026-06-02', '08:21:00', '17:15:00', 7.67, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:59:00', '13:13:00'),
(214, 45, '2026-06-02', '07:50:00', '16:59:00', 7.95, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:58:00', '13:10:00'),
(215, 48, '2026-06-02', '08:04:00', '17:06:00', 7.98, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:06:00', '13:09:00'),
(216, 35, '2026-06-02', '07:56:00', '17:06:00', 7.97, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:57:00', '13:09:00'),
(217, 49, '2026-06-02', '07:57:00', '17:05:00', 8.08, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:07:00', '13:10:00'),
(218, 27, '2026-06-02', '08:06:00', '17:03:00', 8.03, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:00:00', '12:55:00'),
(219, 23, '2026-06-02', '08:27:00', '17:42:00', 8.27, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:59:00', '12:58:00'),
(220, 36, '2026-06-02', '08:39:00', '17:39:00', 8.00, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:58:00', '12:58:00'),
(221, 42, '2026-06-02', '08:08:00', '17:00:00', 7.65, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:56:00', '13:09:00'),
(222, 40, '2026-06-02', '07:54:00', '17:07:00', 8.12, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:04:00', '13:10:00'),
(223, 22, '2026-06-02', '07:54:00', '17:13:00', 8.45, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:06:00', '12:58:00'),
(224, 50, '2026-06-02', '08:03:00', '17:02:00', 8.10, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:09:00', '13:02:00'),
(225, 30, '2026-06-02', '07:54:00', '17:04:00', 8.35, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:10:00', '12:59:00'),
(226, 32, '2026-06-02', '08:05:00', '17:09:00', 7.92, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:00:00', '13:09:00'),
(227, 21, '2026-06-02', '08:08:00', '17:11:00', 8.22, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:08:00', '12:58:00'),
(228, 38, '2026-06-02', '08:08:00', '17:07:00', 8.10, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:07:00', '13:00:00'),
(229, 39, '2026-06-02', '07:55:00', '16:50:00', 7.72, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:03:00', '13:15:00'),
(230, 29, '2026-06-02', '08:22:00', '17:16:00', 7.92, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:08:00', '13:07:00'),
(231, 34, '2026-06-02', '08:12:00', '17:13:00', 8.27, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:10:00', '12:55:00'),
(232, 31, '2026-06-02', '08:00:00', '17:16:00', 8.35, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:09:00', '13:04:00'),
(233, 47, '2026-06-02', '08:03:00', '16:58:00', 7.90, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:08:00', '13:09:00'),
(234, 37, '2026-06-02', '08:30:00', '17:30:00', 8.10, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:09:00', '13:03:00'),
(235, 46, '2026-06-02', '07:53:00', '17:01:00', 7.95, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:02:00', '13:13:00'),
(236, 33, '2026-06-02', '08:07:00', '17:11:00', 7.82, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:57:00', '13:12:00'),
(237, 28, '2026-06-02', '07:54:00', '16:52:00', 8.17, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:07:00', '12:55:00'),
(238, 26, '2026-06-02', '08:08:00', '17:02:00', 8.08, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:09:00', '12:58:00'),
(239, 24, '2026-06-02', '08:08:00', '17:28:00', 8.35, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:09:00', '13:08:00'),
(240, 44, '2026-06-02', '08:21:00', '17:37:00', 8.28, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:04:00', '13:03:00'),
(241, 41, '2026-06-01', '07:51:00', '16:47:00', 8.08, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:05:00', '12:56:00'),
(242, 45, '2026-06-01', '08:25:00', '17:26:00', 8.05, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:06:00', '13:04:00'),
(243, 48, '2026-06-01', '08:05:00', '16:57:00', 7.72, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:00:00', '13:09:00'),
(244, 35, '2026-06-01', '07:52:00', '17:09:00', 8.22, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:05:00', '13:09:00'),
(245, 49, '2026-06-01', '08:06:00', '17:14:00', 8.12, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:08:00', '13:09:00'),
(246, 27, '2026-06-01', '08:44:00', '18:00:00', 8.25, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:56:00', '12:57:00'),
(247, 23, '2026-06-01', '08:36:00', '17:47:00', 8.27, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:05:00', '13:00:00'),
(248, 36, '2026-06-01', '07:59:00', '16:56:00', 7.83, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:57:00', '13:04:00'),
(249, 42, '2026-06-01', '07:58:00', '16:54:00', 7.92, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:04:00', '13:05:00'),
(250, 40, '2026-06-01', '08:24:00', '17:22:00', 7.92, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:57:00', '13:00:00'),
(251, 22, '2026-06-01', '08:10:00', '17:25:00', 8.27, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:57:00', '12:56:00'),
(252, 25, '2026-06-01', '08:15:00', '17:35:00', 8.38, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:10:00', '13:07:00'),
(253, 50, '2026-06-01', '08:37:00', '17:52:00', 8.13, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:05:00', '13:12:00'),
(254, 30, '2026-06-01', '07:52:00', '17:06:00', 8.07, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:00:00', '13:10:00'),
(255, 32, '2026-06-01', '08:05:00', '17:25:00', 8.12, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:57:00', '13:10:00'),
(256, 21, '2026-06-01', '07:58:00', '17:15:00', 8.03, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:59:00', '13:14:00'),
(257, 38, '2026-06-01', '07:53:00', '16:54:00', 8.12, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:03:00', '12:57:00'),
(258, 39, '2026-06-01', '08:02:00', '17:01:00', 7.78, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:55:00', '13:07:00'),
(259, 29, '2026-06-01', '07:53:00', '16:47:00', 8.05, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:04:00', '12:55:00'),
(260, 34, '2026-06-01', '07:56:00', '17:15:00', 8.15, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:57:00', '13:07:00'),
(261, 31, '2026-06-01', '07:52:00', '16:53:00', 8.22, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:07:00', '12:55:00'),
(262, 47, '2026-06-01', '08:00:00', '17:12:00', 8.22, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:59:00', '12:58:00'),
(263, 37, '2026-06-01', '07:58:00', '17:10:00', 8.18, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:07:00', '13:08:00'),
(264, 46, '2026-06-01', '07:58:00', '17:08:00', 8.35, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:10:00', '12:59:00'),
(265, 33, '2026-06-01', '08:05:00', '17:15:00', 8.02, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:05:00', '13:14:00'),
(266, 28, '2026-06-01', '07:51:00', '17:02:00', 8.15, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:56:00', '12:58:00'),
(267, 43, '2026-06-01', '08:06:00', '17:14:00', 7.98, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:01:00', '13:10:00'),
(268, 26, '2026-06-01', '08:40:00', '17:41:00', 8.18, 'completo', NULL, '2026-06-12 00:09:05', 5, '12:06:00', '12:56:00'),
(269, 24, '2026-06-01', '07:50:00', '16:59:00', 8.05, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:55:00', '13:01:00'),
(270, 44, '2026-06-01', '08:17:00', '17:14:00', 7.80, 'completo', NULL, '2026-06-12 00:09:05', 5, '11:56:00', '13:05:00'),
(271, 14, '2026-06-11', '08:04:00', '17:04:00', 7.98, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:55:00', '12:56:00'),
(272, 17, '2026-06-11', '07:54:00', '16:51:00', 8.15, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:09:00', '12:57:00'),
(273, 11, '2026-06-11', '07:50:00', '16:56:00', 7.98, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:59:00', '13:06:00'),
(274, 16, '2026-06-11', '07:55:00', '17:04:00', 7.90, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:57:00', '13:12:00'),
(275, 20, '2026-06-11', '07:55:00', '17:01:00', 7.92, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:55:00', '13:06:00'),
(276, 19, '2026-06-11', '07:51:00', '16:56:00', 8.07, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:06:00', '13:07:00'),
(277, 16, '2026-06-10', '07:51:00', '16:41:00', 7.85, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:08:00', '13:07:00'),
(278, 17, '2026-06-10', '08:40:00', '17:51:00', 8.13, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:58:00', '13:01:00'),
(279, 11, '2026-06-10', '07:57:00', '17:08:00', 8.27, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:05:00', '13:00:00'),
(280, 20, '2026-06-10', '08:04:00', '17:15:00', 8.20, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:58:00', '12:57:00'),
(281, 19, '2026-06-10', '07:52:00', '17:00:00', 8.15, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:59:00', '12:58:00'),
(282, 14, '2026-06-10', '07:58:00', '16:48:00', 7.67, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:59:00', '13:09:00'),
(283, 17, '2026-06-09', '08:00:00', '17:04:00', 7.95, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:02:00', '13:09:00'),
(284, 11, '2026-06-09', '08:38:00', '17:36:00', 7.93, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:04:00', '13:06:00'),
(285, 16, '2026-06-09', '07:51:00', '16:51:00', 7.95, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:04:00', '13:07:00'),
(286, 20, '2026-06-09', '08:24:00', '17:18:00', 7.75, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:55:00', '13:04:00'),
(287, 19, '2026-06-09', '07:54:00', '17:13:00', 8.38, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:06:00', '13:02:00'),
(288, 14, '2026-06-09', '07:54:00', '16:59:00', 8.25, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:06:00', '12:56:00'),
(289, 17, '2026-06-08', '08:43:00', '17:53:00', 8.02, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:01:00', '13:10:00'),
(290, 11, '2026-06-08', '08:06:00', '17:13:00', 8.15, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:08:00', '13:06:00'),
(291, 16, '2026-06-08', '07:56:00', '16:51:00', 7.75, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:00:00', '13:10:00'),
(292, 20, '2026-06-08', '07:59:00', '16:59:00', 7.95, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:08:00', '13:11:00'),
(293, 19, '2026-06-08', '08:18:00', '17:28:00', 8.02, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:02:00', '13:11:00'),
(294, 14, '2026-06-08', '08:44:00', '18:02:00', 8.47, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:09:00', '12:59:00'),
(295, 17, '2026-06-05', '07:50:00', '17:02:00', 7.93, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:57:00', '13:13:00'),
(296, 11, '2026-06-05', '08:01:00', '17:09:00', 8.28, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:05:00', '12:56:00'),
(297, 16, '2026-06-05', '08:00:00', '16:58:00', 7.90, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:01:00', '13:05:00'),
(298, 20, '2026-06-05', '08:01:00', '17:04:00', 7.82, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:57:00', '13:11:00'),
(299, 19, '2026-06-05', '07:54:00', '16:59:00', 8.02, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:08:00', '13:12:00'),
(300, 14, '2026-06-05', '07:52:00', '17:04:00', 8.22, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:08:00', '13:07:00'),
(301, 17, '2026-06-04', '08:05:00', '16:59:00', 7.90, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:00:00', '13:00:00'),
(302, 11, '2026-06-04', '08:06:00', '16:56:00', 7.80, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:03:00', '13:05:00'),
(303, 16, '2026-06-04', '08:14:00', '17:04:00', 8.05, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:10:00', '12:57:00'),
(304, 20, '2026-06-04', '07:58:00', '17:07:00', 8.08, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:08:00', '13:12:00'),
(305, 19, '2026-06-04', '08:07:00', '17:04:00', 7.95, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:05:00', '13:05:00'),
(306, 14, '2026-06-04', '07:59:00', '17:11:00', 8.12, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:06:00', '13:11:00'),
(307, 17, '2026-06-03', '07:52:00', '17:09:00', 8.27, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:09:00', '13:10:00'),
(308, 11, '2026-06-03', '08:04:00', '17:17:00', 8.28, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:05:00', '13:01:00'),
(309, 16, '2026-06-03', '07:57:00', '16:47:00', 7.75, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:56:00', '13:01:00'),
(310, 20, '2026-06-03', '08:29:00', '17:28:00', 8.00, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:05:00', '13:04:00'),
(311, 19, '2026-06-03', '07:50:00', '17:03:00', 8.40, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:08:00', '12:57:00'),
(312, 14, '2026-06-03', '08:18:00', '17:26:00', 8.13, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:08:00', '13:08:00'),
(313, 17, '2026-06-02', '07:56:00', '17:11:00', 8.13, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:56:00', '13:03:00'),
(314, 11, '2026-06-02', '08:27:00', '17:27:00', 7.90, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:56:00', '13:02:00'),
(315, 16, '2026-06-02', '08:37:00', '17:51:00', 8.23, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:01:00', '13:01:00'),
(316, 20, '2026-06-02', '07:51:00', '17:09:00', 8.32, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:07:00', '13:06:00'),
(317, 19, '2026-06-02', '07:53:00', '17:13:00', 8.30, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:59:00', '13:01:00'),
(318, 14, '2026-06-02', '08:44:00', '17:45:00', 7.87, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:04:00', '13:13:00'),
(319, 17, '2026-06-01', '07:56:00', '17:15:00', 8.40, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:01:00', '12:56:00'),
(320, 11, '2026-06-01', '07:58:00', '16:53:00', 7.92, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:10:00', '13:10:00'),
(321, 16, '2026-06-01', '08:07:00', '17:05:00', 7.82, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:05:00', '13:14:00'),
(322, 20, '2026-06-01', '08:27:00', '17:20:00', 7.75, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:59:00', '13:07:00'),
(323, 19, '2026-06-01', '08:31:00', '17:38:00', 8.00, 'completo', NULL, '2026-06-16 00:15:15', 4, '11:55:00', '13:02:00'),
(324, 14, '2026-06-01', '08:01:00', '16:51:00', 7.97, 'completo', NULL, '2026-06-16 00:15:15', 4, '12:04:00', '12:56:00'),
(325, 51, '2026-06-16', '08:00:00', '16:00:00', 7.00, 'atraso', NULL, '2026-06-17 01:18:25', 5, '11:00:00', '12:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `telefone` varchar(20) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `id_empresa` int(11) NOT NULL,
  `tentativas_login` int(11) NOT NULL DEFAULT 0,
  `bloqueado_ate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_funcionario`, `nome`, `email`, `senha`, `tipo`, `status`, `ultimo_login`, `created_at`, `telefone`, `cidade`, `foto`, `cargo`, `departamento`, `id_empresa`, `tentativas_login`, `bloqueado_ate`) VALUES
(12, NULL, 'ACGLL', 'demo@meupontodiario.com.br', '$2y$10$eFyK7aB2SteZUMVlYsjUkuWK3YOYGU7pSnbXIXDhbZtsko7NsCCe.', 'rh', 'ativo', '2026-06-15 21:49:02', '2026-06-09 02:52:57', '(11) 99999-0000', 'São Paulo', NULL, 'Dono', NULL, 4, 0, NULL),
(13, 11, 'João Silva', 'joao@empresa.com', '$2y$10$Pop9sVCvE4Yx6sWqppufXOCFt.QPkXl2/vpa36a0rfmA4fpQ/MNN6', 'funcionario', 'ativo', NULL, '2026-06-09 03:05:12', '11999991111', 'São Paulo', NULL, 'Analista de TI', 'TI', 4, 2, NULL),
(14, 12, 'Maria Souza', 'maria@empresa.com', '$2y$10$lDY2XUzi8RVvYw1xCFzLeuSJ/ZFkhM.LyttKcwFlM4ItAJI3N7R7u', 'funcionario', 'ferias', NULL, '2026-06-09 03:05:12', '11999992222', 'Campinas', NULL, 'Auxiliar RH', 'RH', 4, 0, NULL),
(15, 13, 'Pedro Santos', 'pedro@empresa.com', '$2y$10$gZkgW/PaFSOFxT5ixlqfr.vVozjXElNK7bRCXudmqekKWNxWdiHLO', 'funcionario', 'licenca', NULL, '2026-06-09 03:05:12', '11999993333', 'Santos', NULL, 'Desenvolvedor Full Stack', 'TI', 4, 0, NULL),
(16, 14, 'Ana Oliveira', 'ana@empresa.com', '$2y$10$npTxMYq8a7b1ajLtGnkaGuebYJHUB2My2NQR6.Sv9GoqfD5lDahbS', 'funcionario', 'ativo', '2026-06-09 16:22:00', '2026-06-09 03:05:12', '11999994444', 'São Paulo', NULL, 'Analista Financeiro', 'Financeiro', 4, 0, NULL),
(17, 15, 'Lucas Pereira', 'lucas@empresa.com', '$2y$10$VC/FGORnRiL9dp5Ry087OudYMcooq/4Yjnwp8vUvg8NshJSNja1US', 'funcionario', 'afastado', NULL, '2026-06-09 03:05:12', '11999995555', 'Guarulhos', NULL, 'Analista de Marketing', 'Marketing', 4, 0, NULL),
(18, 16, 'Fernanda Costa', 'fernanda@empresa.com', '$2y$10$ytnuA8ZT7s8WgEozrruBEO/Bu.CnNeKOg6d3OeDaAjTvKKIj/o5Vy', 'funcionario', 'ativo', NULL, '2026-06-09 03:05:12', '11999996666', 'Osasco', NULL, 'Designer UX/UI', 'Design', 4, 0, NULL),
(19, 17, 'Ricardo Lima', 'ricardo@empresa.com', '$2y$10$K9aExqxZenvKLfVfj7N8FOAqeJJFlET0gZU43dDK0VDxBEliCBFSC', 'funcionario', 'ativo', NULL, '2026-06-09 03:05:12', '11999997777', 'Sorocaba', NULL, 'Supervisor de Produção', 'Produção', 4, 0, NULL),
(20, 18, 'Juliana Alves', 'juliana@empresa.com', '$2y$10$rA77xAwSJp7A39/oZ8rVp.vVAKAv2XeD/Jf.mXM7RPROmz2t116FS', 'funcionario', 'ferias', NULL, '2026-06-09 03:05:12', '11999998888', 'São Paulo', NULL, 'Analista Comercial', 'Comercial', 4, 0, NULL),
(21, 19, 'Bruno Martins', 'bruno@empresa.com', '$2y$10$FI84T4iaHtpPdZPkA79ueO3.Yb2i5gcbjHFd7ZE7KWGIEdRXY80HC', 'funcionario', 'ativo', '2026-06-16 21:26:19', '2026-06-09 03:05:12', '11999999999', 'São Bernardo', NULL, 'Técnico de Suporte', 'TI', 4, 3, '2026-06-17 02:31:44'),
(22, 20, 'Carla Rocha', 'carla@empresa.com', '$2y$10$iYiNGRoSO6VyfVGVwQeq.eAGS5J9vtN29s7hOgIRyPPq5XJnrIMSS', 'funcionario', 'ativo', NULL, '2026-06-09 03:05:12', '11988880000', 'São Paulo', NULL, 'Coordenadora RH', 'RH', 4, 0, NULL),
(23, NULL, 'Mariana Alves', 'Alves@technova.com.br', '$2y$10$//6b0igvCvXGEFh3Kg919OynR7ze.S0nc.hczpYwFEFVij1sf5OE2', 'rh', 'ativo', '2026-06-16 21:58:12', '2026-06-11 23:26:23', '(11) 97777-2020', 'São Paulo', NULL, 'Gerente de RH', NULL, 5, 0, NULL),
(24, 21, 'João Silva', 'joao.silva@technova.com.br', '$2y$10$rFUxfy23YQyltmLM/poAsuktIXYTy4p7qSMA6wr7QE238gwDssjSW', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:03', '11999991111', 'São Paulo', NULL, 'Analista de TI', 'TI', 5, 0, NULL),
(25, 22, 'Maria Oliveira', 'maria.oliveira@technova.com.br', '$2y$10$GRRLjj61haT06yFAeID.E.hnH/XSF3jhG6SsRorv8WrbktybbIBX.', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:03', '11999992222', 'São Paulo', NULL, 'Desenvolvedora Back-end', 'TI', 5, 0, NULL),
(26, 23, 'Pedro Santos', 'pedro.santos@technova.com.br', '$2y$10$p3hOxE3N1YrTrptGY.4PwOYmLeJzgSzQ9nfaGCHEaeJs3HLHEFoTy', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:03', '11999993333', 'Santos', NULL, 'Desenvolvedor Front-end', 'TI', 5, 0, NULL),
(27, 24, 'Ana Costa', 'ana.costa@technova.com.br', '$2y$10$gCDU6AbL8IrcKD4IPgtVreCUQkS5C7pI9uoS7HC33nxZhyppnVE3W', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:03', '11999994444', 'São Paulo', NULL, 'Analista de RH', 'RH', 5, 0, NULL),
(28, 25, 'Lucas Pereira', 'lucas.pereira@technova.com.br', '$2y$10$mrqlzC4KUppN5//mDJK6WuO3krUZ6fH5XDDQikRc2IfZVoE1G0GFC', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:03', '11999995555', 'São Vicente', NULL, 'Designer UX UI', 'Design', 5, 0, NULL),
(29, 26, 'Beatriz Souza', 'beatriz.souza@technova.com.br', '$2y$10$96FidJynG1NOWiBzpZDYQuYSIyvEMIEaHnHUWU7Ujg4mLTRZZfr1u', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:03', '11999996666', 'São Paulo', NULL, 'Analista Financeira', 'Financeiro', 5, 0, NULL),
(30, 27, 'Rafael Almeida', 'rafael.almeida@technova.com.br', '$2y$10$X0bNaY20ZnbsW9rmQbxQh.Ed2KliFHyyOVi.N7D5/V85CeivL6PVC', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:03', '11999997777', 'Praia Grande', NULL, 'Coordenador de Suporte', 'Suporte', 5, 0, NULL),
(31, 28, 'Camila Ferreira', 'camila.ferreira@technova.com.br', '$2y$10$zXCuP9zOuYOi8FkvnXQIoO1DjyVp7FSIuKjMBqoTn19knut/FhptG', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:03', '11999998888', 'Santos', NULL, 'Técnica de Suporte', 'Suporte', 5, 0, NULL),
(32, 29, 'Gustavo Martins', 'gustavo.martins@technova.com.br', '$2y$10$vNckOeCwjZaKZMUjBQ6jcOl/TZ6yQVRC4oG9LfpFNeqwUGh7vsdfi', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:03', '11999990001', 'São Paulo', NULL, 'Administrador de Redes', 'Infraestrutura', 5, 0, NULL),
(33, 30, 'Larissa Gomes', 'larissa.gomes@technova.com.br', '$2y$10$EytwPto7OXjfPbebmGqHf.awMf9FLhkbzAQEDsgwAx1RjtOSwj.tG', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990002', 'São Paulo', NULL, 'Product Owner', 'Produto', 5, 0, NULL),
(34, 31, 'Felipe Barbosa', 'felipe.barbosa@technova.com.br', '$2y$10$NVBXjqwA6wV00BccmCPyAOQ9Kisj4x6/XzAXXhcf9KW0yH3Q7lYBi', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990003', 'São Paulo', NULL, 'Scrum Master', 'Produto', 5, 0, NULL),
(35, 32, 'Juliana Rocha', 'juliana.rocha@technova.com.br', '$2y$10$rp9pa3zdQZdoGbTkjSfUoO8jJw0OBRrXlXPsBZqEGvm4Kn0TsX4hy', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990004', 'Praia Grande', NULL, 'Gerente de Suporte', 'Suporte', 5, 0, NULL),
(36, 33, 'Carlos Mendes', 'carlos.mendes@technova.com.br', '$2y$10$ci7a8PNu70YFsy.FXndzkOeiw/7hy5Ptdwwp2FpZ8N2f.MAtzmLxK', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990005', 'São Paulo', NULL, 'Gerente de TI', 'TI', 5, 0, NULL),
(37, 34, 'Fernanda Lima', 'fernanda.lima@technova.com.br', '$2y$10$RCwk6LeSbVxnD7WzGmtqlekP.HuFicOtzNtdlK..5kuKSHWiHxQw6', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990006', 'São Vicente', NULL, 'Coordenadora de Design', 'Design', 5, 0, NULL),
(38, 35, 'Roberto Lima', 'roberto.lima@technova.com.br', '$2y$10$KxC2WH2fbVvbNWYx8qdge./Z0ohgzU/6aerJqpzUZc0TeqIMJQ/Py', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990007', 'São Paulo', NULL, 'Gerente Financeiro', 'Financeiro', 5, 0, NULL),
(39, 36, 'Patricia Nunes', 'patricia.nunes@technova.com.br', '$2y$10$mPqbIy1rs9cSpH/.dZm1Ied8Cwiy8tHPfsyUD5e05oVR5zRzC.L/O', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990008', 'São Paulo', NULL, 'Gerente de Produto', 'Produto', 5, 0, NULL),
(40, 37, 'Diego Ribeiro', 'diego.ribeiro@technova.com.br', '$2y$10$On39.XoN0ERFiP0tu4XZu.wZZjatzb.YNqIvVtnYY0oNdNjBGWKF6', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990009', 'Santos', NULL, 'Analista de Dados', 'BI', 5, 0, NULL),
(41, 38, 'Isabela Cardoso', 'isabela.cardoso@technova.com.br', '$2y$10$cJoZO4BkuBg1TreB.hHgg.mwSsAc4/3LVn0UptyHWIX5d/SFauDRu', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990010', 'São Paulo', NULL, 'Cientista de Dados', 'BI', 5, 0, NULL),
(42, 39, 'Henrique Castro', 'henrique.castro@technova.com.br', '$2y$10$bt5ZX2519qoKeN5uicp.yuCoLDawPmkAfW.uiTfegwvirpDs.JOyu', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990011', 'São Paulo', NULL, 'Coordenador de BI', 'BI', 5, 0, NULL),
(43, 40, 'Mariana Alves', 'mariana.alves@technova.com.br', '$2y$10$aiOgvBlP08qZGvgRqXoYCuLHTlSbfrwbMkIM8MOEF3mYSq23HRwLm', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990012', 'São Paulo', NULL, 'Gerente de RH', 'RH', 5, 0, NULL),
(44, 41, 'Thiago Moreira', 'thiago.moreira@technova.com.br', '$2y$10$jxfpLkN7Huu0aavCBdnVA.KCe8B.ZE1ILvg/NzyUvDY0GvGicAbP.', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990013', 'São Paulo', NULL, 'Analista de Segurança', 'Segurança da Informação', 5, 0, NULL),
(45, 42, 'Natália Dias', 'natalia.dias@technova.com.br', '$2y$10$RrJ1838XimSttDwfLFcS9eV.RSIZINK/rB9k3a/bFs1gNlEeJmGC2', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990014', 'Santos', NULL, 'DevOps Junior', 'Infraestrutura', 5, 0, NULL),
(46, 43, 'Bruno Teixeira', 'bruno.teixeira@technova.com.br', '$2y$10$.2JqZagXMKPpZNY3F5gRu.NsKZmKbgKPg7Ui8q1cv2.tTvN0ftFO6', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990015', 'São Paulo', NULL, 'DevOps Pleno', 'Infraestrutura', 5, 0, NULL),
(47, 44, 'Amanda Reis', 'amanda.reis@technova.com.br', '$2y$10$WGdv021qKuLaIN.hsOhEjOLzjMs5iobCJ5hp.qDQ8H/LKFd4BBvEq', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990016', 'São Vicente', NULL, 'Assistente de RH', 'RH', 5, 0, NULL),
(48, 45, 'Vinícius Duarte', 'vinicius.duarte@technova.com.br', '$2y$10$mzTNemqthXww.rPxE1xZM.eEF3fyICkRSoF05RLiukoW0gxnMMN82', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990017', 'Praia Grande', NULL, 'QA Tester', 'Qualidade', 5, 0, NULL),
(49, 46, 'Carolina Matos', 'carolina.matos@technova.com.br', '$2y$10$pv0olWoieqYtNKr6Ff8jde2RW8Wc6FfxcsSFh6viiUn8P/k1TDT3i', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:04', '11999990018', 'São Paulo', NULL, 'Coordenadora de Qualidade', 'Qualidade', 5, 0, NULL),
(50, 47, 'Eduardo Ramos', 'eduardo.ramos@technova.com.br', '$2y$10$LPp00J/6R/WVhBiSr9eUKePc1lF3j5zzGA5Sm5r38NZN933XYknQm', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:05', '11999990019', 'Santos', NULL, 'QA Automatizador', 'Qualidade', 5, 0, NULL),
(51, 48, 'Sofia Martins', 'sofia.martins@technova.com.br', '$2y$10$9JFiZRtY5rIC0cJjkuvhhO06YJmQcp3Y1w/tQKxLdfLc4KQE.Fwvi', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:05', '11999990020', 'São Paulo', NULL, 'Analista Comercial', 'Comercial', 5, 0, NULL),
(52, 49, 'Ricardo Lopes', 'ricardo.lopes@technova.com.br', '$2y$10$4Jq.7hCcUZPhxIEYUYSrueSdGo0LlQ7IcrDcgbRBKSuq3EU4MFPE.', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:05', '11999990021', 'São Paulo', NULL, 'Gerente Comercial', 'Comercial', 5, 0, NULL),
(53, 50, 'Letícia Araujo', 'leticia.araujo@technova.com.br', '$2y$10$TYbp6Zrjm49GO8XF5.eLueiT3NWoxT4n0OuOjEUtLHo.v1Oir..bO', 'funcionario', 'ativo', NULL, '2026-06-11 23:27:05', '11999990022', 'São Vicente', NULL, 'Assistente Administrativo', 'Administrativo', 5, 0, NULL),
(54, 51, 'Davi keterson', 'Davi.keter@tecnova.com.br', '$2y$10$w0IAx3fmpwirQfoHg8WLiekYClPN.9phu3oIDWbC8Nx2XQ489ElUu', 'funcionario', 'ativo', NULL, '2026-06-17 01:03:21', '1399741726', 'Santos', NULL, 'auxiliar de logistica', 'log', 5, 0, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `assinaturas`
--
ALTER TABLE `assinaturas`
  ADD PRIMARY KEY (`id_assinatura`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Índices de tabela `atividades`
--
ALTER TABLE `atividades`
  ADD PRIMARY KEY (`id_atividade`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `banco_horas`
--
ALTER TABLE `banco_horas`
  ADD PRIMARY KEY (`id_banco`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Índices de tabela `banco_horas_movimentacao`
--
ALTER TABLE `banco_horas_movimentacao`
  ADD PRIMARY KEY (`id_mov`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Índices de tabela `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `config_notificacoes`
--
ALTER TABLE `config_notificacoes`
  ADD PRIMARY KEY (`id_config`),
  ADD UNIQUE KEY `unico_usuario_empresa` (`id_usuario`,`id_empresa`);

--
-- Índices de tabela `duvidas`
--
ALTER TABLE `duvidas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `cnpj` (`cnpj`);

--
-- Índices de tabela `ferias`
--
ALTER TABLE `ferias`
  ADD PRIMARY KEY (`id_ferias`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Índices de tabela `ferias_meses_disponiveis`
--
ALTER TABLE `ferias_meses_disponiveis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `empresa_mes` (`id_empresa`,`mes`);

--
-- Índices de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`id_funcionario`);

--
-- Índices de tabela `holerites`
--
ALTER TABLE `holerites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `funcionario_id` (`funcionario_id`);

--
-- Índices de tabela `licencas_medicas`
--
ALTER TABLE `licencas_medicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Índices de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id_notificacao`),
  ADD KEY `idx_usuario_empresa` (`id_usuario_destino`,`id_empresa`),
  ADD KEY `idx_lida` (`lida`);

--
-- Índices de tabela `ocorrencias`
--
ALTER TABLE `ocorrencias`
  ADD PRIMARY KEY (`id_ocorrencia`);

--
-- Índices de tabela `pontos`
--
ALTER TABLE `pontos`
  ADD PRIMARY KEY (`id_ponto`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- AUTO_INCREMENT para tabelas despejadas
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
  MODIFY `id_atividade` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `banco_horas`
--
ALTER TABLE `banco_horas`
  MODIFY `id_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de tabela `banco_horas_movimentacao`
--
ALTER TABLE `banco_horas_movimentacao`
  MODIFY `id_mov` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `ferias`
--
ALTER TABLE `ferias`
  MODIFY `id_ferias` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `ferias_meses_disponiveis`
--
ALTER TABLE `ferias_meses_disponiveis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=409;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de tabela `holerites`
--
ALTER TABLE `holerites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `licencas_medicas`
--
ALTER TABLE `licencas_medicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id_notificacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `ocorrencias`
--
ALTER TABLE `ocorrencias`
  MODIFY `id_ocorrencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `pontos`
--
ALTER TABLE `pontos`
  MODIFY `id_ponto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=326;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `assinaturas`
--
ALTER TABLE `assinaturas`
  ADD CONSTRAINT `assinaturas_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`);

--
-- Restrições para tabelas `atividades`
--
ALTER TABLE `atividades`
  ADD CONSTRAINT `atividades_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Restrições para tabelas `banco_horas`
--
ALTER TABLE `banco_horas`
  ADD CONSTRAINT `banco_horas_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE CASCADE;

--
-- Restrições para tabelas `banco_horas_movimentacao`
--
ALTER TABLE `banco_horas_movimentacao`
  ADD CONSTRAINT `banco_horas_movimentacao_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ferias`
--
ALTER TABLE `ferias`
  ADD CONSTRAINT `ferias_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE CASCADE;

--
-- Restrições para tabelas `holerites`
--
ALTER TABLE `holerites`
  ADD CONSTRAINT `holerites_ibfk_1` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id_funcionario`);

--
-- Restrições para tabelas `licencas_medicas`
--
ALTER TABLE `licencas_medicas`
  ADD CONSTRAINT `licencas_medicas_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE CASCADE;

--
-- Restrições para tabelas `pontos`
--
ALTER TABLE `pontos`
  ADD CONSTRAINT `pontos_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE CASCADE;

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionarios` (`id_funcionario`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
