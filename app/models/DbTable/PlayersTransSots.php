<?php

/*
 * переезды сот
 */
class App_Model_DbTable_PlayersTransSots extends App_Model_Abstract_Trans
{

	protected $_name = 'players_trans_dom';
	protected $_name2 = 'players_trans_colony';
	protected $_cacheName = 'up';
	protected $_tagsMap = array(
		'getTransByWorld' => array('up'),
		'getTransByAlliance' => array('up'),
		'getTransByPlayer' => array('up'),
	);

	/*
	 * переезды игрока
	 */
	protected function notcached_getTransByPlayer( $idP, $limit )
	{
		$selectDom = $this->select()
				->setIntegrityCheck(false)
				->from($this,
						array(
							'old_adr' => "CONCAT_WS('.', ring, old_compl, old_sota)",
							'new_adr' => "CONCAT_WS('.', ring, new_compl, new_sota)",
							'date' => "DATE_FORMAT(`date` , '%H:%i %d.%m.%y')",
							'sort_date' => 'date'))
				->join('players', "players.id = {$this->_name}.id_player",array())
				->where("{$this->_name}.id_player = ?", $idP, Zend_Db::INT_TYPE);

		$selectCol = $this->select()
				->setIntegrityCheck(false)
				->from($this->_name2,
						array(
							'old_adr' => "CONCAT( '4.', old_compl, '.', old_sota)",
							'new_adr' => "CONCAT( '4.', new_compl, '.', new_sota)",
							'date' => "DATE_FORMAT(`date` , '%H:%i %d.%m.%y')",
							'sort_date' => 'date'))
				->where("{$this->_name2}.id_player = ?", $idP, Zend_Db::INT_TYPE);

		$select = $this->select()
				->union(array($selectDom, $selectCol))
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
