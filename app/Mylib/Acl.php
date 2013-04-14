<?php
/*
 * Access control list
 */
class Mylib_Acl extends Zend_Acl
{
	public function __construct()
	{
		//roles
		$this->addRole(new Zend_Acl_Role('guest'));
		$this->addRole(new Zend_Acl_Role('user'), 'guest');
		$this->addRole(new Zend_Acl_Role('moder'), 'user');
		//$this->addRole(new Zend_Acl_Role('demo'), 'user');

		//resourses
		$this->add(new Zend_Acl_Resource('profile')); //всё что связанно с профилем юзера
		$this->add(new Zend_Acl_Resource('autosearch')); //весь автопоиск юзера
		$this->add(new Zend_Acl_Resource('logs')); //логи скриптов
		$this->add(new Zend_Acl_Resource('others')); //другие фишки для залогиненых
		$this->add(new Zend_Acl_Resource('monitoring')); //весь персональный мониторинг юзера

		//юзеры
		$this->allow( 'user', 'profile', array('view', 'edit') );
		$this->allow( 'user', 'autosearch', array('view', 'add', 'del'));
		$this->allow( 'user', 'others', array('fast_search_limit_x2', 'full_search_limit_x5', 'world_history_unlimit'));
		//$this->allow( 'user', 'monitoring', array('manage') );

		//модеры
		$this->allow( 'moder', 'logs', array('view'));

	}
}