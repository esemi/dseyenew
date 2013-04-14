<?php

class Zend_View_Helper_MemInfo extends Zend_View_Helper_Abstract
{
    public function MemInfo()
    {
        return sprintf('Пик памяти %s Mb', round(memory_get_peak_usage()/1024/1024, 2) );
    }
}
