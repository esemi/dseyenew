<?php

class Zend_View_Helper_PremIcon extends Zend_View_Helper_Abstract
{
	public function PremIcon( $status, $otherClass = '' )
	{
		return sprintf( '<div title="%s" class="%s %s inline-block"></div>',
				($status == 0) ? 'премиум выключен' : 'премиум включен',
				($status == 0) ? 'premium_disable' : 'premium_enable',
				$otherClass );
	}
}
