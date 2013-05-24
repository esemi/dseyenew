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
	protected function notcached_getTransByAlliance( $idA, $limit ){}

	 /*
	 * переезды игроков мира
	 */
	protected function notcached_getTransByWorld( $idW, $limit = null, $date = null, $returnCount = false){}

}
