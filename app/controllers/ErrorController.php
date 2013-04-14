<?php

class ErrorController extends Zend_Controller_Action
{
	public function errorAction()
	{
		$errors = $this->_getParam('error_handler');

		$notFoundTypes = array(
			Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE,
			Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER,
			Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION
		);
		$log = $this->getInvokeArg('bootstrap')->getResource('Log');
		if( in_array($errors->type, $notFoundTypes) || $errors->exception instanceof Mylib_Exception_NotFound)
		{
			$this->_setNotFoundMeta();
			$log->notice($this->_getLogMessage($errors));
		}else{
			$this->_setFatalErrorMeta();
			$log->crit($this->_getLogMessage($errors));
		}

		if ($this->getInvokeArg('displayExceptionMessage') == true)
			$this->view->exceptionMessage = $errors->exception->getMessage();


		if ($this->getInvokeArg('displayExceptions') == true)
			$this->view->exception = $errors->exception;

		$this->view->request = $errors->request;
	}

	protected function _getLogMessage($errors)
	{
		return sprintf('%s %s %s %s %s',
				$errors->exception->getMessage(),
				$errors->request->getClientIp(),
				$errors->request->getRequestUri(),
				$errors->request->getServer('HTTP_REFERER', 'undef'),
				$errors->request->getServer('HTTP_USER_AGENT', 'undef'));
	}

	protected function _setNotFoundMeta()
	{
		$this->getResponse()->setHttpResponseCode(404);
		$this->view->keywords = '404, Страница не найдена';
		$this->view->description = 'Страница не найдена';
		$this->view->title = 'Страница не найдена';
		$this->view->message = 'Страница не найдена';
	}

	protected function _setFatalErrorMeta()
	{
		$this->getResponse()->setHttpResponseCode(500);
		$this->view->keywords = 'Ошибка, Error';
		$this->view->description = 'Ошибка приложения';
		$this->view->title = 'Ошибка приложения';
		$this->view->message = 'Ошибка приложения';
	}
}