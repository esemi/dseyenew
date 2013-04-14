<?php
/*
 * выводит стандартное сообщение
 *
 * @param string type (error|success)
 * @param string text message
 */
class Zend_View_Helper_PrintMessage extends Zend_View_Helper_Abstract
{
    public function printMessage( $type = null, $text = null )
    {
        if( is_null($type) || is_null($text) )
            return '';
        
        switch ($type)
        {
            case 'error':
                printf('<p class="color-red text-center">%s</p>', $text);
            break;

            case 'success':
                printf('<p class="color-green text-center">%s</p>', $text);
            break;

            default:
                throw new Exception('Undefined error type (helper printMessage())');
            break;
        }
    }
}
