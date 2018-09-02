<?php

class Request{

  /**
   * Возвращает значение переменной POST или значение по умолчанию
   */
  public static function post($name, $defaultValue = ''){
    if( isset($_POST[$name]) ) return ($_POST[$name]);
    return $defaultValue;
  }

  /**
   * Проверяет, есть ли переменные POST
   */
  public static function issetPost(){
    return !empty($_POST);
  }

  /**
   * Возвращает значение переменной GET или значение по умолчанию
   */
  public static function get($name, $defaultValue = ''){
    if( isset($_GET[$name]) ) return ($_GET[$name]);
    return $defaultValue;
  }

  /**
   * Проверяет, есть ли переменные GET
   */
  public static function issetGet(){
    return !empty($_GET);
  }

  /**
   * Читает или устанавливает переменную сессии
   */
  public static function session($name, $value = 'no-value'){
    $name = PAGE_TYPE.'_'.$name;

    if( $value == 'no-value' ){ // if get
      if( isset($_SESSION[$name]) ) return $_SESSION[$name];
      return false;
    }else{ // if set
      if( $value == '' ){
        unset($_SESSION[$name]);
      }else{
        $_SESSION[$name] = $value;
      }
    }
  }

  /**
   * Возвращает путь к папке входного скрипта (index.php)
   */
  public static function basedir(){
    $basedir = dirname($_SERVER['PHP_SELF']);
    if( $basedir != "/" ) $basedir.= "/";
    if( $basedir[strlen($basedir)-1] != '/' ) $basedir.= "/";
    return $basedir;
  }

  /**
   * Возвращает путь к корневой папке проекта
   */
  public static function rootdir(){
    $basedir = self::basedir();
    $basedir.= "../";
    return $basedir;
  }

  /**
   * Возвращает файловый путь к корневой папке проекта
   */
  public static function rootpath(){
    return __DIR__.'/../';
  }

  /**
   * Читает текущий адрес и возвращает путь в виде: контроллер/действие
   */
  public static function path(){
    $basedir = self::basedir();

    $page = $_SERVER['REQUEST_URI'];
    if( $basedir != "/" ){
      $page = preg_replace('@'.$basedir.'@', '', $page, 1);
    }

    $page = trim($page, '/');

    $url = parse_url($page);

    $path = $url['path'];

    if( isset(App::get()->config['urlManager']) ){
      $urlManager = App::get()->config['urlManager'];
      if( isset($urlManager[$path]) ){
        $path = $urlManager[$path];
      }else
      if( isset($urlManager[str_replace('/index', '', $path)]) ){
        $path = $urlManager[str_replace('/index', '', $path)];
      }
    }

    return $path;
  }

  /**
   * Определяет абсолютный адрес проекта
   */
  public static function site(){
    return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/';
  }
}
