<?php
/*
 * добавляет js код для загрузки и построения графиков
 * @param type string {player|online|live|indexPie}
 */
class Zend_View_Helper_AddGraph extends Zend_View_Helper_Abstract
{
	public function addGraph( $type )
	{
		$this->view->headScript()->captureStart();
		echo "jQuery(document).ready(function(){";
		echo "var g = new Graph();";

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
			case 'online':
				echo "g.loadAndDrawOnlineGraph();";
				break;
			case 'indexPie':
				echo "loadAndDrawIndexPieGraph();";
				break;
			default:
				throw new Exception('Error type in view helper "addGraph"');
				break;
		}

		echo "});";
		$this->view->headScript()->captureEnd();
	}

}
