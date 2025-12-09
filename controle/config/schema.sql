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
  `cliente_id` int(11) NOT NULL,
  `plano_id` int(11) NOT NULL,
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
    `tipo` ENUM('fixa', 'normal') NOT NULL,
    `pago` BOOLEAN DEFAULT FALSE,
    `observacao` TEXT,
    `recibo_path` VARCHAR(255) DEFAULT NULL
);

CREATE TABLE `locutores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
);

CREATE TABLE `clientes_locutores` (
  `cliente_id` int(11) NOT NULL,
  `locutor_id` int(11) NOT NULL,
  PRIMARY KEY (`cliente_id`,`locutor_id`),
  KEY `locutor_id` (`locutor_id`)
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


ALTER TABLE `clientes_locutores`
  ADD CONSTRAINT `clientes_locutores_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `clientes_locutores_ibfk_2` FOREIGN KEY (`locutor_id`) REFERENCES `locutores` (`id`);

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
