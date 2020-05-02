-- MySQL dump 10.14  Distrib 10.0.4-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: dseye
-- ------------------------------------------------------
-- Server version	10.0.4-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `dseye`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `dseye` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `dseye`;

--
-- Table structure for table `addon_stat`
--

DROP TABLE IF EXISTS `addon_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addon_stat` (
  `date_create` datetime NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `agent` varchar(255) NOT NULL,
  `opt_data` text NOT NULL,
  KEY `date_create` (`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alliances`
--

DROP TABLE IF EXISTS `alliances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alliances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` enum('active','delete') NOT NULL DEFAULT 'active',
  `date_create` datetime NOT NULL,
  `date_delete` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_world_2` (`id_world`,`name`),
  KEY `id_world` (`id_world`),
  KEY `status` (`status`),
  CONSTRAINT `alliances_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4408 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alliances_property`
--

DROP TABLE IF EXISTS `alliances_property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alliances_property` (
  `id_alliance` int(10) unsigned NOT NULL,
  `count_voran` smallint(5) unsigned NOT NULL,
  `count_liens` smallint(5) unsigned NOT NULL,
  `count_psol` smallint(5) unsigned NOT NULL,
  `rank_old_voran` int(10) unsigned NOT NULL,
  `rank_old_liens` int(10) unsigned NOT NULL,
  `rank_old_psol` int(10) unsigned NOT NULL,
  `rank_new_voran` int(10) unsigned NOT NULL,
  `rank_new_liens` int(10) unsigned NOT NULL,
  `rank_new_psol` int(10) unsigned NOT NULL,
  `bo_voran` int(10) unsigned NOT NULL,
  `bo_liens` int(10) unsigned NOT NULL,
  `bo_psol` int(10) unsigned NOT NULL,
  `ra_voran` int(10) unsigned NOT NULL,
  `ra_liens` int(10) unsigned NOT NULL,
  `ra_psol` int(10) unsigned NOT NULL,
  `nra_voran` int(10) unsigned NOT NULL,
  `nra_liens` int(10) unsigned NOT NULL,
  `nra_psol` int(10) unsigned NOT NULL,
  `archeology_voran` int(10) unsigned NOT NULL,
  `archeology_liens` int(10) unsigned NOT NULL,
  `archeology_psol` int(10) unsigned NOT NULL,
  `building_voran` int(10) unsigned NOT NULL,
  `building_liens` int(10) unsigned NOT NULL,
  `building_psol` int(10) unsigned NOT NULL,
  `science_voran` int(10) unsigned NOT NULL,
  `science_liens` int(10) unsigned NOT NULL,
  `science_psol` int(10) unsigned NOT NULL,
  `count_colony_liens` smallint(5) unsigned NOT NULL,
  `count_colony_voran` smallint(5) unsigned NOT NULL,
  `count_colony_psol` smallint(5) unsigned NOT NULL,
  `level_voran` smallint(5) unsigned NOT NULL,
  `level_liens` smallint(5) unsigned NOT NULL,
  `level_psol` smallint(5) unsigned NOT NULL,
  UNIQUE KEY `id_alliance` (`id_alliance`),
  CONSTRAINT `alliances_property_ibfk_1` FOREIGN KEY (`id_alliance`) REFERENCES `alliances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antibrut`
--

DROP TABLE IF EXISTS `antibrut`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antibrut` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(150) NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`ip`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=33224 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cron_lock`
--

DROP TABLE IF EXISTS `cron_lock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cron_lock` (
  `type` char(10) NOT NULL,
  `counter` int(10) unsigned NOT NULL,
  `date_last_lock` datetime NOT NULL,
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cron_logs`
--

DROP TABLE IF EXISTS `cron_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cron_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(150) NOT NULL,
  `text` mediumtext NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `result` enum('none','success','warning','fail') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=13150930 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `game_versions`
--

DROP TABLE IF EXISTS `game_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_versions` (
  `id` tinyint(1) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `forum_search_pattern` varchar(255) DEFAULT NULL,
  `onlinestat_url` varchar(255) DEFAULT NULL,
  `main_csv_rep` varchar(100) NOT NULL,
  `old_ranks_rep` varchar(100) DEFAULT NULL,
  `new_ranks_rep` varchar(100) DEFAULT NULL,
  `game_url` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `monitor_groups`
--

DROP TABLE IF EXISTS `monitor_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitor_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `monitor_items`
--

DROP TABLE IF EXISTS `monitor_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitor_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `id_user` smallint(5) unsigned NOT NULL,
  `id_group` int(10) unsigned DEFAULT NULL,
  `date_add` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_player_2` (`id_user`,`id_player`),
  KEY `date_add` (`date_add`),
  KEY `id_group` (`id_group`),
  CONSTRAINT `monitor_items_ibfk_2` FOREIGN KEY (`id_group`) REFERENCES `monitor_groups` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `monitor_items_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat` enum('bugfix','update') NOT NULL DEFAULT 'bugfix',
  `title` varchar(50) NOT NULL,
  `text` text NOT NULL,
  `rank` mediumint(9) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=186 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `id_rase` tinyint(2) unsigned NOT NULL,
  `id_alliance` int(10) unsigned NOT NULL,
  `nik` varchar(50) NOT NULL,
  `ring` tinyint(1) unsigned NOT NULL,
  `compl` smallint(6) unsigned NOT NULL,
  `sota` tinyint(1) unsigned NOT NULL,
  `dom_name` varchar(50) NOT NULL,
  `rank_old` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'рейтинг',
  `rank_new` int(10) unsigned NOT NULL DEFAULT '0',
  `bo` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT 'боевые очки',
  `nra` decimal(5,2) unsigned NOT NULL DEFAULT '0.00' COMMENT 'новый РА',
  `ra` decimal(5,2) unsigned NOT NULL DEFAULT '0.00' COMMENT 'старый РА',
  `gate` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0-close, 1-open статус ворот',
  `gate_shield` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Включен ли щит',
  `gate_newbee` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Работает ли защита новичка',
  `gate_ban` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Забанен ли игрок',
  `premium` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'Куплен премиум',
  `level` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'уровень',
  `liga` enum('I','II','III') NOT NULL DEFAULT 'I' COMMENT 'лига',
  `archeology` mediumint(9) unsigned NOT NULL DEFAULT '0' COMMENT 'археология',
  `building` mediumint(9) unsigned NOT NULL DEFAULT '0' COMMENT 'строительство',
  `science` mediumint(9) unsigned NOT NULL DEFAULT '0' COMMENT 'наука',
  `delta_rank_old` int(11) NOT NULL DEFAULT '0',
  `delta_bo` decimal(11,2) NOT NULL DEFAULT '0.00',
  `date_create` datetime NOT NULL,
  `date_delete` datetime DEFAULT NULL,
  `status` enum('active','delete') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_world_2` (`id_world`,`nik`),
  KEY `id_world` (`id_world`),
  KEY `id_alliance` (`id_alliance`),
  KEY `id_rase` (`id_rase`),
  KEY `status` (`status`),
  CONSTRAINT `players_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `players_ibfk_5` FOREIGN KEY (`id_rase`) REFERENCES `rases` (`id`),
  CONSTRAINT `players_ibfk_6` FOREIGN KEY (`id_alliance`) REFERENCES `alliances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=255980 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `players_increment_stat` AFTER UPDATE ON `players`
 FOR EACH ROW BEGIN

	IF OLD.`liga` != NEW.`liga`
		THEN INSERT DELAYED INTO players_trans_ligue (`id_player`, `old_ligue`, `new_ligue`, `date`) VALUES (OLD.id, OLD.`liga`, NEW.`liga`, now() );
	END IF;

	IF OLD.`gate` != NEW.`gate`
	THEN
		IF NEW.`gate` = 1
			THEN	INSERT DELAYED INTO players_changes (`id_player`, `type`, `date`) VALUES (OLD.id, 'gate_open', now());
			ELSE	INSERT DELAYED INTO players_changes (`id_player`, `type`, `date`) VALUES (OLD.id, 'gate_close', now());
		END IF;
	END IF;

	IF OLD.`gate_shield` != NEW.`gate_shield`
	THEN
		IF NEW.`gate_shield` = 1
			THEN	INSERT DELAYED INTO players_changes (`id_player`, `type`, `date`) VALUES (OLD.id, 'shield_enable', now());
			ELSE	INSERT DELAYED INTO players_changes (`id_player`, `type`, `date`) VALUES (OLD.id, 'shield_disable', now());
		END IF;
	END IF;

	IF OLD.`gate_newbee` != NEW.`gate_newbee`
	THEN
		IF NEW.`gate_newbee` = 1
			THEN	INSERT DELAYED INTO players_changes (`id_player`, `type`, `date`) VALUES (OLD.id, 'newbee_enable', now());
			ELSE	INSERT DELAYED INTO players_changes (`id_player`, `type`, `date`) VALUES (OLD.id, 'newbee_disable', now());
		END IF;
	END IF;

	IF OLD.`gate_ban` != NEW.`gate_ban`
	THEN
		IF NEW.`gate_ban` = 1
			THEN	INSERT DELAYED INTO players_changes (`id_player`, `type`, `date`) VALUES (OLD.id, 'ban', now());
			ELSE	INSERT DELAYED INTO players_changes (`id_player`, `type`, `date`) VALUES (OLD.id, 'unban', now());
		END IF;
	END IF;

	IF OLD.`premium` != NEW.`premium`
	THEN
		IF NEW.`premium` = 1
			THEN	INSERT DELAYED INTO players_changes (`id_player`, `type`, `date`) VALUES (OLD.id, 'premium_enable', now());
			ELSE	INSERT DELAYED INTO players_changes (`id_player`, `type`, `date`) VALUES (OLD.id, 'premium_disable', now());
		END IF;
	END IF;

	IF OLD.`status` != NEW.`status`
	THEN
		IF NEW.status = 'active'
			THEN	INSERT INTO players_input (`id_world`, `id_player`, `date`) VALUES (OLD.`id_world`, OLD.`id`, now());
			ELSE	INSERT INTO players_output (`id_world`, `id_player`, `date`) VALUES (OLD.`id_world`, OLD.`id`, now());
		END IF;
	END IF;

	IF OLD.`id_alliance` != NEW.`id_alliance`
	THEN INSERT DELAYED INTO players_trans_alliance (`id_player`, `old_alliance`, `new_alliance`, `date`) VALUES (OLD.id, OLD.`id_alliance`, NEW.`id_alliance`,  now() );
	END IF;

	IF OLD.`compl` != NEW.`compl` OR OLD.`sota` != NEW.`sota`
	THEN INSERT DELAYED INTO players_trans_sots (`id_player`, `old_ring`, `old_compl`, `old_sota`, `new_ring`, `new_compl`, `new_sota`, `date`) VALUES (OLD.id, OLD.ring, OLD.`compl`, OLD.`sota`, OLD.ring, NEW.`compl`, NEW.`sota`, now() );
	END IF;

	IF OLD.`rank_old` != NEW.`rank_old`
	THEN INSERT INTO stat_players_rank_old( `id_player`, `date`, `value`, `delta` ) VALUES (OLD.id, now(), NEW.rank_old, CAST(NEW.rank_old AS SIGNED) - CAST(OLD.rank_old AS SIGNED));
	END IF;

	IF OLD.`rank_new` != NEW.`rank_new`
	THEN INSERT DELAYED INTO stat_players_rank_new( `id_player`, `date`, `value`, `delta` ) VALUES (OLD.id, now(), NEW.rank_new, CAST(NEW.rank_new AS SIGNED) - CAST(OLD.rank_new AS SIGNED));
	END IF;

	IF OLD.`bo` != NEW.`bo`
	THEN INSERT INTO stat_players_bo( `id_player`, `date`, `value`, `delta` ) VALUES (OLD.id, now(), NEW.bo, CAST(NEW.bo AS DECIMAL(11,2)) - CAST(OLD.bo AS DECIMAL(11,2)));
	END IF;

	IF OLD.`nra` != NEW.`nra`
	THEN INSERT DELAYED INTO stat_players_nra( `id_player`, `date`, `value`, `delta` ) VALUES (OLD.id, now(), NEW.nra, CAST(NEW.nra AS DECIMAL(5,2)) - CAST(OLD.nra AS DECIMAL(5,2)));
	END IF;

	IF OLD.`ra` != NEW.`ra`
	THEN INSERT DELAYED INTO stat_players_ra( `id_player`, `date`, `value`, `delta` ) VALUES (OLD.id, now(), NEW.ra, CAST(NEW.ra AS DECIMAL(5,2)) - CAST(OLD.ra AS DECIMAL(5,2)));
	END IF;

	IF OLD.`level` != NEW.`level`
	THEN INSERT DELAYED INTO stat_players_level( `id_player`, `date`, `value`, `delta` ) VALUES (OLD.id, now(), NEW.level, CAST(NEW.level AS SIGNED) - CAST(OLD.level AS SIGNED));
	END IF;

	IF OLD.`archeology` != NEW.`archeology`
	THEN INSERT DELAYED INTO stat_players_archeology( `id_player`, `date`, `value`, `delta` ) VALUES (OLD.id, now(), NEW.archeology, CAST(NEW.archeology AS SIGNED) - CAST(OLD.archeology AS SIGNED));
	END IF;

	IF OLD.`building` != NEW.`building`
	THEN INSERT DELAYED INTO stat_players_building( `id_player`, `date`, `value`, `delta` ) VALUES (OLD.id, now(), NEW.building, CAST(NEW.building AS SIGNED) - CAST(OLD.building AS SIGNED));
	END IF;

	IF OLD.`science` != NEW.`science`
	THEN INSERT DELAYED INTO stat_players_science( `id_player`, `date`, `value`, `delta` ) VALUES (OLD.id, now(), NEW.science, CAST(NEW.science AS SIGNED) - CAST(OLD.science AS SIGNED));
	END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `players_changes`
--

DROP TABLE IF EXISTS `players_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_changes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `type` enum('gate_open','gate_close','premium_enable','premium_disable','ban','unban','newbee_enable','newbee_disable','shield_enable','shield_disable') NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `date` (`date`),
  KEY `type` (`type`),
  CONSTRAINT `players_changes_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=690426 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_colony`
--

DROP TABLE IF EXISTS `players_colony`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_colony` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `compl` smallint(6) NOT NULL,
  `sota` tinyint(1) NOT NULL,
  `col_name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  CONSTRAINT `players_colony_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=665768 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_input`
--

DROP TABLE IF EXISTS `players_input`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_input` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `id_world` (`id_world`),
  KEY `date` (`date`),
  CONSTRAINT `players_input_ibfk_2` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `players_input_ibfk_3` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=505322 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_max_bo_delta`
--

DROP TABLE IF EXISTS `players_max_bo_delta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_max_bo_delta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `id_player` int(10) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `id_world` (`id_world`,`date`),
  KEY `date` (`date`),
  CONSTRAINT `players_max_bo_delta_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `players_max_bo_delta_ibfk_3` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=851312 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_max_rank_old_delta`
--

DROP TABLE IF EXISTS `players_max_rank_old_delta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_max_rank_old_delta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `id_player` int(10) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `id_world` (`id_world`,`date`),
  KEY `date` (`date`),
  CONSTRAINT `players_max_rank_old_delta_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `players_max_rank_old_delta_ibfk_3` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4600214 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_output`
--

DROP TABLE IF EXISTS `players_output`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_output` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `id_world` (`id_world`),
  KEY `date` (`date`),
  CONSTRAINT `players_output_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `players_output_ibfk_2` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=365557 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_trans_alliance`
--

DROP TABLE IF EXISTS `players_trans_alliance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_trans_alliance` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `old_alliance` int(10) unsigned NOT NULL,
  `new_alliance` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `old_alliance` (`old_alliance`),
  KEY `new_alliance` (`new_alliance`),
  KEY `date` (`date`),
  CONSTRAINT `players_trans_alliance_ibfk_5` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `players_trans_alliance_ibfk_6` FOREIGN KEY (`old_alliance`) REFERENCES `alliances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `players_trans_alliance_ibfk_7` FOREIGN KEY (`new_alliance`) REFERENCES `alliances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15500 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_trans_ligue`
--

DROP TABLE IF EXISTS `players_trans_ligue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_trans_ligue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `old_ligue` enum('I','II','III') NOT NULL,
  `new_ligue` enum('I','II','III') NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `date` (`date`),
  CONSTRAINT `players_trans_ligue_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18511 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_trans_sots`
--

DROP TABLE IF EXISTS `players_trans_sots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_trans_sots` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `old_ring` tinyint(1) unsigned NOT NULL,
  `old_compl` smallint(6) unsigned DEFAULT NULL,
  `old_sota` tinyint(1) unsigned DEFAULT NULL,
  `new_ring` tinyint(1) unsigned NOT NULL,
  `new_compl` smallint(6) unsigned DEFAULT NULL,
  `new_sota` tinyint(1) unsigned DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `date` (`date`),
  CONSTRAINT `players_trans_sots_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=110905 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rases`
--

DROP TABLE IF EXISTS `rases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rases` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_props`
--

DROP TABLE IF EXISTS `search_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_props` (
  `uid` char(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `prop` text NOT NULL,
  `date_touch` datetime NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `date_create` (`date_touch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_alliances`
--

DROP TABLE IF EXISTS `stat_alliances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_alliances` (
  `id_alliance` int(10) unsigned NOT NULL,
  `count_voran` smallint(5) unsigned NOT NULL,
  `count_liens` smallint(5) unsigned NOT NULL,
  `count_psol` smallint(5) unsigned NOT NULL,
  `rank_old_voran` int(10) unsigned NOT NULL,
  `rank_old_liens` int(10) unsigned NOT NULL,
  `rank_old_psol` int(10) unsigned NOT NULL,
  `rank_new_voran` int(10) unsigned NOT NULL,
  `rank_new_liens` int(10) unsigned NOT NULL,
  `rank_new_psol` int(10) unsigned NOT NULL,
  `bo_voran` int(10) unsigned NOT NULL,
  `bo_liens` int(10) unsigned NOT NULL,
  `bo_psol` int(10) unsigned NOT NULL,
  `nra_voran` int(10) unsigned NOT NULL,
  `nra_liens` int(10) unsigned NOT NULL,
  `nra_psol` int(10) unsigned NOT NULL,
  `ra_voran` int(10) unsigned NOT NULL,
  `ra_liens` int(10) unsigned NOT NULL,
  `ra_psol` int(10) unsigned NOT NULL,
  `level_voran` smallint(5) unsigned NOT NULL,
  `level_liens` smallint(5) unsigned NOT NULL,
  `level_psol` smallint(5) unsigned NOT NULL,
  `archeology_voran` int(10) unsigned NOT NULL,
  `archeology_liens` int(10) unsigned NOT NULL,
  `archeology_psol` int(10) unsigned NOT NULL,
  `building_voran` int(10) unsigned NOT NULL,
  `building_liens` int(10) unsigned NOT NULL,
  `building_psol` int(10) unsigned NOT NULL,
  `science_voran` int(10) unsigned NOT NULL,
  `science_liens` int(10) unsigned NOT NULL,
  `science_psol` int(10) unsigned NOT NULL,
  `count_colony_voran` smallint(5) unsigned NOT NULL,
  `count_colony_liens` smallint(5) unsigned NOT NULL,
  `count_colony_psol` smallint(5) unsigned NOT NULL,
  `date_create` date NOT NULL,
  PRIMARY KEY (`id_alliance`,`date_create`),
  CONSTRAINT `stat_alliances_ibfk_1` FOREIGN KEY (`id_alliance`) REFERENCES `alliances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_online`
--

DROP TABLE IF EXISTS `stat_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_online` (
  `id_version` tinyint(1) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `count` smallint(5) NOT NULL,
  PRIMARY KEY (`id_version`,`date`),
  KEY `date` (`date`),
  CONSTRAINT `stat_online_ibfk_1` FOREIGN KEY (`id_version`) REFERENCES `game_versions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_players_archeology`
--

DROP TABLE IF EXISTS `stat_players_archeology`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_players_archeology` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` mediumint(9) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  PRIMARY KEY (`id_player`,`date`),
  CONSTRAINT `stat_players_archeology_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_players_bo`
--

DROP TABLE IF EXISTS `stat_players_bo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_players_bo` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` decimal(11,2) unsigned NOT NULL,
  `delta` decimal(11,2) NOT NULL,
  PRIMARY KEY (`id_player`,`date`),
  CONSTRAINT `stat_players_bo_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_players_building`
--

DROP TABLE IF EXISTS `stat_players_building`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_players_building` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` mediumint(9) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  PRIMARY KEY (`id_player`,`date`),
  CONSTRAINT `stat_players_building_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_players_level`
--

DROP TABLE IF EXISTS `stat_players_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_players_level` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` mediumint(9) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  PRIMARY KEY (`id_player`,`date`),
  CONSTRAINT `stat_players_level_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_players_nra`
--

DROP TABLE IF EXISTS `stat_players_nra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_players_nra` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` decimal(5,2) unsigned NOT NULL,
  `delta` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id_player`,`date`),
  CONSTRAINT `stat_players_nra_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_players_ra`
--

DROP TABLE IF EXISTS `stat_players_ra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_players_ra` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` decimal(5,2) unsigned NOT NULL,
  `delta` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id_player`,`date`),
  CONSTRAINT `stat_players_ra_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_players_rank_new`
--

DROP TABLE IF EXISTS `stat_players_rank_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_players_rank_new` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` int(10) unsigned NOT NULL,
  `delta` int(10) NOT NULL,
  PRIMARY KEY (`id_player`,`date`),
  CONSTRAINT `stat_players_rank_new_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_players_rank_old`
--

DROP TABLE IF EXISTS `stat_players_rank_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_players_rank_old` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` int(10) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  PRIMARY KEY (`id_player`,`date`),
  CONSTRAINT `stat_players_rank_old_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_players_science`
--

DROP TABLE IF EXISTS `stat_players_science`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_players_science` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` mediumint(9) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  PRIMARY KEY (`id_player`,`date`),
  CONSTRAINT `stat_players_science_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stat_worlds`
--

DROP TABLE IF EXISTS `stat_worlds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_worlds` (
  `id_world` smallint(5) unsigned NOT NULL,
  `input` smallint(5) unsigned NOT NULL,
  `output` smallint(5) unsigned NOT NULL,
  `count_voran` smallint(5) unsigned NOT NULL,
  `count_liens` smallint(5) unsigned NOT NULL,
  `count_psol` smallint(5) unsigned NOT NULL,
  `rank_old_voran` int(10) unsigned NOT NULL,
  `rank_old_liens` int(10) unsigned NOT NULL,
  `rank_old_psol` int(10) unsigned NOT NULL,
  `rank_new_voran` int(10) unsigned NOT NULL,
  `rank_new_liens` int(10) unsigned NOT NULL,
  `rank_new_psol` int(10) unsigned NOT NULL,
  `bo_voran` int(10) unsigned NOT NULL,
  `bo_liens` int(10) unsigned NOT NULL,
  `bo_psol` int(10) unsigned NOT NULL,
  `nra_voran` int(10) unsigned NOT NULL,
  `nra_liens` int(10) unsigned NOT NULL,
  `nra_psol` int(10) unsigned NOT NULL,
  `ra_voran` int(10) unsigned NOT NULL,
  `ra_liens` int(10) unsigned NOT NULL,
  `ra_psol` int(10) unsigned NOT NULL,
  `level_voran` mediumint(6) unsigned NOT NULL,
  `level_liens` mediumint(6) unsigned NOT NULL,
  `level_psol` mediumint(6) unsigned NOT NULL,
  `archeology_voran` int(10) unsigned NOT NULL,
  `archeology_liens` int(10) unsigned NOT NULL,
  `archeology_psol` int(10) unsigned NOT NULL,
  `building_voran` int(10) unsigned NOT NULL,
  `building_liens` int(10) unsigned NOT NULL,
  `building_psol` int(10) unsigned NOT NULL,
  `science_voran` int(10) unsigned NOT NULL,
  `science_liens` int(10) unsigned NOT NULL,
  `science_psol` int(10) unsigned NOT NULL,
  `count_colony_voran` smallint(5) unsigned NOT NULL,
  `count_colony_liens` smallint(5) unsigned NOT NULL,
  `count_colony_psol` smallint(5) unsigned NOT NULL,
  `count_alliance` smallint(5) unsigned NOT NULL,
  `count_notavaliable_gate` smallint(5) unsigned NOT NULL,
  `count_premium` smallint(5) unsigned NOT NULL,
  `date_create` date NOT NULL,
  PRIMARY KEY (`id_world`,`date_create`),
  CONSTRAINT `stat_worlds_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `pass` varchar(50) NOT NULL,
  `salt` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `approved` enum('no','yes') NOT NULL DEFAULT 'no',
  `role` enum('user','moder') NOT NULL DEFAULT 'user',
  `date_create` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  KEY `login_2` (`login`,`pass`,`salt`)
) ENGINE=InnoDB AUTO_INCREMENT=4444 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_approved`
--

DROP TABLE IF EXISTS `users_approved`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_approved` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned NOT NULL,
  `token` varchar(100) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_activate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`,`date_create`,`date_activate`),
  KEY `id_user` (`id_user`,`date_create`),
  CONSTRAINT `users_approved_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4465 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_history`
--

DROP TABLE IF EXISTS `users_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `agent` varchar(255) NOT NULL,
  `action` mediumtext NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_user_2` (`id_user`,`date_create`),
  CONSTRAINT `users_history_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11647 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_remember`
--

DROP TABLE IF EXISTS `users_remember`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_remember` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned NOT NULL,
  `token` varchar(100) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_activate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `date_create` (`date_create`,`date_activate`),
  KEY `token` (`token`),
  CONSTRAINT `users_remember_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_search`
--

DROP TABLE IF EXISTS `users_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_search` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned NOT NULL,
  `id_world` smallint(5) unsigned NOT NULL,
  `name` text NOT NULL,
  `prop` text NOT NULL COMMENT 'Серриализованный объект настроек поиска',
  `date_touch` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_world` (`id_world`),
  CONSTRAINT `users_search_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worlds`
--

DROP TABLE IF EXISTS `worlds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worlds` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `id_version` tinyint(1) unsigned NOT NULL,
  `name` varchar(30) NOT NULL,
  `type` enum('КпК','РпР') NOT NULL,
  `date_create` datetime NOT NULL COMMENT 'дата добавления в БД',
  `date_birth` date NOT NULL,
  `intro` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_version` (`id_version`),
  CONSTRAINT `worlds_ibfk_2` FOREIGN KEY (`id_version`) REFERENCES `game_versions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worlds_battle`
--

DROP TABLE IF EXISTS `worlds_battle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worlds_battle` (
  `id_world` smallint(5) unsigned NOT NULL,
  `turn_time` int(10) unsigned NOT NULL DEFAULT '60',
  `min_time` decimal(6,3) unsigned NOT NULL DEFAULT '0.000',
  `max_time` decimal(6,3) unsigned NOT NULL DEFAULT '0.500',
  `default_time` decimal(6,3) unsigned NOT NULL DEFAULT '0.300',
  UNIQUE KEY `id_world` (`id_world`),
  CONSTRAINT `worlds_battle_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worlds_csv`
--

DROP TABLE IF EXISTS `worlds_csv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worlds_csv` (
  `id_world` smallint(5) unsigned NOT NULL,
  `url` varchar(150) NOT NULL,
  `parser` enum('old_rank','new_rank') NOT NULL,
  `hash` char(32) NOT NULL,
  `date_check` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`),
  KEY `date_check` (`date_check`),
  CONSTRAINT `worlds_csv_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worlds_dshelp`
--

DROP TABLE IF EXISTS `worlds_dshelp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worlds_dshelp` (
  `id_world` smallint(5) unsigned NOT NULL,
  `name` varchar(20) NOT NULL,
  `date_check` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`),
  CONSTRAINT `worlds_dshelp_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worlds_game_parse`
--

DROP TABLE IF EXISTS `worlds_game_parse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worlds_game_parse` (
  `id_world` smallint(5) unsigned NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `date_check` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`),
  CONSTRAINT `worlds_game_parse_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worlds_newranks`
--

DROP TABLE IF EXISTS `worlds_newranks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worlds_newranks` (
  `id_world` smallint(5) unsigned NOT NULL,
  `url` varchar(150) NOT NULL,
  `date_check` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`),
  CONSTRAINT `worlds_newranks_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worlds_nra_update`
--

DROP TABLE IF EXISTS `worlds_nra_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worlds_nra_update` (
  `id_world` smallint(5) unsigned NOT NULL,
  `date_upd` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`),
  CONSTRAINT `worlds_nra_update_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worlds_oldranks`
--

DROP TABLE IF EXISTS `worlds_oldranks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worlds_oldranks` (
  `id_world` smallint(5) unsigned NOT NULL,
  `url` varchar(150) NOT NULL,
  `date_check` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`),
  CONSTRAINT `worlds_oldranks_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worlds_property`
--

DROP TABLE IF EXISTS `worlds_property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worlds_property` (
  `id_world` smallint(5) unsigned NOT NULL,
  `compls_voran` smallint(5) unsigned NOT NULL,
  `compls_liens` smallint(5) unsigned NOT NULL,
  `compls_psol` smallint(5) unsigned NOT NULL,
  `compls_mels` smallint(5) unsigned NOT NULL,
  `count_voran` smallint(5) unsigned NOT NULL,
  `count_liens` smallint(5) unsigned NOT NULL,
  `count_psol` smallint(5) unsigned NOT NULL,
  `rank_old_voran` int(10) unsigned NOT NULL,
  `rank_old_liens` int(10) unsigned NOT NULL,
  `rank_old_psol` int(10) unsigned NOT NULL,
  `rank_new_voran` int(10) unsigned NOT NULL,
  `rank_new_liens` int(10) unsigned NOT NULL,
  `rank_new_psol` int(10) unsigned NOT NULL,
  `bo_voran` int(10) unsigned NOT NULL,
  `bo_liens` int(10) unsigned NOT NULL,
  `bo_psol` int(10) unsigned NOT NULL,
  `ra_voran` int(10) unsigned NOT NULL,
  `ra_liens` int(10) unsigned NOT NULL,
  `ra_psol` int(10) unsigned NOT NULL,
  `nra_voran` int(10) unsigned NOT NULL,
  `nra_liens` int(10) unsigned NOT NULL,
  `nra_psol` int(10) unsigned NOT NULL,
  `archeology_voran` int(10) unsigned NOT NULL,
  `archeology_liens` int(10) unsigned NOT NULL,
  `archeology_psol` int(10) unsigned NOT NULL,
  `building_voran` int(10) unsigned NOT NULL,
  `building_liens` int(10) unsigned NOT NULL,
  `building_psol` int(10) unsigned NOT NULL,
  `science_voran` int(10) unsigned NOT NULL,
  `science_liens` int(10) unsigned NOT NULL,
  `science_psol` int(10) unsigned NOT NULL,
  `count_alliance` smallint(5) unsigned NOT NULL,
  `level_voran` mediumint(7) unsigned NOT NULL,
  `level_liens` mediumint(7) unsigned NOT NULL,
  `level_psol` mediumint(7) unsigned NOT NULL,
  `count_colony_voran` smallint(5) unsigned NOT NULL,
  `count_colony_liens` smallint(5) unsigned NOT NULL,
  `count_colony_psol` smallint(5) unsigned NOT NULL,
  `count_notavaliable_gate` smallint(5) unsigned NOT NULL,
  `count_premium` smallint(5) unsigned NOT NULL,
  UNIQUE KEY `id_world` (`id_world`),
  CONSTRAINT `worlds_property_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'dseye'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-05-02 16:17:57
