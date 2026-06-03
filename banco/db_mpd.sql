-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 03-Jun-2026 às 02:13
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
(5, 5, 'Importou o funcionário Maria Souza', 'success', '2026-06-02 16:37:25'),
(6, 5, 'Importou o funcionário Pedro Santos', 'success', '2026-06-02 16:37:25'),
(7, 5, 'Importou o funcionário Ana Oliveira', 'success', '2026-06-02 16:37:25'),
(8, 5, 'Importou o funcionário Lucas Pereira', 'success', '2026-06-02 16:37:25'),
(9, 5, 'Importou o funcionário Fernanda Costa', 'success', '2026-06-02 16:37:25'),
(10, 5, 'Importou o funcionário Ricardo Lima', 'success', '2026-06-02 16:37:26'),
(11, 5, 'Importou o funcionário Juliana Alves', 'success', '2026-06-02 16:37:26'),
(12, 5, 'Importou o funcionário Bruno Martins', 'success', '2026-06-02 16:37:26'),
(13, 5, 'Importou o funcionário Carla Rocha', 'success', '2026-06-02 16:37:26'),
(14, 23, 'Enviou solicitação de licença médica', 'warning', '2026-06-02 16:44:35'),
(15, 23, 'Enviou solicitação de licença médica', 'warning', '2026-06-02 17:31:44'),
(16, 23, 'Enviou solicitação de licença médica', 'warning', '2026-06-02 17:36:19'),
(17, 23, 'Aprovou uma solicitação de férias', 'success', '2026-06-02 17:40:45'),
(18, 23, 'Aprovou uma solicitação de férias', 'success', '2026-06-02 18:06:14'),
(19, 23, 'Rejeitou uma solicitação de férias', 'danger', '2026-06-02 18:31:17');

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
(10, 16, '2026-06', '8.00', '2.00', '3.50', '1.50', '2026-06-02', 'positivo', 1),
(11, 17, '2026-06', '-2.00', '-2.00', '1.00', '3.00', '2026-06-02', 'negativo', 1),
(12, 18, '2026-06', '5.25', '1.25', '2.25', '1.00', '2026-06-02', 'positivo', 1),
(13, 19, '2026-06', '0.00', '0.00', '0.00', '0.00', '2026-06-02', 'neutro', 1),
(14, 20, '2026-06', '11.75', '3.75', '5.00', '1.25', '2026-06-02', 'positivo', 1),
(15, 21, '2026-06', '18.00', '6.00', '8.00', '2.00', '2026-06-02', 'positivo', 1),
(16, 22, '2026-06', '7.50', '2.50', '4.00', '1.50', '2026-06-02', 'positivo', 1),
(17, 23, '2026-06', '2.83', '2.83', '3.00', '0.17', '2026-06-02', 'positivo', 1),
(18, 24, '2026-06', '3.00', '1.00', '2.00', '1.00', '2026-06-02', 'positivo', 1);

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

--
-- Extraindo dados da tabela `comunicados`
--

INSERT INTO `comunicados` (`id`, `titulo`, `conteudo`, `categoria`, `fixado`, `autor`, `publico`, `data_publicacao`, `id_empresa`) VALUES
(3, 'Aniversariantes do mês', 'bla bla', 'Comemoração', 1, 'Félix Almeida', 'Todos', '2026-06-02 18:28:49', 1);

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

--
-- Extraindo dados da tabela `duvidas`
--

INSERT INTO `duvidas` (`id`, `nome`, `email`, `duvida`, `data_envio`) VALUES
(2, 'GUILHERME GONÇALVES ALVES', 'agui53592@gmail.com', 'testando o forme', '2026-05-21 16:06:20');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `empresas`
--

INSERT INTO `empresas` (`id_empresa`, `razao_social`, `nome_fantasia`, `cnpj`, `segmento`, `email`, `telefone`, `responsavel`, `cargo_responsavel`, `endereco`, `cidade`, `estado`, `cep`, `logo`, `plano`, `status`, `data_cadastro`) VALUES
(1, 'Desenvolver tecnologias revolucionárias', 'Revotech', '79.468.715/0001-23', 'Tecnologia', 'RevoTech1234@gmail.com', '13 98192-3276', 'Félix Almeida', 'Dono', 'Rua Borboletas Psicodélicas', 'São Paulo', 'SP', '04313-110', NULL, 'medio', 'ativa', '2026-06-02 03:09:13'),
(2, '', '', '', '', '', '', '', '', '', '', '', '', NULL, 'pequeno', 'ativa', '2026-06-02 20:17:39');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `ferias`
--

INSERT INTO `ferias` (`id_ferias`, `id_funcionario`, `id_empresa`, `data_inicio`, `data_fim`, `dias`, `data_solicitacao`, `status`, `data_visto`, `mensagem_colaborador`, `alteracoes_restantes`, `motivo_rejeicao`) VALUES
(5, 23, 1, '2027-05-01', '2027-05-30', 30, '2026-06-02 17:36:40', 'rejeitado', '2026-06-02 18:31:17', 'Sua solicitação de férias foi rejeitada pelo RH.', 0, 'ferias ja aprovadas'),
(6, 23, 1, '2026-06-01', '2026-06-30', 30, '2026-06-02 18:30:51', 'pendente', NULL, 'Solicitação alterada. Aguardando visualização do RH.', 0, NULL);

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
(2, 'Administrador RH', 'Gerente RH', 'Recursos Humanos', '09:00:00', 1, '2026-05-16 00:18:27', 0, NULL, NULL),
(5, 'Ana Silva', 'Desenvolvedora', 'TI', '08:30:00', 1, '2026-05-16 01:11:55', 0, NULL, NULL),
(16, 'Maria Souza', 'Auxiliar RH', 'RH', '08:00:00', 1, '2026-06-02 19:37:25', 1, '5x2', 'Ana Costa'),
(17, 'Pedro Santos', 'Desenvolvedor Full Stack', 'TI', '08:00:00', 1, '2026-06-02 19:37:25', 1, '12x36', 'Carlos Mendes'),
(18, 'Ana Oliveira', 'Analista Financeiro', 'Financeiro', '09:00:00', 1, '2026-06-02 19:37:25', 1, '5x2', 'Roberto Lima'),
(19, 'Lucas Pereira', 'Analista de Marketing', 'Marketing', '08:30:00', 1, '2026-06-02 19:37:25', 1, '5x2', 'Mariana Rocha'),
(20, 'Fernanda Costa', 'Designer UX/UI', 'Design', '09:00:00', 1, '2026-06-02 19:37:25', 1, 'Home Office', 'Carlos Mendes'),
(21, 'Ricardo Lima', 'Supervisor de Produção', 'Produção', '07:00:00', 1, '2026-06-02 19:37:25', 1, '6x1', 'Diretoria'),
(22, 'Juliana Alves', 'Analista Comercial', 'Comercial', '08:00:00', 1, '2026-06-02 19:37:26', 1, '5x2', 'Patrícia Gomes'),
(23, 'Bruno Martins', 'Técnico de Suporte', 'TI', '08:00:00', 1, '2026-06-02 19:37:26', 1, '12x36', 'Carlos Mendes'),
(24, 'Carla Rocha', 'Coordenadora RH', 'RH', '08:00:00', 1, '2026-06-02 19:37:26', 1, '5x2', 'Diretoria');

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

--
-- Extraindo dados da tabela `holerites`
--

INSERT INTO `holerites` (`id`, `funcionario_id`, `arquivo`, `periodo`, `data_envio`, `status`, `id_empresa`) VALUES
(4, 23, 'uploads/holerites/holerite_1_23_6a1f3581570c6.pdf', 'Junho/2026', '2026-06-02 16:56:49', 'enviado', 1);

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
(6, 23, 'uploads/licencas/licenca_6a1f3ec368e29.jpg', 'jpg', 'Gripe', '2026-06-01', '2026-06-03', 3, 'Gripe forte, médico orientou a ter repouso de 2 dias.', '2026-06-02 20:36:19', 1, 'visto', '2026-06-02 18:17:29', 'Sua licença médica foi visualizada pelo RH.');

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `ocorrencias`
--

INSERT INTO `ocorrencias` (`id_ocorrencia`, `id_empresa`, `id_usuario`, `tipo_reporte`, `nome`, `categoria`, `andar`, `sala`, `local_especifico`, `descricao`, `testemunhas`, `evidencia`, `status`, `data_ocorrencia`) VALUES
(1, 1, 23, 'Identificado', 'Bruno Martins', 'Equipamento danificado', '1º Andar', 'Sala 4', 'lab 4', 'Computador quebrado', '', 'uploads/ocorrencias/ocorrencia_6a1f40756bed62.13799707.jpg', 'em_analise', '2026-06-02 17:43:33');

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
  `id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `pontos`
--

INSERT INTO `pontos` (`id_ponto`, `id_funcionario`, `data`, `hora_entrada`, `hora_saida`, `total_horas`, `status`, `justificativa`, `created_at`, `id_empresa`) VALUES
(3, 5, '2026-03-08', '08:50:00', NULL, NULL, 'em andamento', NULL, '2026-05-16 01:12:13', 0),
(4, 16, '2026-06-01', '08:00:00', '17:00:00', '8.00', 'completo', '', '2026-06-02 19:37:39', 1),
(5, 16, '2026-06-02', '08:10:00', '17:00:00', '7.83', 'completo', 'Atraso justificado', '2026-06-02 19:37:39', 1),
(6, 16, '2026-06-03', '08:00:00', '18:00:00', '9.00', 'completo', '', '2026-06-02 19:37:39', 1),
(7, 17, '2026-06-01', '08:15:00', '17:00:00', '7.75', 'atraso', 'Atraso', '2026-06-02 19:37:39', 1),
(8, 17, '2026-06-02', '08:00:00', '17:00:00', '8.00', 'completo', '', '2026-06-02 19:37:39', 1),
(9, 17, '2026-06-03', '08:30:00', '17:00:00', '7.50', 'atraso', '', '2026-06-02 19:37:39', 1),
(10, 18, '2026-06-01', '09:00:00', '18:00:00', '8.00', 'completo', '', '2026-06-02 19:37:39', 1),
(11, 18, '2026-06-02', '09:00:00', '18:30:00', '8.50', 'completo', '', '2026-06-02 19:37:39', 1),
(12, 18, '2026-06-03', '09:10:00', '18:00:00', '7.83', 'completo', '', '2026-06-02 19:37:39', 1),
(13, 19, '2026-06-01', '08:30:00', '17:30:00', '8.00', 'completo', '', '2026-06-02 19:37:39', 1),
(14, 19, '2026-06-02', '08:30:00', '17:30:00', '8.00', 'completo', '', '2026-06-02 19:37:39', 1),
(15, 19, '2026-06-03', '00:00:00', '00:00:00', NULL, 'ausente', 'Afastamento', '2026-06-02 19:37:39', 1),
(16, 20, '2026-06-01', '09:00:00', '18:30:00', '8.50', 'completo', '', '2026-06-02 19:37:39', 1),
(17, 20, '2026-06-02', '09:00:00', '19:00:00', '9.00', 'completo', 'Projeto extra', '2026-06-02 19:37:39', 1),
(18, 20, '2026-06-03', '09:10:00', '18:00:00', '7.83', 'completo', '', '2026-06-02 19:37:39', 1),
(19, 21, '2026-06-01', '07:00:00', '17:00:00', '9.00', 'completo', '', '2026-06-02 19:37:39', 1),
(20, 21, '2026-06-02', '07:00:00', '18:00:00', '10.00', 'completo', 'Hora extra', '2026-06-02 19:37:39', 1),
(21, 21, '2026-06-03', '07:15:00', '17:00:00', '8.75', 'completo', '', '2026-06-02 19:37:39', 1),
(22, 22, '2026-06-01', '08:00:00', '17:00:00', '8.00', 'completo', '', '2026-06-02 19:37:39', 1),
(23, 22, '2026-06-02', '08:00:00', '18:00:00', '9.00', 'completo', '', '2026-06-02 19:37:39', 1),
(24, 22, '2026-06-03', '08:20:00', '17:00:00', '7.67', 'completo', '', '2026-06-02 19:37:39', 1),
(25, 23, '2026-06-01', '08:00:00', '18:00:00', '9.00', 'completo', '', '2026-06-02 19:37:39', 1),
(26, 23, '2026-06-02', '08:10:00', '17:00:00', '7.83', 'completo', '', '2026-06-02 19:37:39', 1),
(27, 23, '2026-06-03', '08:00:00', '19:00:00', '10.00', 'completo', 'Plantão extra', '2026-06-02 19:37:39', 1),
(28, 24, '2026-06-01', '08:00:00', '17:00:00', '8.00', 'completo', '', '2026-06-02 19:37:39', 1),
(29, 24, '2026-06-02', '08:00:00', '17:30:00', '8.50', 'completo', '', '2026-06-02 19:37:39', 1),
(30, 24, '2026-06-03', '08:05:00', '17:00:00', '7.92', 'completo', '', '2026-06-02 19:37:39', 1);

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
(3, 2, 'Administrador RH', 'rh@empresa.com', '$2y$10$mlY4g7mWOo0n6QlyFJE1EeELkvlrdtJ4MEQiPDgsLjsQmTUhlZOn2', 'rh', 'ativo', '2026-06-01 23:54:14', '2026-05-16 00:24:30', '(11) 98765-1234', 'São Paulo, SP', NULL, 'Gerente de RH', 'Recursos Humanos', 0),
(4, NULL, 'João Santos', 'joao@empresa.com', '$2y$10$ptIRnPXcMYLzUncmVeBfGOk4Cyb5TWkW5TQW1DolVevkhTrcCygqy', 'funcionario', 'ativo', '2026-05-26 20:47:50', '2026-05-26 23:29:03', NULL, NULL, NULL, NULL, NULL, 0),
(5, NULL, 'Félix Almeida', 'RevoTech1234@gmail.com', '$2y$10$5hfVbcTUT6bW2PEvTr1vIuJIc9HuZIWQuuLTLOoC1Iw46yBek0oXS', 'rh', 'ativo', '2026-06-02 18:27:09', '2026-06-02 03:09:13', '13 98192-3276', 'São Paulo', NULL, 'Dono', NULL, 1),
(16, 16, 'Maria Souza', 'maria@empresa.com', '$2y$10$UjdGFtMyfWcitjnoPLeoge4CVh8trrDquet7sT55/oK5a0jeWyRWm', 'funcionario', 'ferias', NULL, '2026-06-02 19:37:25', '11999992222', 'Campinas', NULL, 'Auxiliar RH', 'RH', 1),
(17, 17, 'Pedro Santos', 'pedro@empresa.com', '$2y$10$uXDoW8vj7MHhq/i7xsYCD.V4lKLcZKqAi1cSSG1TSSmArTRlZ6L12', 'funcionario', 'licenca', NULL, '2026-06-02 19:37:25', '11999993333', 'Santos', NULL, 'Desenvolvedor Full Stack', 'TI', 1),
(18, 18, 'Ana Oliveira', 'ana@empresa.com', '$2y$10$SHqG8f1TpcHFWAiZWiqHP.Ih1TUep6w8KTcLADQwRlhsXeqZz4jcu', 'funcionario', 'ativo', NULL, '2026-06-02 19:37:25', '11999994444', 'São Paulo', NULL, 'Analista Financeiro', 'Financeiro', 1),
(19, 19, 'Lucas Pereira', 'lucas@empresa.com', '$2y$10$aquxezNptDyKjmyLQI/ks.GmB.zYfeUTaH/uBoHqFjk4gC2ziWeWG', 'funcionario', 'afastado', NULL, '2026-06-02 19:37:25', '11999995555', 'Guarulhos', NULL, 'Analista de Marketing', 'Marketing', 1),
(20, 20, 'Fernanda Costa', 'fernanda@empresa.com', '$2y$10$Q.SVjy65sCr34VhYP31wU.3ZOaTyyrQ5WS2jNesi.5fYy63Q/.8o6', 'funcionario', 'ativo', NULL, '2026-06-02 19:37:25', '11999996666', 'Osasco', NULL, 'Designer UX/UI', 'Design', 1),
(21, 21, 'Ricardo Lima', 'ricardo@empresa.com', '$2y$10$tVDqR25FFQLkgRwUnRhuUOnjMwZgHxsM2YV0JGj4C33TP2kIz//zm', 'funcionario', 'ativo', NULL, '2026-06-02 19:37:26', '11999997777', 'Sorocaba', NULL, 'Supervisor de Produção', 'Produção', 1),
(22, 22, 'Juliana Alves', 'juliana@empresa.com', '$2y$10$JFoS.fLNw4bUD66bE1fRaucyyCWnjr3J9M1DHYfihcX.mJLoEfP/e', 'funcionario', 'ferias', NULL, '2026-06-02 19:37:26', '11999998888', 'São Paulo', NULL, 'Analista Comercial', 'Comercial', 1),
(23, 23, 'Bruno Martins', 'bruno@empresa.com', '$2y$10$tHVpiNAgzlNqNvUNdFebveiUPJOXi068UbVHpTdb51gL3caSZW8Ui', 'funcionario', 'ativo', '2026-06-02 18:29:50', '2026-06-02 19:37:26', '11999999999', 'São Bernardo', 'uploads/perfis/perfil_23_6a1f48c08c44e.jpg', 'Técnico de Suporte', 'TI', 1),
(24, 24, 'Carla Rocha', 'carla@empresa.com', '$2y$10$MjF12c1fpskKx4.Z4h7Lh.DSx2yj3narvJ2VAUmukcSOb2ac1wu0G', 'funcionario', 'ativo', NULL, '2026-06-02 19:37:26', '11988880000', 'São Paulo', NULL, 'Coordenadora RH', 'RH', 1),
(25, NULL, '', '', '$2y$10$YXS9dwH6Gm3ZVYRo8pqMN..6wMTFcx8Y1sJz4KOgqf.Lq6jkEdEIS', 'rh', 'ativo', NULL, '2026-06-02 20:17:39', '', '', NULL, '', NULL, 2),
(26, NULL, 'amanda ', 'empresateste23@empresa.com', '$2y$10$xnDI4DsgI0/5GK5Nr/VNBePvLrRxf54oyJrrD3FaE3DpkMWxXtt1e', 'rh', 'ativo', NULL, '2026-06-02 21:33:52', '13 98192-3289', 'São Paulo', NULL, 'Dono', NULL, 5);

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
  MODIFY `id_atividade` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `banco_horas`
--
ALTER TABLE `banco_horas`
  MODIFY `id_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `banco_horas_movimentacao`
--
ALTER TABLE `banco_horas_movimentacao`
  MODIFY `id_mov` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `duvidas`
--
ALTER TABLE `duvidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `ferias`
--
ALTER TABLE `ferias`
  MODIFY `id_ferias` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `holerites`
--
ALTER TABLE `holerites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `licencas_medicas`
--
ALTER TABLE `licencas_medicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `ocorrencias`
--
ALTER TABLE `ocorrencias`
  MODIFY `id_ocorrencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `pontos`
--
ALTER TABLE `pontos`
  MODIFY `id_ponto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
