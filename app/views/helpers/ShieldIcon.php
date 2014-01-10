<?php

class Zend_View_Helper_ShieldIcon extends Zend_View_Helper_Abstract
{
	public function ShieldIcon( $status, $otherClass = '' )
	{
		return sprintf( '<div title="%s"  class="%s %s inline-block"></div>',
				($status == 0) ? 'силовой щит выключен' : 'силовой щит включен',
				($status == 0) ? 'shield_disable' : 'shield_enable',
				$otherClass );
	}
}
