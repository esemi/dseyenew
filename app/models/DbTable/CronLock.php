<?php

/*
 * модель таблицы счётчиков текущих проуессов крона (для локов)
 */
class App_Model_DbTable_CronLock extends Mylib_DbTable_Cached
{
    protected $_name = 'cron_lock';
    protected $_cacheName = 'default';

	/*
	 * получаем текущий лимит по типу таски
	 */
	public function getCurrentCounter( $type )
	{
		$select = $this->select()
				->from($this, array('counter'))
				->where("type = ?", $type)
				->limit(1);
		$res = $this->fetchRow($select);
		return (is_null($res)) ? 0 : intval($res->counter);
	}

	public function incCounter($type)
	{
		$sql = "INSERT INTO `{$this->_name}` (`type`, `counter`, `date_last_lock`) VALUES (?, 1, NOW()) ON DUPLICATE KEY UPDATE `counter` = `counter` + 1, `date_last_lock` = NOW()";
		return $this->_db->query($sql, array($type) );
	}

	public function decCounter($type)
	{
		$sql = "INSERT INTO `{$this->_name}` (`type`, `counter`, `date_last_lock`) VALUES (?, 0, NOW()) ON DUPLICATE KEY UPDATE `counter` = `counter` - 1";
		return $this->_db->query($sql, array($type) );
	}

}