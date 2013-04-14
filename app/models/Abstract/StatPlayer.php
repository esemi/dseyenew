<?php

/*
 * абстрактный класс для всех моделей статистики игроков
 */
abstract class App_Model_Abstract_StatPlayer
	extends Mylib_DbTable_Cached
	implements App_Model_Interface_Clearable
{
	protected $_primary = array('id_player','date');
	protected $_cacheName = 'up';

	protected $_tagsMap = array(
		'getStat' => array('up','dshelpra','ranks'),
	);

	final public function clearOld( $days )
	{
		return $this->delete( $this->_db->quoteInto( '`date` < NOW() - INTERVAL ? DAY', $days, Zend_Db::INT_TYPE ) );
	}

	/*
	 * максимальные дельты по миру за сегодня
	 * cli
	 */
	public function getMaxDelts($idW, $border, $limit, $abs=true)
	{
		$deltaCol = ($abs) ? 'ABS(`delta`)' : 'delta';
		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array('id_player', 'date', 'delta'))
				->join('players', "players.id = {$this->_name}.id_player", array())
				->where("id_world = ?", $idW, Zend_Db::INT_TYPE)
				->where("{$deltaCol} >= ?", $border, Zend_Db::INT_TYPE)
				->where('DATE(`date`) = CURRENT_DATE')
				->order("{$deltaCol} DESC")
				->limit($limit);
		return $this->fetchAll($select)->toArray();
	}

	/*
	 * статистика для графиков
	 */
	protected function notcached_getStat($idP)
	{
		$select = $this->select()
					->from($this, array( 'value', 'date' => "DATE_FORMAT( `date` , '%i.%H.%d.%m.%Y' )" ))
					->where('id_player = ?', $idP, Zend_Db::INT_TYPE)
					->order("{$this->_name}.date ASC");
		return $this->fetchAll($select)->toArray();
	}

	/**
	 * время последнего изменения
	 * НРА
	 */
	public function getLastChangeDate( $idP )
	{
		$select = $this->select()
				->from($this,array('date'))
				->where("id_player = ?", $idP, Zend_Db::INT_TYPE)
				->order("date DESC")
				->limit(1);
		$data = $this->fetchRow($select);
		return (is_null($data)) ? null : $data->date;
	}

	/**
	 * время последнего положительного изменения
	 * НРА
	 */
	public function getLastPossibleChangeDate( $idP )
	{
		$select = $this->select()
				->from($this,array('date'))
				->where("id_player = ?", $idP, Zend_Db::INT_TYPE)
				->where("delta > 0")
				->order("date DESC")
				->limit(1);
		$data = $this->fetchRow($select);
		return (is_null($data)) ? null : $data->date;
	}

	/**
	 * время последнего отрицательного изменения
	 * НРА
	 */
	public function getLastNegativeChangeDate( $idP )
	{
		$select = $this->select()
				->from($this,array('date'))
				->where("id_player = ?", $idP, Zend_Db::INT_TYPE)
				->where("delta < 0")
				->order("date DESC")
				->limit(1);
		$data = $this->fetchRow($select);
		return (is_null($data)) ? null : $data->date;
	}

}
