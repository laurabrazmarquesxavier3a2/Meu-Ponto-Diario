-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 26/05/2026 às 22:01
-- Versão do servidor: 8.0.42
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
-- Estrutura para tabela `atividades`
--

CREATE TABLE `atividades` (
  `id_atividade` int NOT NULL,
  `id_usuario` int NOT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipo` enum('success','primary','warning','danger') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'primary',
  `data_atividade` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `atividades`
--

INSERT INTO `atividades` (`id_atividade`, `id_usuario`, `descricao`, `tipo`, `data_atividade`) VALUES
(1, 3, 'Aprovado pedido de férias - Maria Silva', 'success', '2026-05-15 22:47:33'),
(2, 3, 'Enviado holerite para João Santos', 'primary', '2026-05-15 22:47:33'),
(3, 3, 'Revisado licença médica - Ana Costa', 'warning', '2026-05-15 22:47:33'),
(4, 3, 'Atualizado informações de benefícios', 'danger', '2026-05-15 22:47:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `banco_horas`
--

CREATE TABLE `banco_horas` (
  `id_banco` int NOT NULL,
  `id_funcionario` int NOT NULL,
  `mes` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `saldo_total` decimal(6,2) DEFAULT '0.00',
  `saldo_mes` decimal(6,2) DEFAULT '0.00',
  `horas_extras_mes` decimal(6,2) DEFAULT '0.00',
  `horas_debito_mes` decimal(6,2) DEFAULT '0.00',
  `data_atualizacao` date DEFAULT NULL,
  `status` enum('positivo','negativo','neutro') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'neutro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `banco_horas_movimentacao`
--

CREATE TABLE `banco_horas_movimentacao` (
  `id_mov` int NOT NULL,
  `id_funcionario` int NOT NULL,
  `data` date NOT NULL,
  `tipo` enum('extra','debito') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `horas` decimal(5,2) NOT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comunicados`
--

CREATE TABLE `comunicados` (
  `id` int NOT NULL,
  `titulo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `conteudo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `categoria` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fixado` tinyint(1) DEFAULT '0',
  `autor` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `publico` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data_publicacao` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `duvidas`
--

CREATE TABLE `duvidas` (
  `id` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `duvida` text NOT NULL,
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `duvidas`
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
-- Estrutura para tabela `funcionarios`
--

CREATE TABLE `funcionarios` (
  `id_funcionario` int NOT NULL,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cargo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `departamento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `horario_padrao` time DEFAULT '09:00:00',
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `funcionarios`
--

INSERT INTO `funcionarios` (`id_funcionario`, `nome`, `cargo`, `departamento`, `horario_padrao`, `ativo`, `created_at`) VALUES
(2, 'Administrador RH', 'Gerente RH', 'Recursos Humanos', '09:00:00', 1, '2026-05-16 00:18:27'),
(3, 'Mara Silva', 'Analista RH', 'RH', '08:00:00', 1, '2026-05-16 01:11:55'),
(4, 'João Santos', 'Assistente Administrativo', 'Financeiro', '09:00:00', 1, '2026-05-16 01:11:55'),
(5, 'Ana Silva', 'Desenvolvedora', 'TI', '08:30:00', 1, '2026-05-16 01:11:55');

-- --------------------------------------------------------

--
-- Estrutura para tabela `holerites`
--

CREATE TABLE `holerites` (
  `id` int NOT NULL,
  `funcionario_id` int NOT NULL,
  `arquivo` varchar(255) DEFAULT NULL,
  `periodo` varchar(20) NOT NULL,
  `data_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pendente','enviado') DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_general_ci;
-- --------------------------------------------------------

--
-- Estrutura para tabela `licencas_medicas`
--

CREATE TABLE `licencas_medicas` (
  `id` int NOT NULL,
  `id_funcionario` int NOT NULL,
  `arquivo_atestado` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipo_arquivo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `motivo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `dias` int DEFAULT NULL,
  `observacao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data_envio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `licencas_medicas`
--

INSERT INTO `licencas_medicas` (`id`, `id_funcionario`, `arquivo_atestado`, `tipo_arquivo`, `motivo`, `data_inicio`, `data_fim`, `dias`, `observacao`, `data_envio`) VALUES
(1, 2, '../uploads/licencas/6a15f80135a9b.png', 'png', 'gripe ', '2026-05-06', '2026-05-08', 3, '', '2026-05-26 19:44:01'),
(2, 2, '../uploads/licencas/6a15f98ad343a.png', 'png', 'virose', '2026-05-12', '2026-05-14', 3, '', '2026-05-26 19:50:34'),
(3, 2, 'uploads/licencas/6a15fac647bcc.png', 'png', 'dor de cabeça', '2026-05-04', '2026-05-07', 4, '', '2026-05-26 19:55:50');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pontos`
--

CREATE TABLE `pontos` (
  `id_ponto` int NOT NULL,
  `id_funcionario` int NOT NULL,
  `data` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_saida` time DEFAULT NULL,
  `total_horas` decimal(5,2) DEFAULT NULL,
  `status` enum('completo','atraso','em andamento','ausente') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'em andamento',
  `justificativa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pontos`
--

INSERT INTO `pontos` (`id_ponto`, `id_funcionario`, `data`, `hora_entrada`, `hora_saida`, `total_horas`, `status`, `justificativa`, `created_at`) VALUES
(1, 3, '2026-03-08', '08:45:00', '18:10:00', 8.25, 'completo', NULL, '2026-05-16 01:12:13'),
(2, 4, '2026-03-08', '09:15:00', '18:00:00', 7.45, 'atraso', NULL, '2026-05-16 01:12:13'),
(3, 5, '2026-03-08', '08:50:00', NULL, NULL, 'em andamento', NULL, '2026-05-16 01:12:13');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL,
  `id_funcionario` int DEFAULT NULL,
  `nome` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipo` enum('rh','funcionario') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'funcionario',
  `status` enum('ativo','inativo') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'ativo',
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `telefone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cidade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cargo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `departamento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_funcionario`, `nome`, `email`, `senha`, `tipo`, `status`, `ultimo_login`, `created_at`, `telefone`, `cidade`, `foto`, `cargo`, `departamento`) VALUES
(3, 2, 'Administrador RH', 'rh@empresa.com', '$2y$10$mlY4g7mWOo0n6QlyFJE1EeELkvlrdtJ4MEQiPDgsLjsQmTUhlZOn2', 'rh', 'ativo', '2026-05-26 16:57:47', '2026-05-16 00:24:30', '(11) 98765-1234', 'São Paulo, SP', NULL, 'Gerente de RH', 'Recursos Humanos');

--
-- Índices para tabelas despejadas
--

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
-- Índices de tabela `duvidas`
--
ALTER TABLE `duvidas`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT de tabela `atividades`
--
ALTER TABLE `atividades`
  MODIFY `id_atividade` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `banco_horas`
--
ALTER TABLE `banco_horas`
  MODIFY `id_banco` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `banco_horas_movimentacao`
--
ALTER TABLE `banco_horas_movimentacao`
  MODIFY `id_mov` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `duvidas`
--
ALTER TABLE `duvidas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id_funcionario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `holerites`
--
ALTER TABLE `holerites`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `licencas_medicas`
--
ALTER TABLE `licencas_medicas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `pontos`
--
ALTER TABLE `pontos`
  MODIFY `id_ponto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

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
