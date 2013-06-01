<?php

/*
 * изменения лиг игроков
 */
class App_Model_DbTable_PlayersTransLigue extends App_Model_Abstract_Trans
{

	protected
			$_name = 'players_trans_ligue',
			$_cacheName = 'up',
			$_tagsMap = array(
				'getTransByAlliance' => array('up', 'ranks'),
			);

	/*
	 * изменения игрока
	 */
	protected function notcached_getTransByPlayer( $idP, $limit ){}

	/*
	 * изменения игроков альянса
	 */
	protected function notcached_getTransByAlliance( $idA, $limit ){

		$select = $this->select()
					->setIntegrityCheck(false)
					->from($this, array( 'id' => 'id_player', 'old_ligue', 'new_ligue', 'date' => "DATE_FORMAT(date , '%H:%i %d.%m.%y')" ))
					->join('players', "players.id = id_player", array( 'nik', 'id_rase' ))
					->where("players.id_alliance = ?", $idA, Zend_Db::INT_TYPE)
					->order("{$this->_name}.date DESC")
					->limit($limit);
		return $this->fetchAll($select)->toArray();
	}


	/*
	 * изменения игроков мира
	 */
	protected function notcached_getTransByWorld( $idW, $limit = null, $date = null, $returnCount = true){}

}
