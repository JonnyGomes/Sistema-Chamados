-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           5.7.40-log - MySQL Community Server (GPL)
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              10.1.0.5464
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Copiando estrutura do banco de dados para chamados
CREATE DATABASE IF NOT EXISTS `chamados` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `chamados`;

-- Copiando estrutura para tabela chamados.chamado
CREATE TABLE IF NOT EXISTS `chamado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('interno','externo') COLLATE utf8mb4_unicode_ci DEFAULT 'interno',
  `status` enum('aberto','em_atendimento','concluido','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'aberto',
  `usuario_abertura_id` int(11) NOT NULL,
  `responsavel_id` int(11) DEFAULT NULL,
  `data_abertura` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_fechamento` datetime DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_chamado_usuario_abertura` (`usuario_abertura_id`),
  KEY `fk_chamado_responsavel` (`responsavel_id`),
  KEY `idx_chamado_status` (`status`),
  KEY `idx_chamado_tipo` (`tipo`),
  CONSTRAINT `fk_chamado_responsavel` FOREIGN KEY (`responsavel_id`) REFERENCES `system_users` (`id`),
  CONSTRAINT `fk_chamado_usuario_abertura` FOREIGN KEY (`usuario_abertura_id`) REFERENCES `system_users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.chamado: ~2 rows (aproximadamente)
DELETE FROM `chamado`;
/*!40000 ALTER TABLE `chamado` DISABLE KEYS */;
/*!40000 ALTER TABLE `chamado` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.chamado_anexo
CREATE TABLE IF NOT EXISTS `chamado_anexo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chamado_id` int(11) NOT NULL,
  `arquivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_upload` datetime DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_anexo_chamado` (`chamado_id`),
  KEY `fk_anexo_usuario` (`usuario_id`),
  CONSTRAINT `fk_anexo_chamado` FOREIGN KEY (`chamado_id`) REFERENCES `chamado` (`id`),
  CONSTRAINT `fk_anexo_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `system_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.chamado_anexo: ~0 rows (aproximadamente)
DELETE FROM `chamado_anexo`;
/*!40000 ALTER TABLE `chamado_anexo` DISABLE KEYS */;
/*!40000 ALTER TABLE `chamado_anexo` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.chamado_historico
CREATE TABLE IF NOT EXISTS `chamado_historico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chamado_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `status` enum('aberto','em_atendimento','concluido','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_hora` datetime DEFAULT CURRENT_TIMESTAMP,
  `observacao` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_historico_usuario` (`usuario_id`),
  KEY `idx_historico_chamado` (`chamado_id`),
  KEY `idx_historico_status` (`status`),
  CONSTRAINT `fk_historico_chamado` FOREIGN KEY (`chamado_id`) REFERENCES `chamado` (`id`),
  CONSTRAINT `fk_historico_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `system_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.chamado_historico: ~0 rows (aproximadamente)
DELETE FROM `chamado_historico`;
/*!40000 ALTER TABLE `chamado_historico` DISABLE KEYS */;
/*!40000 ALTER TABLE `chamado_historico` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.ponto
CREATE TABLE IF NOT EXISTS `ponto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `data_hora_entrada` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_hora_saida` datetime DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `tipo` enum('entrada','saida') COLLATE utf8mb4_unicode_ci DEFAULT 'entrada',
  PRIMARY KEY (`id`),
  KEY `idx_ponto_usuario` (`usuario_id`),
  KEY `idx_ponto_data_entrada` (`data_hora_entrada`),
  CONSTRAINT `fk_ponto_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `system_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.ponto: ~0 rows (aproximadamente)
DELETE FROM `ponto`;
/*!40000 ALTER TABLE `ponto` DISABLE KEYS */;
/*!40000 ALTER TABLE `ponto` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_access_log
CREATE TABLE IF NOT EXISTS `system_access_log` (
  `id` int(11) NOT NULL,
  `sessionid` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_time` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_year` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_month` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_day` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logout_time` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `impersonated` char(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `impersonated_by` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_access_log_login_idx` (`login`),
  KEY `sys_access_log_year_idx` (`login_year`),
  KEY `sys_access_log_month_idx` (`login_month`),
  KEY `sys_access_log_day_idx` (`login_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_access_log: ~0 rows (aproximadamente)
DELETE FROM `system_access_log`;
/*!40000 ALTER TABLE `system_access_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_access_log` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_change_log
CREATE TABLE IF NOT EXISTS `system_change_log` (
  `id` int(11) NOT NULL,
  `logdate` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tablename` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primarykey` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pkvalue` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operation` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `columnname` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oldvalue` text COLLATE utf8mb4_unicode_ci,
  `newvalue` text COLLATE utf8mb4_unicode_ci,
  `access_ip` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_trace` text COLLATE utf8mb4_unicode_ci,
  `session_id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `php_sapi` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_year` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_month` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_day` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_change_log_login_idx` (`login`),
  KEY `sys_change_log_date_idx` (`logdate`),
  KEY `sys_change_log_year_idx` (`log_year`),
  KEY `sys_change_log_month_idx` (`log_month`),
  KEY `sys_change_log_day_idx` (`log_day`),
  KEY `sys_change_log_class_idx` (`class_name`),
  KEY `sys_change_log_table_idx` (`tablename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_change_log: ~0 rows (aproximadamente)
DELETE FROM `system_change_log`;
/*!40000 ALTER TABLE `system_change_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_change_log` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_document
CREATE TABLE IF NOT EXISTS `system_document` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `submission_date` date DEFAULT NULL,
  `archive_date` date DEFAULT NULL,
  `filename` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_trash` char(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_folder_id` int(11) DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `content_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_document: ~0 rows (aproximadamente)
DELETE FROM `system_document`;
/*!40000 ALTER TABLE `system_document` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_document` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_document_bookmark
CREATE TABLE IF NOT EXISTS `system_document_bookmark` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `system_document_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_document_bookmark_user_idx` (`system_user_id`),
  KEY `sys_document_bookmark_document_idx` (`system_document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_document_bookmark: ~0 rows (aproximadamente)
DELETE FROM `system_document_bookmark`;
/*!40000 ALTER TABLE `system_document_bookmark` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_document_bookmark` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_document_user
CREATE TABLE IF NOT EXISTS `system_document_user` (
  `id` int(11) NOT NULL,
  `document_id` int(11) DEFAULT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_document_user: ~0 rows (aproximadamente)
DELETE FROM `system_document_user`;
/*!40000 ALTER TABLE `system_document_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_document_user` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_folder
CREATE TABLE IF NOT EXISTS `system_folder` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `created_at` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `in_trash` char(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_folder_parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_folder: ~0 rows (aproximadamente)
DELETE FROM `system_folder`;
/*!40000 ALTER TABLE `system_folder` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_folder` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_folder_bookmark
CREATE TABLE IF NOT EXISTS `system_folder_bookmark` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `system_folder_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_folder_bookmark_user_idx` (`system_user_id`),
  KEY `sys_folder_bookmark_folder_idx` (`system_folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_folder_bookmark: ~0 rows (aproximadamente)
DELETE FROM `system_folder_bookmark`;
/*!40000 ALTER TABLE `system_folder_bookmark` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_folder_bookmark` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_folder_group
CREATE TABLE IF NOT EXISTS `system_folder_group` (
  `id` int(11) NOT NULL,
  `system_folder_id` int(11) DEFAULT NULL,
  `system_group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_folder_group_folder_idx` (`system_folder_id`),
  KEY `sys_folder_group_group_idx` (`system_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_folder_group: ~0 rows (aproximadamente)
DELETE FROM `system_folder_group`;
/*!40000 ALTER TABLE `system_folder_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_folder_group` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_folder_user
CREATE TABLE IF NOT EXISTS `system_folder_user` (
  `id` int(11) NOT NULL,
  `system_folder_id` int(11) DEFAULT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_folder_user: ~0 rows (aproximadamente)
DELETE FROM `system_folder_user`;
/*!40000 ALTER TABLE `system_folder_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_folder_user` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_group
CREATE TABLE IF NOT EXISTS `system_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_group_name_idx` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela chamados.system_group: ~3 rows (aproximadamente)
DELETE FROM `system_group`;
/*!40000 ALTER TABLE `system_group` DISABLE KEYS */;
INSERT INTO `system_group` (`id`, `name`) VALUES
	(1, 'Administradores '),
	(3, 'Financeiro '),
	(2, 'Gerencia ');
/*!40000 ALTER TABLE `system_group` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_group_program
CREATE TABLE IF NOT EXISTS `system_group_program` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_group_id` int(11) DEFAULT NULL,
  `system_program_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_group_program_program_idx` (`system_program_id`),
  KEY `sys_group_program_group_idx` (`system_group_id`),
  CONSTRAINT `system_group_program_ibfk_1` FOREIGN KEY (`system_group_id`) REFERENCES `system_group` (`id`),
  CONSTRAINT `system_group_program_ibfk_2` FOREIGN KEY (`system_program_id`) REFERENCES `system_program` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela chamados.system_group_program: ~48 rows (aproximadamente)
DELETE FROM `system_group_program`;
/*!40000 ALTER TABLE `system_group_program` DISABLE KEYS */;
INSERT INTO `system_group_program` (`id`, `system_group_id`, `system_program_id`) VALUES
	(1, 2, 1),
	(2, 2, 72),
	(3, 2, 73),
	(4, 2, 74),
	(5, 2, 75),
	(6, 2, 76),
	(7, 2, 77),
	(8, 2, 78),
	(9, 2, 79),
	(10, 2, 80),
	(11, 2, 81),
	(12, 2, 89),
	(13, 2, 90),
	(14, 2, 91),
	(15, 2, 93),
	(16, 2, 94),
	(17, 2, 95),
	(18, 2, 96),
	(19, 2, 121),
	(20, 2, 122),
	(21, 2, 123),
	(22, 2, 124),
	(23, 2, 125),
	(24, 2, 126),
	(25, 2, 127),
	(26, 2, 128),
	(27, 2, 129),
	(28, 2, 130),
	(29, 2, 131),
	(30, 2, 132),
	(31, 2, 133),
	(32, 1, 134),
	(33, 2, 134),
	(34, 3, 134),
	(35, 1, 135),
	(36, 2, 135),
	(37, 3, 135),
	(38, 1, 136),
	(39, 2, 136),
	(40, 3, 136),
	(41, 1, 137),
	(42, 2, 137),
	(43, 3, 137),
	(44, 1, 138),
	(45, 2, 138),
	(46, 3, 138),
	(47, 1, 139),
	(48, 2, 139),
	(49, 3, 139);
/*!40000 ALTER TABLE `system_group_program` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_message_tag
CREATE TABLE IF NOT EXISTS `system_message_tag` (
  `id` int(11) NOT NULL,
  `system_message_id` int(11) NOT NULL,
  `tag` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_message_tag_msg_idx` (`system_message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_message_tag: ~0 rows (aproximadamente)
DELETE FROM `system_message_tag`;
/*!40000 ALTER TABLE `system_message_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_message_tag` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_notification
CREATE TABLE IF NOT EXISTS `system_notification` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `system_user_to_id` int(11) DEFAULT NULL,
  `subject` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `dt_message` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action_url` text COLLATE utf8mb4_unicode_ci,
  `action_label` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked` char(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_notification_user_id_idx` (`system_user_id`),
  KEY `sys_notification_user_to_idx` (`system_user_to_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_notification: ~0 rows (aproximadamente)
DELETE FROM `system_notification`;
/*!40000 ALTER TABLE `system_notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_notification` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_post
CREATE TABLE IF NOT EXISTS `system_post` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `updated_at` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_post_user_idx` (`system_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_post: ~2 rows (aproximadamente)
DELETE FROM `system_post`;
/*!40000 ALTER TABLE `system_post` DISABLE KEYS */;
INSERT INTO `system_post` (`id`, `system_user_id`, `title`, `content`, `created_at`, `active`, `updated_at`, `updated_by`) VALUES
	(1, 1, 'Primeira noticia', '<p style="text-align: justify; "><span style="font-family: &quot;Source Sans Pro&quot;; font-size: 18px;">﻿</span><span style="font-family: &quot;Source Sans Pro&quot;; font-size: 18px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Id cursus metus aliquam eleifend mi in nulla posuere sollicitudin. Tincidunt nunc pulvinar sapien et ligula ullamcorper. Odio pellentesque diam volutpat commodo sed egestas egestas. Eget egestas purus viverra accumsan in nisl nisi scelerisque. Habitant morbi tristique senectus et netus et malesuada. Vitae ultricies leo integer malesuada nunc vel risus commodo viverra. Vehicula ipsum a arcu cursus. Rhoncus est pellentesque elit ullamcorper dignissim. Faucibus in ornare quam viverra orci sagittis eu. Nisi scelerisque eu ultrices vitae auctor. Tellus cras adipiscing enim eu turpis egestas. Eget lorem dolor sed viverra ipsum nunc aliquet. Neque convallis a cras semper auctor neque. Bibendum ut tristique et egestas. Amet nisl suscipit adipiscing bibendum.</span></p><p style="text-align: justify;"><span style="font-family: &quot;Source Sans Pro&quot;; font-size: 18px;">Mattis nunc sed blandit libero volutpat sed cras ornare. Leo duis ut diam quam nulla. Tempus imperdiet nulla malesuada pellentesque elit eget gravida cum sociis. Non quam lacus suspendisse faucibus. Enim nulla aliquet porttitor lacus luctus accumsan tortor posuere ac. Dignissim enim sit amet venenatis urna. Elit sed vulputate mi sit. Sit amet nisl suscipit adipiscing bibendum est. Maecenas accumsan lacus vel facilisis. Orci phasellus egestas tellus rutrum tellus pellentesque eu tincidunt tortor. Aenean pharetra magna ac placerat vestibulum lectus mauris ultrices eros. Augue lacus viverra vitae congue eu consequat ac felis. Bibendum neque egestas congue quisque egestas diam. Facilisis magna etiam tempor orci eu lobortis elementum. Rhoncus est pellentesque elit ullamcorper dignissim cras tincidunt lobortis. Pellentesque adipiscing commodo elit at imperdiet dui accumsan sit amet. Nullam eget felis eget nunc. Nec ullamcorper sit amet risus nullam eget felis. Lacus vel facilisis volutpat est velit egestas dui id.</span></p>', '2022-11-03 14:59:39', 'Y', NULL, NULL),
	(2, 1, 'Segunda noticia', '<p style="text-align: justify; "><span style="font-size: 18px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ac orci phasellus egestas tellus rutrum. Pretium nibh ipsum consequat nisl vel pretium lectus quam. Faucibus scelerisque eleifend donec pretium vulputate sapien. Mattis molestie a iaculis at erat pellentesque adipiscing commodo elit. Ultricies mi quis hendrerit dolor magna eget. Quam id leo in vitae turpis massa sed elementum tempus. Eget arcu dictum varius duis at consectetur lorem. Quis varius quam quisque id diam. Consequat interdum varius sit amet mattis vulputate. Purus non enim praesent elementum facilisis leo vel fringilla. Nulla facilisi nullam vehicula ipsum a arcu. Habitant morbi tristique senectus et netus et malesuada fames. Risus commodo viverra maecenas accumsan lacus. Mattis molestie a iaculis at erat pellentesque adipiscing commodo elit. Imperdiet proin fermentum leo vel orci porta non pulvinar neque. Massa massa ultricies mi quis hendrerit. Vel turpis nunc eget lorem dolor sed viverra ipsum nunc. Quisque egestas diam in arcu cursus euismod quis.</span></p><p style="text-align: justify; "><span style="font-size: 18px;">Posuere morbi leo urna molestie at elementum eu facilisis. Dolor morbi non arcu risus quis varius quam. Fermentum posuere urna nec tincidunt praesent semper feugiat nibh. Consectetur adipiscing elit ut aliquam purus sit. Gravida cum sociis natoque penatibus et magnis. Sollicitudin aliquam ultrices sagittis orci. Tortor consequat id porta nibh venenatis cras sed felis. Dictumst quisque sagittis purus sit amet volutpat consequat mauris nunc. Arcu dictum varius duis at consectetur. Mauris commodo quis imperdiet massa tincidunt nunc pulvinar. At tellus at urna condimentum mattis pellentesque. Tellus mauris a diam maecenas sed.</span></p>', '2022-11-03 15:03:31', 'Y', NULL, NULL);
/*!40000 ALTER TABLE `system_post` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_post_share_group
CREATE TABLE IF NOT EXISTS `system_post_share_group` (
  `id` int(11) NOT NULL,
  `system_group_id` int(11) DEFAULT NULL,
  `system_post_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_post_share_group: ~0 rows (aproximadamente)
DELETE FROM `system_post_share_group`;
/*!40000 ALTER TABLE `system_post_share_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_post_share_group` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_preference
CREATE TABLE IF NOT EXISTS `system_preference` (
  `id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  KEY `sys_preference_id_idx` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_preference: ~8 rows (aproximadamente)
DELETE FROM `system_preference`;
/*!40000 ALTER TABLE `system_preference` DISABLE KEYS */;
INSERT INTO `system_preference` (`id`, `value`) VALUES
	('mail_from', 'seusistema@gmail.com'),
	('smtp_auth', '0'),
	('smtp_host', NULL),
	('smtp_port', '587'),
	('smtp_user', 'seusistema@gmail.com'),
	('smtp_pass', 'teste'),
	('mail_support', 'seusistema@gmail.com'),
	('term_policy', NULL);
/*!40000 ALTER TABLE `system_preference` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_program
CREATE TABLE IF NOT EXISTS `system_program` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT NULL,
  `controller` varchar(256) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_program_name_idx` (`name`),
  KEY `sys_program_controller_idx` (`controller`)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela chamados.system_program: ~131 rows (aproximadamente)
DELETE FROM `system_program`;
/*!40000 ALTER TABLE `system_program` DISABLE KEYS */;
INSERT INTO `system_program` (`id`, `name`, `controller`, `icon`) VALUES
	(1, 'Dashboard', 'SystemAdministrationDashboard', 'fa-dashboard'),
	(3, 'System Message Tag form', 'SystemMessageTagForm', NULL),
	(4, 'System schedule list', 'SystemScheduleList', NULL),
	(5, 'System schedule form', 'SystemScheduleForm', NULL),
	(6, 'System schedule log', 'SystemScheduleLogList', NULL),
	(7, 'Text document editor', 'SystemTextDocumentEditor', NULL),
	(8, 'System Wiki page picker', 'SystemWikiPagePicker', NULL),
	(9, 'System Modules Check View', 'SystemModulesCheckView', NULL),
	(10, 'System Program Form', 'SystemProgramForm', NULL),
	(11, 'System Program List', 'SystemProgramList', NULL),
	(12, 'System Group Form', 'SystemGroupForm', NULL),
	(13, 'System Group List', 'SystemGroupList', NULL),
	(14, 'System Unit Form', 'SystemUnitForm', NULL),
	(15, 'System Unit List', 'SystemUnitList', NULL),
	(16, 'System Role Form', 'SystemRoleForm', NULL),
	(17, 'System Role List', 'SystemRoleList', NULL),
	(18, 'System User Form', 'SystemUserForm', NULL),
	(19, 'System User List', 'SystemUserList', NULL),
	(20, 'System Preference form', 'SystemPreferenceForm', NULL),
	(21, 'System Log Dashboard', 'SystemLogDashboard', NULL),
	(22, 'System Access Log', 'SystemAccessLogList', NULL),
	(23, 'System ChangeLog View', 'SystemChangeLogView', NULL),
	(24, 'System Sql Log', 'SystemSqlLogList', NULL),
	(25, 'System Request Log', 'SystemRequestLogList', NULL),
	(26, 'System Request Log View', 'SystemRequestLogView', NULL),
	(27, 'System PHP Error', 'SystemPHPErrorLogView', NULL),
	(28, 'System Session vars', 'SystemSessionVarsView', NULL),
	(29, 'System Database Browser', 'SystemDatabaseExplorer', NULL),
	(30, 'System Table List', 'SystemTableList', NULL),
	(31, 'System Data Browser', 'SystemDataBrowser', NULL),
	(32, 'System SQL Panel', 'SystemSQLPanel', NULL),
	(33, 'System Modules', 'SystemModulesCheckView', NULL),
	(34, 'System files diff', 'SystemFilesDiff', NULL),
	(35, 'System Information', 'SystemInformationView', NULL),
	(36, 'System PHP Info', 'SystemPHPInfoView', NULL),
	(37, 'Common Page', 'CommonPage', NULL),
	(38, 'Welcome View', 'WelcomeView', NULL),
	(39, 'Welcome dashboard', 'WelcomeDashboardView', NULL),
	(40, 'System Profile View', 'SystemProfileView', NULL),
	(41, 'System Profile Form', 'SystemProfileForm', NULL),
	(42, 'System Notification List', 'SystemNotificationList', NULL),
	(43, 'System Notification Form View', 'SystemNotificationFormView', NULL),
	(44, 'System Support form', 'SystemSupportForm', NULL),
	(45, 'System Profile 2FA Form', 'SystemProfile2FAForm', NULL),
	(46, 'System Wiki form', 'SystemWikiForm', NULL),
	(47, 'System Wiki page picker', 'SystemWikiPagePicker', NULL),
	(48, 'System Post list', 'SystemPostList', NULL),
	(49, 'System Post form', 'SystemPostForm', NULL),
	(50, 'System schedule list', 'SystemScheduleList', NULL),
	(51, 'System schedule form', 'SystemScheduleForm', NULL),
	(52, 'System schedule log', 'SystemScheduleLogList', NULL),
	(53, 'System Message Form', 'SystemMessageForm', NULL),
	(54, 'System Message List', 'SystemMessageList', NULL),
	(55, 'System Message Form View', 'SystemMessageFormView', NULL),
	(56, 'System Documents', 'SystemDriveList', NULL),
	(57, 'System Folder form', 'SystemFolderForm', NULL),
	(58, 'System Share folder', 'SystemFolderShareForm', NULL),
	(59, 'System Share document', 'SystemDocumentShareForm', NULL),
	(60, 'System Document properties', 'SystemDocumentFormWindow', NULL),
	(61, 'System Folder properties', 'SystemFolderFormView', NULL),
	(62, 'System Document upload', 'SystemDriveDocumentUploadForm', NULL),
	(63, 'Post View list', 'SystemPostFeedView', NULL),
	(64, 'Post Comment form', 'SystemPostCommentForm', NULL),
	(65, 'Post Comment list', 'SystemPostCommentList', NULL),
	(66, 'System Wiki search', 'SystemWikiSearchList', NULL),
	(67, 'System Wiki view', 'SystemWikiView', NULL),
	(69, 'System Contacts list', 'SystemContactsList', NULL),
	(70, 'Text document editor', 'SystemTextDocumentEditor', NULL),
	(71, 'System document create form', 'SystemDriveDocumentCreateForm', NULL),
	(72, 'Associados Formulário', 'AssociadosForm', NULL),
	(73, 'Associados Lista', 'AssociadosList', NULL),
	(74, 'Parcela Formulário', 'ParcelaForm', NULL),
	(75, 'Parcela Lista', 'ParcelaList', NULL),
	(76, 'Associados Ativos', 'AssociadosAtivosReport', NULL),
	(77, 'Categoria Despesa Lista ', 'CategoriaDespesaList', NULL),
	(78, 'Categoria Despesa Formulário', 'CategoriaDespesaForm', NULL),
	(79, 'Despesa Lista', 'DespesaList', NULL),
	(80, 'Despesa Formulário', 'DespesaForm', NULL),
	(81, 'Aniversariantes relatório', 'AniversariantesReport', NULL),
	(82, 'System Message Tag form', 'SystemMessageTagForm', NULL),
	(83, 'System schedule list', 'SystemScheduleList', NULL),
	(84, 'System schedule form', 'SystemScheduleForm', NULL),
	(85, 'System schedule log', 'SystemScheduleLogList', NULL),
	(86, 'Text document editor', 'SystemTextDocumentEditor', NULL),
	(87, 'System Wiki page picker', 'SystemWikiPagePicker', NULL),
	(88, 'System Modules Check View', 'SystemModulesCheckView', NULL),
	(89, 'Categoria Despesa', 'CategoriaDespesa', NULL),
	(90, 'Despesa', 'Despesa', NULL),
	(91, 'Parcela', 'Parcela', NULL),
	(92, 'System Wiki List', 'SystemWikiList', NULL),
	(93, 'Public View', 'PublicView', NULL),
	(94, 'Parcela Recibo View', 'ParcelaReciboView', NULL),
	(95, 'Parcelas Report', 'ParcelasReport', NULL),
	(96, 'Parcelasn Report', 'ParcelasnReport', NULL),
	(97, 'System Administration Dashboard', 'SystemAdministrationDashboard', NULL),
	(98, 'System Program Form', 'SystemProgramForm', NULL),
	(99, 'System Program List', 'SystemProgramList', NULL),
	(100, 'System Group Form', 'SystemGroupForm', NULL),
	(101, 'System Group List', 'SystemGroupList', NULL),
	(102, 'System Unit Form', 'SystemUnitForm', NULL),
	(103, 'System Unit List', 'SystemUnitList', NULL),
	(104, 'System Role Form', 'SystemRoleForm', NULL),
	(105, 'System Role List', 'SystemRoleList', NULL),
	(106, 'System User Form', 'SystemUserForm', NULL),
	(107, 'System User List', 'SystemUserList', NULL),
	(108, 'System Preference form', 'SystemPreferenceForm', NULL),
	(109, 'System Log Dashboard', 'SystemLogDashboard', NULL),
	(110, 'System Access Log', 'SystemAccessLogList', NULL),
	(111, 'System ChangeLog View', 'SystemChangeLogView', NULL),
	(112, 'System Sql Log', 'SystemSqlLogList', NULL),
	(113, 'System Request Log', 'SystemRequestLogList', NULL),
	(114, 'System Request Log View', 'SystemRequestLogView', NULL),
	(115, 'System PHP Error', 'SystemPHPErrorLogView', NULL),
	(116, 'System Session vars', 'SystemSessionVarsView', NULL),
	(117, 'System Database Browser', 'SystemDatabaseExplorer', NULL),
	(118, 'System Table List', 'SystemTableList', NULL),
	(119, 'System Data Browser', 'SystemDataBrowser', NULL),
	(120, 'System SQL Panel', 'SystemSQLPanel', NULL),
	(121, 'System Modules', 'SystemModulesCheckView', NULL),
	(122, 'System files diff', 'SystemFilesDiff', NULL),
	(123, 'System Information', 'SystemInformationView', NULL),
	(124, 'System PHP Info', 'SystemPHPInfoView', NULL),
	(125, 'Common Page', 'CommonPage', NULL),
	(126, 'Welcome View', 'WelcomeView', NULL),
	(127, 'Welcome dashboard', 'WelcomeDashboardView', NULL),
	(128, 'System Profile View', 'SystemProfileView', NULL),
	(129, 'System Profile Form', 'SystemProfileForm', NULL),
	(130, 'System Notification List', 'SystemNotificationList', NULL),
	(131, 'System Notification Form View', 'SystemNotificationFormView', NULL),
	(132, 'System Support form', 'SystemSupportForm', NULL),
	(133, 'System Profile 2FA Form', 'SystemProfile2FAForm', NULL),
	(134, 'Chamado Externo Form', 'ChamadoExternoForm', NULL),
	(135, 'Chamado Form', 'ChamadoForm', NULL),
	(136, 'Ponto Form', 'PontoForm', NULL),
	(137, 'Ponto List', 'PontoList', NULL),
	(138, 'Chamado List', 'ChamadoList', NULL),
	(139, 'Chamado Mapa', 'ChamadoMapa', NULL);
/*!40000 ALTER TABLE `system_program` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_program_method_role
CREATE TABLE IF NOT EXISTS `system_program_method_role` (
  `id` int(11) NOT NULL,
  `system_program_id` int(11) DEFAULT NULL,
  `system_role_id` int(11) DEFAULT NULL,
  `method_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_program_method_role_program_idx` (`system_program_id`),
  KEY `sys_program_method_role_role_idx` (`system_role_id`),
  CONSTRAINT `system_program_method_role_ibfk_1` FOREIGN KEY (`system_program_id`) REFERENCES `system_program` (`id`),
  CONSTRAINT `system_program_method_role_ibfk_2` FOREIGN KEY (`system_role_id`) REFERENCES `system_role` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_program_method_role: ~0 rows (aproximadamente)
DELETE FROM `system_program_method_role`;
/*!40000 ALTER TABLE `system_program_method_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_program_method_role` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_request_log
CREATE TABLE IF NOT EXISTS `system_request_log` (
  `id` int(11) NOT NULL,
  `endpoint` text COLLATE utf8mb4_unicode_ci,
  `logdate` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_year` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_month` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_day` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_ip` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_method` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `http_host` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `server_port` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_uri` text COLLATE utf8mb4_unicode_ci,
  `request_method` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `query_string` text COLLATE utf8mb4_unicode_ci,
  `request_headers` text COLLATE utf8mb4_unicode_ci,
  `request_body` text COLLATE utf8mb4_unicode_ci,
  `request_duration` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_request_log_login_idx` (`login`),
  KEY `sys_request_log_date_idx` (`logdate`),
  KEY `sys_request_log_year_idx` (`log_year`),
  KEY `sys_request_log_month_idx` (`log_month`),
  KEY `sys_request_log_day_idx` (`log_day`),
  KEY `sys_request_log_class_idx` (`class_name`),
  KEY `sys_request_log_method_idx` (`class_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_request_log: ~0 rows (aproximadamente)
DELETE FROM `system_request_log`;
/*!40000 ALTER TABLE `system_request_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_request_log` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_role
CREATE TABLE IF NOT EXISTS `system_role` (
  `id` int(11) NOT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_code` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_role_name_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_role: ~2 rows (aproximadamente)
DELETE FROM `system_role`;
/*!40000 ALTER TABLE `system_role` DISABLE KEYS */;
INSERT INTO `system_role` (`id`, `name`, `custom_code`) VALUES
	(1, 'Administração ', NULL),
	(2, 'Financeiro ', NULL);
/*!40000 ALTER TABLE `system_role` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_schedule_log
CREATE TABLE IF NOT EXISTS `system_schedule_log` (
  `id` int(11) NOT NULL,
  `logdate` varchar(19) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `sys_schedule_log_class_idx` (`class_name`),
  KEY `sys_schedule_log_method_idx` (`method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_schedule_log: ~0 rows (aproximadamente)
DELETE FROM `system_schedule_log`;
/*!40000 ALTER TABLE `system_schedule_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_schedule_log` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_sql_changes
CREATE TABLE IF NOT EXISTS `system_sql_changes` (
  `id` int(11) NOT NULL,
  `db_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sql_date` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sql_hash` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sql_command` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `sys_sqlchanges_dbname_idx` (`db_name`),
  KEY `sys_sqlchanges_sqldate_idx` (`sql_date`),
  KEY `sys_sqlchanges_sqlhash_idx` (`sql_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_sql_changes: ~0 rows (aproximadamente)
DELETE FROM `system_sql_changes`;
/*!40000 ALTER TABLE `system_sql_changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_sql_changes` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_sql_log
CREATE TABLE IF NOT EXISTS `system_sql_log` (
  `id` int(11) NOT NULL,
  `logdate` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `database_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sql_command` text COLLATE utf8mb4_unicode_ci,
  `statement_type` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `access_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_trace` text COLLATE utf8mb4_unicode_ci,
  `session_id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `php_sapi` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_id` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_year` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_month` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_day` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_sql_log_login_idx` (`login`),
  KEY `sys_sql_log_date_idx` (`logdate`),
  KEY `sys_sql_log_database_idx` (`database_name`),
  KEY `sys_sql_log_class_idx` (`class_name`),
  KEY `sys_sql_log_year_idx` (`log_year`),
  KEY `sys_sql_log_month_idx` (`log_month`),
  KEY `sys_sql_log_day_idx` (`log_day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_sql_log: ~0 rows (aproximadamente)
DELETE FROM `system_sql_log`;
/*!40000 ALTER TABLE `system_sql_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_sql_log` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_unit
CREATE TABLE IF NOT EXISTS `system_unit` (
  `id` int(11) NOT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `connection_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_code` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_unit_name_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_unit: ~2 rows (aproximadamente)
DELETE FROM `system_unit`;
/*!40000 ALTER TABLE `system_unit` DISABLE KEYS */;
INSERT INTO `system_unit` (`id`, `name`, `connection_name`, `custom_code`) VALUES
	(1, 'Unidade A', 'unit_a', NULL),
	(2, 'Unidade B', 'unit_b', NULL);
/*!40000 ALTER TABLE `system_unit` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_users
CREATE TABLE IF NOT EXISTS `system_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT NULL,
  `login` varchar(128) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `frontpage_id` int(11) DEFAULT NULL,
  `system_unit_id` int(11) DEFAULT NULL,
  `active` char(1) DEFAULT NULL,
  `accepted_term_policy` char(1) DEFAULT NULL,
  `accepted_term_policy_at` datetime DEFAULT NULL,
  `function_name` varchar(256) DEFAULT NULL,
  `about` varchar(50) DEFAULT NULL,
  `accepted_term_policy_data` varchar(50) DEFAULT NULL,
  `custom_code` varchar(256) DEFAULT NULL,
  `otp_secret` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `sys_user_program_idx` (`frontpage_id`),
  KEY `sys_user_unit_idx` (`system_unit_id`),
  KEY `sys_users_name_idx` (`name`),
  CONSTRAINT `fk_frontpage` FOREIGN KEY (`frontpage_id`) REFERENCES `system_program` (`id`),
  CONSTRAINT `system_users_ibfk_1` FOREIGN KEY (`frontpage_id`) REFERENCES `system_program` (`id`),
  CONSTRAINT `system_users_ibfk_2` FOREIGN KEY (`system_unit_id`) REFERENCES `system_unit` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela chamados.system_users: ~2 rows (aproximadamente)
DELETE FROM `system_users`;
/*!40000 ALTER TABLE `system_users` DISABLE KEYS */;
INSERT INTO `system_users` (`id`, `name`, `login`, `password`, `email`, `address`, `phone`, `frontpage_id`, `system_unit_id`, `active`, `accepted_term_policy`, `accepted_term_policy_at`, `function_name`, `about`, `accepted_term_policy_data`, `custom_code`, `otp_secret`) VALUES
	(1, 'Administrador', 'admin', '$2y$10$ugPj8cwxGuaScUm9tFMkdejk59CeWhNenEpOP5gEAbkXAQY6mZmUy', 'admin@localhost', NULL, NULL, 38, 1, 'Y', 'N', NULL, NULL, NULL, NULL, NULL, NULL),
	(2, 'Jonny ', 'Jonny', '$2y$10$ugPj8cwxGuaScUm9tFMkdejk59CeWhNenEpOP5gEAbkXAQY6mZmUy', 'jonny@gmail.com', NULL, NULL, 1, 1, 'Y', NULL, NULL, 'Administrador', NULL, NULL, NULL, NULL);
/*!40000 ALTER TABLE `system_users` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_user_group
CREATE TABLE IF NOT EXISTS `system_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_user_id` int(11) DEFAULT NULL,
  `system_group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_user_group_group_idx` (`system_group_id`),
  KEY `sys_user_group_user_idx` (`system_user_id`),
  CONSTRAINT `system_user_group_ibfk_1` FOREIGN KEY (`system_user_id`) REFERENCES `system_users` (`id`),
  CONSTRAINT `system_user_group_ibfk_2` FOREIGN KEY (`system_group_id`) REFERENCES `system_group` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela chamados.system_user_group: ~4 rows (aproximadamente)
DELETE FROM `system_user_group`;
/*!40000 ALTER TABLE `system_user_group` DISABLE KEYS */;
INSERT INTO `system_user_group` (`id`, `system_user_id`, `system_group_id`) VALUES
	(14, 1, 1),
	(15, 1, 2),
	(16, 1, 3),
	(17, 2, 2);
/*!40000 ALTER TABLE `system_user_group` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_user_old_password
CREATE TABLE IF NOT EXISTS `system_user_old_password` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `password` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_user_old_password_user_idx` (`system_user_id`),
  CONSTRAINT `system_user_old_password_ibfk_1` FOREIGN KEY (`system_user_id`) REFERENCES `system_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_user_old_password: ~0 rows (aproximadamente)
DELETE FROM `system_user_old_password`;
/*!40000 ALTER TABLE `system_user_old_password` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_user_old_password` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_user_program
CREATE TABLE IF NOT EXISTS `system_user_program` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `system_program_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `system_user_id` (`system_user_id`),
  KEY `system_program_id` (`system_program_id`),
  CONSTRAINT `system_user_program_ibfk_1` FOREIGN KEY (`system_user_id`) REFERENCES `system_users` (`id`),
  CONSTRAINT `system_user_program_ibfk_2` FOREIGN KEY (`system_program_id`) REFERENCES `system_program` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_user_program: ~137 rows (aproximadamente)
DELETE FROM `system_user_program`;
/*!40000 ALTER TABLE `system_user_program` DISABLE KEYS */;
INSERT INTO `system_user_program` (`id`, `system_user_id`, `system_program_id`) VALUES
	(1, 1, 1),
	(2, 1, 3),
	(3, 1, 4),
	(4, 1, 5),
	(5, 1, 6),
	(6, 1, 7),
	(7, 1, 8),
	(8, 1, 9),
	(9, 1, 10),
	(10, 1, 11),
	(11, 1, 12),
	(12, 1, 13),
	(13, 1, 14),
	(14, 1, 15),
	(15, 1, 16),
	(16, 1, 17),
	(17, 1, 18),
	(18, 1, 19),
	(19, 1, 20),
	(20, 1, 21),
	(21, 1, 22),
	(22, 1, 23),
	(23, 1, 24),
	(24, 1, 25),
	(25, 1, 26),
	(26, 1, 27),
	(27, 1, 28),
	(28, 1, 29),
	(29, 1, 30),
	(30, 1, 31),
	(31, 1, 32),
	(32, 1, 33),
	(33, 1, 34),
	(34, 1, 35),
	(35, 1, 36),
	(36, 1, 37),
	(37, 1, 38),
	(38, 1, 39),
	(39, 1, 40),
	(40, 1, 41),
	(41, 1, 42),
	(42, 1, 43),
	(43, 1, 44),
	(44, 1, 45),
	(45, 1, 46),
	(46, 1, 47),
	(47, 1, 48),
	(48, 1, 49),
	(49, 1, 50),
	(50, 1, 51),
	(51, 1, 52),
	(52, 1, 53),
	(53, 1, 54),
	(54, 1, 55),
	(55, 1, 56),
	(56, 1, 57),
	(57, 1, 58),
	(58, 1, 59),
	(59, 1, 60),
	(60, 1, 61),
	(61, 1, 62),
	(62, 1, 63),
	(63, 1, 64),
	(64, 1, 65),
	(65, 1, 66),
	(66, 1, 67),
	(67, 1, 69),
	(68, 1, 70),
	(69, 1, 71),
	(70, 1, 72),
	(71, 1, 73),
	(72, 1, 74),
	(73, 1, 75),
	(74, 1, 76),
	(75, 1, 77),
	(76, 1, 78),
	(77, 1, 79),
	(78, 1, 80),
	(79, 1, 81),
	(80, 1, 82),
	(81, 1, 83),
	(82, 1, 84),
	(83, 1, 85),
	(84, 1, 86),
	(85, 1, 87),
	(86, 1, 88),
	(87, 1, 89),
	(88, 1, 90),
	(89, 1, 91),
	(90, 1, 92),
	(91, 1, 93),
	(92, 1, 94),
	(93, 1, 95),
	(94, 1, 96),
	(95, 1, 97),
	(96, 1, 98),
	(97, 1, 99),
	(98, 1, 100),
	(99, 1, 101),
	(100, 1, 102),
	(101, 1, 103),
	(102, 1, 104),
	(103, 1, 105),
	(104, 1, 106),
	(105, 1, 107),
	(106, 1, 108),
	(107, 1, 109),
	(108, 1, 110),
	(109, 1, 111),
	(110, 1, 112),
	(111, 1, 113),
	(112, 1, 114),
	(113, 1, 115),
	(114, 1, 116),
	(115, 1, 117),
	(116, 1, 118),
	(117, 1, 119),
	(118, 1, 120),
	(119, 1, 121),
	(120, 1, 122),
	(121, 1, 123),
	(122, 1, 124),
	(123, 1, 125),
	(124, 1, 126),
	(125, 1, 127),
	(126, 1, 128),
	(127, 1, 129),
	(128, 1, 130),
	(129, 1, 131),
	(130, 1, 132),
	(131, 1, 133),
	(132, 2, 1),
	(133, 2, 134),
	(134, 2, 135),
	(135, 2, 136),
	(136, 2, 137),
	(137, 2, 138);
/*!40000 ALTER TABLE `system_user_program` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_user_role
CREATE TABLE IF NOT EXISTS `system_user_role` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `system_role_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_user_role_user_idx` (`system_user_id`),
  KEY `sys_user_role_role_idx` (`system_role_id`),
  CONSTRAINT `system_user_role_ibfk_1` FOREIGN KEY (`system_user_id`) REFERENCES `system_users` (`id`),
  CONSTRAINT `system_user_role_ibfk_2` FOREIGN KEY (`system_role_id`) REFERENCES `system_role` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_user_role: ~2 rows (aproximadamente)
DELETE FROM `system_user_role`;
/*!40000 ALTER TABLE `system_user_role` DISABLE KEYS */;
INSERT INTO `system_user_role` (`id`, `system_user_id`, `system_role_id`) VALUES
	(2, 1, 1),
	(3, 2, 1);
/*!40000 ALTER TABLE `system_user_role` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_user_unit
CREATE TABLE IF NOT EXISTS `system_user_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_user_id` int(11) NOT NULL,
  `system_unit_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_user_unit_user_idx` (`system_user_id`),
  KEY `sys_user_unit_unit_idx` (`system_unit_id`),
  CONSTRAINT `system_user_unit_ibfk_1` FOREIGN KEY (`system_user_id`) REFERENCES `system_users` (`id`),
  CONSTRAINT `system_user_unit_ibfk_2` FOREIGN KEY (`system_unit_id`) REFERENCES `system_unit` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela chamados.system_user_unit: ~3 rows (aproximadamente)
DELETE FROM `system_user_unit`;
/*!40000 ALTER TABLE `system_user_unit` DISABLE KEYS */;
INSERT INTO `system_user_unit` (`id`, `system_user_id`, `system_unit_id`) VALUES
	(4, 1, 1),
	(5, 1, 2),
	(6, 2, 1);
/*!40000 ALTER TABLE `system_user_unit` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_wiki_page
CREATE TABLE IF NOT EXISTS `system_wiki_page` (
  `id` int(11) NOT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  `created_at` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `searchable` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sys_wiki_page_user_idx` (`system_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_wiki_page: ~4 rows (aproximadamente)
DELETE FROM `system_wiki_page`;
/*!40000 ALTER TABLE `system_wiki_page` DISABLE KEYS */;
INSERT INTO `system_wiki_page` (`id`, `system_user_id`, `created_at`, `updated_at`, `title`, `description`, `content`, `active`, `searchable`, `updated_by`) VALUES
	(1, 1, '2022-11-02 15:33:58', '2022-11-02 15:35:10', 'Manual de operacoes', 'Este manual explica os procedimentos basicos de operacao', '<p style="text-align: justify; "><span style="font-size: 18px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Sapien nec sagittis aliquam malesuada bibendum arcu vitae. Quisque egestas diam in arcu cursus euismod quis. Risus nec feugiat in fermentum posuere urna nec tincidunt praesent. At imperdiet dui accumsan sit amet. Est pellentesque elit ullamcorper dignissim cras tincidunt lobortis. Elementum facilisis leo vel fringilla est ullamcorper. Id porta nibh venenatis cras. Viverra orci sagittis eu volutpat odio facilisis mauris sit. Senectus et netus et malesuada fames ac turpis. Sociis natoque penatibus et magnis dis parturient montes. Vel turpis nunc eget lorem dolor sed viverra ipsum nunc. Sed viverra tellus in hac habitasse. Tellus id interdum velit laoreet id donec ultrices tincidunt arcu. Pharetra et ultrices neque ornare aenean euismod elementum. Volutpat blandit aliquam etiam erat velit scelerisque in. Neque aliquam vestibulum morbi blandit cursus risus. Id consectetur purus ut faucibus pulvinar elementum.</span></p><p style="text-align: justify; "><br></p>', 'Y', 'Y', NULL),
	(2, 1, '2022-11-02 15:35:04', '2022-11-02 15:37:49', 'Instrucoes de lancamento', 'Este manual explica as instrucoes de lancamento de produto', '<p><span style="font-size: 18px;">Non curabitur gravida arcu ac tortor dignissim convallis. Nunc scelerisque viverra mauris in aliquam sem fringilla ut morbi. Nunc eget lorem dolor sed viverra. Et odio pellentesque diam volutpat commodo sed egestas. Enim lobortis scelerisque fermentum dui faucibus in ornare quam viverra. Faucibus et molestie ac feugiat. Erat velit scelerisque in dictum non consectetur a erat nam. Quis risus sed vulputate odio ut enim blandit volutpat. Pharetra vel turpis nunc eget lorem dolor sed viverra. Nisl tincidunt eget nullam non nisi est sit. Orci phasellus egestas tellus rutrum tellus pellentesque eu. Et tortor at risus viverra adipiscing at in tellus integer. Risus ultricies tristique nulla aliquet enim. Ac felis donec et odio pellentesque diam volutpat commodo sed. Ut morbi tincidunt augue interdum. Morbi tempus iaculis urna id volutpat.</span></p><p><a href="index.php?class=SystemWikiView&amp;method=onLoad&amp;key=3" generator="adianti">Sub pagina de instrucoes 1</a></p><p><a href="index.php?class=SystemWikiView&amp;method=onLoad&amp;key=4" generator="adianti">Sub pagina de instrucoes 2</a><br><span style="font-size: 18px;"><br></span><br></p>', 'Y', 'Y', NULL),
	(3, 1, '2022-11-02 15:36:59', '2022-11-02 15:37:21', 'Instrucoes - sub pagina 1', 'Instrucoes - sub pagina 1', '<p><span style="font-size: 18px;">Follow these steps:</span></p><ol><li><span style="font-size: 18px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</span></li><li><span style="font-size: 18px;">Sapien nec sagittis aliquam malesuada bibendum arcu vitae.</span></li><li><span style="font-size: 18px;">Quisque egestas diam in arcu cursus euismod quis.</span><br></li></ol>', 'Y', 'N', NULL),
	(4, 1, '2022-11-02 15:37:17', '2022-11-02 15:37:22', 'Instrucoes - sub pagina 2', 'Instrucoes - sub pagina 2', '<p><span style="font-size: 18px;">Follow these steps:</span></p><ol><li><span style="font-size: 18px;">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</span></li><li><span style="font-size: 18px;">Sapien nec sagittis aliquam malesuada bibendum arcu vitae.</span></li><li><span style="font-size: 18px;">Quisque egestas diam in arcu cursus euismod quis.</span></li></ol>', 'Y', 'N', NULL);
/*!40000 ALTER TABLE `system_wiki_page` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados.system_wiki_share_group
CREATE TABLE IF NOT EXISTS `system_wiki_share_group` (
  `id` int(11) NOT NULL,
  `system_group_id` int(11) DEFAULT NULL,
  `system_wiki_page_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados.system_wiki_share_group: ~0 rows (aproximadamente)
DELETE FROM `system_wiki_share_group`;
/*!40000 ALTER TABLE `system_wiki_share_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_wiki_share_group` ENABLE KEYS */;

-- Copiando estrutura para tabela chamados._usersystem_folder
CREATE TABLE IF NOT EXISTS `_usersystem_folder` (
  `id` int(11) NOT NULL,
  `system_folder_id` int(11) DEFAULT NULL,
  `system_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copiando dados para a tabela chamados._usersystem_folder: ~0 rows (aproximadamente)
DELETE FROM `_usersystem_folder`;
/*!40000 ALTER TABLE `_usersystem_folder` DISABLE KEYS */;
/*!40000 ALTER TABLE `_usersystem_folder` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
