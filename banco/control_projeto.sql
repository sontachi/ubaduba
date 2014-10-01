-- phpMyAdmin SQL Dump
-- version 4.1.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Tempo de geração: 02/04/2014 às 16:16
-- Versão do servidor: 5.5.36-cll
-- Versão do PHP: 5.4.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Banco de dados: `control_projeto`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE IF NOT EXISTS `alunos` (
  `ra` varchar(10) NOT NULL,
  `fk_curso` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `sexo` varchar(1) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`ra`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `alunos`
--

INSERT INTO `alunos` (`ra`, `fk_curso`, `nome`, `sexo`, `email`) VALUES
('11024783', 1, 'Renato Nori', 'M', 'renatoioshida@gmail.com'),
('11194982', 1, 'Vitor Novo', 'M', 'vitornovos@gmail.com'),
('12121314', 2, 'Henrique', 'M', 'eduardo@gmail.com'),
('1234', 2, 'Aluno', 'M', 'aluno@gmail.com');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE IF NOT EXISTS `cursos` (
  `pk_curso` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  PRIMARY KEY (`pk_curso`),
  KEY `pk_curso` (`pk_curso`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Fazendo dump de dados para tabela `cursos`
--

INSERT INTO `cursos` (`pk_curso`, `nome`) VALUES
(1, 'Engenharia de Computação'),
(2, 'Engenharia Civil'),
(3, 'Engenharia Ambiental'),
(4, 'kkk');

-- --------------------------------------------------------

--
-- Estrutura para tabela `horarios`
--

CREATE TABLE IF NOT EXISTS `horarios` (
  `pk_horario` int(11) NOT NULL AUTO_INCREMENT,
  `fk_turma` int(11) NOT NULL,
  `fk_sala` int(11) NOT NULL,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  PRIMARY KEY (`pk_horario`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Fazendo dump de dados para tabela `horarios`
--

INSERT INTO `horarios` (`pk_horario`, `fk_turma`, `fk_sala`, `data_inicio`, `data_fim`) VALUES
(1, 1, 1, '2014-04-09 08:00:00', '2014-04-09 10:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logins`
--

CREATE TABLE IF NOT EXISTS `logins` (
  `usuario` varchar(20) NOT NULL,
  `permissao` int(11) NOT NULL,
  `senha` varchar(100) NOT NULL,
  PRIMARY KEY (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `logins`
--

INSERT INTO `logins` (`usuario`, `permissao`, `senha`) VALUES
('1234', 1, '81dc9bdb52d04dc20036dbd8313ed055'),
('3', 2, '88052b22c8c2349c0599bd39a654c534'),
('admin', 10, '88052b22c8c2349c0599bd39a654c534');

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_acesso`
--

CREATE TABLE IF NOT EXISTS `log_acesso` (
  `pk_acesso` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(20) NOT NULL,
  `navegador` text NOT NULL,
  `usuario` varchar(20) NOT NULL,
  `data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL,
  `permissao` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pk_acesso`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Fazendo dump de dados para tabela `log_acesso`
--

INSERT INTO `log_acesso` (`pk_acesso`, `ip`, `navegador`, `usuario`, `data`, `status`, `permissao`) VALUES
(1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.74.9 (KHTML, like Gecko) Version/7.0.2 Safari/537.74.9', 'admin', '2014-04-02 18:13:28', 1, 10),
(2, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.74.9 (KHTML, like Gecko) Version/7.0.2 Safari/537.74.9', '3', '2014-04-02 18:15:54', 1, 2),
(3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36', '3', '2014-04-02 18:28:18', 1, 2),
(4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36', '3', '2014-04-02 18:29:59', 1, 2),
(5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36', '3', '2014-04-02 18:30:21', 1, 2),
(6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36', 'admin', '2014-04-02 18:30:44', 1, 10),
(7, '127.0.0.1', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36', 'admin', '2014-04-02 18:43:34', 1, 10),
(8, '127.0.0.1', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36', 'admin', '2014-04-02 18:44:15', 1, 10),
(9, '127.0.0.1', 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36', 'admin', '2014-04-02 18:46:57', 1, 10),
(10, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.74.9 (KHTML, like Gecko) Version/7.0.2 Safari/537.74.9', 'admin', '2014-04-02 18:50:49', 1, 10);

-- --------------------------------------------------------

--
-- Estrutura para tabela `materias`
--

CREATE TABLE IF NOT EXISTS `materias` (
  `pk_materia` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `creditos` int(11) NOT NULL,
  PRIMARY KEY (`pk_materia`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Fazendo dump de dados para tabela `materias`
--

INSERT INTO `materias` (`pk_materia`, `nome`, `creditos`) VALUES
(1, 'Cálculo A', 4),
(2, 'Cálculo I', 4),
(3, 'Sistemas de Informação', 4);

-- --------------------------------------------------------

--
-- Estrutura para tabela `professores`
--

CREATE TABLE IF NOT EXISTS `professores` (
  `rp` varchar(10) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `sexo` varchar(1) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`rp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `professores`
--

INSERT INTO `professores` (`rp`, `nome`, `sexo`, `email`) VALUES
('3', 'Daniela', 'M', 'daniela@daniela.com'),
('P1000', 'Otávio', 'M', 'otavio@gmail.com'),
('P1001', 'Juan', 'M', 'juan@gmail.com'),
('P1002', 'Fernando K', 'M', 'fek@gmail.com'),
('P1003', 'Thiago Aguirre', 'M', 'thiago@gmail.com');

-- --------------------------------------------------------

--
-- Estrutura para tabela `rel_turmas_alunos`
--

CREATE TABLE IF NOT EXISTS `rel_turmas_alunos` (
  `fk_turma` int(11) NOT NULL,
  `ra` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Fazendo dump de dados para tabela `rel_turmas_alunos`
--

INSERT INTO `rel_turmas_alunos` (`fk_turma`, `ra`) VALUES
(1, 1234),
(1, 12121314),
(1, 11024783);

-- --------------------------------------------------------

--
-- Estrutura para tabela `salas`
--

CREATE TABLE IF NOT EXISTS `salas` (
  `pk_sala` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(30) NOT NULL,
  PRIMARY KEY (`pk_sala`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Fazendo dump de dados para tabela `salas`
--

INSERT INTO `salas` (`pk_sala`, `nome`) VALUES
(1, 'A03'),
(2, 'B87');

-- --------------------------------------------------------

--
-- Estrutura para tabela `turmas`
--

CREATE TABLE IF NOT EXISTS `turmas` (
  `pk_turma` int(11) NOT NULL AUTO_INCREMENT,
  `fk_materia` int(11) NOT NULL,
  `rp` varchar(11) NOT NULL,
  `fk_curso` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  PRIMARY KEY (`pk_turma`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

--
-- Fazendo dump de dados para tabela `turmas`
--

INSERT INTO `turmas` (`pk_turma`, `fk_materia`, `rp`, `fk_curso`, `numero`, `data_inicio`, `data_fim`) VALUES
(1, 1, 'P1000', 1, '101011', '0000-00-00', '0000-00-00'),
(2, 1, 'P1001', 1, '101012', '0000-00-00', '0000-00-00'),
(4, 2, 'P1000', 1, '1', '0000-00-00', '0000-00-00'),
(5, 2, 'P1003', 1, '111236', '0000-00-00', '0000-00-00'),
(6, 3, 'P1003', 3, '1243', '0000-00-00', '0000-00-00');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
