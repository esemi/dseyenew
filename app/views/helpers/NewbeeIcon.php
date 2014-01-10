<?php

class Zend_View_Helper_NewbeeIcon extends Zend_View_Helper_Abstract
{
	public function NewbeeIcon( $status, $otherClass = '' )
	{
		return sprintf( '<div title="%s" class="%s %s inline-block"></div>',
				($status == 0) ? 'защита новичка выключена' : 'защита новичка включена',
				($status == 0) ? 'newbee_disable' : 'newbee_enable',
				$otherClass );
	}
}
