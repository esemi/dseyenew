<?php

class Zend_View_Helper_DecodeChangeIcons extends Zend_View_Helper_Abstract
{
	public function DecodeChangeIcons($type, $onlyLastIcon=false)
	{
		switch ($type){
			case 'gate_open':
				return ($onlyLastIcon) ? $this->view->GateIcon(1) : $this->view->GateIcon(0) .' &rarr; '. $this->view->GateIcon(1);
			break;

			case 'gate_close':
				return ($onlyLastIcon) ? $this->view->GateIcon(0) : $this->view->GateIcon(1) .' &rarr; '. $this->view->GateIcon(0);
			break;

			case 'premium_enable':
				return ($onlyLastIcon) ? $this->view->PremIcon(1) : $this->view->PremIcon(0) .' &rarr; '. $this->view->PremIcon(1);
			break;

			case 'premium_disable':
				return ($onlyLastIcon) ? $this->view->PremIcon(0) : $this->view->PremIcon(1) .' &rarr; '. $this->view->PremIcon(0);
			break;

			case 'ban':
				return ($onlyLastIcon) ? $this->view->BanIcon(1) : $this->view->BanIcon(0) .' &rarr; '. $this->view->BanIcon(1);
			break;

			case 'unban':
				return ($onlyLastIcon) ? $this->view->BanIcon(0) : $this->view->BanIcon(1) .' &rarr; '. $this->view->BanIcon(0);
			break;

			case 'newbee_enable':
				return ($onlyLastIcon) ? $this->view->NewbeeIcon(1) : $this->view->NewbeeIcon(0) .' &rarr; '. $this->view->NewbeeIcon(1);
			break;

			case 'newbee_disable':
				return ($onlyLastIcon) ? $this->view->NewbeeIcon(0) : $this->view->NewbeeIcon(1) .' &rarr; '. $this->view->NewbeeIcon(0);
			break;

			case 'shield_enable':
				return ($onlyLastIcon) ? $this->view->ShieldIcon(1) : $this->view->ShieldIcon(0) .' &rarr; '. $this->view->ShieldIcon(1);
			break;

			case 'shield_disable':
				return ($onlyLastIcon) ? $this->view->ShieldIcon(0) : $this->view->ShieldIcon(1) .' &rarr; '. $this->view->ShieldIcon(0);
			break;
		}

	}
}
