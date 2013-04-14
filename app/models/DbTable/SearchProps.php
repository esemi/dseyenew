<?php

/*
 * хранилище всех настроек автопоисков (tinyurl)
 */
class App_Model_DbTable_SearchProps extends Mylib_DbTable_Cached
	implements App_Model_Interface_Clearable
{
	protected $_name = 'search_props';
	protected $_cacheName = 'default';

	public function clearOld($days)
	{
		return $this->delete(array( $this->_db->quoteInto( 'date_touch < NOW() - INTERVAL ? DAY', $days, Zend_Db::INT_TYPE ) ));
	}

	/*
	 * добавление нового поиска
	 */
	public function insertOrUpdate( $prop )
	{
		$uid = hash('sha256', serialize($prop));
		$sql = "INSERT INTO `{$this->_name}` (uid, prop, date_touch) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE `date_touch` = NOW()";
		$this->_db->query($sql, array($uid, serialize($prop)) );
		return $uid;
	}


	public function getByUid( $uid )
	{
		$select = $this->select()
				->from($this, array('uid', 'prop', 'date_touch'))
				->where('uid = ?', $uid)
				->limit(1);
		return $this->fetchRow($select);
	}

}
