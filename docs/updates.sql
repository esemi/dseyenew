UPDATE `dseye_new`.`game_versions` SET `old_ranks_rep` = 'http://destinysphere.ru/ds/ranking.php?sort=thefinal&dir=desc', `new_ranks_rep` = 'http://destinysphere.ru/ds/ranking_new.php?sort=thefinal&dir=desc' WHERE `game_versions`.`id` = 1;
UPDATE `dseye_new`.`game_versions` SET `old_ranks_rep` = 'http://ds-game.ru/ds/ranking.php?sort=thefinal&dir=desc', `new_ranks_rep` = 'http://ds-game.ru/ds/ranking_new.php?sort=thefinal&dir=desc' WHERE `game_versions`.`id` = 2;
UPDATE `dseye_new`.`game_versions` SET `old_ranks_rep` = 'http://test.destinysphere.ru/ds/ranking.php?sort=thefinal&dir=desc', `new_ranks_rep` = 'http://test.destinysphere.ru/ds/ranking_new.php?sort=thefinal&dir=desc' WHERE `game_versions`.`id` = 3;
UPDATE `dseye_new`.`game_versions` SET `old_ranks_rep` = 'http://dseye.ru/csv/game_csv_archive/http://www.destinysphere.de/ds/ranking.php?sort=thefinal&dir=desc', `new_ranks_rep` = 'http://www.destinysphere.de/ds/ranking_new.php?sort=thefinal&dir=desc' WHERE `game_versions`.`id` = 4;
UPDATE `dseye_new`.`game_versions` SET `old_ranks_rep` = 'http://ds-game.su/ds/ranking.php?sort=thefinal&dir=desc', `new_ranks_rep` = 'http://ds-game.su/ds/ranking_new.php?sort=thefinal&dir=desc' WHERE `game_versions`.`id` = 5;

UPDATE `worlds_oldranks` SET `url` = REPLACE(`url`, '&sort=username&dir=asc', '');
UPDATE `worlds_newranks` SET `url` = REPLACE(`url`, '&sort=username&dir=asc', '');

CREATE TABLE `dseye_new`.`worlds_battle_data` (
`id_world` SMALLINT( 5 ) UNSIGNED NOT NULL ,
`turn_time` INT UNSIGNED NOT NULL DEFAULT '60',
`min_time` DECIMAL( 6, 3 ) UNSIGNED NOT NULL DEFAULT '0',
`max_time` DECIMAL( 6, 3 ) UNSIGNED NOT NULL DEFAULT '0.5',
`default_time` DECIMAL( 6, 3 ) UNSIGNED NOT NULL DEFAULT '0.3',
UNIQUE (`id_world`)) ENGINE = InnoDB;

UPDATE `dseye_new`.`worlds` SET `intro` = 'Невероятный и непредсказуемый мир, вспыхнувший на окраинах вселенной по воле Создателей, дабы оттачивать на нём своё Божественное мастерство созидать новое.... В этом мире возможно всё! Тут происходят невероятные события и творятся чудеса! Мир, поражающий своими перевоплощениями и нововведениями, постоянно меняющейся структурой мира! Смогут ли жители противостоять постоянному вмешательству Создателей и быть на вершине славы или будут растерзаны мифическими армиями Богов? Существуют легенды о жителях, ушедших в небо и ставших по правую руку с Богами. Мир не подвластен реке времени, и поэтому тут протекает ураганная жизнь и молниеносные сражения! Только самые смелые и отчаянные решаются поселиться в этом мире!<b>В данном мире статус ворот пока не работает.</b>' WHERE `worlds`.`id` = 26;

RENAME TABLE `dseye_new`.`worlds_battle_data` TO `dseye_new`.`worlds_battle` ;
ALTER TABLE `worlds_battle` ADD FOREIGN KEY ( `id_world` ) REFERENCES `dseye_new`.`worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;
INSERT INTO `worlds_battle` (`id_world`, `turn_time`, `min_time`, `max_time`, `default_time`) VALUES
(3, 60, 0.000, 0.500, 0.300),
(6, 60, 0.000, 0.500, 0.300),
(7, 30, 0.000, 0.500, 0.300),
(8, 60, 0.000, 0.500, 0.300),
(10, 60, 0.050, 0.500, 0.300),
(11, 30, 0.000, 0.500, 0.300),
(12, 60, 0.020, 0.500, 0.300),
(16, 30, 0.000, 0.500, 0.300),
(18, 60, 0.000, 0.500, 0.300),
(19, 6000, 0.000, 0.500, 0.300),
(20, 30, 0.000, 0.250, 0.300),
(21, 60, 0.000, 0.500, 0.300),
(22, 60, 0.000, 0.500, 0.300),
(23, 30, 0.020, 1.000, 0.300),
(28, 60, 0.000, 0.250, 0.300),
(29, 30, 0.000, 0.500, 0.300);

DROP TABLE `form`;
DROP TABLE `feedback`, `todo`;


CREATE TABLE IF NOT EXISTS `worlds_nra_update` (
  `id_world` smallint(5) unsigned NOT NULL,
  `date_upd` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `worlds_nra_update` ADD FOREIGN KEY ( `id_world` ) REFERENCES `dseye_new`.`worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;
ALTER TABLE `worlds_nra_update` ADD `force_update` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `cron_logs` CHANGE `type` `type` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

CREATE TABLE `dseye_new`.`cron_lock` (
`type` CHAR( 10 ) NOT NULL ,
`counter` INT UNSIGNED NOT NULL ,
`date_last_lock` DATETIME NOT NULL ,
PRIMARY KEY ( `type` )
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `addon_stat` (
  `date_create` datetime NOT NULL,
  `action` varchar(255) NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  `agent` varchar(255) NOT NULL,
  `opt_data` text NOT NULL,
  KEY `date_create` (`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `dseye_new`.`game_versions` SET `forum_search_pattern` = 'http://alphaforum.destinysphere.ru/search.php?terms=all&author={-author-}&sc=1&sf=all&sr=posts&sk=t&sd=d&st=0&ch=300' WHERE `game_versions`.`id` =3;

UPDATE `dseye_new`.`game_versions` SET `forum_search_pattern` = 'http://alphaforum.destinysphere.ru/search.php?terms=all&author={-author-}&sc=1&sf=all&sr=posts&sk=t&sd=d&st=0&ch=300' WHERE `game_versions`.`id` =3;
ALTER TABLE `cron_logs` ADD INDEX ( `date` );

DROP TRIGGER IF EXISTS `players_delts`;
CREATE TABLE IF NOT EXISTS `players_trans_ligue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `old_ligue` enum('I','II','III') NOT NULL,
  `new_ligue` enum('I','II','III') NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `players_trans_ligue` ADD CONSTRAINT `players_trans_ligue_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `game_versions` ADD `game_url` VARCHAR( 100 ) NOT NULL;

CREATE TABLE IF NOT EXISTS `worlds_game_parse` (
  `id_world` smallint(5) unsigned NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `date_check` datetime NOT NULL,
  UNIQUE KEY `id_world` (`id_world`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `worlds_game_parse` ADD CONSTRAINT `worlds_game_parse_ibfk_1` FOREIGN KEY (`id_world`) REFERENCES `worlds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `worlds_game_parse` (`id_world`, `login`, `password`, `date_check`) VALUES (8, 'dseye_voda', '8185b208378a44ab', '2013-02-04 20:42:57');
