<?php

class IndexController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$this->view->headTitle("Главная");

		$this->view->lastNews = $this->_helper->modelLoad('News')->listAll();
	}


	/*
	 * мануал по проекту
	 * можно передавать якорь на нужный раздел
	 */
	public function helpAction()
	{
		$this->view->keywords = 'Справка, faq, вопросы, проблемы';
		$this->view->description = 'Краткий экскурс по основным возможностям и инстукция по использованию системы';
		$this->view->headTitle("Справка");

		//настройки системы
		$boot = $this->getFrontController()->getParam('bootstrap');
		$this->view->scav = $boot->getOption('scav');
		$this->view->delta = $boot->getOption('deltaMax');
		$this->view->limits = $boot->getOption('limits');
	}


	public function aboutAction()
	{
		$this->view->helpLink = $this->view->url( array('id'=>'about'), 'helpView', true );

		$this->view->keywords = 'О проекте';
		$this->view->description = 'Описание проекта и настройки системы';
		$this->view->headTitle("О проекте");

		//настройки системы
		$boot = $this->getFrontController()->getParam('bootstrap');
		$this->view->botName = $boot->getOption('botname');
		$this->view->scav = $boot->getOption('scav');
		$this->view->delta = $boot->getOption('deltaMax');
	}


	public function contactAction()
	{
		$this->view->keywords = 'Контакты, Связаться с нами';
		$this->view->description = 'Контакты для связи с нами';
		$this->view->headTitle("Контакты");
	}


	/*
	 * просмотр одной новости либо списка всех новостей
	 * новости по дефолту скрыты
	 * если передан параметр якоря то яваскрипт откроет нужную новость
	 */
	public function newsAction()
	{
		$this->view->keywords = 'Новости';
		$this->view->description = 'Последние новости проекта';
		$this->view->headTitle("Новости");

		$this->view->news = $this->_helper->modelLoad('News')->listAll();
	}

	/*
	 * лента новостей в формате atom
	 */
	public function newsfeedAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$news = $this->_helper->modelLoad('News')->listAll();

		//описание фида (для импорта отдельный формат)
		//http://framework.zend.com/manual/en/zend.feed.importing.html
		$feedArray = array(
			'title'       => 'Новости DSeye.ru',
			'link'        => $this->view->url(array( ), 'newsRss', true),
			'charset'     => 'UTF-8',
			'description' => 'Новости DSeye.ru - анализатора онлайн игры Destiny Sphere.',
			'email'       => 'support@dseye.ru',
			'entries'     => array()
		);

		//добавляем записи в фид
		foreach ($news as $item)
		{
			$feedArray['entries'][] = array(
				'title'       => $item['title'],
				'link'        => $this->view->url(array( 'idN' => $item['id'] ), 'newsView', true),
				'description' => $item['title'],
				'content'     => $item['text'],
				'lastUpdate'  => $item['date_unix']
			);
		}

		Zend_Feed::importArray($feedArray, 'atom')->send();
	}

}





