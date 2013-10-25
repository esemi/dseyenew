<?php
class Zend_View_Helper_LinkCanonical extends Zend_View_Helper_Abstract
{
	public function linkCanonical($href = null){
		$this->view->headLink(array('rel' => 'canonical', 'href' => $href));
	}
}
