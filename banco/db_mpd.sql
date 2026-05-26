-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 26-Maio-2026 às 23:51
-- Versão do servidor: 5.7.36
-- versão do PHP: 8.0.16

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
(1, 3, 'Aprovado pedido de férias - Maria Silva', 'success', '2026-05-15 22:47:33'),
(2, 3, 'Enviado holerite para João Santos', 'primary', '2026-05-15 22:47:33'),
(3, 3, 'Revisado licença médica - Ana Costa', 'warning', '2026-05-15 22:47:33'),
(4, 3, 'Atualizado informações de benefícios', 'danger', '2026-05-15 22:47:33');

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
  `status` enum('positivo','negativo','neutro') DEFAULT 'neutro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `descricao` varchar(255) DEFAULT NULL
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
  `data_publicacao` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
(1, 'guibaldo', 'jaca@madura.com', 'não sei php', '2026-05-21 15:16:30'),
(2, 'GUILHERME GONÇALVES ALVES', 'agui53592@gmail.com', 'testando o forme', '2026-05-21 16:06:20'),
(3, 'Carlos jacinto pinto', 'shaolinmataporco@hotmal', 'familia não sei ajudar no tcc', '2026-05-21 16:25:39'),
(4, 'Lyvia lacre', 'taylor.swift@gmail.com', 'textão textão textão', '2026-05-21 16:29:39'),
(5, 'laurinha', 'cademinhas@compras.com', 'testando os bagulho', '2026-05-21 16:32:49'),
(6, 'ana carolina', 'agui53592@gmail.com', 'textão textao', '2026-05-21 22:34:46');

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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `funcionarios`
--

INSERT INTO `funcionarios` (`id_funcionario`, `nome`, `cargo`, `departamento`, `horario_padrao`, `ativo`, `created_at`) VALUES
(2, 'Administrador RH', 'Gerente RH', 'Recursos Humanos', '09:00:00', 1, '2026-05-16 00:18:27'),
(3, 'Mara Silva', 'Analista RH', 'RH', '08:00:00', 1, '2026-05-16 01:11:55'),
(4, 'João Santos', 'Assistente Administrativo', 'Financeiro', '09:00:00', 1, '2026-05-16 01:11:55'),
(5, 'Ana Silva', 'Desenvolvedora', 'TI', '08:30:00', 1, '2026-05-16 01:11:55');

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
  `status` enum('pendente','enviado') DEFAULT 'pendente'
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
  `data_envio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `licencas_medicas`
--

INSERT INTO `licencas_medicas` (`id`, `id_funcionario`, `arquivo_atestado`, `tipo_arquivo`, `motivo`, `data_inicio`, `data_fim`, `dias`, `observacao`, `data_envio`) VALUES
(1, 2, '../uploads/licencas/6a15f80135a9b.png', 'png', 'gripe ', '2026-05-06', '2026-05-08', 3, '', '2026-05-26 19:44:01'),
(2, 2, '../uploads/licencas/6a15f98ad343a.png', 'png', 'virose', '2026-05-12', '2026-05-14', 3, '', '2026-05-26 19:50:34'),
(3, 2, 'uploads/licencas/6a15fac647bcc.png', 'png', 'dor de cabeça', '2026-05-04', '2026-05-07', 4, '', '2026-05-26 19:55:50');

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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `pontos`
--

INSERT INTO `pontos` (`id_ponto`, `id_funcionario`, `data`, `hora_entrada`, `hora_saida`, `total_horas`, `status`, `justificativa`, `created_at`) VALUES
(1, 3, '2026-03-08', '08:45:00', '18:10:00', '8.25', 'completo', NULL, '2026-05-16 01:12:13'),
(2, 4, '2026-03-08', '09:15:00', '18:00:00', '7.45', 'atraso', NULL, '2026-05-16 01:12:13'),
(3, 5, '2026-03-08', '08:50:00', NULL, NULL, 'em andamento', NULL, '2026-05-16 01:12:13');

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
  `tipo` enum('rh','funcionario') DEFAULT 'funcionario',
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `telefone` varchar(20) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_funcionario`, `nome`, `email`, `senha`, `tipo`, `status`, `ultimo_login`, `created_at`, `telefone`, `cidade`, `foto`, `cargo`, `departamento`) VALUES
(3, 2, 'Administrador RH', 'rh@empresa.com', '$2y$10$mlY4g7mWOo0n6QlyFJE1EeELkvlrdtJ4MEQiPDgsLjsQmTUhlZOn2', 'rh', 'ativo', '2026-05-26 20:32:27', '2026-05-16 00:24:30', '(11) 98765-1234', 'São Paulo, SP', NULL, 'Gerente de RH', 'Recursos Humanos'),
(4, 4, 'João Santos', 'joao@empresa.com', '$2y$10$ptIRnPXcMYLzUncmVeBfGOk4Cyb5TWkW5TQW1DolVevkhTrcCygqy', 'funcionario', 'ativo', '2026-05-26 20:47:50', '2026-05-26 23:29:03', NULL, NULL, NULL, NULL, NULL);

--
-- Índices para tabelas despejadas
--

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
-- AUTO_INCREMENT de tabela `atividades`
--
ALTER TABLE `atividades`
  MODIFY `id_atividade` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `banco_horas`
--
ALTER TABLE `banco_horas`
  MODIFY `id_banco` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de tabela `duvidas`
--
ALTER TABLE `duvidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `holerites`
--
ALTER TABLE `holerites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `licencas_medicas`
--
ALTER TABLE `licencas_medicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `pontos`
--
ALTER TABLE `pontos`
  MODIFY `id_ponto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para despejos de tabelas
--

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
