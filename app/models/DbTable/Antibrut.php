<?php

/*
 * модель auth_antibrut
 *
 * Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('antibrut')
 */
class App_Model_DbTable_Antibrut extends Mylib_DbTable_Cached
    implements App_Model_Interface_Clearable
{
    protected $_name = 'antibrut';
    protected $_cacheName = 'default';

    /*
     * удаляем данные старше N дней
     */
    public function clearOld( $days )
    {
        return $this->delete( $this->_db->quoteInto( 'date < NOW() - INTERVAL ? DAY', $days, Zend_Db::INT_TYPE ) );
    }


    /*
     * проверка на слишком частые попытки различных действий
     * @return boolean true/false
     */
    public function checkIP( $type, $ip, $try, $minutes)
    {
        $select = $this->select()
                ->from( $this, array('count' => 'COUNT(*)') )
                ->where( 'ip = INET_ATON(?)', $ip )
                ->where( 'type = ?', $type )
                ->where( "`date` > NOW() - INTERVAL ? MINUTE", $minutes, Zend_Db::INT_TYPE );

        $res = $this->fetchRow($select);
        return ( $try > intval($res->count) ) ? true : false;
    }

    /*
     * добавляем действие с аутентификацией по IP
     *
     * @param string $type ('register' | 'login' | 'registerretry' | 'feedback' | 'form') Action type
     * @param string $ip Client IP
     *
     */
    public function addIP( $type, $ip )
    {
        return $this->insert( array(
            'type' => $type,
            'ip' =>  new Zend_Db_Expr( $this->_db->quoteInto("INET_ATON(?)", $ip) )
        ) );

    }

}
?>
