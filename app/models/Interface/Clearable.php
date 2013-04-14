<?php
/*
 * метод clearOld для очистки старых данных
 */
interface App_Model_Interface_Clearable
{
    public function clearOld($days);        
}
