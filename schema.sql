-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 29/12/2025 às 10:35
-- Versão do servidor: 10.11.15-MariaDB-cll-lve
-- Versão do PHP: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `novaf_radio_fm2`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `comercial_id` int(11) NOT NULL,
  `horario_programado` datetime NOT NULL,
  `status` enum('pendente','executado','cancelado') NOT NULL DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `apoios_clientes`
--

CREATE TABLE `apoios_clientes` (
  `id` int(11) NOT NULL,
  `apoio_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `valor_doado` decimal(10,2) NOT NULL,
  `forma_anuncio` text DEFAULT NULL,
  `data_apoio` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `apoios_culturais`
--

CREATE TABLE `apoios_culturais` (
  `id` int(11) NOT NULL,
  `nome_projeto` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `meta_arrecadacao` decimal(10,2) DEFAULT 0.00,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `empresa` varchar(100) NOT NULL,
  `cnpj_cpf` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` varchar(200) DEFAULT NULL,
  `credito_permuta` decimal(10,2) DEFAULT 0.00,
  `saldo_permuta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `data_cadastro` datetime NOT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `empresa`, `cnpj_cpf`, `email`, `telefone`, `endereco`, `credito_permuta`, `saldo_permuta`, `data_cadastro`, `ativo`) VALUES
(1, 'SUPERMERCADO BRASIL LTDA. - ME', '49.672.508/0001-25', 'marlise_marodin@outlook.com', '(41) 99288-4256', 'Rodovia Engenheiro Darci Gomes de Moraes, 5545', 0.00, 0.00, '2025-12-10 00:00:00', 1),
(2, 'Tatianne Franciele do Nacimento', '03.691.407/0002-81', 'tatiannefrancieledonacimento@gmail.com', '(41) 9564-0339', 'PR 412, nº 2215 - Canoas - Pontal do Paraná', 0.00, 0.00, '2025-10-10 00:00:00', 1),
(3, 'KD Materiais de Construção', '21.092.775/0001-04', 'kdconstrucenter@gmail.com', '41 99717-1490', 'Avenida Sebastião Caboto, 9092 - Shangri-lá 83255-000 - Pontal do Paraná - PR', 1480.23, 3480.23, '2025-09-10 10:46:47', 1),
(4, 'PARADA 20 LTDA - ME', '48.574.449/0001-90', 'parada20pontal@gmail.com', '(41) 99894-4184', 'Rodovia Engenheiro Darci Gomes de Morais, 5583 Pontal do Paraná', 0.00, 0.00, '2025-10-10 00:00:00', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente_colaboradores`
--

CREATE TABLE `cliente_colaboradores` (
  `cliente_id` int(11) NOT NULL,
  `colaborador_id` int(11) NOT NULL,
  `percentual_comissao` decimal(5,2) NOT NULL DEFAULT 50.00
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `cliente_colaboradores`
--

INSERT INTO `cliente_colaboradores` (`cliente_id`, `colaborador_id`, `percentual_comissao`) VALUES
(1, 1, 50.00),
(2, 2, 50.00),
(3, 1, 50.00),
(4, 2, 50.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cobrancas`
--

CREATE TABLE `cobrancas` (
  `id` int(11) NOT NULL,
  `contrato_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `plano_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `referencia` varchar(7) NOT NULL,
  `pago` tinyint(1) DEFAULT 0,
  `data_pagamento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `cobrancas`
--

INSERT INTO `cobrancas` (`id`, `contrato_id`, `cliente_id`, `plano_id`, `valor`, `referencia`, `pago`, `data_pagamento`) VALUES
(1, 1, 1, 1, 500.00, '2025-12', 1, '2025-12-25'),
(2, 1, 1, 1, 500.00, '2026-01', 0, NULL),
(3, 1, 1, 1, 500.00, '2026-02', 0, NULL),
(4, 2, 2, 1, 500.00, '2025-10', 1, '2025-12-27'),
(5, 2, 2, 1, 500.00, '2025-11', 1, '2025-12-27'),
(6, 2, 2, 1, 500.00, '2025-12', 1, '2025-12-27'),
(7, 2, 2, 1, 500.00, '2026-01', 0, NULL),
(8, 3, 3, 1, 500.00, '2025-09', 1, '2025-12-27'),
(9, 3, 3, 1, 500.00, '2025-10', 1, '2025-12-27'),
(10, 3, 3, 1, 500.00, '2025-11', 1, '2025-12-27'),
(11, 3, 3, 1, 500.00, '2025-12', 1, '2025-12-27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `colaboradores`
--

CREATE TABLE `colaboradores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(15) NOT NULL,
  `funcao` enum('locutor','socio','socio_locutor','parceiro') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `colaboradores`
--

INSERT INTO `colaboradores` (`id`, `nome`, `email`, `telefone`, `funcao`) VALUES
(1, 'Julio Cezar da Costa Reis', 'djjulioreis@yahoo.com.br', '41998652797', 'socio_locutor'),
(2, 'Paulo Patrick', 'pauloagrale@gmail.com', '41 99678-2804', 'socio_locutor'),
(3, 'José Antonio Coelho', 'naturenge@onda.com.br', '41 99152-7233', 'socio'),
(4, 'Claudino Nunes', 'claudinonunes@gmail.com', '41 99277-6400', 'locutor'),
(5, 'Omar Emilio Ponczkovski', 'omaroepemilio@gmail.com', '41 99536-0393', 'locutor'),
(6, 'Adones Berlim', 'adonesberlim@novafm.com.br', '41 9873-9881', 'locutor'),
(7, 'Ronaldo Kunhl', 'ronaldokunhl@gmail.com', '41 99772-3368', 'locutor'),
(8, 'Gil Ribeiro', 'grproducoes136@gmail.com', '41 98896-4826', 'locutor');

-- --------------------------------------------------------

--
-- Estrutura para tabela `comerciais`
--

CREATE TABLE `comerciais` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `caminho_arquivo` varchar(255) NOT NULL,
  `duracao` int(11) NOT NULL COMMENT 'Em segundos',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `contratos`
--

CREATE TABLE `contratos` (
  `id` int(11) NOT NULL,
  `identificacao` varchar(255) DEFAULT NULL,
  `cliente_id` int(11) NOT NULL,
  `plano_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `contratos`
--

INSERT INTO `contratos` (`id`, `identificacao`, `cliente_id`, `plano_id`, `valor`, `data_inicio`, `data_fim`) VALUES
(1, 'Mercado Brasil', 1, 1, 500.00, '2025-12-16', '2026-02-16'),
(2, 'D  Rose', 2, 1, 500.00, '2025-10-10', '2026-01-10'),
(3, 'KD Materiais', 3, 1, 500.00, '2025-09-10', '2025-12-10'),
(4, 'Parada 20', 4, 1, 500.00, '2025-10-10', '2026-01-10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `despesas`
--

CREATE TABLE `despesas` (
  `id` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `tipo` enum('fixa','normal') NOT NULL,
  `pago` tinyint(1) DEFAULT 0,
  `observacao` text DEFAULT NULL,
  `recibo_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `despesas`
--

INSERT INTO `despesas` (`id`, `descricao`, `valor`, `data_vencimento`, `data_pagamento`, `tipo`, `pago`, `observacao`, `recibo_path`) VALUES
(1, 'Arrendamento Radio', 2700.00, '2025-12-02', '2025-12-25', 'fixa', 1, 'Arrendamento da radio e aluguel', NULL),
(2, 'Craxa ', 30.00, '2025-12-27', '2025-12-27', 'normal', 1, 'Crachas Adones e Gil', 'uploads/2025/12/694fedcf596434.23172532.jpeg'),
(3, 'Agua dezembro', 77.68, '2025-12-22', '2025-12-27', 'fixa', 1, 'Agua', 'uploads/2025/12/694fee5275abf8.92060496.jpeg'),
(4, 'Luz dezembro', 181.45, '2025-12-09', '2025-12-27', 'fixa', 1, 'Conta luz', 'uploads/2025/12/694fee86b5ac83.69533316.jpeg');

-- --------------------------------------------------------

--
-- Estrutura para tabela `investimentos_socios`
--

CREATE TABLE `investimentos_socios` (
  `id` int(11) NOT NULL,
  `socio_id` int(11) NOT NULL,
  `tipo` enum('investimento','retirada') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data` date NOT NULL,
  `descricao` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `investimentos_socios`
--

INSERT INTO `investimentos_socios` (`id`, `socio_id`, `tipo`, `valor`, `data`, `descricao`) VALUES
(1, 2, 'investimento', 10809.00, '2025-09-01', 'Valores investios'),
(2, 2, 'investimento', 6636.73, '2025-12-27', 'Investimento em equipamentos e serviços'),
(3, 1, 'investimento', 3000.00, '2025-12-27', 'Sistema financeiro da Radio');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `target`, `target_id`, `timestamp`) VALUES
(1, 4, 'create', 'despesa', 1, '2025-12-18 05:21:01'),
(2, 4, 'create', 'despesa', 2, '2025-12-27 17:31:43'),
(3, 4, 'create', 'despesa', 3, '2025-12-27 17:33:54'),
(4, 4, 'create', 'despesa', 4, '2025-12-27 17:34:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos`
--

CREATE TABLE `planos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `insercoes_mes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `planos`
--

INSERT INTO `planos` (`id`, `nome`, `descricao`, `preco`, `insercoes_mes`) VALUES
(1, 'Basico', '210 inserções mensal, sendo 7 por dia, com direito a dois spots gravados por mês\r\nprazo mínimo contrato de 3 meses.', 500.00, 210),
(2, 'Profissional ', '450 inserções mensal, sendo 15 por dia, com direito a quatro spots gravados por mês\r\nprazo mínimo contrato de 3 meses.', 1000.00, 450),
(3, 'Plus', '1000 inserções mensal, sendo 30 por dia, com direito a quatro spots gravados por mês\r\nprazo mínimo contrato de 3 meses.\r\nInclui mais banners e divulgação em todos os projetos da Nova FM, incluso, site, podcast, eventos e redes sociais.', 2000.00, 1000),
(4, 'Apoio', '7 inserções por dia de 5 segundos cada, pacote mínimo de 3 meses, não tem troca de chamada.', 100.00, 210),
(5, 'Especial', 'Plano para clientes antigos que ja tinham contrato com a radio', 300.00, 180);

-- --------------------------------------------------------

--
-- Estrutura para tabela `socios`
--

CREATE TABLE `socios` (
  `id` int(11) NOT NULL,
  `colaborador_id` int(11) NOT NULL,
  `reinvestir_comissao` tinyint(1) NOT NULL DEFAULT 1,
  `saldo_investido` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `socios`
--

INSERT INTO `socios` (`id`, `colaborador_id`, `reinvestir_comissao`, `saldo_investido`) VALUES
(1, 1, 1, 3000.00),
(2, 2, 1, 17445.73),
(3, 3, 1, 6636.73);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipos_anuncio`
--

CREATE TABLE `tipos_anuncio` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel_acesso` enum('admin','visualizador') NOT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel_acesso`, `data_criacao`) VALUES
(1, 'Paulo', 'pauloagrale@gmail.com', '$2y$10$GNM1QmRp62O1U9w2V1oqtuUC528oLJ.l5wd3qWsQdlmeyrshdmeA.', 'admin', '2025-12-17 03:48:25'),
(2, 'Julio Reis', 'djjulioreis@yahoo.com.br', '$2y$10$7ZL80sUEPq9gvGmEvRUFeu7wqK0kLqC4eQt8x5.j1aOJ8g8QPyD72', 'admin', '2025-12-17 03:49:23'),
(3, 'Coelho', 'naturenge@gmail.com', '$2y$10$emFzZyff380GZGc6kzxaBOnBVpdAt2UcjrC0iIq3nwXg8aswMchIK', 'admin', '2025-12-17 03:55:09'),
(4, 'Admin', 'admin@test.com', '$2y$10$hV7e99opwQv6Md3KUWzNpevlzhjzIlM5dYQuug6ptebEmenojUeZy', 'admin', '2025-12-17 03:55:51');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comercial_id` (`comercial_id`);

--
-- Índices de tabela `apoios_clientes`
--
ALTER TABLE `apoios_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `apoio_id` (`apoio_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `apoios_culturais`
--
ALTER TABLE `apoios_culturais`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cnpj_cpf` (`cnpj_cpf`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `cliente_colaboradores`
--
ALTER TABLE `cliente_colaboradores`
  ADD PRIMARY KEY (`cliente_id`,`colaborador_id`),
  ADD KEY `colaborador_id` (`colaborador_id`);

--
-- Índices de tabela `cobrancas`
--
ALTER TABLE `cobrancas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contrato_id` (`contrato_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `plano_id` (`plano_id`);

--
-- Índices de tabela `colaboradores`
--
ALTER TABLE `colaboradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `comerciais`
--
ALTER TABLE `comerciais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `plano_id` (`plano_id`);

--
-- Índices de tabela `despesas`
--
ALTER TABLE `despesas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `investimentos_socios`
--
ALTER TABLE `investimentos_socios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `socio_id` (`socio_id`);

--
-- Índices de tabela `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `planos`
--
ALTER TABLE `planos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `socios`
--
ALTER TABLE `socios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `colaborador_id` (`colaborador_id`);

--
-- Índices de tabela `tipos_anuncio`
--
ALTER TABLE `tipos_anuncio`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `apoios_clientes`
--
ALTER TABLE `apoios_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `apoios_culturais`
--
ALTER TABLE `apoios_culturais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `cobrancas`
--
ALTER TABLE `cobrancas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `colaboradores`
--
ALTER TABLE `colaboradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `comerciais`
--
ALTER TABLE `comerciais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `despesas`
--
ALTER TABLE `despesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `investimentos_socios`
--
ALTER TABLE `investimentos_socios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `planos`
--
ALTER TABLE `planos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `socios`
--
ALTER TABLE `socios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `tipos_anuncio`
--
ALTER TABLE `tipos_anuncio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`comercial_id`) REFERENCES `comerciais` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `apoios_clientes`
--
ALTER TABLE `apoios_clientes`
  ADD CONSTRAINT `apoios_clientes_ibfk_1` FOREIGN KEY (`apoio_id`) REFERENCES `apoios_culturais` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `apoios_clientes_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cliente_colaboradores`
--
ALTER TABLE `cliente_colaboradores`
  ADD CONSTRAINT `cliente_colaboradores_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cliente_colaboradores_ibfk_2` FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cobrancas`
--
ALTER TABLE `cobrancas`
  ADD CONSTRAINT `cobrancas_ibfk_1` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`),
  ADD CONSTRAINT `cobrancas_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `cobrancas_ibfk_3` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`);

--
-- Restrições para tabelas `comerciais`
--
ALTER TABLE `comerciais`
  ADD CONSTRAINT `comerciais_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `contratos`
--
ALTER TABLE `contratos`
  ADD CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `contratos_ibfk_2` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`);

--
-- Restrições para tabelas `investimentos_socios`
--
ALTER TABLE `investimentos_socios`
  ADD CONSTRAINT `investimentos_socios_ibfk_1` FOREIGN KEY (`socio_id`) REFERENCES `colaboradores` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `socios`
--
ALTER TABLE `socios`
  ADD CONSTRAINT `socios_ibfk_1` FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
