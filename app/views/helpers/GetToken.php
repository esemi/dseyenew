<?php

class Zend_View_Helper_GetToken extends Zend_View_Helper_Abstract
{
    public function getToken()
    {
        $user = Zend_Auth::getInstance()->getStorage()->read();
        return ( is_null($user) ) ? null : $user->csrf;
    }
}
