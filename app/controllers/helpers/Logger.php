<?php

/*
 * хелпер для логирования (стандартные и наши методы логирования)
 */
class Action_Helper_Logger extends Zend_Controller_Action_Helper_Abstract
{
	protected
			$_request,
			$_ip,
			$_referer,
			$_agent,
			$_uri;

	public function init() //not __construct for $this->getActionController() call (action controller set into __construct of HelperBroker)
	{
		$this->_request = $this->getActionController()->getRequest();
		$this->_ip = $this->_request->getClientIp();
		$this->_referer = $this->_request->getServer('HTTP_REFERER', '');
		$this->_agent = $this->_request->getServer('HTTP_USER_AGENT', '');
		$this->_uri = $this->_request->getRequestUri();
		$this->_log = $this->getActionController()->getInvokeArg('bootstrap')->getResource('Log');
	}

	public function customError($error)
	{
		$this->_log->error($this->_ip. ' '. $this->_uri. ' '.  $error. ' '. $this->_referer. ' '. serialize($this->_request->getPost()). ' '. $this->_agent);
	}

	public function csrfError()
	{
		$this->_log->csrf($this->_ip. ' '. $this->_uri. ' '. $this->_referer. ' '. serialize($this->_request->getPost()). ' '. $this->_agent);
	}

	public function ajaxAccess()
	{
		$this->_log->ajax($this->_ip. ' '. $this->_uri. ' '. $this->_referer. ' '. serialize($this->_request->getPost()). ' '. $this->_agent);
	}

	public function direct()
	{
		return $this;
	}

}
