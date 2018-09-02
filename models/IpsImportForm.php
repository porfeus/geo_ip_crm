<?php
class IpsImportForm extends Ips{
	public function attributeLabels(){

		return array_merge(parent::attributeLabels(), [
			'variant' => App::t('Откуда импортировать'),
			'field' => App::t('Список IP:PORT (каждый с новой строки)'),
			'file' => App::t('Выберите файл с компьютера (поддерживаемые расширения: txt и csv)'),
		]);
	}

	public function rules(){
		return [
			[['variant'], 'required'],
			[['field'], 'fieldFilter'],
			[['file'], 'fileFilter'],
		];
	}

	public function fieldFilter($attribute){
		$value = $this->{$attribute};
		if( $this->variant != 'field' || !empty($value) ) return true;
		$this->addError($attribute, App::t('Значение не может быть пустым'));
		return false;
	}

	public function fileFilter($attribute){
		$value = $this->{$attribute};

		if( $this->variant != 'file' ) return true;

		if( empty($value['tmp_name']) ){
			$this->addError($attribute, App::t('Выберите файл'));
			return false;
		}

		$name = explode('.', $value['name']);
		$format = array_pop($name);
		if( strcasecmp($format, 'txt') != 0 && strcasecmp($format, 'csv') != 0 ){
			$this->addError($attribute, App::t('Неправильный формат файла'));
			return false;
		}

		return true;
	}
}
