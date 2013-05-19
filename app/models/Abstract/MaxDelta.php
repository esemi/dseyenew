<?php

/*
 * абстрактный класс для всех моделей максимальных изменений показателей (дельты)
 */
abstract class App_Model_Abstract_MaxDelta
	extends Mylib_DbTable_Cached
	implements App_Model_Interface_Clearable
{
	protected $_cacheName = 'up';

	protected $_tagsMap = array(
		'getDeltsByWorld' => array('dshelpra','ranks'),
	);

	/*
	 * удаление дельт за текущий день
	 * up cli
	 */
	final public function clearToday($idW)
	{
		return $this->delete(array(
			'DATE(`date`) = CURRENT_DATE',
			$this->_db->quoteInto( 'id_world = ?', $idW, Zend_Db::INT_TYPE )
		));
	}

	/*
	 * удаление старых дельт
	 * scav cli
	 */
	final public function clearOld( $days )
	{
		return $this->delete( $this->_db->quoteInto( '`date` < NOW() - INTERVAL ? DAY', $days, Zend_Db::INT_TYPE ) );
	}

	/*
	 * множественное добавление новых дельт
	 * up cli
	 */
	public function add($idW, Array $data)
	{
		foreach($data as $row)
			$res = $this->insert(array_merge($row, array('id_world' => $idW)));
	}


	/*
	 * максимальные дельты игроков мира
	 */
	final protected function notcached_getDeltsByWorld( $idW, $limit = null, $date = null, $returnCount = true )
	{
		$data = array( "delts" => array( ), "count" => 0 );

		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array(
					'delta',
					'date' => "DATE_FORMAT({$this->_name}.date , '%H:%i')",
					'sort_delta' => "ABS({$this->_name}.delta)" ))
				->join('players', "players.id = id_player", array( 'id', 'nik', 'id_rase', 'id_alliance' ))
				->join('alliances', "players.id_alliance = alliances.id", array( 'alliance' => 'name' ))
				->where("{$this->_name}.id_world = ?", $idW, Zend_Db::INT_TYPE)
				->order("date DESC")
				->order('sort_delta DESC');
		if( !is_null($limit) )
			$select->limit($limit);

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

			$data["delts"] = $this->fetchAll($select)->toArray();
		}

		return $data;
	}


}
