<?php

/*
 * модель auth_antibrut
 *
 */
class App_Model_DbTable_Antibrut extends Mylib_DbTable_Cached
	implements App_Model_Interface_Clearable
{
	protected
			$_name = 'antibrut',
			$_cacheName = 'default';

	/*
	 * удаляем данные старше N дней
	 */
	public function clearOld( $days )
	{
		return $this->delete( $this->_db->quoteInto( 'date < NOW() - INTERVAL ? DAY', $days, Zend_Db::INT_TYPE ) );
	}



}