<?php
class Zend_View_Helper_WordWrap extends Zend_View_Helper_Abstract
{
	public function wordWrap( $text, $maxLen = 64 )
	{
		$len = (mb_strlen($text) > $maxLen)
			?
			mb_strripos(mb_substr($text, 0, $maxLen), ' ')
			:
			$maxLen;
		$cutStr = mb_substr($text, 0, $len);
		return ($len != $maxLen)
			?
			"{$cutStr}..."
			:
			"{$cutStr}";
	}


}
