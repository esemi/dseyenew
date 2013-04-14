ALTER TABLE `users_search` CHANGE `date_create` `date_touch` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `users` DROP `last_login`;
ALTER TABLE `users_search` CHANGE `name` `name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `antibrut` CHANGE `type` `type` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `users_history` DROP `post`;
ALTER TABLE `news` DROP `desc`;

CREATE TABLE IF NOT EXISTS `search_props` (
	`uid` char(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
	`prop` int(11) NOT NULL,
	`date_create` datetime NOT NULL,
	PRIMARY KEY (`uid`),
	KEY `date_create` (`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `search_props` CHANGE `date_create` `date_touch` DATETIME NOT NULL;
ALTER TABLE `search_props` CHANGE `prop` `prop` TEXT NOT NULL;

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