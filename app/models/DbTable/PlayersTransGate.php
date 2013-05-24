<?php

/*
 * изменения статусов ворот игроков
 */
class App_Model_DbTable_PlayersTransGate extends App_Model_Abstract_Trans
{

	protected $_name = 'players_trans_gate';
	protected $_cacheName = 'up';
	protected $_tagsMap = array(
		'getTransByWorld' => array('up'),
		'getTransByAlliance' => array('up'),
		'getTransByPlayer' => array('up'),
	);

	/*
	 * изменения игрока
	 */
	protected function notcached_getTransByPlayer( $idP, $limit )
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array( 'old_gate', 'new_gate', 'date' => "DATE_FORMAT(`date`, '%H:%i %d.%m.%y')" ))
				->where('id_player = ?', $idP, Zend_Db::INT_TYPE)
				->order("{$this->_name}.date DESC")
				->limit($limit);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * изменения игроков альянса
	 */
	protected function notcached_getTransByAlliance( $idA, $limit )
	{
		$select = $this->select()
					->setIntegrityCheck(false)
					->from($this, array( 'id' => 'id_player', 'old_gate', 'new_gate', 'date' => "DATE_FORMAT(date , '%H:%i %d.%m.%y')" ))
					->join('players', "players.id = id_player", array( 'nik', 'id_rase' ))
					->where("players.id_alliance = ?", $idA, Zend_Db::INT_TYPE)
					->order("{$this->_name}.date DESC")
					->limit($limit);
		return $this->fetchAll($select)->toArray();
	}


	/*
	 * изменения игроков мира
	 */
	protected function notcached_getTransByWorld( $idW, $limit = null, $date = null, $returnCount = true)
	{
		$data = array( "transes" => array( ), "count" => 0 );

		$select = $this->select()
					->setIntegrityCheck(false)
					->from($this, array( 'id' => 'id_player', 'old_gate', 'new_gate', 'date' => "DATE_FORMAT(date , '%H:%i')" ) )
					->join('players', "players.id = id_player", array( 'nik', 'id_rase', 'id_alliance' ))
					->join('alliances', "alliances.id = players.id_alliance", array('alliance' => 'name'))
					->where("players.id_world = ?", $idW, Zend_Db::INT_TYPE)
					->order("{$this->_name}.date DESC");

		if( !is_null($limit) )
			$select->limit( $limit );

		if(is_null($date))
			$select->where("DATE({$this->_name}.date) = CURRENT_DATE");
		else
			$select->where("DATE_FORMAT({$this->_name}.date, '%d-%m-%Y') = ?", $date);


		if( $returnCount === true )
		{
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$count = (int) $adapter->count();
		}


		if( ($returnCount === true && $count > 0) || $returnCount === false )
		{
			if( isset($count) )
				$data["count"] = $count - $limit;

			$data["transes"] = $this->fetchAll($select)->toArray();
		}

		return $data;
	}

}
