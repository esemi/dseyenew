<?php

/*
 * переезды домашек
 */
class App_Model_DbTable_PlayersTransDom extends App_Model_Abstract_Trans
	implements App_Model_Interface_Clearable
{

	protected $_name = 'players_trans_dom';
	protected $_cacheName = 'up';

	protected function notcached_getTransByPlayer( $idP, $limit )
	{
		return false;
	}

	protected function notcached_getTransByAlliance( $idA, $limit)
	{
		return false;
	}

	protected function notcached_getTransByWorld($idW, $date = null, $returnCount = false, $limit = 10)
	{
		return false;
	}
}
