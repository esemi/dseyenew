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
		'getTransByAlliance' => array('up'),
		'getTransByPlayer' => array('up'),
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
	protected function notcached_getTransByAlliance( $idA, $limit )
	{
		$selectDom = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					'order_date' => "{$this->_name}.date",
					'date' => "DATE_FORMAT({$this->_name}.date , '%H:%i %d.%m.%y')",
					'id' => 'id_player' ))
				->join('players', "players.id = id_player",
						array(
							'nik', 'id_rase',
							'old_adr' => "CONCAT_WS('.', ring, old_compl, old_sota)",
							'new_adr' => "CONCAT_WS('.', ring, new_compl, new_sota)" ))
				->where("players.id_alliance = ?", $idA, Zend_Db::INT_TYPE);

		$selectCol = $this->select()
				->setIntegrityCheck(false)
				->from($this->_name2, array(
					'order_date' => "{$this->_name2}.date",
					'date' => "DATE_FORMAT({$this->_name2}.date , '%H:%i %d.%m.%y')",
					'id' => 'id_player') )
				->join('players', "players.id = id_player",
						array(
							'nik', 'id_rase',
							'old_adr' => "CONCAT( '4.', old_compl, '.', old_sota)",
							'new_adr' => "CONCAT( '4.', new_compl, '.', new_sota)" ))
				->where("players.id_alliance = ?", $idA, Zend_Db::INT_TYPE);

		$select = $this->select()
				->union(array($selectDom, $selectCol))
				->order('order_date DESC')
				->limit($limit);


		return $this->fetchAll($select)->toArray();
	}

	 /*
	 * переезды игроков мира
	 */
	protected function notcached_getTransByWorld( $idW, $limit = null, $date = null, $returnCount = false)
	{
		$data = array( "transes" => array( ), "count" => 0 );

		$selectDom = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					'order_date' => "{$this->_name}.date",
					'date' => "DATE_FORMAT({$this->_name}.date , '%H:%i')",
					'id' => 'id_player') )
				->join('players', "players.id = id_player", array(
					'nik', 'id_rase', 'id_alliance',
					'old_adr' => "CONCAT_WS('.', ring, old_compl, old_sota)",
					'new_adr' => "CONCAT_WS('.', ring, new_compl, new_sota)" ))
				->join('alliances', 'alliances.id = players.id_alliance', array('alliance' => 'name'))
				->where("players.id_world = ?", $idW, Zend_Db::INT_TYPE);

		$selectCol = $this->select()
				->setIntegrityCheck(false)
				->from($this->_name2, array(
					'order_date' => "{$this->_name2}.date",
					'date' => "DATE_FORMAT({$this->_name2}.date , '%H:%i')",
					'id' => 'id_player') )
				->join('players', "players.id = id_player", array(
					'nik', 'id_rase', 'id_alliance',
					'old_adr' => "CONCAT( '4.', old_compl, '.', old_sota)",
					'new_adr' => "CONCAT( '4.', new_compl, '.', new_sota)" ))
				->join('alliances', "alliances.id = players.id_alliance", array('alliance' => 'name'))
				->where("players.id_world = ?", $idW, Zend_Db::INT_TYPE);

		if(is_null($date))
		{
			$selectDom->where("DATE({$this->_name}.date) = CURRENT_DATE");
			$selectCol->where("DATE({$this->_name2}.date) = CURRENT_DATE");
		}else{
			$selectDom->where("DATE_FORMAT({$this->_name}.date, '%d-%m-%Y') = ?", $date);
			$selectCol->where("DATE_FORMAT({$this->_name2}.date, '%d-%m-%Y') = ?", $date);
		}

		$select = $this->select()
				->union(array($selectDom, $selectCol))
				->order("order_date DESC");

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
