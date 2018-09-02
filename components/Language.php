<?php

class Language{

  private $_languages = false;

  public function __construct($app){
    $this->app = $app;
  }

  public function t($text, $language = ''){
    if( empty($language) ){
      $language = $this->activeId;
    }

    $data = $this->data($language);

    if( isset($data[$text]) ){
      return $data[$text];
    }
    return $text;
  }

  public function getActiveId(){
    if( $this->app->user->role == 'admin' ) return 'ru';

    if( Request::session('active_language') ){
      return Request::session('active_language');
    }/*else
    if( isset($_COOKIE['lang']) ){
      return $_COOKIE['lang'];
    }*/
    return $this->app->config['default_language'];
  }

  public function setActive($id){
    $languages = $this->data();

    if( isset($languages[$id]) ){
      if( $this->getActiveId() != $id ){
        Request::session('confirm_language', 1);
      }
      Request::session('active_language', $id);
      $this->saveActive();
    }
  }

  public function saveActive(){
    if( $this->app->user->role == 'user' ){
      $this->app->user->identity->language = $this->getActiveId();
      $this->app->user->identity->update();
    }
    //setcookie("lang", $this->getActiveId(), time() + 86400*365, '/');
  }

  public function data($filterId = ''){
    if( $this->_languages ){
      if( !empty($filterId) ){
        return $this->_languages[$filterId];
      }
      return $this->_languages;
    }

    $languages = array();

    $dir = __DIR__.'/../language/';
    $od = opendir($dir);
    while($rd = readdir($od)){
      $file = $dir.$rd;
      if( !is_file($file) ) continue;
      $id = str_replace('.php', '', basename($file));

      $languages[$id] = require($file);
    }

    $this->_languages = $languages;

    if( !empty($filterId) ){
      return $this->_languages[$filterId];
    }
    return $this->_languages;
  }

  public function dropdown(){

    $languages = $this->data();

    $html = '
    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
            <i class="lang-sm" lang="'.$this->activeId.'"></i> '.$languages[$this->activeId]['language_title'].' <i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu">';

    foreach($languages as $id=>$data){
      if( $id == $this->activeId ) continue;
      $html .= '
          <li>
              <a href="'.Request::path().'?lang='.$id.'"><i class="lang-sm" lang="'.$id.'"></i> '.$data['language_title'].'</a>
          </li>';
    }
    $html .= '
        </ul>
    </li>';

    return $html;
  }

  public function __get($name){
    $method = 'get'.ucfirst(strtolower($name));
    return $this->{$method}();
  }
}
