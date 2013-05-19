<?php

/*
 * модель таблицы логов крона
 */
class App_Model_DbTable_CronLogs extends Mylib_DbTable_Cached
    implements App_Model_Interface_Clearable
{
    protected $_name = 'cron_logs';
    protected $_cacheName = 'default';

	/*
	 * удаляем неинтересные логи старше N дней
	 * scav cli
	 */
	public function clearOld( $days )
	{
		return $this->delete( array($this->_db->quoteInto( 'date < NOW() - INTERVAL ? DAY', $days, Zend_Db::INT_TYPE )) );
	}


	/*
	 * получить последние логи по типу
	 */
	public function getLogsByType( $type, $limit=10 )
	{
		$select = $this->select()
				->from($this, array('id', 'date', 'size' => 'ROUND(LENGTH(`text`) / 1024)') )
				->where("type = ?", $type)
				->order('date DESC')
				->limit($limit);

		return $this->fetchAll($select)->toArray();
	}

}