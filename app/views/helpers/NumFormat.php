<?php
class Zend_View_Helper_NumFormat extends Zend_View_Helper_Abstract
{
	public function NumFormat( $int, $isDecimal = false )
	{
		 return ($isDecimal === false) ?
				 number_format( (int)$int, 0, '', '`')
				 :
				 number_format( (float)$int, 2, '.', '`');
	}
}
