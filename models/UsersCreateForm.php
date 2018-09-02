<?php
class UsersCreateForm extends Users{

	public function defaultValues(){
		return [
			'activated_num' => 0,
      'check_interval_time' => 0,
      'check_limit_num' => 0,
      'import_load_length' => 0,
		];
	}

	public function attributeLabels(){
		return array_merge(parent::attributeLabels(), [
			'activated_type' => App::t('Время действия'),
			'activated_num' => App::t('Укажите время'),
			'language' => App::t('Язык сообщения'),
		]);
	}
	public function rules(){
		return array_merge(parent::rules(), [
			[['language', 'activated_type'], 'required'],
			[['accounts_num', 'activated_num'], 'number'],
		]);
	}
}
