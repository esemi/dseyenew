<?php

/*
 * хелпер для создания и авто перехвата сообщений из flashMessenger-ра
 *
 */
class Action_Helper_Messenger extends Zend_Controller_Action_Helper_Abstract
{
	public function addMessage($type, $message){
		$this->getActionController()->getHelper('flashMessenger')->addMessage(array( $type => $message) );
	}

	public function getMessage(){
		$messages = $this->getActionController()->getHelper('flashMessenger')->getMessages();
		if( count($messages) > 0 && is_array($messages) ){
			foreach($messages as $mes){
				foreach( $mes as $type => $message){
					return array($type, $message);
				}
			}
		}else{
			return null;
		}
	}

	public function direct(){
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$mess = $this->getMessage();
		if( !empty($mess) ){
			$viewRenderer->view->messType = $mess[0];
			$viewRenderer->view->messText = $mess[1];
		}

	}
}
