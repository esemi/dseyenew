
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

INSERT INTO players_trans_sots (`id_player`, old_ring, `old_compl`, `old_sota`, new_ring, `new_compl`, `new_sota`, `date`)
SELECT `id_player`, 4 AS old_ring, `old_compl`, `old_sota`, 4 AS new_ring, `new_compl`, `new_sota`, `date` FROM `players_trans_colony`;

INSERT INTO players_trans_sots (`id_player`, old_ring, `old_compl`, `old_sota`, new_ring, `new_compl`, `new_sota`, `date`)
(SELECT `id_player`, ring AS old_ring, `old_compl`, `old_sota`, ring AS new_ring, `new_compl`, `new_sota`, `date` FROM `players_trans_dom` JOIN players ON players.id = id_player);

ALTER TABLE `players_trans_sots` DROP FOREIGN KEY `players_trans_sots_ibfk_1` ,
ADD FOREIGN KEY ( `id_player` ) REFERENCES `dseye_new`.`players` (
`id`
) ON DELETE CASCADE ON UPDATE CASCADE ;