
CREATE TABLE IF NOT EXISTS `players_trans_sots` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `old_ring` tinyint(1) unsigned NOT NULL,
  `old_compl` smallint(6) unsigned NOT NULL,
  `old_sota` tinyint(1) unsigned NOT NULL,
  `new_ring` tinyint(1) unsigned NOT NULL,
  `new_compl` smallint(6) unsigned NOT NULL,
  `new_sota` tinyint(1) unsigned NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `players_trans_sots` CHANGE `old_ring` `old_ring` TINYINT( 1 ) UNSIGNED NOT NULL ,
CHANGE `old_compl` `old_compl` SMALLINT( 6 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `old_sota` `old_sota` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `new_ring` `new_ring` TINYINT( 1 ) UNSIGNED NOT NULL ,
CHANGE `new_compl` `new_compl` SMALLINT( 6 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `new_sota` `new_sota` TINYINT( 1 ) UNSIGNED NULL DEFAULT NULL;

INSERT INTO players_trans_sots (`id_player`, old_ring, `old_compl`, `old_sota`, new_ring, `new_compl`, `new_sota`, `date`)
SELECT `id_player`, 4 AS old_ring, `old_compl`, `old_sota`, 4 AS new_ring, `new_compl`, `new_sota`, `date` FROM `players_trans_colony`;

INSERT INTO players_trans_sots (`id_player`, old_ring, `old_compl`, `old_sota`, new_ring, `new_compl`, `new_sota`, `date`)
(SELECT `id_player`, ring AS old_ring, `old_compl`, `old_sota`, ring AS new_ring, `new_compl`, `new_sota`, `date` FROM `players_trans_dom` JOIN players ON players.id = id_player);

ALTER TABLE `players_trans_sots` ADD FOREIGN KEY ( `id_player` ) REFERENCES `dseye_new`.`players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ;

DROP TABLE players_trans_colony;
DROP TABLE players_trans_dom;


CREATE TABLE IF NOT EXISTS `players_changes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_player` int(10) unsigned NOT NULL,
  `type` enum('gate_open','gate_close') NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_player` (`id_player`),
  KEY `date` (`date`),
  KEY `type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `players_changes`
  ADD CONSTRAINT `players_changes_ibfk_1` FOREIGN KEY (`id_player`) REFERENCES `players` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

INSERT INTO `players_changes` ( id_player, `type` , `date` )
SELECT `id_player` , IF( `new_gate` =1, 'gate_open', 'gate_close' ) AS `type` , `date`
FROM `players_trans_gate`;

DROP TABLE players_trans_gate;

ALTER TABLE `players_changes` CHANGE `type` `type` ENUM( 'gate_open', 'gate_close', 'premium_enable', 'premium_disable', 'ban', 'unban',
'newbee_enable', 'newbee_disable', 'shield_enable', 'shield_disable' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

DROP TABLE stat_players_mesto;
ALTER TABLE `players` DROP `mesto`;