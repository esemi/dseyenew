<?php

/*
 * пользователи
 */
class App_Model_DbTable_Users extends Mylib_DbTable_Cached
{
	protected $_name = 'users';
	protected $_cacheName = 'default';

	/*
	 * валидация данных регистрации
	 *
	 * @return mixed true or string with error text
	 */
	public function validateNewUser( $data, $captcha = null )
	{
		//обязательные поля
		if( empty($data['login']) || empty($data['pass']) || empty($data['email']) ||
			( !is_null($captcha) && (empty($data['recaptcha_challenge_field']) || empty($data['recaptcha_response_field']) ) )
		)
			return 'Не переданны одно или несколько обязательных полей';

		//капча
		if(!is_null($captcha))
		{
			$captchaRes = $captcha->verify($data['recaptcha_challenge_field'], $data['recaptcha_response_field']);
			if( !$captchaRes->isValid() )
				return 'Текст с изображения введён неверно';
		}

		//логин неверен
		if( !$this->_checkLogin($data['login']) )
			return 'Недопустимый логин';

		//логин уже занят
		$validator = new Zend_Validate_Db_NoRecordExists( array( 'table' => 'users', 'field' => 'login' ) );
		if (!$validator->isValid($data['login']))
			return 'Данный логин уже занят';

		//пароль неверен
		if( !$this->_checkPass($data['pass']) )
			return 'Некорректный пароль';

		//мыло
		if( mb_strlen($data['email']) > 255 )
			return 'Некорректный адрес электронной почты';

		//мыло
		$validMail = new Zend_Validate_EmailAddress( array( 'mx' => true, 'deep' => true ) );
		if( !$validMail->isValid( $data['email'] ) )
		{
			$m = $validMail->getMessages();
			$m = implode(', ', $m);
			return sprintf('Некорректный адрес электронной почты<br>(%s)', $m);
		}

		return true;
	}

	public function validatePass( $pass, $pass2, $idU, $oldPass )
	{
		//пароли не совпадают
		if( $pass !== $pass2 )
			return 'Пароли не совпадают';

		//пароль неверен
		if( !$this->_checkPass($pass) )
			return 'Некорректное значение нового пароля';

		//проверим старый пароль
		$select = $this->select()
					->from($this, array('id'))
					->where('id = ?', $idU, Zend_Db::INT_TYPE)
					->where('pass = SHA1( CONCAT( ?, `salt` ) )', $oldPass)
					->limit(1);

		if( is_null($this->fetchRow($select)) )
			return 'Некорректное значение текущего пароля';

		return true;
	}

	/*
	 * поиск активированнного юзера для восстановления пароля
	 */
	public function findForRemember( $login, $email )
	{
		$select = $this->select()
				->from($this, array('id','email','login'))
				->where('login = ?', $login)
				->where('email = ?', $email)
				->limit(1);

		return $this->fetchRow($select);
	}


	/*
	 * активация аккаунта юзера
	 */
	public function approve( $idU )
	{
		return $this->update(
				array( 'approved' =>  'yes' ),
				array( $this->_db->quoteInto( 'id = ?', $idU, Zend_Db::INT_TYPE ) ) );
	}


	/*
	 * добавление нового юзера
	 */
	public function addNew( $post )
	{
		$salt = md5(uniqid('', true));
		return $this->insert( array(
			'login' => $post['login'],
			'pass' =>  new Zend_Db_Expr( $this->_db->quoteInto("SHA1( CONCAT( ?, '{$salt}') )", $post['pass']) ),
			'salt' => $salt,
			'email' => $post['email'],
			'date_create' => new Zend_Db_Expr('NOW()') ));
	}

	/*
	 * обновление пароля
	 */
	public function updPass( $idU, $newPass = null )
	{
		if( is_null($newPass))
			$newPass = Mylib_Utils::rand_str();

		$this->update(
				array( 'pass' =>  new Zend_Db_Expr( $this->_db->quoteInto("SHA1( CONCAT( ?, `salt`) )", $newPass) ) ),
				array( $this->_db->quoteInto( 'id = ?', $idU, Zend_Db::INT_TYPE ) ) );
		return $newPass;
	}

	/*
	 * ищет юзера по имени
	 */
	public function findByLogin( $login )
	{
		$select = $this->select()
				->from($this, array('id','email','login', 'approved'))
				->where('login = ?', $login)
				->limit(1);

		return $this->fetchRow($select);
}


	/*
	 * получает всю инфу по id
	 */
	public function getInfo( $idU )
	{
		$select = $this->select()
				->from($this, array('id','email','login', 'role', 'approved','created' => "DATE_FORMAT(`date_create` , '%d.%m.%Y')"))
				->where('id = ?', $idU, Zend_Db::INT_TYPE)
				->limit(1);

		$result = $this->fetchRow($select);
		return (is_null($result)) ? null : $result->toArray();
	}

	/*
	 * регулярки для проверки полей (логин,пароль,...)
	 */
	protected function _checkLogin($login)
	{
		return (preg_match('/^[\w]{3,50}$/', $login));
	}
	protected function _checkPass($pass)
	{
		return ( mb_strlen($pass) >= 6 );
	}

}
