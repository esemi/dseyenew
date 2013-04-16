<?php

/*
 * колонии игроков
 */
class App_Model_DbTable_PlayersColony extends Mylib_DbTable_Cached
{

    protected $_name = 'players_colony';
    protected $_cacheName = 'up';

	protected $_tagsMap = array(
		'findByName' => array('up'),
		'findByAddress' => array('up'),
	);

    public function add($idP, $compl, $sota, $name)
    {
        return $this->insert( array(
            'id_player' => $idP,
            'compl' =>  $compl,
            'sota' => $sota,
            'col_name' => $name) );
    }

    public function upd($id, $compl, $sota)
    {
        return $this->update(
                array('compl' => $compl, 'sota' => $sota),
                array( $this->_db->quoteInto( 'id = ?', $id, Zend_Db::INT_TYPE ) )
                );
    }

    public function del($id)
    {
        return $this->delete( $this->_db->quoteInto('id = ?', $id, Zend_Db::INT_TYPE ) );
    }

    /*
     * для up cli
     */
    public function delByPlayers(Array $ids)
    {
        return $this->delete( $this->_db->quoteInto('id_player IN (?)', $ids) );
    }

    /*
     * для up cli
     */
    public function getByPlayer($idP)
    {
        $select = $this->select()
                ->where('id_player = ?', $idP, Zend_Db::INT_TYPE);
        return $this->fetchAll($select)->toArray();
    }

    /*
     * получить максимальный комплекс на мельсе
     * up cli
     */
    public function getMaxCompl($idW)
    {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this, array('compl' => "MAX({$this->_name}.compl)"))
                ->joinLeft('players', "players.id = {$this->_name}.id_player", array())
                ->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
                ->where('status = ?', 'active')
                ->order( new Zend_Db_Expr('NULL') );
        $res = $this->fetchRow($select);
        return (is_null($res['compl'])) ? 0 : $res['compl'];
    }


    /*
     * получить количество колоний по рассам
     * up cli
     * day cli
     */
    public function getColonyCountByRases($idW)
    {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this, array('count_colony' => "COUNT(*)"))
                ->joinLeft('players', "players.id = {$this->_name}.id_player", array('id_rase'))
                ->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
                ->where('status = ?', 'active')
                ->group('id_rase')
                ->order( new Zend_Db_Expr('NULL') );
        return $this->fetchAll($select)->toArray();
    }

    /*
     * получить количество колоний альянсов по рассам
     * cli
     */
    public function getCountByAllianceByRase($idW)
    {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($this, array('count_colony' => "COUNT(*)"))
                ->joinLeft('players', "players.id = {$this->_name}.id_player", array('id_alliance', 'id_rase'))
                ->where('id_world = ?', $idW, Zend_Db::INT_TYPE)
                ->where('status = ?', 'active')
                ->group('id_alliance')
                ->group('id_rase')
                ->order( new Zend_Db_Expr('NULL') );
        return $this->fetchAll($select)->toArray();
    }


	/**
	 * поиск id игроков по имени колонии (аддон)
	 * @TODO check db perfomance
	 * @return array Array of int user_ids
	 */
	protected function notcached_findByName( $term )
	{
		$select = $this->select()
				->from($this, array('id'))
				->where('col_name = ?', $term);

		return $this->fetchAll($select)->toArray();
	}

	/**
	 * Поиск id игроков по адресу колонии (аддон)
	 * @TODO check db perfomance
	 * @return array Array of int user_ids
	 */
	protected function notcached_findByAddress( $term )
	{
		$select = $this->select()
				->from($this, array('id'))
				->where('CONCAT_WS(".", "4", compl, sota ) = ?', $term);

		return $this->fetchAll($select)->toArray();
	}


}
