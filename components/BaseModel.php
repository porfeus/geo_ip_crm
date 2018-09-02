<?php

class BaseModel extends ActiveRecord{
  public $errors = [];

  public function __construct(){
    parent::__construct();

    $defaultValues = $this->defaultValues();
    foreach( $defaultValues as $key=>$val ){
      $this->data[$key] = $val;
    }
  }

  public function beforeSave($insert){}

	public function insert(){
		$this->beforeSave(true);
		parent::insert();
	}

	public function update(){
		$this->beforeSave(false);
		parent::update();
	}

  public function beforeDelete(){}

	public function delete(){
		$this->beforeDelete();
		parent::delete();
	}

  public function defaultValues(){
    return [];
  }

  public function save($validate = true){
    $primary = $this->{$this->primaryKey};
    if( $validate ){
      if( !$this->validate() ) return false;
    }
    if( empty($primary) ){
      $this->insert();
    }else{
      $this->update();
    }
    return true;
  }

  public function addError($name, $error){
    if( isset($this->errors[$name]) ) return;
    $this->errors[$name] = $error;
  }

  public function blank($attribute){
    $var = $this->{$attribute};
    return empty($var);
  }

  public function getData($attribute, $defaultValue){
    $var = $this->{$attribute};
    if( empty($var) ) return $defaultValue;
    return $var;
  }

  public function getError($name){
    if( isset($this->errors[$name]) ) return $this->errors[$name];
    return '';
  }

  public function getLabel($name){
    $labels = $this->attributeLabels();
    if( isset($labels[$name]) ) return $labels[$name];
    return $name;
  }

  public function attributeLabels(){
    return [];
  }

  public function load($assoc){

    $modelAttributes = $this->attributeLabels();
    foreach($assoc as $key=>$val){
      if( !isset($modelAttributes[$key]) ) continue;
      $this->{$key} = $val;
    }
  }

  public function validate(){
    $rules = $this->rules();
    $totalResult = true;

    foreach($rules as $rule){
      list($attributes, $validator) = $rule;
      foreach($attributes as $attribute){
        $result = $this->{$validator}($attribute, $rule);
        if( !$result ) $totalResult = false;
      }
    }

    return $totalResult;
  }

  public function number($attribute, $rule){
    $value = trim($this->{$attribute});
    if( $value == '' || is_numeric($value) ) return true;
    if( empty($rule['message']) ){
      $this->addError($attribute, App::t('Значение должно быть числом'));
    }else{
      $this->addError($attribute, $rule['message']);
    }
    return false;
  }

  public function required($attribute, $rule){
    $value = $this->{$attribute};
    if( is_array($value) ){
      if( trim(implode('', $value)) != '' ) return true;
    }else
    if( trim($value) != '' ) return true;
    if( empty($rule['message']) ){
      $this->addError($attribute, App::t('Значение не может быть пустым'));
    }else{
      $this->addError($attribute, $rule['message']);
    }
    return false;
  }

  public function unique($attribute, $rule){
    $value = $this->{$attribute};
    $id = intval($this->id);

    if( $value == '' ) return true;

    $model = new static();
    $model->select('count(1) as count');
    $model->eq($attribute, $value);
    if( $this->getData('id', false) ){
      $model->ne('id', $id);
    }
    $model->find();

    if( !$model->count ) return true;
    if( empty($rule['message']) ){
      $this->addError($attribute, App::t('Значение должно быть уникальным'));
    }else{
      $this->addError($attribute, $rule['message']);
    }
    return false;
  }
}
