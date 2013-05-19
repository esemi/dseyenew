<?php

/*
 * модель feedback
 */

class App_Model_DbTable_Feedback extends Mylib_DbTable_Cached
{

    protected $_name = 'feedback';
    protected $_cacheName = 'default';


    /*
     * получить список всех feedback-ов
     */
    public function notcached_listAll()
    {
        $select = $this->select();
        $select->from($this, array( 'id', 'title', 'text', 'position' ))
               ->order('position DESC');
        return $this->fetchAll($select);
    }

    /*
     * инкремент рейтинга
     */
    public function incRank( $idF )
    {
        return $this->update(
                array( 'position' => new Zend_Db_Expr('position + 1') ), 
                array( "id = ?" => $idF) );
    }

}
?>
