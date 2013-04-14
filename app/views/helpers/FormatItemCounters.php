<?php

/*
 * отображение счётчиком основных сущностей (миры/игроки/альянсы)
 */
class Zend_View_Helper_FormatItemCounters extends Zend_View_Helper_Abstract
{
	public function formatItemCounters( $text, $count, $v=null, $l=null, $p=null, $format=true )
	{
		$out = sprintf('<strong class="bold">%s:</strong> %s', $text, $this->getVal($count, $format));

		if(!is_null($v) || !is_null($l) || !is_null($p))
			$out .= sprintf(' <nobr>(<span class="%s">%s</span> : <span class="%s">%s</span> : <span class="%s">%s</span>)</nobr>',
						$this->view->RaseColor(1),$this->getVal($v, $format),
						$this->view->RaseColor(2),$this->getVal($l, $format),
						$this->view->RaseColor(3),$this->getVal($p, $format) );

		return $out;
	}

	private function getVal($val, $format)
	{
		if(is_null($val) || $val == 0)
			return ' - ';

		return ($format) ? $this->view->NumFormat($val) : $val;
	}
}
