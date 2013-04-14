<?php

/*
 * активация новых юзеров
 */
class App_Model_DbTable_UsersApproved extends Mylib_DbTable_Cached
{
	protected $_name = 'users_approved';
	protected $_cacheName = 'default';

	/*
	 * поиск id юзера по токену и обновление даты активации токена
	 */
	public function findAndUpdToken( $token )
	{
		$select = $this->select()
				->from( $this, array( 'id', 'id_user' ) )
				->where( 'token = ?', $token )
				->where( "date_activate IS NULL" );
		$res = $this->fetchRow($select);

		if( !is_null($res) )
			$this->update(
					array( 'date_activate' => new Zend_Db_Expr('NOW()') ),
					array( $this->_db->quoteInto( 'id = ?', $res->id, Zend_Db::INT_TYPE ) ) );

		return ( !is_null($res) ) ? intval($res->id_user) : null;
	}


	/*
	 * добавление нового запроса на активацию email
	 */
	public function add( $idU )
	{
		$token = hash('sha256', uniqid( mt_rand(), true ));
		$this->insert( array( 'id_user' => $idU, 'token' =>  $token ));
		return $token;
	}


}
