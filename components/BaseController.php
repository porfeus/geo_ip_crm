<?php
class BaseController{
  private $jsFiles = [];
  private $cssFiles = [];

  public function __construct($app){
    $this->app = $app;
  }

  public function registerJsFile($path){
    $this->jsFiles[] = '<script src="'.Request::rootdir().$path.'"></script>';
  }

  public function registerCssFile($path){
    $this->cssFiles[] = '<link href="'.Request::rootdir().$path.'" rel="stylesheet">';
  }

  public function render($name, $params = array()){
    extract($params, EXTR_OVERWRITE);
    $content = $this->renderPartial($name, $params);
    $file = '../views/layout.php';
    ob_start();
    include($file);
    $html = ob_get_clean();

    $htmlAndCss = implode('', [
      implode(PHP_EOL, $this->cssFiles),
      PHP_EOL,
      implode(PHP_EOL, $this->jsFiles),
    ]);
    $html = str_ireplace('</head>', $htmlAndCss.'</head>', $html);
    return $html;
  }

  public function renderPartial($name, $params = array()){
    extract($params, EXTR_OVERWRITE);
    $file = '../views/'.$name.'.php';
    ob_start();
    include($file);
    return ob_get_clean();
  }

  public function redirect($page){
    if( isset($this->app->config['urlManager']) ){
      $urlManager = array_flip($this->app->config['urlManager']);
      if( isset($urlManager[$page]) ){
        $page = $urlManager[$page];
      }else
      if( isset($urlManager[str_replace('/index', '', $page)]) ){
        $page = $urlManager[str_replace('/index', '', $page)];
      }
    }
    header('Location: '.Request::basedir().str_replace('/index', '', $page));
    exit;
  }

  public function beforeAction(){
    if( !$this->checkAccess() ){
      if( !$this->app->user->isGuest ){
        return $this->redirect('main/index');
      }else{
        return $this->redirect('main/login');
      }
    }

    if( $this->app->user->role == 'user' ){
      $identity = $this->app->user->identity;

      if(
        $identity->activationTimeout() &&
        $this->app->actionName != 'logout'
      ){
        return $this->redirect('main/logout');
      }

      if( $identity->banned && $this->app->actionName != 'logout' ){
        return $this->redirect('main/logout');
      }

      if( $identity->usersOnlineLimited() && $this->app->actionName != 'logout' ){
        return $this->redirect('main/logout');
      }else{
        $identity->last_update_time = time();
        $identity->usersOnlineSet();
        $identity->update();
      }
    }

    if( Request::get('lang') ){
      $this->app->language->setActive( Request::get('lang') );
    }
  }

  public function accessRules(){
    return [];
  }

  public function checkAccess(){
    $accessDenied = false;
    $rules = static::accessRules();
    if( count($rules) ){
      $accessDenied = true;
    }

    foreach($rules as $rule){
      if( !in_array($this->app->user->role, $rule['roles']) ) continue;
      if( $rule['allow'] ){
        if( !isset($rule['actions']) ){
          $accessDenied = false;
        }else
        if( in_array($this->app->actionName, $rule['actions']) ){
          $accessDenied = false;
        }
      }else{
        if( !isset($rule['actions']) ){
          $accessDenied = true;
        }else
        if( in_array($this->app->actionName, $rule['actions']) ){
          $accessDenied = true;
        }
      }
    }

    return !$accessDenied;
  }
}
