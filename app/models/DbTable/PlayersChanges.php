<?php

/*
 * изменения статусов ворот и премиумов игроков
 */
class App_Model_DbTable_PlayersChanges extends App_Model_Abstract_Trans
{

	protected $_name = 'players_changes';
	protected $_cacheName = 'up';
	protected $_tagsMap = array();

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
	protected function notcached_getTransByAlliance( $idA, $limit ){}

	/*
	 * изменения игроков мира
	 */
	protected function notcached_getTransByWorld( $idW, $limit = null, $date = null, $returnCount = true){}
}