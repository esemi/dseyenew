<?php

define('APPLICATION_ENV',( trim(`hostname`) == 'vm10420.majordomo.ru')
		? 'production' : 'development');

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../app'));

define('ZEND_PATH', realpath(dirname(__FILE__) . '/../../Zend'));

define('LOG_PATH', realpath(dirname(__FILE__) . '/../../logs/dseye'));

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
