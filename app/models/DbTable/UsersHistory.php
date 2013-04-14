<?php

/*
 * история пользователей
 */
class App_Model_DbTable_UsersHistory extends Mylib_DbTable_Cached
{
	protected $_name = 'users_history';
	protected $_cacheName = 'default';

	/*
	 * добавление новой записи в историю юзера
	 */
	public function add( $idU, $text, $request )
	{
		$this->insert( array(
			'id_user' => $idU,
			'action' =>  $text,
			'agent' => $request->getServer('HTTP_USER_AGENT', 'undefined'),
			'ip' =>  new Zend_Db_Expr( $this->_db->quoteInto("INET_ATON(?)", $request->getClientIp(false)) ) ));
	}

	/*
	 * получить несколько последних записей
	 */
	public function lastOf( $idU, $count = 5 )
	{
		$select = $this->select();
		$select->from($this, array(
					'action',
					'ip' => 'INET_NTOA(`ip`)',
					'date' => "DATE_FORMAT(`date_create` , '%H:%i %d.%m.%Y')" ))
				->where('id_user = ?', $idU, Zend_Db::INT_TYPE)
				->order('date_create DESC')
				->limit( $count );
		return $this->fetchAll($select);
	}
}
