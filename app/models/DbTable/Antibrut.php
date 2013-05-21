<?php

/*
 * модель auth_antibrut
 *
 * Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('antibrut')
 */
class App_Model_DbTable_Antibrut extends Mylib_DbTable_Cached
	implements App_Model_Interface_Clearable
{
	protected
			$_name = 'antibrut',
			$_cacheName = 'default',
			$_prop = null;

	public function init()
	{
		parent::init();

		$this->_prop = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('antibrut');
	}


	/*
	 * удаляем данные старше N дней
	 */
	public function clearOld( $days )
	{
		return $this->delete( $this->_db->quoteInto( 'date < NOW() - INTERVAL ? DAY', $days, Zend_Db::INT_TYPE ) );
	}


	/*
	 * проверка на слишком частые попытки различных действий
	 * @return boolean true/false
	 */
	public function checkIP( $type, $ip)
	{
		if( !isset($this->_prop[$type]) )
			throw new Exception("Not found antibrut type {$type}");

		$select = $this->select()
				->from( $this, array('count' => 'COUNT(*)') )
				->where( 'ip = INET_ATON(?)', $ip )
				->where( 'type = ?', $type )
				->where( "`date` > NOW() - INTERVAL ? MINUTE", $this->_prop[$type]['minutes'], Zend_Db::INT_TYPE );

		$res = $this->fetchRow($select);
		return ( $this->_prop[$type]['try'] > intval($res->count) ) ? true : false;
	}

	/*
	 * добавляем действие с аутентификацией по IP
	 *
	 * @param string $type ('register' | 'login' | 'registerretry' | 'feedback' | 'form') Action type
	 * @param string $ip Client IP
	 *
	 */
	public function addIP( $type, $ip )
	{
		return $this->insert( array(
			'type' => $type,
			'ip' =>  new Zend_Db_Expr( $this->_db->quoteInto("INET_ATON(?)", $ip) )
		) );

	}

}
?>