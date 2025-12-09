-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 08/12/2025 às 22:27
-- Versão do servidor: 10.11.14-MariaDB-cll-lve
-- Versão do PHP: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `novaf_radio_fm`
--

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
  `ativo` tinyint(1) DEFAULT 1,
  `plano_id` int(11) NOT NULL,
  `data_vencimento` tinyint(4) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `empresa`, `cnpj_cpf`, `email`, `telefone`, `endereco`, `credito_permuta`, `saldo_permuta`, `data_cadastro`, `ativo`, `plano_id`, `data_vencimento`) VALUES
(1, 'KD Materiais de Construção', '21.092.775/0001-04', 'kdconstrucenter@gmail.com', '41 99717-1490', 'Avenida Sebastião Caboto, 9092 - Shangri-lá 83255-000 - Pontal do Paraná - PR', 2980.23, 1980.23, '2025-09-10 19:46:18', 0, 1, 10),
(17, 'julio reis', '744.892.089-87', 'djjulioreis@yahoo.com.br', '(41) 99865-2797', 'Nelson Medrado, 175', 0.00, 0.00, '2025-12-07 00:00:00', 1, 2, 1),
(18, 'julio reis', '172.895.658-55', 'djjulioreis@hotmail.com', '(41) 99865-2797', 'Nelson Medrado, 125', 0.00, 0.00, '2025-12-07 00:00:00', 1, 1, 10);

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes_locutores`
--

CREATE TABLE `clientes_locutores` (
  `cliente_id` int(11) NOT NULL,
  `locutor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `clientes_locutores`
--

INSERT INTO `clientes_locutores` (`cliente_id`, `locutor_id`) VALUES
(1, 1);

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
(3, 1, 1, 1, 0.00, '2025-12', 1, NULL),
(18, 13, 17, 1, 48.39, '2025-12', 0, NULL),
(19, 13, 17, 1, 500.00, '2026-01', 0, NULL),
(20, 14, 18, 1, 48.39, '2025-12', 0, NULL),
(21, 14, 18, 1, 500.00, '2026-01', 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `contratos`
--

CREATE TABLE `contratos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `plano_id` int(11) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `contratos`
--

INSERT INTO `contratos` (`id`, `cliente_id`, `plano_id`, `data_inicio`, `data_fim`) VALUES
(1, 1, 1, '2025-10-06', '2026-01-06'),
(13, 17, 1, '2025-12-07', '2026-01-07'),
(14, 18, 1, '2025-12-07', '2026-01-07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `despesas`
--

CREATE TABLE `despesas` (
  `id` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `tipo` enum('fixa','normal') NOT NULL,
  `pago` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `despesas`
--

INSERT INTO `despesas` (`id`, `descricao`, `valor`, `data_vencimento`, `tipo`, `pago`) VALUES
(1, 'Arrendamento Radio', 2700.00, '2025-12-01', 'fixa', 1),
(2, 'Streaming video audio', 69.98, '2025-12-04', 'fixa', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `locutores`
--

CREATE TABLE `locutores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `locutores`
--

INSERT INTO `locutores` (`id`, `nome`, `email`, `telefone`) VALUES
(1, 'Julio Reis', 'djjulioreis@yahoo.com.br', '41 99865-2797'),
(2, 'Paulo Patrick', 'pauloagrale@gmail.com', '41 9678-2804'),
(3, 'Gil Ribeiro', 'grproducoes136@gmail.com', '41 98896-4826'),
(4, 'Luiz Carlos - Guaraguaçu', 'Mirandaluizcarlos907@gmail.com', '41 98465-0099'),
(5, 'Claudino Nunes', 'claudinonunes0@gmail.com', '41 9277-6400');

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
(2, 'Empresarial', '450 inserções mensal, sendo 15 por dia, com direito a quatro spots gravados por mês\r\nprazo mínimo contrato de 3 meses.', 1000.00, 450),
(3, 'Plus', '1000 inserções mensal, sendo 30 por dia, com direito a quatro spots gravados por mês\r\nprazo mínimo contrato de 3 meses.\r\n', 2000.00, 1000);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipos_anuncio`
--

CREATE TABLE `tipos_anuncio` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Despejando dados para a tabela `tipos_anuncio`
--

INSERT INTO `tipos_anuncio` (`id`, `nome`) VALUES
(1, 'Spot de Rádio'),
(2, 'Banner Online'),
(3, 'Promoção Especial');

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
(1, 'Admin', 'admin@test.com', '$2y$10$G9ibktqzcLHXG3HDJvHk9OrW3SLcbHn5aZ6OdvooZT3urxqdw62M6', 'admin', '2025-12-06 02:01:21'),
(2, 'Paulo Patrick', 'pauloagrale@gmail.com', '$2y$10$bKdKHhP.rrYvVovPJUFVTO.ZdpGIfxe2q0.6TGJxnqR/53jOwaRGu', 'visualizador', '2025-12-07 23:01:07');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cnpj_cpf` (`cnpj_cpf`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `clientes_locutores`
--
ALTER TABLE `clientes_locutores`
  ADD PRIMARY KEY (`cliente_id`,`locutor_id`),
  ADD KEY `locutor_id` (`locutor_id`);

--
-- Índices de tabela `cobrancas`
--
ALTER TABLE `cobrancas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contrato_id` (`contrato_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `plano_id` (`plano_id`);

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
-- Índices de tabela `locutores`
--
ALTER TABLE `locutores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `planos`
--
ALTER TABLE `planos`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `cobrancas`
--
ALTER TABLE `cobrancas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `despesas`
--
ALTER TABLE `despesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `locutores`
--
ALTER TABLE `locutores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `planos`
--
ALTER TABLE `planos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `tipos_anuncio`
--
ALTER TABLE `tipos_anuncio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `clientes_locutores`
--
ALTER TABLE `clientes_locutores`
  ADD CONSTRAINT `clientes_locutores_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `clientes_locutores_ibfk_2` FOREIGN KEY (`locutor_id`) REFERENCES `locutores` (`id`);

--
-- Restrições para tabelas `cobrancas`
--
ALTER TABLE `cobrancas`
  ADD CONSTRAINT `cobrancas_ibfk_1` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`),
  ADD CONSTRAINT `cobrancas_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `cobrancas_ibfk_3` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`);

--
-- Restrições para tabelas `contratos`
--
ALTER TABLE `contratos`
  ADD CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `contratos_ibfk_2` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
