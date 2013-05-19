<?php

/*
 * модель todo
 */

class App_Model_DbTable_Todo extends Mylib_DbTable_Cached
{

    protected $_name = 'todo';
    protected $_cacheName = 'default';
    
    /*
     * получить список всех todo
     */
    public function notcached_listAll()
    {
        $select = $this->select()
                ->from($this, array( 'status', 'title' ))
                ->order('status ASC');
        return $this->fetchAll($select);
    }

}
?>
