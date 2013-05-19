<?php

/*
 * модель настроект основного обновления миров
 */
class App_Model_DbTable_WorldsCsv extends Mylib_DbTable_Cached
{
    protected $_name = 'worlds_csv';
    protected $_primary = 'id_world';
    protected $_cacheName = 'up';
    protected $_tagsMap = array(
        'getUpdDate' => array('up'),
    );

    /*
     * мир для обновления основных рейтингов
     * @return array | null
     */
    public function getOldCsvWorld( $minutes )
    {
        $select = $this->select()
                        ->where("date_check < NOW() - INTERVAL ? MINUTE", $minutes, Zend_Db::INT_TYPE)
                        ->order('date_check ASC')
                        ->limit(1);
        $res = $this->fetchRow($select);
        return (is_null($res)) ? null : $res->toArray();
    }

    public function updCheck($idW)
    {
        return $this->update(
                    array('date_check' => new Zend_Db_Expr('NOW()')),
                    $this->_db->quoteInto('id_world = ?', $idW, Zend_Db::INT_TYPE)
                    );
    }

    public function updHash($idW, $hash)
    {
        return $this->update(
                    array('hash' => $hash),
                    $this->_db->quoteInto('id_world = ?', $idW, Zend_Db::INT_TYPE)
                    );
    }

    protected function notcached_getUpdDate($idW)
    {
        $select = $this->select()
                        ->from($this, array('date' => 'DATE_FORMAT(`date_check`,"%H:%i %d.%m.%Y")'))
                        ->where("id_world = ?", $idW, Zend_Db::INT_TYPE)
                        ->limit(1);
        $res = $this->fetchRow($select);
        return $res['date'];
    }

}
