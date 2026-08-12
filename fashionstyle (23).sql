-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 12/08/2026 às 16:19
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `looks`
--

INSERT INTO `looks` (`id`, `idusuario`, `nome`, `tags`, `cores`, `idroupa1`, `idroupa2`, `idroupa3`, `idroupa4`, `idroupa5`, `publicado`, `legenda`, `data_publicacao`) VALUES
(54, 15, 'Look rolê', 'festa', NULL, 97, 95, 96, NULL, NULL, 1, 'Saindo do feed direto pro rolê.', '2026-08-11 23:12:57');

-- --------------------------------------------------------

--
-- Estrutura para tabela `palavras_bloqueadas`
--

DROP TABLE IF EXISTS `palavras_bloqueadas`;
CREATE TABLE IF NOT EXISTS `palavras_bloqueadas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `palavra` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `palavra` (`palavra`)
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `palavras_bloqueadas`
--

INSERT INTO `palavras_bloqueadas` (`id`, `palavra`) VALUES
(1, 'idiota'),
(2, 'imbecil'),
(3, 'burro'),
(4, 'retardado'),
(5, 'otario'),
(6, 'babaca'),
(7, 'trouxa'),
(8, 'inutil'),
(9, 'nojento'),
(10, 'ridiculo'),
(11, 'patetico'),
(12, 'escroto'),
(13, 'lixo'),
(14, 'feio'),
(15, 'horroroso'),
(16, 'verme'),
(17, 'fracassado'),
(18, 'vagabundo'),
(19, 'vagabunda'),
(20, 'covarde'),
(21, 'canalha'),
(22, 'cretino'),
(23, 'palhaco'),
(24, 'animal'),
(25, 'doente'),
(26, 'maluco'),
(27, 'louco'),
(28, 'babão'),
(29, 'tapado'),
(30, 'anta'),
(31, 'otaria'),
(32, 'corno'),
(33, 'corna'),
(34, 'arrombado'),
(35, 'arrombada'),
(36, 'fdp'),
(37, 'filho da puta'),
(38, 'filha da puta'),
(39, 'puta'),
(40, 'puto'),
(41, 'merda'),
(42, 'bosta'),
(43, 'caralho'),
(44, 'cacete'),
(45, 'porra'),
(46, 'foda'),
(47, 'foder'),
(48, 'fudido'),
(49, 'fudida'),
(50, 'vai tomar no cu'),
(51, 'tomar no cu'),
(52, 'cuzao'),
(53, 'pau no cu'),
(54, 'desgracado'),
(55, 'desgracada'),
(56, 'infeliz'),
(57, 'miseravel'),
(58, 'safado'),
(59, 'safada'),
(60, 'pilantra'),
(61, 'sem nocao'),
(62, 'troglodita'),
(63, 'asno'),
(64, 'jumento'),
(65, 'energumeno'),
(66, 'abestado'),
(67, 'mongol'),
(68, 'debil'),
(69, 'imundo'),
(70, 'ridicula');

-- --------------------------------------------------------

--
-- Estrutura para tabela `palavras_proibidas`
--

DROP TABLE IF EXISTS `palavras_proibidas`;
CREATE TABLE IF NOT EXISTS `palavras_proibidas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `palavra` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `palavra` (`palavra`)
) ENGINE=MyISAM AUTO_INCREMENT=368 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `palavras_proibidas`
--

INSERT INTO `palavras_proibidas` (`id`, `palavra`) VALUES
(1, 'arrombada'),
(2, 'arrombado'),
(3, 'babaca'),
(4, 'bosta'),
(5, 'buceta'),
(6, 'bunda'),
(7, 'cacete'),
(8, 'cagado'),
(9, 'caralho'),
(10, 'corno'),
(11, 'cu'),
(12, 'desgraça'),
(13, 'fdp'),
(14, 'filha da puta'),
(15, 'filho da puta'),
(16, 'foda'),
(17, 'fodase'),
(18, 'foder'),
(19, 'merda'),
(20, 'otaria'),
(21, 'otario'),
(22, 'pau'),
(23, 'pau no cu'),
(24, 'pica'),
(25, 'piranha'),
(26, 'porra'),
(27, 'pqp'),
(28, 'puta'),
(29, 'puta que pariu'),
(30, 'putaria'),
(31, 'puto'),
(32, 'rapariga'),
(33, 'sacanagem'),
(34, 'safada'),
(35, 'safado'),
(36, 'vagabunda'),
(37, 'vagabundo'),
(38, 'vaitomarnocu'),
(39, 'vtnc'),
(40, 'anal'),
(41, 'anus'),
(42, 'arrombadas'),
(43, 'arrombados'),
(44, 'arrombar'),
(45, 'babacas'),
(46, 'bacurinha'),
(47, 'bagos'),
(48, 'biscate'),
(49, 'biscates'),
(50, 'bicha'),
(51, 'bichas'),
(52, 'boceta'),
(53, 'bocetas'),
(54, 'boquete'),
(55, 'boqueteira'),
(56, 'boqueteiro'),
(57, 'bostas'),
(58, 'bostana'),
(59, 'bucetas'),
(60, 'bucetão'),
(61, 'bucetinha'),
(62, 'bundão'),
(63, 'bundas'),
(64, 'caceta'),
(65, 'cacetes'),
(66, 'cagada'),
(67, 'cagados'),
(68, 'cagar'),
(69, 'cagão'),
(70, 'cagou'),
(71, 'carai'),
(72, 'caraio'),
(73, 'caralhada'),
(74, 'caralhos'),
(75, 'chereca'),
(76, 'chota'),
(77, 'chupada'),
(78, 'chupar'),
(79, 'chupeta'),
(80, 'clitoris'),
(81, 'corna'),
(82, 'cornas'),
(83, 'cornos'),
(84, 'cornuda'),
(85, 'cornudo'),
(86, 'cretina'),
(87, 'cretino'),
(88, 'culhao'),
(89, 'culhoes'),
(90, 'cuzão'),
(91, 'cuzinho'),
(92, 'desgraçada'),
(93, 'desgraçado'),
(94, 'desgraçados'),
(95, 'escrota'),
(96, 'escrotas'),
(97, 'escroto'),
(98, 'escrotos'),
(99, 'filha de uma puta'),
(100, 'filho de uma puta'),
(101, 'filhos da puta'),
(102, 'fodas'),
(103, 'foda-se'),
(104, 'fode'),
(105, 'fodedor'),
(106, 'foderam'),
(107, 'fodeu'),
(108, 'fodida'),
(109, 'fodidas'),
(110, 'fodido'),
(111, 'fodidos'),
(112, 'fudendo'),
(113, 'fuder'),
(114, 'fudeu'),
(115, 'fudida'),
(116, 'fudido'),
(117, 'glozinho'),
(118, 'gospideira'),
(119, 'gostosa'),
(120, 'gostosão'),
(121, 'grelo'),
(122, 'grelos'),
(123, 'idiota'),
(124, 'idiotas'),
(125, 'imbecil'),
(126, 'imbecis'),
(127, 'kenga'),
(128, 'krl'),
(129, 'krlh'),
(130, 'lixo'),
(131, 'lixos'),
(132, 'mamada'),
(133, 'mamar'),
(134, 'marmita'),
(135, 'masturbar'),
(136, 'masturbação'),
(137, 'merdas'),
(138, 'meretriz'),
(139, 'meu caralho'),
(140, 'meu pau'),
(141, 'mijo'),
(142, 'miseravel'),
(143, 'miseraveis'),
(144, 'morde fronha'),
(145, 'mula'),
(146, 'otarias'),
(147, 'otarios'),
(148, 'paunocu'),
(149, 'paus'),
(150, 'picas'),
(151, 'picão'),
(152, 'paspalho'),
(153, 'piranhas'),
(154, 'piroca'),
(155, 'pirocas'),
(156, 'pirocão'),
(157, 'piru'),
(158, 'pissa'),
(159, 'porras'),
(160, 'prepucio'),
(161, 'prostituta'),
(162, 'prostituto'),
(163, 'prostitutas'),
(164, 'punheta'),
(165, 'punheteira'),
(166, 'punheteiro'),
(167, 'putas'),
(168, 'putarias'),
(169, 'putos'),
(170, 'putaquepariu'),
(171, 'quenga'),
(172, 'quengas'),
(173, 'rabão'),
(174, 'rabo'),
(175, 'rabos'),
(176, 'raparigas'),
(177, 'rola'),
(178, 'rolas'),
(179, 'rolão'),
(180, 'rosquinha'),
(181, 'safadas'),
(182, 'safados'),
(183, 'sapatão'),
(184, 'siririca'),
(185, 'siriricas'),
(186, 'tarada'),
(187, 'tarado'),
(188, 'tarados'),
(189, 'teta'),
(190, 'tetas'),
(191, 'tetona'),
(192, 'tmnc'),
(193, 'traveco'),
(194, 'travecos'),
(195, 'trouxa'),
(196, 'trouxas'),
(197, 'vagabundas'),
(198, 'vagabundos'),
(199, 'vai se foder'),
(200, 'vai se fuder'),
(201, 'vai tomar no cu'),
(202, 'vfd'),
(203, 'viado'),
(204, 'viados'),
(205, 'veado'),
(206, 'veados'),
(207, 'vsf'),
(208, 'xavasca'),
(209, 'xereca'),
(210, 'xerecas'),
(211, 'xota'),
(212, 'xotas'),
(213, 'xoxota'),
(214, 'xoxotas'),
(215, 'abestada'),
(216, 'abestado'),
(217, 'abobada'),
(218, 'abobado'),
(219, 'analfalbeta'),
(220, 'analfabeto'),
(221, 'animal'),
(222, 'anta'),
(223, 'arrombadinha'),
(224, 'arrombadinho'),
(225, 'asno'),
(226, 'baitola'),
(227, 'baitolas'),
(228, 'bandida'),
(229, 'bandido'),
(230, 'biscatinha'),
(231, 'boçal'),
(232, 'bocó'),
(233, 'bosta seca'),
(234, 'broxa'),
(235, 'broxas'),
(236, 'broxante'),
(237, 'bunda mole'),
(238, 'bundamole'),
(239, 'cabaça'),
(240, 'cabaço'),
(241, 'cabra machorra'),
(242, 'cadela'),
(243, 'cadelas'),
(244, 'cagador'),
(245, 'cagadona'),
(246, 'caipira'),
(247, 'canalha'),
(248, 'canalhas'),
(249, 'chata'),
(250, 'chato'),
(251, 'chibata'),
(252, 'chibatas'),
(253, 'chupador'),
(254, 'chupadora'),
(255, 'cornuto'),
(256, 'crápula'),
(257, 'cuzona'),
(258, 'cuzonas'),
(259, 'desgraçadão'),
(260, 'desgraçadinha'),
(261, 'diabo'),
(262, 'égua'),
(263, 'escória'),
(264, 'estúpida'),
(265, 'estúpido'),
(266, 'fedida'),
(267, 'fedido'),
(268, 'filho de uma égua'),
(269, 'fio da puta'),
(270, 'fiodaputa'),
(271, 'fodão'),
(272, 'fodedora'),
(273, 'fudeção'),
(274, 'fuleco'),
(275, 'galinha'),
(276, 'galinhas'),
(277, 'gentalha'),
(278, 'idiotice'),
(279, 'imbecilidade'),
(280, 'inútil'),
(281, 'inúteis'),
(282, 'jumenta'),
(283, 'jumento'),
(284, 'lacaio'),
(285, 'lambe saco'),
(286, 'lambesaco'),
(287, 'lesada'),
(288, 'lesado'),
(289, 'lixo humano'),
(290, 'mamadora'),
(291, 'mamador'),
(292, 'mané'),
(293, 'marmita de malandro'),
(294, 'merdinha'),
(295, 'mequetrefe'),
(296, 'miseravelzinho'),
(297, 'mocorongo'),
(298, 'moleque'),
(299, 'nazista'),
(300, 'necrófilo'),
(301, 'pamonha'),
(302, 'parasita'),
(303, 'pateta'),
(304, 'pau de arara'),
(305, 'pau mole'),
(306, 'paumole'),
(307, 'pedófilo'),
(308, 'pênis'),
(309, 'peste'),
(310, 'pestilento'),
(311, 'pica mole'),
(312, 'picamole'),
(313, 'pilantra'),
(314, 'pinto'),
(315, 'pintos'),
(316, 'pintão'),
(317, 'porcaria'),
(318, 'psicopata'),
(319, 'putaça'),
(320, 'putona'),
(321, 'rabuda'),
(322, 'rabudo'),
(323, 'rançoso'),
(324, 'rombudo'),
(325, 'sarnento'),
(326, 'sêmen'),
(327, 'sodomia'),
(328, 'sodomita'),
(329, 'taradão'),
(330, 'tesão'),
(331, 'traste'),
(332, 'trepada'),
(333, 'trepador'),
(334, 'trepadora'),
(335, 'trepar'),
(336, 'vaca'),
(337, 'vacas'),
(338, 'vadia'),
(339, 'vadias'),
(340, 'vadio'),
(341, 'vadios'),
(342, 'vagabundagem'),
(343, 'vagina'),
(344, 'vaginas'),
(345, 'verme'),
(346, 'vermes'),
(347, 'xilindró'),
(348, 'zé arruela'),
(349, 'zerruela'),
(350, 'b1cha'),
(351, 'b0sta'),
(352, 'buc3ta'),
(353, 'c4ralho'),
(354, 'p0rra'),
(355, 'p1ca'),
(356, 'p1roca'),
(357, 'pvt4'),
(358, 'pvt0'),
(359, 'v1ado'),
(360, 'v14do'),
(361, 'x3reca'),
(362, 'p.u.t.a'),
(363, 'f.d.p'),
(364, 'c.a.r.a.l.h.o'),
(365, 'p.o.r.r.a'),
(366, 'v.t.n.c'),
(367, 'v.s.f');

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
  `tags` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idusuario` (`idusuario`),
  KEY `idCategoria` (`idCategoria`),
  KEY `fk_roupas_estacao` (`idEstacao`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `roupas`
--

INSERT INTO `roupas` (`id`, `idusuario`, `foto`, `cor1`, `cor2`, `idCategoria`, `idEstacao`, `estacoes`, `tags`) VALUES
(95, 15, 'uploads/roupas/roupa_6a7bce44d0907.jpg', '#4f5057', '#ffffff', 14, NULL, '1,2,3,4', '0'),
(96, 15, 'uploads/roupas/roupa_6a7bd3e1927c4.jpg', '#0b0b0b', '#ffffff', 29, NULL, '1,2,3,4', 'Festas casuais'),
(97, 15, 'uploads/roupas/roupa_6a7bd3f6196e3.jpg', '#000000', '#ffffff', 12, NULL, '1', 'vintage');

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tendencias`
--

DROP TABLE IF EXISTS `tendencias`;
CREATE TABLE IF NOT EXISTS `tendencias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `categoria` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mes` int NOT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `nome_usuario`, `email`, `cpf`, `senha`, `token_redefinicao`, `token_expires_at`, `foto`, `telefone`, `data_nascimento`, `nivel_acesso`) VALUES
(15, 'Emanuelle soares', 'manusoaresf1', 'emanuelle.2023325655@aluno.iffar.edu.br', '03218742005', 'batata123', NULL, NULL, 'uploads/perfis/15_1786486152.jpg', '55 9685-6206', '2008-02-19', 'admin'),
(16, 'Alicia monteiro', 'alicia.2026', 'Aliciamonteiro@gmail.com', '01354909011', '123456', NULL, NULL, 'uploads/alicia.2026_1786535609.jpg', '55 99489717', '2000-08-19', 'usuario');

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
