<?php
class UsersGenerateForm extends Users{

	public function defaultValues(){
		return [
			'activated_num' => 0,
      'check_interval_time' => 0,
      'check_limit_num' => 0,
      'import_load_length' => 0,
			'login_type' => 'ID+RANDOM',
			'login_prefix' => 'ID',
		];
	}

	public function attributeLabels(){
		return array_merge(parent::attributeLabels(), [
			'login_type' => App::t('Шаблон логина'),
			'login_prefix' => App::t('Префикс логина'),
			'accounts_num' => App::t('Количество аккаунтов'),
			'activated_type' => App::t('Время действия'),
			'activated_num' => App::t('Укажите время'),
			'language' => App::t('Язык сообщения'),
		]);
	}

	public function rules(){
		return [
			[[
				'accounts_num', 'activated_num', 'users_limit',
				'language', 'login_type', 'login_prefix', 'activated_type', 'need_email'
			], 'required'],
			[['accounts_num', 'activated_num', 'users_limit'], 'number'],
		];
	}
}
