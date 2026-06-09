-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 09-Jun-2026 às 01:39
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

-- --------------------------------------------------------

--
-- Estrutura da tabela `ferias_meses_disponiveis`
--

CREATE TABLE `ferias_meses_disponiveis` (
  `id` int(11) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `mes` tinyint(4) NOT NULL,
  `disponivel` tinyint(4) NOT NULL DEFAULT '1',
  `atualizado_em` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `ferias_meses_disponiveis`
--

INSERT INTO `ferias_meses_disponiveis` (`id`, `id_empresa`, `mes`, `disponivel`, `atualizado_em`) VALUES
(1, 1, 1, 1, '2026-06-08 19:33:18'),
(2, 1, 2, 1, '2026-06-08 19:33:18'),
(3, 1, 3, 1, '2026-06-08 19:33:18'),
(4, 1, 4, 1, '2026-06-08 19:33:18'),
(5, 1, 5, 1, '2026-06-08 19:33:18'),
(6, 1, 6, 1, '2026-06-08 19:33:18'),
(7, 1, 7, 1, '2026-06-08 19:33:18'),
(8, 1, 8, 1, '2026-06-08 19:33:18'),
(9, 1, 9, 1, '2026-06-08 19:33:18'),
(10, 1, 10, 1, '2026-06-08 19:33:18'),
(11, 1, 11, 1, '2026-06-08 19:33:18'),
(12, 1, 12, 1, '2026-06-08 19:33:18');

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
  MODIFY `id_atividade` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `banco_horas`
--
ALTER TABLE `banco_horas`
  MODIFY `id_banco` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `banco_horas_movimentacao`
--
ALTER TABLE `banco_horas_movimentacao`
  MODIFY `id_mov` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `duvidas`
--
ALTER TABLE `duvidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `ferias`
--
ALTER TABLE `ferias`
  MODIFY `id_ferias` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ferias_meses_disponiveis`
--
ALTER TABLE `ferias_meses_disponiveis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `holerites`
--
ALTER TABLE `holerites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `licencas_medicas`
--
ALTER TABLE `licencas_medicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ocorrencias`
--
ALTER TABLE `ocorrencias`
  MODIFY `id_ocorrencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pontos`
--
ALTER TABLE `pontos`
  MODIFY `id_ponto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
