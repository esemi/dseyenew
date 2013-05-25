<?php

/*
 * переходы игроков по альянсам
 *
 */
class App_Model_DbTable_PlayersTransAlliance extends App_Model_Abstract_Trans
{

    protected $_name = 'players_trans_alliance';
    protected $_cacheName = 'up';
    protected $_tagsMap = array(
        'getTransByWorld' => array('up'),
        'getTransByAlliance' => array('up'),
        'getTransByPlayer' => array('up'),
    );


	/*
	 * переходы игрока по альянсам
	 */
	protected function notcached_getTransByPlayer( $idP, $limit ){}


    /*
     * переходы игроков альянса
     */
    protected function notcached_getTransByAlliance( $idA, $limit )
    {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this, array( 'old_alliance', 'new_alliance', 'date' => "DATE_FORMAT(`date` , '%H:%i %d.%m.%y')" ))
                ->join('players', 'players.id = id_player', array( 'id', 'nik', 'id_rase' ))
                ->join(array( 'al1' => 'alliances' ), 'al1.id = old_alliance', array( 'old_id' => 'id', 'old_name' => 'name' ))
                ->join(array( 'al2' => 'alliances' ), 'al2.id = new_alliance', array( 'new_id' => 'id', 'new_name' => 'name' ))
                ->where("old_alliance = ? OR new_alliance = ?", $idA, Zend_Db::INT_TYPE)
                ->order("{$this->_name}.date  DESC")
                ->limit($limit);
        return $this->fetchAll($select)->toArray();
    }


	/*
	 * переходы игроков мира
	 */
	protected function notcached_getTransByWorld( $idW, $limit = null, $date = null, $returnCount = true){}
}