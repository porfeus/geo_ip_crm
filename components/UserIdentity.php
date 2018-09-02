<?php

class UserIdentity{

  public function __construct($app){
    $this->app = $app;

    $this->setIdentity();
  }

  public function setIdentity(){
    $this->isGuest = !$this->isLogged();
    $this->identity = new StdClass;
    $this->role = 'guest';

    if( !$this->isGuest ){

      $this->role = Request::session('role');

      if( $this->role == 'admin' ){
        $this->identity->login = Request::session('login');
        $this->identity->id = 'admin';
      }else{
        $user = new Users();
        $identity = $user->eq('login', Request::session('login'))->find();
        if( empty($identity->data) ){
          $this->logout();
          return $this->app->controller->redirect('main/login');
        }
        $this->identity = $identity;
      }
    }
  }

  public function isLogged(){
    return Request::session('login');
  }

  public function login($login, $password){
    $success = false;

    $login = trim($login);
    $password = trim($password);

    if( PAGE_TYPE == 'admin' ){
      if(
        $login == $this->app->config['admin_login'] &&
        $password == $this->app->config['admin_password']
      ){
        Request::session('login', $login);
        Request::session('role', 'admin');
        $success = true;
      }
    }else{
      $user = new Users();
      $count = $user->select('count(1) as count')
        ->eq('login', $login)
        ->eq('password', $password)
        ->find()->count;

      if( $count > 0 ){
	  	Request::session('login', $login);
		Request::session('role', 'user');
        $success = true;
      }
    }

    if( $success ){
      $this->setIdentity();
    }

    return $success;
  }

  public function logout(){
    Request::session('login', '');
    Request::session('captcha_off', '');
    Request::session('active_language', '');
  }

  public function __get($name){
    $method = 'get'.ucfirst(strtolower($name));
    return $this->{$method}();
  }
}
