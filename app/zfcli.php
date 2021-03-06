<?php

define('APPLICATION_ENV','cli');

define('APPLICATION_PATH', realpath(__DIR__));

define('ZEND_PATH', realpath(__DIR__ . '/../../Zend'));
define('LOG_PATH', realpath(dirname(__FILE__) . '/../logs'));
define('CACHE_PATH', realpath(dirname(__FILE__) . '/../cache'));
define('SESSIONS_PATH', realpath(dirname(__FILE__) . '/../sessions'));

set_include_path(implode(PATH_SEPARATOR, array(
	ZEND_PATH,
	get_include_path()
)));


// Zend_Application
require_once 'Zend/Application.php';

$application = new Zend_Application(
	APPLICATION_ENV,
	APPLICATION_PATH . '/configs/application.ini'
);


try
{
	$opts = new Zend_Console_Getopt(
		array(
			'help|h' => 'Displays usage information.',
			'action|a=s' => 'onlinestat | oldranks | newranks | csv | scavenger | up | day | nra | gate',
		)
	);
	$opts->parse();

}catch (Zend_Console_Getopt_Exception $e) {
	exit($e->getMessage() ."\n\n". $e->getUsageMessage());
}

if(isset($opts->h))
	exit($opts->getUsageMessage());


switch( $opts->a )
{
	//сборщик мусора и ротатор статистики
	case 'scavenger':
	//сбор статистики по онлайну
	case 'onlinestat':
	//обновление собственных csv
	case 'csv':
	//ежедневный сборщик статитики по альянсам и мирам
	case 'day':

	//обновление старых рейтингов игроков одного мира
	case 'oldranks':
	//обновление основных рейтингов
	case 'up':
	//обновление новых рейтингов игроков одного мира
	case 'newranks':
	//обновление НРА
	case 'nra':
	//обновление статуса ворот
	case 'gate':

		$request = new Zend_Controller_Request_Simple($opts->a,'cli');
	break;
}

if(!isset($request))
	exit('Запрос не составлен (Cli error)');


$front = $application->getBootstrap()
		->bootstrap('frontController')
		->getResource('frontController');

$front->setRequest($request)
	->setResponse(new Zend_Controller_Response_Cli())
	->setRouter(new Mylib_Router_Cli())
	->throwExceptions(true);

$application->bootstrap()
			->run();


?>
