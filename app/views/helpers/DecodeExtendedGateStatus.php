<?php

/*
 * выводит читаемый статус игровых сущностей (миры/альянсы/игроки)
 */
class Zend_View_Helper_DecodeExtendedGateStatus extends Zend_View_Helper_Abstract
{
	public function decodeExtendedGateStatus( $data )
	{
		$statuses = array();
		if($data['gate_shield'] == 1)
			$statuses[] = 'щит';
		if($data['gate_newbee'] == 1)
			$statuses[] = 'новичок';
		if($data['gate_ban'] == 1)
			$statuses[] = 'бан';

		if( count($statuses) > 0 )
			return 'Недоступен (' . implode(', ', $statuses) . ')';
		return '';
	}
}
