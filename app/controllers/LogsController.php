<?php

/*
 * контроллер логов
 *
 * сводка и подробный просмотр
 *
 */
class LogsController extends Zend_Controller_Action
{
	/*
	 * лог крона и подробные логи всех действий
	 */
	public function indexAction()
	{
		$this->_helper->checkAccess('logs','view','redirect');

		$res = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('resources');
		$log = $res['log'];

		$this->view->excepLog =Mylib_Utils::tail(realpath($log['main']['writerParams']['stream']), 30);
		$this->view->csrfLog =Mylib_Utils::tail(realpath($log['csrf']['writerParams']['stream']), 30);
		$this->view->errorsLog =Mylib_Utils::tail(realpath($log['error']['writerParams']['stream']), 30);

		$this->view->up = $this->_helper->modelLoad('CronLogs')->getLogsByType('up');
		$this->view->nra = $this->_helper->modelLoad('CronLogs')->getLogsByType('nra');
		$this->view->day = $this->_helper->modelLoad('CronLogs')->getLogsByType('day');
		$this->view->online = $this->_helper->modelLoad('CronLogs')->getLogsByType('onlineStatus');
		$this->view->scav = $this->_helper->modelLoad('CronLogs')->getLogsByType('scavenger');
		$this->view->oldranks = $this->_helper->modelLoad('CronLogs')->getLogsByType('oldRanks');
		$this->view->newranks = $this->_helper->modelLoad('CronLogs')->getLogsByType('newRanks');
		$this->view->gate = $this->_helper->modelLoad('CronLogs')->getLogsByType('gate');
		$this->view->csv = $this->_helper->modelLoad('CronLogs')->getLogsByType('csv');
	}

    /*
     * просмотр подробного лога
     */
    public function viewAction()
    {
        $this->_helper->checkAccess('logs','view','redirect');

        $this->_helper->layout->disableLayout();

        $log = $this->_helper->modelLoad('CronLogs')->find( (int) $this->_getParam('idL') )->current();
        if( is_null($log) )
            throw new Mylib_Exception_NotFound('Log not found');

        $this->view->log = $log->text;
    }


}





