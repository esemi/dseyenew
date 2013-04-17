<?php

/*
 * Статистика использования аддона
 */
class App_Model_DbTable_AddonStat extends Mylib_DbTable_Cached
{
	protected $_primary = 'date_create';
	protected $_name = 'addon_stat';
	protected $_cacheName = 'default';

	/*
	 * добавление новой записи в статистику использования аддона
	 */
	public function add( $action, $request, $data=array() )
	{
		$this->insert( array(
			'date_create' => new Zend_Db_Expr('NOW()'),
			'action' =>  $action,
			'ip' =>  new Zend_Db_Expr( $this->_db->quoteInto("INET_ATON(?)", $request->getClientIp(false)) ),
			'agent' => $request->getServer('HTTP_USER_AGENT', 'undefined'),
			'opt_data' => serialize($data)
		));
	}

}
