<?php
class Ips extends BaseModel{
	public $table = 'ips';
	public $primaryKey = 'id';

	public function attributeLabels(){
		return [
			'id' => App::t('ID'),
			'ip' => App::t('IP'),
			'port' => App::t('Порт'),
			'country_code' => App::t('Код страны'),
			'country_name' => App::t('Страна'),
			'city_name' => App::t('Город'),
			'owner_id' => App::t('Владелец'),
			'last_check_time' => App::t('Время последней проверки'),
		];
	}

	public function rules(){
		return [
			[['ip', 'port', 'owner_id'], 'required'],
			[['ip'], 'unique', 'message' => App::t('Такой ip уже есть в базе')],
		];
	}

	public function setGeoInfo(){
		$record = GeoIp2::getIpInfo($this->ip);
		if( $record->country->isoCode )
			$this->country_code = $record->country->isoCode;
		if( $record->country->name )
			$this->country_name = $record->country->name;
		if( $record->city->name )
			$this->city_name = $record->city->name;

		$this->last_check_time = time();
	}
}
