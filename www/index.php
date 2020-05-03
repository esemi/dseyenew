<?php

define('APPLICATION_ENV', (getenv('APPLICATION_ENV')) ? getenv('APPLICATION_ENV') : 'production');

define('ZEND_PATH', realpath(dirname(__FILE__) . '/../../Zend'));
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../app'));
define('LOG_PATH', realpath(dirname(__FILE__) . '/../logs'));
define('CACHE_PATH', realpath(dirname(__FILE__) . '/../cache'));
define('SESSIONS_PATH', realpath(dirname(__FILE__) . '/../sessions'));

//временная заглушка
/*if( APPLICATION_ENV == 'production' )
{
     include 'index_.html';
     exit;
}/**/

set_include_path(implode(PATH_SEPARATOR, array(
	ZEND_PATH,
	get_include_path()
)));

require_once 'Zend/Application.php';


$application = new Zend_Application(
	APPLICATION_ENV,
	APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap()
			->run();
