<?php

/*
 * переезды сот (виртуальная таблица над двумя табличками - переездов колоний и домашек)
 */
class App_Model_DbTable_PlayersTransSots extends App_Model_Abstract_Trans
{
	const COLONY_RING = 4;

	protected $_name = 'players_trans_sots';
	protected $_cacheName = 'up';
	protected $_tagsMap = array(
		'getTransByWorld' => array('up'),
		'getTransByAlliance' => array('up'),
		'getTransByPlayer' => array('up'),
	);


	/**
	 * время последнего изменения домашней соты
	 * НРА
	 */
	public function getLastDomChangeDate( $idP )
	{
		$select = $this->select()
				->from($this,array('date'))
				->where("id_player = ?", $idP, Zend_Db::INT_TYPE)
				->where("old_ring != ?", self::COLONY_RING, Zend_Db::INT_TYPE)
				->order("date DESC")
				->limit(1);

		$data = $this->fetchRow($select);
		return (is_null($data)) ? null : $data->date;
	}

	/**
	 * время последнего приобритения колонии
	 * НРА
	 */
	public function getLastNewColonyDate( $idP )
	{
		$select = $this->select()
				->from($this,array('date'))
				->where("id_player = ?", $idP, Zend_Db::INT_TYPE)
				->where("old_ring = ?", self::COLONY_RING, Zend_Db::INT_TYPE)
				->where("old_compl IS NULL")
				->order("date DESC")
				->limit(1);
		$data = $this->fetchRow($select);
		return (is_null($data)) ? null : $data->date;
	}

	public function addTransColony($idP, $oldC=null, $oldS=null, $newC=null, $newS=null)
	{
		$data = array(
			'id_player' => $idP,
			'old_ring' => self::COLONY_RING,
			'new_ring' => self::COLONY_RING,
			'date' => new Zend_Db_Expr('NOW()') );

		if( !is_null($oldC) && !is_null($oldS) )
		{
			$data['old_compl'] = $oldC;
			$data['old_sota'] = $oldS;
		}

		if( !is_null($newC) && !is_null($newS) ){
			$data['new_compl'] =  $newC;
			$data['new_sota'] = $newS;
		}

		return $this->insert( $data );
	}

	/*
	 * переезды игрока
	 */
	protected function notcached_getTransByPlayer( $idP, $limit )
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this,
						array(
							'old_adr' => "CONCAT(old_ring, '.', old_compl, '.', old_sota)",
							'new_adr' => "CONCAT(new_ring, '.', new_compl, '.', new_sota)",
							'date' => "DATE_FORMAT(`date` , '%H:%i %d.%m.%y')",
							'sort_date' => 'date'))
				->where('id_player = ?', $idP, Zend_Db::INT_TYPE)
				->order("sort_date DESC")
				->limit( $limit );

		return $this->fetchAll($select)->toArray();
	}

	/*
	 * переезды игроков альянса
	 */
	protected function notcached_getTransByAlliance( $idA, $limit )
	{
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					'order_date' => "{$this->_name}.date",
					'date' => "DATE_FORMAT({$this->_name}.date , '%H:%i %d.%m.%y')",
					'id' => 'id_player' ))
				->join('players', "players.id = id_player",
						array(
							'nik', 'id_rase',
							'old_adr' => "CONCAT(old_ring, '.', old_compl, '.', old_sota)",
							'new_adr' => "CONCAT(new_ring, '.', new_compl, '.', new_sota)" ))
				->where("players.id_alliance = ?", $idA, Zend_Db::INT_TYPE)
				->order('order_date DESC')
				->limit($limit);

		return $this->fetchAll($select)->toArray();
	}

	 /*
	 * переезды игроков мира
	 */
	protected function notcached_getTransByWorld( $idW, $limit = null, $date = null, $returnCount = true)
	{
		$data = array( "transes" => array( ), "count" => 0 );

		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					'order_date' => "{$this->_name}.date",
					'date' => "DATE_FORMAT({$this->_name}.date , '%H:%i')",
					'id' => 'id_player') )
				->join('players', "players.id = id_player", array(
					'nik', 'id_rase', 'id_alliance',
					'old_adr' => "CONCAT(old_ring, '.', old_compl, '.', old_sota)",
					'new_adr' => "CONCAT(new_ring, '.', new_compl, '.', new_sota)" ))
				->join('alliances', 'alliances.id = players.id_alliance', array('alliance' => 'name'))
				->where("players.id_world = ?", $idW, Zend_Db::INT_TYPE)
				->order("order_date DESC");

		if(is_null($date)){
			$select->where("DATE({$this->_name}.date) = CURRENT_DATE");
		}else{
			$select->where("DATE_FORMAT({$this->_name}.date, '%d-%m-%Y') = ?", $date);
		}

		//берём количество общее
		if( $returnCount === true ){
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$count = $adapter->count();
		}

		if( !is_null($limit) )
			$select->limit( $limit );

		//берём данные ограниченные
		if( ($returnCount === true && $count > 0) || $returnCount === false ){
			if(!empty($count) && !empty($limit)){
				$data["count"] = $count - $limit;
			}
			$data["transes"] = $this->fetchAll($select)->toArray();
		}

		return $data;
	}



}
