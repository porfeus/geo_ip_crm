<?php
class App{
  static $_this;
  static $_classConstructed = false;
  public $defaultControllerName = 'main';
  public $defaultActionName = 'index';

  public function __construct($config){
    if( self::$_classConstructed ){
      throw new ErrorException('Повторная инициализация класса App');
    }

    session_start();

    //properties
    self::$_classConstructed = true;
    $this->config = $config;
    self::$_this = $this;

    //methods
    $this->dbConnect();
    $this->setControllerAndAction();

    //manual route settings
    if( isset($this->config['controllerName']) ){
      $this->controllerName = $this->config['controllerName'];
      $this->actionName = $this->config['actionName'];
    }

    //objects
    $controller = self::getTrueName($this->controllerName).'Controller';
    $this->controller = new $controller($this);
    $this->language = new Language($this);
    $this->user = new UserIdentity($this);
  }

  public static function get(){
    return self::$_this;
  }

  public static function t($text, $language = ''){
    return self::get()->language->t($text, $language);
  }

  public static function import($path){
    require_once Request::rootpath().$path;
  }

  public function dbConnect(){
    $driver = $this->config['DB_DRIVER'];
    $host = $this->config['DB_HOSTNAME'];
    $user = $this->config['DB_USERNAME'];
    $pass = $this->config['DB_PASSWORD'];
    $dbname = $this->config['DB_DATABASE'];
    try
    {
      $this->pdo = new PDO("$driver:host=$host;dbname=$dbname", $user, $pass);
      ActiveRecord::setDb($this->pdo);
    }
    catch(  PDOException $e  )
    {
      echo "Database connect error!<br />";
      echo "You have an error: ".$e->getMessage()."<br />";
      echo "On line: ".$e->getLine();
      exit;
    }
  }

  public function run(){
    $this->runAction($this->actionName);
  }

  public static function getTrueName($name){
    $name = strtolower($name);
    $name = explode('-', $name);
    foreach($name as $key=>$value){
      $name[$key] = ucfirst($value);
    }
    return implode('', $name);
  }

  public function runAction($name){
    $method = 'action'.self::getTrueName($name);

    if( method_exists($this->controller, $method) ){

      $this->controller->beforeAction();
      $result = $this->controller->{$method}();
    }else{
      $result = $this->controller->renderPartial('404');
    }

    echo $result;
  }

  public function setControllerAndAction(){
    $path = Request::path();
    list($controller, $action) = explode('/', $path);

    if( empty($controller) ){
      $controller = $this->defaultControllerName;
    }
    if( empty($action) ){
      $action = $this->defaultActionName;
    }

    $this->controllerName = $controller;
    $this->actionName = $action;
  }

  public function __get($name){
    $method = 'get'.ucfirst(strtolower($name));
    return $this->{$method}();
  }
}
