<?php

/*
 * игроки, добавленный в перс мониторинг пользователей
 */
class App_Model_DbTable_MonitorItems extends Mylib_DbTable_Cached
{
    protected $_name = 'monitor_items';
    protected $_cacheName = 'default';
    
    /*
     * наличие игрока у пользователя
     */
    public function issetByUser( $idP, $idU )
    {
        $select = $this->select()
                ->from($this, array('id_player'))
                ->where('id_player = ?', $idP, Zend_Db::INT_TYPE)
                ->where('id_user = ?', $idU, Zend_Db::INT_TYPE)
                ->limit(1);

        return !is_null($this->fetchRow($select));
    }

    /*
     * добавляем игрока в мониторинг пользователя
     */
    public function add( $idP, $idU )
    {
        return $this->insert( array(
            'id_player' => $idP,
            'id_user' =>  $idU,
            'date_add' => new Zend_Db_Expr('NOW()') ));
    }
    
    /*
     * удалить игрока из мониторинга
     */
    public function del( $idP, $idU )
    {
        return $this->delete( 
                array(
                    'id_player = ?' => $idP,
                    'id_user = ?' =>  $idU) );
    }




    /*
     * получить id-name групп
     *
    public function getItemGroupsByUser( $idU )
    {
        $select = $this->select()
                ->from($this, array('id_group'))
                ->where('id_player = ?', $idP)
                ->where('id_user = ?', $idU)
                ->limit(1);
    }
    */
    
}
