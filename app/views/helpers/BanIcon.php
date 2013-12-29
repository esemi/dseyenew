<?php

class Zend_View_Helper_BanIcon extends Zend_View_Helper_Abstract
{
	public function BanIcon( $status, $otherClass = '' )
	{
		return sprintf( '<div  class="%s %s inline-block"></div>',
				($status == 0) ? 'unban' : 'ban',
				$otherClass );
	}
}
