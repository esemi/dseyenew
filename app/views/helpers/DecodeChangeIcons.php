<?php

class Zend_View_Helper_DecodeChangeIcons extends Zend_View_Helper_Abstract
{
	public function DecodeChangeIcons($type)
	{
		switch ($type){
			case 'gate_open':
				return $this->view->GateIcon(0) .' &rarr; '. $this->view->GateIcon(1);
			break;

			case 'gate_close':
				return $this->view->GateIcon(1) .' &rarr; '. $this->view->GateIcon(0);
			break;

			case 'premium_enable':
				return $this->view->PremIcon(0) .' &rarr; '. $this->view->PremIcon(1);
			break;

			case 'premium_disable':
				return $this->view->PremIcon(1) .' &rarr; '. $this->view->PremIcon(0);
			break;

			case 'ban':
				return $this->view->BanIcon(0) .' &rarr; '. $this->view->BanIcon(1);
			break;

			case 'unban':
				return $this->view->BanIcon(1) .' &rarr; '. $this->view->BanIcon(0);
			break;

			case 'newbee_enable':
				return $this->view->NewbeeIcon(0) .' &rarr; '. $this->view->NewbeeIcon(1);
			break;

			case 'newbee_disable':
				return $this->view->NewbeeIcon(1) .' &rarr; '. $this->view->NewbeeIcon(0);
			break;

			case 'shield_enable':
				return $this->view->ShieldIcon(0) .' &rarr; '. $this->view->ShieldIcon(1);
			break;

			case 'shield_disable':
				return $this->view->ShieldIcon(1) .' &rarr; '. $this->view->ShieldIcon(0);
			break;
		}

	}
}
