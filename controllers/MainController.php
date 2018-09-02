<?php
class MainController extends BaseController{

  public function accessRules(){
    return [
        [
            'allow' => true,
            'roles' => ['admin', 'user'],
        ],
        [
            'allow' => false,
            'actions' => ['download'],
            'roles' => ['user'],
        ],
        [
            'allow' => true,
            'actions' => ['login', 'install'],
            'roles' => ['guest'],
        ],
    ];
  }

  public function actionLogin(){

    $error_message = '';
    $login_error = false;
    $captcha_error = false;
    $need_agree = false;
    $agree_error = false;

    if( Request::issetPost() ){
      if(
        !Request::session('captcha_off') &&
        (
          !Request::post('captcha') ||
          Request::post('captcha') != $_SESSION['captcha']['code']
        ) &&
        (
          (PAGE_TYPE == 'admin' && $this->app->config['show_captcha_admin']) ||
          (PAGE_TYPE == 'user' && $this->app->config['show_captcha_user'])
        )
      ){
        $error_message = App::t('Неправильно введен проверочный код');
        $captcha_error = true;
      }else
      if( $this->app->user->login(Request::post('login'), Request::post('password')) ){
        Request::session('captcha_off', 1);

        $identity = $this->app->user->identity;

        if( $this->app->user->role == 'user' ){

          // Проверка лимита пользователей
          if( $identity->usersOnlineLimited() ){
            $error_message = App::t('Достиг лимит пользователей онлайн на Вашем аккаунте');
            $this->app->user->logout();
          }

          // Проверка блокировки пользователей
          if( $identity->banned ){
            $error_message = App::t('Действие аккаунта заблокировано Администратором. Для выяснения причин обратитесь в службу поддержки');
            $this->app->user->logout();
          }

          // Проверка отсутствия активации
          if( empty($error_message) && !$identity->activated_time ){

            if( Request::post('agree') || !$this->app->config['need_agree'] ){
              $identity->activated_time = time();
              $identity->update();
            }else{
              $need_agree = true;
              if( Request::post('agree') == '0' )  $agree_error = true;
              $error_message = App::t('Согласитесь с условиями');
              Request::session('login', '');
            }
          }

          // Проверка активационного периода
          if( empty($error_message) && !$identity->blank('activated_time') &&
              $identity->activated_time + $identity->activated_add_time <= time()
          ){
            $error_message = App::t('Действие аккаунта приостановлено по истечении времени. Обратитесь в службу поддержки');
            $this->app->user->logout();
          }

          // Сохранение входных данных
          if( empty($error_message) ){
            $identity->ip_old = $identity->ip_new;
            $identity->ip_new = $_SERVER['REMOTE_ADDR'];
            $identity->last_enter_time = time();
            $identity->last_update_time = time();
            $identity->language = $this->app->language->getActiveId();
            $identity->usersOnlineSet();
            $identity->update();
          }
        }

        // Редирект на защищенную страницу
        if( empty($error_message) ){
          return $this->redirect('main/index');
        }

      }else{
        $error_message = App::t('Неправильный логин или пароль');
        $login_error = true;
      }
    }

    include(__DIR__."/../captcha/simple-php-captcha.php");
    $_SESSION['captcha'] = simple_php_captcha();

    return $this->renderPartial('login-'.PAGE_TYPE, array(
      'error_message' => $error_message,
      'login_error' => $login_error,
      'captcha_error' => $captcha_error,
      'need_agree' => $need_agree,
      'agree_error' => $agree_error,
    ));
  }

  public function actionLogout(){
    // Сохранение выходных данных
    if( $this->app->user->role == 'user' ){
      $identity = $this->app->user->identity;
      $identity->last_update_time = 0;
      $identity->usersOnlineDel();
      $identity->update();
    }

    $this->app->user->logout();
    return $this->redirect('main/login');
  }

  public function actionIndex(){
    switch( $this->app->user->role ){
      case 'user':
        return $this->redirect('ips');
      break;
      case 'admin':
        return $this->redirect('users');
      break;
    }
  }

  public function actionDownload(){
    $basename = basename(Request::get('file'));
    $file = __DIR__.'/../files/'.$basename;
    header('Content-Disposition: attachment; filename="'.$basename);
    readfile($file);
  }

  /*
  public function actionSettings(){
    return $this->render('settings');
  }
  */
  public function actionAjax(){
    /*
    switch( Request::post('action') ){
      case 'save-language':
        $this->app->language->saveActive();
      break;
    }
    */
  }

  public function actionInstall(){
    if( Request::get('password') != $this->app->config['admin_password'] ){
      die( App::t('Доступ запрещен!') );
    }

    /*
    $duplicatesData = $this->app->pdo->query('SELECT count(1) as count FROM ips where ip="'.$currentIp.'"');
    $duplicatesData = $duplicatesData->FETCH(PDO::FETCH_ASSOC);
    $duplicatesData['count']
    */

    $result = $this->app->pdo->query('SELECT 1 FROM users');

    if( !$result ){
      $this->app->pdo->exec(file_get_contents('../config/sql.sql'));
      echo App::t('База импортирована.');
    }else{
      echo App::t('База не нуждается в импорте.');
    }
  }
}
