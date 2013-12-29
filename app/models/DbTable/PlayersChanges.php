<?php

/*
 * изменения статусов ворот и премиумов игроков
 */
class App_Model_DbTable_PlayersChanges extends App_Model_Abstract_Trans
{

	protected $_name = 'players_changes';
	protected $_cacheName = 'up';
	protected $_tagsMap = array();

	/*
	 * изменения игрока
	 */
	protected function notcached_getTransByPlayer( $idP, $limit ){}

	/*
	 * изменения игроков альянса
	 */
	protected function notcached_getTransByAlliance( $idA, $limit ){}

	/*
	 * изменения игроков мира
	 */
	protected function notcached_getTransByWorld( $idW, $limit = null, $date = null, $returnCount = true){}
}