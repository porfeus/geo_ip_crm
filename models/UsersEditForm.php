<?php
class UsersEditForm extends Users{

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
		]);
	}
	public function rules(){

		return array_merge(parent::rules(), [
			[['accounts_num', 'activated_num'], 'number'],
			[['activated_type'], 'activatedTypeFilter'],
		]);
	}

	public function activatedTypeFilter($attribute){
    $value = trim($this->{$attribute});
		$num = intval($this->activated_num);
    if( !$num || $value != '' ) return true;
    $this->addError($attribute, App::t('Укажите время действия'));
    return false;
  }
}
