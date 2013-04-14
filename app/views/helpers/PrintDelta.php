<?php
class Zend_View_Helper_PrintDelta extends Zend_View_Helper_Abstract
{
	public function printDelta( $delta, $float = false)
	{
		//цвет дельты
		$class = '';
		if( $delta != 0 )
			$class = ($delta > 0) ? 'color-green' : 'color-red';

		//само значение
		$delta = ( $delta > 0 ) ?
				'+' . $this->view->NumFormat($delta,$float)
				:
				$this->view->NumFormat($delta, $float);


		printf('<span class="%s">%s</span>', $class, $delta);
	}
}
