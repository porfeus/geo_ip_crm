<?php
class Users extends BaseModel{
	use ActivatedTimeTrait;

	public $table = 'users';
	public $primaryKey = 'id';
	public $relations = array(
		'ips' => array(self::HAS_MANY, 'Ips', 'owner_id'),
	);

	public function beforeSave($insert){
		//Меняем тариф при создании аккаунта и при изменении тарифа неактивированного аккаунта
		if( $insert || !$this->activated_time ){
			$this->tariff_time = $this->activated_add_time;
		}
	}

	public function beforeDelete(){
		App::get()->pdo->exec('DELETE FROM ips WHERE owner_id = "'.$this->id.'"');
	}

	public function attributeLabels(){
		return [
			'id' => App::t('ID'),
			'login' => App::t('Логин'),
			'password' => App::t('Пароль'),
			'email' => App::t('E-mail'),
			'need_email' => App::t('Запросить e-mail?'),
			'language' => App::t('Язык'),
			'activated_time' => App::t('Активирован с'),
			'activated_add_time' => App::t('Активирован до'),
			'tariff_time' => App::t('Тариф'),
			'users_limit' => App::t('Лимит человек'),
			'users_online' => App::t('IP-адреса онлайн'),
			'email_send_time' => App::t('Последняя отправка письма'),
			'ip_old' => App::t('Старый IP'),
			'ip_new' => App::t('Новый IP'),
			'last_enter_time' => App::t('Последний вход'),
			'last_update_time' => App::t('Последняя активность'),
			'last_check_time' => App::t('Последний импорт IP-адресов'),
			'check_interval_time' => App::t('Интервал между проверками IP (в секундах)'),
			'check_limit_on' => App::t('Ограничить количество проверок IP'),
			'check_limit_num' => App::t('Количество проверок IP'),
			'import_load_length' => App::t('Ограничить количество загружаемых IP (0 - без ограничений)'),
			'import_admin_list' => App::t('Проверять только определенные админом IP адреса'),
			'note' => App::t('Примечание'),
			'banned' => App::t('Заблокирован'),
		];
	}

	public function rules(){
		return [
			[['login', 'password', 'need_email', 'users_limit', 'check_interval_time', 'check_limit_on'], 'required'],
			[['login'], 'unique', 'message' => App::t('Такой логин уже есть в базе')],
			[['check_interval_time', 'check_limit_num'], 'number'],
		];
	}

	public function tariffDate(){
		return $this->getTimeTitle($this->tariff_time);
	}

	public function activatedDate(){
		if( $this->activated_time == 0 ) return App::t('Нет');
		return date('d.m.Y H:i', $this->activated_time);
	}

	public function activatedTimeLeft(){
		$time = ($this->activated_time + $this->activated_add_time) - time();
		return $this->getTimeTitle($time);
	}

	public function activatedEndDateForAdmin(){
		if( $this->activated_time > 0 && $this->activated_time + $this->activated_add_time < time() ){

			return '<span style="color: red">-'.$this->deleteTimeLeft().'</span>';
		}else{
			return $this->activatedEndDate();
		}
	}

	public function activatedEndDate(){
		if( $this->activated_time == 0 ){
			return $this->getActivatedTimeTitle();
		}
		return date('d.m.Y H:i', intval($this->activated_time + $this->activated_add_time));
	}

	public function enterDate(){
		if( $this->last_enter_time == 0 ) return App::t('Нет');
		return date('d.m.Y', intval($this->last_enter_time));
	}

	public function onLine(){
		if( $this->last_update_time + 600 > time() ) return 1;
		return 0;
	}

	public function usersOnlineLimited(){
		if( $this->blank('users_online') ) return false;

		$usersOnline = unserialize($this->users_online);
		$ip = $_SERVER['REMOTE_ADDR'];

		unset($usersOnline[$ip]);

		foreach($usersOnline as $userIp=>$userTime){
			if( $userTime + 600 > time() ) continue;
			unset($usersOnline[$userIp]);
		}

		if( count($usersOnline) >= $this->users_limit ) return true;
		return false;
	}

	public function usersOnlineSet(){
		$usersOnline = [];
		if( !$this->blank('users_online') ){
			$usersOnline = unserialize($this->users_online);
		}
		$ip = $_SERVER['REMOTE_ADDR'];

		$usersOnline[$ip] = time();
		$this->users_online = serialize($usersOnline);
	}

	public function usersOnlineDel(){
		$usersOnline = [];
		if( !$this->blank('users_online') ){
			$usersOnline = unserialize($this->users_online);
		}
		$ip = $_SERVER['REMOTE_ADDR'];

		unset($usersOnline[$ip]);
		$this->users_online = serialize($usersOnline);
	}

	public function getLanguage(){
		return $this->getData('language', App::get()->config['default_language']);
	}

	/**
	 * Определяет статус активации
	 */
	public function activationTimeout(){
		if($this->activated_time + $this->activated_add_time < time() ) return true;
		return false;
	}

	/**
	 * Время до удаления аккаунта
	 */
	public function deleteDate(){
		$add_days = App::get()->config['inactive_delete_days']*86400;
		echo date('d.m.Y H:i', $this->activated_time + $this->activated_add_time + $add_days);
	}

	/**
	 * Отсчет до удаления аккаунта
	 */
	public function deleteTimeLeft(){
		$time = ($this->activated_time + $this->activated_add_time) - time();
		$time += App::get()->config['inactive_delete_days'] * 86400;
		return $this->getTimeTitle($time);
	}

	/**
	 * Статус аккаунта - активен (1), приостановлен (2) или заблокирован (3)
	 */
	public function accountStatus(){
		if( $this->banned ) return 3;
		if( $this->activationTimeout() ) return 2;
		return 1;
	}


	/**
	 * Определяет, действует ли задержка перед следующей загрузкой списка ип-адресов
	 */
	public function checkIpsCountdown(){
		if( $this->last_check_time + $this->check_interval_time > time() ){
			return true;
		}else{
			return false;
		}
	}


	/**
	 * Отсчет до снятия задержки перед следующей загрузкой списка ип-адресов
	 */
	public function checkIpsTimeLeft(){
		if( $this->last_check_time + $this->check_interval_time > time() ){
			return ($this->last_check_time + $this->check_interval_time) - time();
		}else{
			return 0;
		}
	}


	/**
	 * Закончилось ли количество проверок
	 */
	public function checkIpsLimited(){
		if( $this->check_limit_on && $this->check_limit_num <= 0 ){
			return true;
		}
		return false;
	}


	/**
	 * Есть непроверенные IP?
	 */
	public function issetUncheckedIps(){
		$ips = new Ips();
		$ips->select('count(1) as count');
		$ips->eq('owner_id', $this->id);
		$ips->eq('last_check_time', '0');
		$result = $ips->find();

		if( isset($result) && $result->count > 0 ) return true;
		return false;
	}


	/**
	 * Превышен лимит добавляемых ип?
	 */
	public function addIpsLimited(){
		$ips = new Ips();
	  $totalIps = $ips->select('count(1) as count')
	    ->eq('owner_id', $this->id)
	    ->find()
	    ->count;
	  if( $this->import_load_length && $totalIps >= $this->import_load_length ){
	    return true;
	  }
		return false;
	}
}
