-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 26/06/2026 às 19:54
-- Versão do servidor: 8.4.7
-- Versão do PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `fashionstyle`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`) VALUES
(9, 'Camisa/Camiseta'),
(10, 'Regata'),
(11, 'Top'),
(12, 'Cropped'),
(13, 'Casaco'),
(14, 'Calça'),
(15, 'Short'),
(16, 'Saia'),
(17, 'Bermuda'),
(18, 'Vestido'),
(19, 'Macacão'),
(20, 'Body'),
(21, 'Biquíni (Parte de Cima)'),
(22, 'Biquíni (Parte de Baixo)'),
(23, 'Maiô'),
(24, 'Canga'),
(26, 'Tênis'),
(27, 'Sandália'),
(28, 'Salto'),
(29, 'Bota'),
(30, 'Bolsa'),
(32, 'Óculos'),
(33, 'Chapéu');

-- --------------------------------------------------------

--
-- Estrutura para tabela `comunidade_comentarios`
--

DROP TABLE IF EXISTS `comunidade_comentarios`;
CREATE TABLE IF NOT EXISTS `comunidade_comentarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idlook` int NOT NULL,
  `idusuario` int NOT NULL,
  `comentario` text NOT NULL,
  `data_comentario` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idlook` (`idlook`),
  KEY `idusuario` (`idusuario`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comunidade_curtidas`
--

DROP TABLE IF EXISTS `comunidade_curtidas`;
CREATE TABLE IF NOT EXISTS `comunidade_curtidas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idlook` int NOT NULL,
  `idusuario` int NOT NULL,
  `data_curtida` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_look` (`idlook`,`idusuario`),
  KEY `idusuario` (`idusuario`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `comunidade_curtidas`
--

INSERT INTO `comunidade_curtidas` (`id`, `idlook`, `idusuario`, `data_curtida`) VALUES
(23, 51, 12, '2026-06-26 18:45:31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `estacoes`
--

DROP TABLE IF EXISTS `estacoes`;
CREATE TABLE IF NOT EXISTS `estacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `estacoes`
--

INSERT INTO `estacoes` (`id`, `nome`) VALUES
(1, 'Verão'),
(2, 'Inverno'),
(3, 'Primavera'),
(4, 'Outono');

-- --------------------------------------------------------

--
-- Estrutura para tabela `looks`
--

DROP TABLE IF EXISTS `looks`;
CREATE TABLE IF NOT EXISTS `looks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idusuario` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `tags` text,
  `cores` varchar(255) DEFAULT NULL,
  `idroupa1` int DEFAULT NULL,
  `idroupa2` int DEFAULT NULL,
  `idroupa3` int DEFAULT NULL,
  `idroupa4` int DEFAULT NULL,
  `idroupa5` int DEFAULT NULL,
  `publicado` tinyint(1) NOT NULL DEFAULT '0',
  `legenda` varchar(255) DEFAULT NULL,
  `data_publicacao` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idusuario` (`idusuario`),
  KEY `idroupa1` (`idroupa1`),
  KEY `idroupa2` (`idroupa2`),
  KEY `idroupa3` (`idroupa3`),
  KEY `fk_looks_roupa5` (`idroupa5`),
  KEY `fk_looks_roupa4` (`idroupa4`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `looks`
--

INSERT INTO `looks` (`id`, `idusuario`, `nome`, `tags`, `cores`, `idroupa1`, `idroupa2`, `idroupa3`, `idroupa4`, `idroupa5`, `publicado`, `legenda`, `data_publicacao`) VALUES
(51, 12, 'Looks simples', 'Sair', NULL, 80, 81, 82, 83, NULL, 1, 'Look simples, para sair em um dia calor', '2026-06-26 15:42:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `roupas`
--

DROP TABLE IF EXISTS `roupas`;
CREATE TABLE IF NOT EXISTS `roupas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idusuario` int NOT NULL,
  `foto` varchar(255) NOT NULL,
  `cor1` varchar(20) DEFAULT NULL,
  `cor2` varchar(20) DEFAULT NULL,
  `idCategoria` int NOT NULL,
  `idEstacao` int DEFAULT NULL,
  `estacoes` varchar(255) DEFAULT NULL,
  `tags` text,
  PRIMARY KEY (`id`),
  KEY `idusuario` (`idusuario`),
  KEY `idCategoria` (`idCategoria`),
  KEY `fk_roupas_estacao` (`idEstacao`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `roupas`
--

INSERT INTO `roupas` (`id`, `idusuario`, `foto`, `cor1`, `cor2`, `idCategoria`, `idEstacao`, `estacoes`, `tags`) VALUES
(80, 12, 'uploads/roupas/roupa_6a3ec48677b35.jpg', '#1b1b1b', '#ffffff', 11, NULL, '1', ''),
(81, 12, 'uploads/roupas/roupa_6a3ec4bb5e90a.jpg', '#6785af', '#ffffff', 16, NULL, '1', '0'),
(82, 12, 'uploads/roupas/roupa_6a3ec4e4c4299.jpg', '#000000', '#ffffff', 26, NULL, '1,2,3,4', ''),
(83, 12, 'uploads/roupas/roupa_6a3ec529e3bac.jpg', '#000000', '#ffffff', 32, NULL, '1,2,3,4', ''),
(84, 12, 'uploads/roupas/roupa_6a3ec5a165ba0.jpg', '#000000', '#ffffff', 19, NULL, '1,2,3,4', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `senha_reset`
--

DROP TABLE IF EXISTS `senha_reset`;
CREATE TABLE IF NOT EXISTS `senha_reset` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idusuario` int NOT NULL,
  `token` varchar(255) NOT NULL,
  `expira` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token` (`token`),
  KEY `fk_usuario_reset` (`idusuario`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `nome_usuario` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `token_redefinicao` varchar(255) DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `nivel_acesso` enum('usuario','admin') NOT NULL DEFAULT 'usuario',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome_usuario` (`nome_usuario`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `nome_usuario`, `email`, `cpf`, `senha`, `token_redefinicao`, `token_expires_at`, `foto`, `telefone`, `data_nascimento`, `nivel_acesso`) VALUES
(10, 'Manu', 'manusoaresf1', 'emanuelle.2023325655@aluno.iffar.edu.br', '03218742005', '$2y$10$6Qms4Ogtpb1v4wDXWi7zH.yNwrxcU2STQAHHOb9ns5ehjOoxphLV.', NULL, NULL, NULL, '55 9685-6206', '2008-02-19', 'admin'),
(12, 'Alicia monteiro', 'Alicia_monteiro', 'Aliciamonteiro@gmail.com', '67311559006', 'alici123', NULL, NULL, NULL, '5422613146', '2008-02-17', 'usuario');

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `comunidade_comentarios`
--
ALTER TABLE `comunidade_comentarios`
  ADD CONSTRAINT `comunidade_comentarios_ibfk_1` FOREIGN KEY (`idlook`) REFERENCES `looks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comunidade_comentarios_ibfk_2` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `comunidade_curtidas`
--
ALTER TABLE `comunidade_curtidas`
  ADD CONSTRAINT `comunidade_curtidas_ibfk_1` FOREIGN KEY (`idlook`) REFERENCES `looks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comunidade_curtidas_ibfk_2` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `looks`
--
ALTER TABLE `looks`
  ADD CONSTRAINT `fk_looks_roupa1` FOREIGN KEY (`idroupa1`) REFERENCES `roupas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_looks_roupa2` FOREIGN KEY (`idroupa2`) REFERENCES `roupas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_looks_roupa3` FOREIGN KEY (`idroupa3`) REFERENCES `roupas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_looks_roupa4` FOREIGN KEY (`idroupa4`) REFERENCES `roupas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_looks_roupa5` FOREIGN KEY (`idroupa5`) REFERENCES `roupas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_looks_usuario` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `roupas`
--
ALTER TABLE `roupas`
  ADD CONSTRAINT `fk_roupas_categoria` FOREIGN KEY (`idCategoria`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_roupas_estacao` FOREIGN KEY (`idEstacao`) REFERENCES `estacoes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_roupas_usuario` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `senha_reset`
--
ALTER TABLE `senha_reset`
  ADD CONSTRAINT `fk_usuario_reset` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
