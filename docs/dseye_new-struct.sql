-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Авг 16 2013 г., 09:10
-- Версия сервера: 5.5.28
-- Версия PHP: 5.3.14

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `dseye_new`
--

-- --------------------------------------------------------

--
-- Структура таблицы `addon_stat`
--

CREATE TABLE IF NOT EXISTS `addon_stat` (
  `date_create` datetime NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `agent` varchar(255) NOT NULL,
  `opt_data` text NOT NULL,
  KEY `date_create` (`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `alliances`
--

CREATE TABLE IF NOT EXISTS `alliances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` enum('active','delete') NOT NULL DEFAULT 'active',
  `date_create` datetime NOT NULL,
  `date_delete` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_world_2` (`id_world`,`name`),
  KEY `id_world` (`id_world`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=1911 ;

-- --------------------------------------------------------

--
-- Структура таблицы `alliances_property`
--

CREATE TABLE IF NOT EXISTS `alliances_property` (
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
  UNIQUE KEY `id_alliance` (`id_alliance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `antibrut`
--

CREATE TABLE IF NOT EXISTS `antibrut` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(150) NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`ip`,`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=822 ;

-- --------------------------------------------------------

--
-- Структура таблицы `cron_lock`
--

CREATE TABLE IF NOT EXISTS `cron_lock` (
  `type` char(10) NOT NULL,
  `counter` int(10) unsigned NOT NULL,
  `date_last_lock` datetime NOT NULL,
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `cron_logs`
--

CREATE TABLE IF NOT EXISTS `cron_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(150) NOT NULL,
  `text` mediumtext NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `result` enum('none','success','warning','fail') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=546310 ;

-- --------------------------------------------------------

--
-- Структура таблицы `game_versions`
--

CREATE TABLE IF NOT EXISTS `game_versions` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Структура таблицы `monitor_groups`
--

CREATE TABLE IF NOT EXISTS `monitor_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `monitor_items`
--

CREATE TABLE IF NOT EXISTS `monitor_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `id_user` smallint(5) unsigned NOT NULL,
  `id_group` int(10) unsigned DEFAULT NULL,
  `date_add` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_player_2` (`id_user`,`id_player`),
  KEY `date_add` (`date_add`),
  KEY `id_group` (`id_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat` enum('bugfix','update') NOT NULL DEFAULT 'bugfix',
  `title` varchar(50) NOT NULL,
  `text` text NOT NULL,
  `rank` mediumint(9) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=174 ;

-- --------------------------------------------------------

--
-- Структура таблицы `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `id_rase` tinyint(2) unsigned NOT NULL,
  `id_alliance` int(10) unsigned NOT NULL,
  `nik` varchar(50) NOT NULL,
  `mesto` smallint(5) unsigned NOT NULL,
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
  `gate_shield` tinyint(1) unsigned NOT NULL COMMENT 'Включен ли щит',
  `gate_newbee` tinyint(1) unsigned NOT NULL COMMENT 'Работает ли защита новичка',
  `gate_ban` tinyint(1) unsigned NOT NULL COMMENT 'Забанен ли игрок',
  `premium` tinyint(1) unsigned NOT NULL COMMENT 'Куплен премиум',
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
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=165688 ;

--
-- Триггеры `players`
--
DROP TRIGGER IF EXISTS `players_increment_stat`;
DELIMITER //
CREATE TRIGGER `players_increment_stat` AFTER UPDATE ON `players`
 FOR EACH ROW BEGIN

	IF OLD.`liga` != NEW.`liga`
		THEN INSERT DELAYED INTO players_trans_ligue (`id_player`, `old_ligue`, `new_ligue`, `date`) VALUES (OLD.id, OLD.`liga`, NEW.`liga`, now() );
	END IF;

	IF OLD.`gate` != NEW.`gate`
		THEN INSERT DELAYED INTO players_trans_gate (`id_player`, `old_gate`, `new_gate`, `date`) VALUES (OLD.id, OLD.`gate`, NEW.`gate`,  now() );
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
	THEN INSERT DELAYED INTO players_trans_dom (`id_player`, `old_compl`, `old_sota`, `new_compl`, `new_sota`, `date`) VALUES (OLD.id, OLD.`compl`, OLD.`sota`, NEW.`compl`, NEW.`sota`, now() );
	END IF;

	IF OLD.`mesto` != NEW.`mesto`
	THEN INSERT DELAYED INTO stat_players_mesto( `id_player`, `date`, `value`, `delta` ) VALUES (OLD.id, now(), NEW.mesto, CAST(NEW.mesto AS SIGNED) - CAST(OLD.mesto AS SIGNED));
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
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `players_colony`
--

CREATE TABLE IF NOT EXISTS `players_colony` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `compl` smallint(6) NOT NULL,
  `sota` tinyint(1) NOT NULL,
  `col_name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=621046 ;

-- --------------------------------------------------------

--
-- Структура таблицы `players_input`
--

CREATE TABLE IF NOT EXISTS `players_input` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `id_world` (`id_world`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=407954 ;

-- --------------------------------------------------------

--
-- Структура таблицы `players_max_bo_delta`
--

CREATE TABLE IF NOT EXISTS `players_max_bo_delta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `id_player` int(10) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `id_world` (`id_world`,`date`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=259307 ;

-- --------------------------------------------------------

--
-- Структура таблицы `players_max_rank_old_delta`
--

CREATE TABLE IF NOT EXISTS `players_max_rank_old_delta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `id_player` int(10) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `id_world` (`id_world`,`date`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1480970 ;

-- --------------------------------------------------------

--
-- Структура таблицы `players_output`
--

CREATE TABLE IF NOT EXISTS `players_output` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_world` smallint(5) unsigned NOT NULL,
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `id_world` (`id_world`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=320846 ;

-- --------------------------------------------------------

--
-- Структура таблицы `players_trans_alliance`
--

CREATE TABLE IF NOT EXISTS `players_trans_alliance` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `old_alliance` int(10) unsigned NOT NULL,
  `new_alliance` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `old_alliance` (`old_alliance`),
  KEY `new_alliance` (`new_alliance`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6084 ;

-- --------------------------------------------------------

--
-- Структура таблицы `players_trans_colony`
--

CREATE TABLE IF NOT EXISTS `players_trans_colony` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `old_compl` smallint(6) unsigned DEFAULT NULL,
  `old_sota` tinyint(1) unsigned DEFAULT NULL,
  `new_compl` smallint(6) unsigned DEFAULT NULL,
  `new_sota` tinyint(1) unsigned DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=535950 ;

-- --------------------------------------------------------

--
-- Структура таблицы `players_trans_dom`
--

CREATE TABLE IF NOT EXISTS `players_trans_dom` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `old_compl` smallint(6) unsigned NOT NULL,
  `old_sota` tinyint(1) unsigned NOT NULL,
  `new_compl` smallint(6) unsigned NOT NULL,
  `new_sota` tinyint(1) unsigned NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2786 ;

-- --------------------------------------------------------

--
-- Структура таблицы `players_trans_gate`
--

CREATE TABLE IF NOT EXISTS `players_trans_gate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `old_gate` tinyint(1) unsigned NOT NULL,
  `new_gate` tinyint(1) unsigned NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=57464 ;

-- --------------------------------------------------------

--
-- Структура таблицы `players_trans_ligue`
--

CREATE TABLE IF NOT EXISTS `players_trans_ligue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `old_ligue` enum('I','II','III') NOT NULL,
  `new_ligue` enum('I','II','III') NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3951 ;

-- --------------------------------------------------------

--
-- Структура таблицы `rases`
--

CREATE TABLE IF NOT EXISTS `rases` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Структура таблицы `search_props`
--

CREATE TABLE IF NOT EXISTS `search_props` (
  `uid` char(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `prop` text NOT NULL,
  `date_touch` datetime NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `date_create` (`date_touch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_alliances`
--

CREATE TABLE IF NOT EXISTS `stat_alliances` (
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
  PRIMARY KEY (`id_alliance`,`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_online`
--

CREATE TABLE IF NOT EXISTS `stat_online` (
  `id_version` tinyint(1) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `count` smallint(5) NOT NULL,
  PRIMARY KEY (`id_version`,`date`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_players_archeology`
--

CREATE TABLE IF NOT EXISTS `stat_players_archeology` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` mediumint(9) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  PRIMARY KEY (`id_player`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_players_bo`
--

CREATE TABLE IF NOT EXISTS `stat_players_bo` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` decimal(11,2) unsigned NOT NULL,
  `delta` decimal(11,2) NOT NULL,
  PRIMARY KEY (`id_player`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_players_building`
--

CREATE TABLE IF NOT EXISTS `stat_players_building` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` mediumint(9) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  PRIMARY KEY (`id_player`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_players_level`
--

CREATE TABLE IF NOT EXISTS `stat_players_level` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` mediumint(9) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  PRIMARY KEY (`id_player`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_players_mesto`
--

CREATE TABLE IF NOT EXISTS `stat_players_mesto` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` smallint(5) unsigned NOT NULL,
  `delta` smallint(5) NOT NULL,
  PRIMARY KEY (`id_player`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_players_nra`
--

CREATE TABLE IF NOT EXISTS `stat_players_nra` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` decimal(5,2) unsigned NOT NULL,
  `delta` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id_player`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_players_ra`
--

CREATE TABLE IF NOT EXISTS `stat_players_ra` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` decimal(5,2) unsigned NOT NULL,
  `delta` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id_player`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_players_rank_new`
--

CREATE TABLE IF NOT EXISTS `stat_players_rank_new` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` int(10) unsigned NOT NULL,
  `delta` int(10) NOT NULL,
  PRIMARY KEY (`id_player`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_players_rank_old`
--

CREATE TABLE IF NOT EXISTS `stat_players_rank_old` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` int(10) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  PRIMARY KEY (`id_player`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_players_science`
--

CREATE TABLE IF NOT EXISTS `stat_players_science` (
  `id_player` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `value` mediumint(9) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  PRIMARY KEY (`id_player`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `stat_worlds`
--

CREATE TABLE IF NOT EXISTS `stat_worlds` (
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
  PRIMARY KEY (`id_world`,`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=270 ;

-- --------------------------------------------------------

--
-- Структура таблицы `users_approved`
--

CREATE TABLE IF NOT EXISTS `users_approved` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned NOT NULL,
  `token` varchar(100) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_activate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`,`date_create`,`date_activate`),
  KEY `id_user` (`id_user`,`date_create`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=271 ;

-- --------------------------------------------------------

--
-- Структура таблицы `users_history`
--

CREATE TABLE IF NOT EXISTS `users_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `agent` varchar(255) NOT NULL,
  `action` mediumtext NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_user_2` (`id_user`,`date_create`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1147 ;

-- --------------------------------------------------------

--
-- Структура таблицы `users_remember`
--

CREATE TABLE IF NOT EXISTS `users_remember` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned NOT NULL,
  `token` varchar(100) NOT NULL,
  `date_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_activate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `date_create` (`date_create`,`date_activate`),
  KEY `token` (`token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Структура таблицы `users_search`
--

CREATE TABLE IF NOT EXISTS `users_search` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) unsigned NOT NULL,
  `id_world` smallint(5) unsigned NOT NULL,
  `name` text NOT NULL,
  `prop` text NOT NULL COMMENT 'Серриализованный объект настроек поиска',
  `date_touch` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_world` (`id_world`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Структура таблицы `worlds`
--

CREATE TABLE IF NOT EXISTS `worlds` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `id_version` tinyint(1) unsigned NOT NULL,
  `name` varchar(30) NOT NULL,
  `type` enum('КпК','РпР') NOT NULL,
  `date_create` datetime NOT NULL COMMENT 'дата добавления в БД',
  `date_birth` date NOT NULL,
  `intro` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_version` (`id_version`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

--
-- Структура таблицы `worlds_battle`
--

CREATE TABLE IF NOT EXISTS `worlds_battle` (
  `id_world` smallint(5) unsigned NOT NULL,
  `turn_time` int(10) unsigned NOT NULL DEFAULT '60',
  `min_time` decimal(6,3) unsigned NOT NULL DEFAULT '0.000',
  `max_time` decimal(6,3) unsigned NOT NULL DEFAULT '0.500',
  `default_time` decimal(6,3) unsigned NOT NULL DEFAULT '0.300',
  UNIQUE KEY `id_world` (`id_world`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `worlds_csv`
--

CREATE TABLE IF NOT EXISTS `worlds_csv` (
  `id_world` smallint(5) unsigned NOT NULL,
  `url` varchar(150) NOT NULL,
  `parser` enum('old_rank','new_rank') NOT NULL,
  `hash` char(32) NOT NULL,
  `date_check` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`),
  KEY `date_check` (`date_check`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `worlds_dshelp`
--

CREATE TABLE IF NOT EXISTS `worlds_dshelp` (
  `id_world` smallint(5) unsigned NOT NULL,
  `name` varchar(20) NOT NULL,
  `date_check` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `worlds_game_parse`
--

CREATE TABLE IF NOT EXISTS `worlds_game_parse` (
  `id_world` smallint(5) unsigned NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `date_check` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `worlds_newranks`
--

CREATE TABLE IF NOT EXISTS `worlds_newranks` (
  `id_world` smallint(5) unsigned NOT NULL,
  `url` varchar(150) NOT NULL,
  `date_check` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `worlds_nra_update`
--

CREATE TABLE IF NOT EXISTS `worlds_nra_update` (
  `id_world` smallint(5) unsigned NOT NULL,
  `date_upd` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `worlds_oldranks`
--

CREATE TABLE IF NOT EXISTS `worlds_oldranks` (
  `id_world` smallint(5) unsigned NOT NULL,
  `url` varchar(150) NOT NULL,
  `date_check` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `worlds_property`
--

CREATE TABLE IF NOT EXISTS `worlds_property` (
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
  UNIQUE KEY `id_world` (`id_world`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `alliances`
--
ALTER TABLE `alliances`
  ADD CONSTRAINT `alliances_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `alliances_property`
--
ALTER TABLE `alliances_property`
  ADD CONSTRAINT `alliances_property_ibfk_1` FOREIGN KEY (`id_alliance`) REFERENCES `alliances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `monitor_items`
--
ALTER TABLE `monitor_items`
  ADD CONSTRAINT `monitor_items_ibfk_2` FOREIGN KEY (`id_group`) REFERENCES `monitor_groups` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `monitor_items_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `players_ibfk_5` FOREIGN KEY (`id_rase`) REFERENCES `rases` (`id`),
  ADD CONSTRAINT `players_ibfk_6` FOREIGN KEY (`id_alliance`) REFERENCES `alliances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `players_colony`
--
ALTER TABLE `players_colony`
  ADD CONSTRAINT `players_colony_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `players_input`
--
ALTER TABLE `players_input`
  ADD CONSTRAINT `players_input_ibfk_2` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `players_input_ibfk_3` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `players_max_bo_delta`
--
ALTER TABLE `players_max_bo_delta`
  ADD CONSTRAINT `players_max_bo_delta_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `players_max_bo_delta_ibfk_3` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`);

--
-- Ограничения внешнего ключа таблицы `players_max_rank_old_delta`
--
ALTER TABLE `players_max_rank_old_delta`
  ADD CONSTRAINT `players_max_rank_old_delta_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `players_max_rank_old_delta_ibfk_3` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`);

--
-- Ограничения внешнего ключа таблицы `players_output`
--
ALTER TABLE `players_output`
  ADD CONSTRAINT `players_output_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `players_output_ibfk_2` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `players_trans_alliance`
--
ALTER TABLE `players_trans_alliance`
  ADD CONSTRAINT `players_trans_alliance_ibfk_5` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `players_trans_alliance_ibfk_6` FOREIGN KEY (`old_alliance`) REFERENCES `alliances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `players_trans_alliance_ibfk_7` FOREIGN KEY (`new_alliance`) REFERENCES `alliances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `players_trans_colony`
--
ALTER TABLE `players_trans_colony`
  ADD CONSTRAINT `players_trans_colony_ibfk_4` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `players_trans_dom`
--
ALTER TABLE `players_trans_dom`
  ADD CONSTRAINT `players_trans_dom_ibfk_3` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `players_trans_gate`
--
ALTER TABLE `players_trans_gate`
  ADD CONSTRAINT `players_trans_gate_ibfk_3` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `players_trans_ligue`
--
ALTER TABLE `players_trans_ligue`
  ADD CONSTRAINT `players_trans_ligue_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_alliances`
--
ALTER TABLE `stat_alliances`
  ADD CONSTRAINT `stat_alliances_ibfk_1` FOREIGN KEY (`id_alliance`) REFERENCES `alliances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_online`
--
ALTER TABLE `stat_online`
  ADD CONSTRAINT `stat_online_ibfk_1` FOREIGN KEY (`id_version`) REFERENCES `game_versions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_players_archeology`
--
ALTER TABLE `stat_players_archeology`
  ADD CONSTRAINT `stat_players_archeology_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_players_bo`
--
ALTER TABLE `stat_players_bo`
  ADD CONSTRAINT `stat_players_bo_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_players_building`
--
ALTER TABLE `stat_players_building`
  ADD CONSTRAINT `stat_players_building_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_players_level`
--
ALTER TABLE `stat_players_level`
  ADD CONSTRAINT `stat_players_level_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_players_mesto`
--
ALTER TABLE `stat_players_mesto`
  ADD CONSTRAINT `stat_players_mesto_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_players_nra`
--
ALTER TABLE `stat_players_nra`
  ADD CONSTRAINT `stat_players_nra_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_players_ra`
--
ALTER TABLE `stat_players_ra`
  ADD CONSTRAINT `stat_players_ra_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_players_rank_new`
--
ALTER TABLE `stat_players_rank_new`
  ADD CONSTRAINT `stat_players_rank_new_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_players_rank_old`
--
ALTER TABLE `stat_players_rank_old`
  ADD CONSTRAINT `stat_players_rank_old_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_players_science`
--
ALTER TABLE `stat_players_science`
  ADD CONSTRAINT `stat_players_science_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `stat_worlds`
--
ALTER TABLE `stat_worlds`
  ADD CONSTRAINT `stat_worlds_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `users_approved`
--
ALTER TABLE `users_approved`
  ADD CONSTRAINT `users_approved_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `users_history`
--
ALTER TABLE `users_history`
  ADD CONSTRAINT `users_history_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `users_remember`
--
ALTER TABLE `users_remember`
  ADD CONSTRAINT `users_remember_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `users_search`
--
ALTER TABLE `users_search`
  ADD CONSTRAINT `users_search_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `worlds`
--
ALTER TABLE `worlds`
  ADD CONSTRAINT `worlds_ibfk_2` FOREIGN KEY (`id_version`) REFERENCES `game_versions` (`id`);

--
-- Ограничения внешнего ключа таблицы `worlds_battle`
--
ALTER TABLE `worlds_battle`
  ADD CONSTRAINT `worlds_battle_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `worlds_csv`
--
ALTER TABLE `worlds_csv`
  ADD CONSTRAINT `worlds_csv_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `worlds_dshelp`
--
ALTER TABLE `worlds_dshelp`
  ADD CONSTRAINT `worlds_dshelp_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `worlds_game_parse`
--
ALTER TABLE `worlds_game_parse`
  ADD CONSTRAINT `worlds_game_parse_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `worlds_newranks`
--
ALTER TABLE `worlds_newranks`
  ADD CONSTRAINT `worlds_newranks_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `worlds_nra_update`
--
ALTER TABLE `worlds_nra_update`
  ADD CONSTRAINT `worlds_nra_update_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `worlds_oldranks`
--
ALTER TABLE `worlds_oldranks`
  ADD CONSTRAINT `worlds_oldranks_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `worlds_property`
--
ALTER TABLE `worlds_property`
  ADD CONSTRAINT `worlds_property_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
