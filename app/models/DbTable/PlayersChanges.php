<?php

/*
 * изменения статусов ворот и премиумов игроков
 */
class App_Model_DbTable_PlayersChanges extends App_Model_Abstract_Trans
{

	protected $_name = 'players_changes';
	protected $_cacheName = 'up';
	protected $_tagsMap = array(
		'getTransByAlliance' => array('gate')
	);

	/**
	 * время последнего изменения
	 * НРА
	 */
	public function getLastChangeDate( $idP )
	{
		$select = $this->select()
				->from($this,array('date'))
				->where("id_player = ?", $idP, Zend_Db::INT_TYPE)
				->where("type IN (?)", array('gate_open','gate_close','premium_enable','premium_disable','shield_enable','shield_disable'))
				->order("date DESC")
				->limit(1);

		$data = $this->fetchRow($select);
		return (is_null($data)) ? null : $data->date;
	}


	/*
	 * изменения игрока
	 */
	protected function notcached_getTransByPlayer( $idP, $limit ){}

	/*
	 * изменения игроков альянса
	 */
	protected function notcached_getTransByAlliance($idA, $limit){
		$select = $this->select()
					->setIntegrityCheck(false)
					->from($this, array( 'id' => 'id_player', 'type', 'date' => "DATE_FORMAT(date , '%H:%i %d.%m.%y')" ))
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