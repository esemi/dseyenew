<?php

/*
 * Виртуальная табличка для агрегации переходов по альянсам, изменений ворот, изменений лиг
 */
class App_Model_DbTable_PlayersTransOthers extends App_Model_Abstract_Trans
{

	protected $_name = 'players_trans_alliance';
	protected $_name2 = 'players_trans_gate';
	protected $_name3 = 'players_trans_ligue';
	protected $_cacheName = 'up';
	protected $_tagsMap = array(
		'getTransByWorld' => array('up', 'ranks'),
		'getTransByPlayer' => array('up', 'ranks'),
	);

	/*
	 * переезды игрока
	 */
	protected function notcached_getTransByPlayer( $idP, $limit )
	{
		$selectAlliance = $this->select()
				->setIntegrityCheck(false)
				->from($this->_name, array(
					'type' => new Zend_Db_Expr('"alliance"'),
					'date' => "DATE_FORMAT(`date` , '%H:%i %d.%m.%y')",
					'sort_date' => 'date'))
				->join(array( 'al1' => 'alliances' ), 'al1.id = old_alliance', array( 'old_val' => 'id', 'old_name' => 'name' ))
				->join(array( 'al2' => 'alliances' ), 'al2.id = new_alliance', array( 'new_val' => 'id', 'new_name' => 'name' ))
				->where("{$this->_name}.id_player = ?", $idP, Zend_Db::INT_TYPE);

		$selectGate = $this->select()
				->setIntegrityCheck(false)
				->from($this->_name2, array(
					'type' => new Zend_Db_Expr('"gate"'),
					'date' => "DATE_FORMAT(`date`, '%H:%i %d.%m.%y')",
					'sort_date' => 'date',
					'old_val' => 'old_gate',
					'old_name' => new Zend_Db_Expr('""'),
					'new_val' => 'new_gate',
					'new_name' => new Zend_Db_Expr('""')))
				->where("{$this->_name2}.id_player = ?", $idP, Zend_Db::INT_TYPE);

		$selectLigue = $this->select()
				->setIntegrityCheck(false)
				->from($this->_name3, array(
					'type' => new Zend_Db_Expr('"ligue"'),
					'date' => "DATE_FORMAT(`date`, '%H:%i %d.%m.%y')",
					'sort_date' => 'date',
					'old_val' => 'old_ligue',
					'old_name' => new Zend_Db_Expr('""'),
					'new_val' => 'new_ligue',
					'new_name' => new Zend_Db_Expr('""')))
				->where("{$this->_name3}.id_player = ?", $idP, Zend_Db::INT_TYPE);

		$select = $this->select()
				->union(array($selectAlliance, $selectGate, $selectLigue))
				->order("sort_date DESC")
				->limit( $limit );

		$result = $this->fetchAll($select);
		return ( !is_null($result) ) ? $result->toArray() : array();
	}

	/*
	 * переезды игроков альянса
	 */
	protected function notcached_getTransByAlliance( $idA, $limit ){}

	 /*
	 * переезды игроков мира
	 */
	protected function notcached_getTransByWorld( $idW, $limit = null, $date = null, $returnCount = false)
	{
		$data = array( "transes" => array( ), "count" => 0 );

		$selectAlliance = $this->select()
				->setIntegrityCheck(false)
				->from($this->_name, array(
					'type' => new Zend_Db_Expr('"alliance"'),
					'sort_date' => 'date',
					'id_player',
					'date' => "DATE_FORMAT(date , '%H:%i')",
					'old_val' => 'old_alliance',
					'new_val' => 'new_alliance'
				))
				->join("players", "players.id = id_player", array( 'nik', 'id_rase', 'id_alliance' ))
				->join(array( 'al1' => 'alliances' ), 'al1.id = old_alliance', array( 'old_name' => 'name' ))
				->join(array( 'al2' => 'alliances' ), 'al2.id = new_alliance', array( 'new_name' => 'name' ))
				->where("players.id_world = ?", $idW, Zend_Db::INT_TYPE);

		$selectGate = $this->select()
				->setIntegrityCheck(false)
				->from($this->_name2, array(
					'type' => new Zend_Db_Expr('"gate"'),
					'sort_date' => 'date',
					'id_player',
					'date' => "DATE_FORMAT(date , '%H:%i')",
					'old_val' => 'old_gate',
					'new_val' => 'new_gate'))
				->join('players', "players.id = id_player", array( 'nik', 'id_rase', 'id_alliance' ))
				->join('alliances', "alliances.id = players.id_alliance", array(
					'old_name' => new Zend_Db_Expr('""'),
					'new_name' => 'name'))
				->where("players.id_world = ?", $idW, Zend_Db::INT_TYPE);

		$selectLigue = $this->select()
				->setIntegrityCheck(false)
				->from($this->_name3, array(
					'type' => new Zend_Db_Expr('"ligue"'),
					'sort_date' => 'date',
					'id_player',
					'date' => "DATE_FORMAT(`date`, '%H:%i')",
					'old_val' => 'old_ligue',
					'new_val' => 'new_ligue'))
				->join('players', "players.id = id_player", array( 'nik', 'id_rase', 'id_alliance' ))
				->join('alliances', "alliances.id = players.id_alliance", array(
					'old_name' => new Zend_Db_Expr('""'),
					'new_name' => 'name'))
				->where("players.id_world = ?", $idW, Zend_Db::INT_TYPE);

		if(is_null($date))
		{
			$selectAlliance->where("DATE({$this->_name}.date) = CURRENT_DATE");
			$selectGate->where("DATE({$this->_name2}.date) = CURRENT_DATE");
			$selectLigue->where("DATE({$this->_name3}.date) = CURRENT_DATE");
		}else{
			$selectAlliance->where("DATE_FORMAT({$this->_name}.date, '%d-%m-%Y') = ?", $date);
			$selectGate->where("DATE_FORMAT({$this->_name2}.date, '%d-%m-%Y') = ?", $date);
			$selectLigue->where("DATE_FORMAT({$this->_name3}.date, '%d-%m-%Y') = ?", $date);
		}

		$select = $this->select()
				->union(array($selectAlliance, $selectGate, $selectLigue))
				->order("sort_date DESC");

		if( !is_null($limit) )
			$select->limit( $limit );

		//берём количество общее
		if( $returnCount === true )
		{
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$count = $adapter->count();
		}

		//берём данные ограниченные
		if( ($returnCount === true && $count > 0) || $returnCount === false )
		{
			if(isset($count))
				$data["count"] = $count - $limit;

			$data["transes"] = $this->fetchAll($select)->toArray();
		}
		return $data;
	}

}
