<?php

class Zend_View_Helper_NewbeeIcon extends Zend_View_Helper_Abstract
{
	public function NewbeeIcon( $status, $otherClass = '' )
	{
		return sprintf( '<div  class="%s %s inline-block"></div>',
				($status == 0) ? 'newbee_disable' : 'newbee_enable',
				$otherClass );
	}
}
