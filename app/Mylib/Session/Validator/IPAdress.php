<?php

class Mylib_Session_Validator_IPAdress extends Zend_Session_Validator_Abstract
{
    public function setup()
    {
        $this->setValidData( $this->_getIp() );
    }
    
    public function validate()
    { 
        return ($this->_getIp() === $this->getValidData());
    }
    
    protected function _getIp()
    {
        // proxy IP address
        if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) 
        {
            $ips = explode(',', $_SERVER['HTTP_CLIENT_IP']);
            return trim($ips[0]);
        }
        
        // proxy IP address
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) 
        {
           $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
           return trim($ips[0]);
        }

        // direct IP address
        if (isset($_SERVER['REMOTE_ADDR'])) 
            return $_SERVER['REMOTE_ADDR'];
        
        return '';
    }
    
}
