<?php
class Zend_View_Helper_AllianceLink extends Zend_View_Helper_Abstract
{
	public function allianceLink( $idW, $idA, $name, $blank = false, $underline = true, $otherClass = '' )
	{
		$url = $this->view->url(array( 'idW' => $idW, 'idA' => $idA ), 'allianceIndex', true) ;
		$target = ($blank) ? 'target="_blank"' : '';
		$line = ($underline) ? 'no-underline' : '';
		$name = $this->view->escape($name);
		
		return sprintf('<a title="Страница альянса %s" %s href="%s" class="%s %s">%s</a>', $name, $target, $url, $line, $otherClass, $name);
	}
}
