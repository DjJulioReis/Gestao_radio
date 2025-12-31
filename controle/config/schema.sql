CREATE DATABASE IF NOT EXISTS novaf_radio_fm;
USE novaf_radio_fm;

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel_acesso` enum('admin','visualizador') NOT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
);

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa` varchar(100) NOT NULL,
  `cnpj_cpf` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` varchar(200) DEFAULT NULL,
  `credito_permuta` decimal(10,2) DEFAULT 0.00,
  `saldo_permuta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `data_cadastro` datetime NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cnpj_cpf` (`cnpj_cpf`),
  UNIQUE KEY `email` (`email`)
);

CREATE TABLE `planos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `insercoes_mes` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `tipos_anuncio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `contratos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identificacao` varchar(255) DEFAULT NULL,
  `cliente_id` int(11) NOT NULL,
  `plano_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `plano_id` (`plano_id`)
);

CREATE TABLE `cobrancas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contrato_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `plano_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `referencia` varchar(7) NOT NULL,
  `pago` tinyint(1) DEFAULT 0,
  `data_pagamento` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contrato_id` (`contrato_id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `plano_id` (`plano_id`)
);

CREATE TABLE `despesas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `descricao` VARCHAR(255) NOT NULL,
    `valor` DECIMAL(10, 2) NOT NULL,
    `data_vencimento` DATE NOT NULL,
    `data_pagamento` DATE DEFAULT NULL,
    `tipo` ENUM('fixa', 'normal') NOT NULL,
    `pago` BOOLEAN DEFAULT FALSE,
    `observacao` TEXT,
    `recibo_path` VARCHAR(255) DEFAULT NULL
);

CREATE TABLE `colaboradores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(15) NOT NULL,
  `funcao` enum('locutor','socio','socio_locutor','parceiro') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
);

CREATE TABLE `socios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `colaborador_id` int(11) NOT NULL,
  `reinvestir_comissao` tinyint(1) NOT NULL DEFAULT 1,
  `saldo_investido` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `colaborador_id` (`colaborador_id`),
  CONSTRAINT `socios_ibfk_1` FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`) ON DELETE CASCADE
);

CREATE TABLE `cliente_colaboradores` (
  `cliente_id` int(11) NOT NULL,
  `colaborador_id` int(11) NOT NULL,
  `percentual_comissao` decimal(5,2) NOT NULL DEFAULT 50.00,
  PRIMARY KEY (`cliente_id`,`colaborador_id`),
  KEY `colaborador_id` (`colaborador_id`),
  CONSTRAINT `cliente_colaboradores_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cliente_colaboradores_ibfk_2` FOREIGN KEY (`colaborador_id`) REFERENCES `colaboradores` (`id`) ON DELETE CASCADE
);

CREATE TABLE `logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  `target` VARCHAR(255) NOT NULL,
  `target_id` INT,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES usuarios(id)
);



ALTER TABLE `cobrancas`
  ADD CONSTRAINT `cobrancas_ibfk_1` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`),
  ADD CONSTRAINT `cobrancas_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `cobrancas_ibfk_3` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`);

ALTER TABLE `contratos`
  ADD CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `contratos_ibfk_2` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`);

CREATE TABLE `apoios_culturais` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome_projeto` VARCHAR(255) NOT NULL,
  `descricao` TEXT,
  `meta_arrecadacao` DECIMAL(10, 2) DEFAULT 0.00,
  `data_inicio` DATE,
  `data_fim` DATE,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `apoios_clientes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `apoio_id` INT NOT NULL,
  `cliente_id` INT NOT NULL,
  `valor_doado` DECIMAL(10, 2) NOT NULL,
  `forma_anuncio` TEXT,
  `data_apoio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`apoio_id`) REFERENCES `apoios_culturais`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE
);

CREATE TABLE `investimentos_socios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `socio_id` int(11) NOT NULL,
  `tipo` enum('investimento','retirada') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data` date NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `socio_id` (`socio_id`),
  CONSTRAINT `investimentos_socios_ibfk_1` FOREIGN KEY (`socio_id`) REFERENCES `colaboradores` (`id`) ON DELETE CASCADE
);

CREATE TABLE `comerciais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `identificador_arquivo` varchar(255) NOT NULL COMMENT 'Ex: COMERCIAL_CLIENTE_X.mp3',
  `duracao` int(11) NOT NULL COMMENT 'Em segundos',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `comerciais_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
);

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comercial_id` int(11) NOT NULL,
  `horario_programado` datetime NOT NULL,
  `status` enum('pendente','executado','cancelado') NOT NULL DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `comercial_id` (`comercial_id`),
  CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`comercial_id`) REFERENCES `comerciais` (`id`) ON DELETE CASCADE
);
