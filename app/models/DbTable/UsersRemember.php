<?php

/*
 * восстановление пароля пользователей
 */
class App_Model_DbTable_UsersRemember extends Mylib_DbTable_Cached
{
	protected
		$_name = 'users_remember',
		$_cacheName = 'default';

	/*
	 * поиск id юзера по токену восстановления пароля
	 */
	public function findAndUpdToken( $token, $hours )
	{
		$select = $this->select()
				->from( $this, array( 'id', 'id_user' ) )
				->where( 'token = ?', $token )
				->where( "date_create > NOW() - INTERVAL ? HOUR", $hours, Zend_Db::INT_TYPE )
				->where( "date_activate IS NULL" );
		$res = $this->fetchRow($select);

		if( !is_null($res) )
			$this->update(
					array( 'date_activate' => new Zend_Db_Expr('NOW()') ),
					array( $this->_db->quoteInto( 'id = ?', $res->id, Zend_Db::INT_TYPE ) ) );

		return ( !is_null($res) ) ? intval($res->id_user) : null;
	}


	/*
	 * добавление нового запроса на воостановление пароля
	 */
	public function add( $idU )
	{
		$token = hash('sha256', uniqid( mt_rand(), true ));
		$this->insert( array( 'id_user' => $idU, 'token' =>  $token ) );
		return $token;
	}


}
