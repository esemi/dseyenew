<?php

/*
 * модель статистики по онлайну
 */
class App_Model_DbTable_StatOnline extends Mylib_DbTable_Cached
	implements App_Model_Interface_Clearable
{

	protected $_name = 'stat_online';
	protected $_primary = array('id_version', 'date');
	protected $_cacheName = 'up';
	protected $_tagsMap = array(
		'getLastVal' => array('onlinestat'),
	);

	/*
	 * чистим старые данные
	 */
	public function clearOld( $days )
	{
		return $this->delete( $this->_db->quoteInto( 'date < CURDATE() - INTERVAL ? DAY', $days, Zend_Db::INT_TYPE ) );
	}


	public function addStat($idV, $count)
	{
		return $this->insert( array(
			'id_version' => $idV,
			'count' =>  $count ) );
	}

	/*
	 * количество игроков online
	 * по часам за месяц
	 * одна серия
	 */
	public function notcached_getAllOnline( $idV )
	{
		$select = $this->select()
						->from($this, array(
							'ser' => 'count',
							'date' => "DATE_FORMAT( `date` , '%H.%d.%m.%Y' )" ))
						->where('id_version = ?', $idV, Zend_Db::INT_TYPE)
						->order("{$this->_name}.date ASC");

		return $this->fetchAll($select)->toArray();
	}


		/*
	 * возвращает последний замер количества онлайна
	 * return int
	 */
	public function notcached_getLastVal($idV)
	{
		$select = $this->select()
						->from($this, array('count'))
						->where('id_version = ?', $idV, Zend_Db::INT_TYPE)
						->order('date DESC')
						->limit(1);
		$result = $this->fetchRow($select);
		return (!is_null($result) ) ? (int)$result->count : 0;
	}


	public function prepareForHourGraph($data)
	{
		$out = new stdClass();
		$out->name = 'Всего';
		$out->realname = 'count';
		$out->visible = true;
		$out->data = array();

		foreach( $data as $val )
			$out->data[] = array($val['date'], floatval($val['ser']));

		return array($out);
	}
}