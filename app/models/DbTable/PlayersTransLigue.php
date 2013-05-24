<?php

/*
 * изменения лиг игроков
 */
class App_Model_DbTable_PlayersTransLigue extends App_Model_Abstract_Trans
{

	protected $_name = 'players_trans_ligue';
	protected $_cacheName = 'up';

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
