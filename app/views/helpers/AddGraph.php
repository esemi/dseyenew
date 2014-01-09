<?php

/**
 * добавляет js код для загрузки и построения графиков
 */
class Zend_View_Helper_AddGraph extends Zend_View_Helper_Abstract
{
	public function addGraph( $type )
	{
		$this->view->headScript()->captureStart();
		echo "jQuery(document).ready(function(){";

		switch( $type )
		{
			case 'player':
				echo "loadAndDrawPlayerGraph();";
				break;
			case 'world':
				echo "loadAndDrawWorldGraph();";
				break;
			case 'alliance':
				echo "loadAndDrawAllianceGraph();";
				break;
		}

		echo "});";
		$this->view->headScript()->captureEnd();
	}
}