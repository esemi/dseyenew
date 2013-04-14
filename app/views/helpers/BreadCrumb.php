<?php
class Zend_View_Helper_BreadCrumb extends Zend_View_Helper_Abstract
{
	public function breadCrumb( $crumbs, $margin=true )
	{
		if( !is_array($crumbs) || count($crumbs) == 0 )
			throw new Exception( 'Error crumbs array in breadCrumb helper' );

		$str = implode(' &mdash; ', $crumbs);
		$marginStr = ($margin) ? 'mrg-left-21' : '';
		printf('<div class="main-width rubber-block"><h2 class="%s small-title mrg-bottom-13">%s</h2></div>',$marginStr, $str);
	}


}
