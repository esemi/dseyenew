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
	protected function notcached_getTransByWorld( $idW, $limit = null, $date = null, $returnCount = true)
	{
		$data = array( "transes" => array( ), "count" => 0 );

		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array( 'old_alliance', 'new_alliance', 'date' => "DATE_FORMAT(date , '%H:%i')" ))
				->join("players", "players.id = id_player", array( 'id', 'nik', 'id_rase' ))
				->join(array( 'al1' => 'alliances' ), 'al1.id = old_alliance', array( 'old_id' => 'id', 'old_name' => 'name' ))
				->join(array( 'al2' => 'alliances' ), 'al2.id = new_alliance', array( 'new_id' => 'id', 'new_name' => 'name' ))
				->where("players.id_world = ?", $idW, Zend_Db::INT_TYPE)
				->order("{$this->_name}.date DESC");

		if( !is_null($limit) )
			$select->limit( $limit );

		if(is_null($date))
			$select->where("DATE({$this->_name}.date) = CURRENT_DATE");
		else
			$select->where("DATE_FORMAT({$this->_name}.date, '%d-%m-%Y') = ?", $date);


		if( $returnCount === true  )
		{
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$count = (int) $adapter->count();
		}

		if( ($returnCount === true && $count > 0) || $returnCount === false )
		{
			if( isset($count) )
				$data["count"] = $count - $limit;

			$data["transes"] = $this->fetchAll($select)->toArray();
		}

		return $data;
	}
}