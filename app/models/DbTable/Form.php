<?php

/*
 * модель form
 */

class App_Model_DbTable_Form extends Mylib_DbTable_Cached
{

    protected $_name = 'form';
    protected $_cacheName = 'default';


    /*
     * добавляем сообщение
     */
    public function addPost( $contact, $text )
    {
        $this->insert(array(
            'contacts' => $contact,
            'text' => $text
        ));
    }


    public function validate( $contact )
    {
        return ( preg_match('/^[\w\s\-\+@\.\,А-ЯЁа-яё]{5,150}$/u', $contact) ) 
                ? true : false;
    }

}
?>
