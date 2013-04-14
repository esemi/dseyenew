<?php

/*
 * ушедшие игроки
 *
 */
class App_Model_DbTable_PlayersOutput extends Mylib_DbTable_Cached
    implements App_Model_Interface_Clearable
{

    protected $_name = 'players_output';
    protected $_cacheName = 'up';
    protected $_tagsMap = array(
        'getOutputWorld' => array('up'),
    );

    /*
     * количество ушедших за сегодня игроков
     * day cli
     */
    public function countTodayByWorld( $idW )
    {
        $select = $this->select()
                ->from($this, array( 'count' => 'COUNT(*)' ))
                ->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
                ->where('DATE(`date`) = CURRENT_DATE');
        return $this->fetchRow($select)->count;
    }

    /*
     * удаление старых записей
     * scav cli
     */
    public function clearOld( $days )
    {
        return $this->delete( $this->_db->quoteInto( '`date` < NOW() - INTERVAL ? DAY', $days, Zend_Db::INT_TYPE ) );
    }



	/*
	 * ушедшие игроки мира
	 */
	protected function notcached_getOutputWorld( $idW, $limit = null , $date = null, $returnCount = true)
	{
		$data = array( "players" => array( ), "count" => 0 );

		$select = $this->select()
				->setIntegrityCheck(false)
				->from($this, array('date'))
				->join('players', "players.id = {$this->_name}.id_player", array( 'id', 'nik', 'id_rase', 'id_alliance' ))
				->join('alliances', "alliances.id = players.id_alliance", array('alliance' => 'name'))
				->where("{$this->_name}.id_world = ?", $idW, Zend_Db::INT_TYPE)
				->order("{$this->_name}.date DESC");

		if( !is_null($limit) )
			$select->limit( $limit );

		if(is_null($date))
			$select->where("DATE({$this->_name}.date) = CURRENT_DATE");
		else
			$select->where("DATE_FORMAT({$this->_name}.date, '%d-%m-%Y') = ?", $date);

		if( $returnCount === true )
		{
			$adapter = new Zend_Paginator_Adapter_DbSelect($select);
			$count = (int) $adapter->count();
		}

		if( ($returnCount === true && $count > 0) || $returnCount === false )
		{
			if( isset($count) )
				$data["count"] = $count - $limit;
			
			$data["players"] = $this->fetchAll($select)->toArray();
		}

		return $data;
	}

}