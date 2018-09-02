<?php
class UsersController extends BaseController{

  public function accessRules(){
    return [
        [
            'allow' => true,
            'roles' => ['admin'],
        ],
    ];
  }

  public function actionIndex(){

    $user = new Users();
    $total = $user->select('count(1) as count')
      ->find()
      ->count;
    $activated = $user->select('count(1) as count')
      ->greaterthan('activated_time', 0)
      ->find()
      ->count;

    if( Request::get('action') == 'ajax' ){
      header('Content-type: application/json');

      $start = Request::post('start');
      $search = Request::post('search');
      $columns = Request::post('columns');

      $length = Request::post('length');
      if( $length > 1000 ) $length = 1000;

      $activatedType = 'yes';
      if( !empty($columns) && !empty($columns[1]) && !empty($columns[1]['search']['value']) ){
        $activatedType = $columns[1]['search']['value'];
      }

      //filter
      $whereAdd = ['1'];
      if( !empty($search['value']) ){
        array_push($whereAdd, ' AND (
          login LIKE "%'.addslashes($search['value']).'%" OR
          email LIKE "%'.addslashes($search['value']).'%"
        )');
      }

      if( $activatedType != 'all' ){
        if( $activatedType == 'no' ){
          array_push($whereAdd, ' AND activated_time = 0');
        }else{
          array_push($whereAdd, ' AND activated_time > 0');
        }
      }

      $user->where(implode('', $whereAdd));
      $user->limit($start, $length);
      $items = $user->findAll();
      //end filter

      //count filter
      $user->select('count(1) as count');
      $user->where(implode('', $whereAdd));
      $filtered = $user->find();
      $filtered = $filtered->count;
      //end count filter

      $data = [];
      foreach($items as $item){
        array_push($data, [
          'id' => $item->id,
          'login' => $item->login,
          'password' => $item->password,
          'email' => $item->email,
          'activated_from' => $item->activatedDate(),
          'activated_time' => $item->activated_time,
          'activated_add_time' => $item->activatedEndDateForAdmin(),
          'users_limit' => $item->users_limit,
          'email_send_time' => $item->email_send_time,
          'ip_old' => $item->getData('ip_old', App::t('Нет')),
          'ip_new' => $item->getData('ip_new', App::t('Нет')),
          'last_enter_time' => $item->enterDate(),
          'online' => $item->onLine(),
          'tariff_time' => $item->tariffDate(),
          'note' => $item->note,
          'banned' => $item->banned,
          'status' => $item->accountStatus(),
        ]);
      }

      $json_data = array(
          "draw"            => intval( $_REQUEST['draw'] ),
          "recordsTotal"    => intval( $total ),
          "recordsFiltered" => intval( $filtered ),
          "data"            => $data
      );
      echo json_encode($json_data);
      exit;
    }

    return $this->render('users/index', array(
      'total' => $total,
      'activated' => $activated,
      'model' => $user,
    ));

  }

  public function actionCreate(){
    $model = new UsersCreateForm();

    if( Request::issetPost() ){
      $model->load($_POST);

      if( $model->validate() ){
        $model->setActivatedTime();

        $user = new Users();
        $user->load($model->data);
        $user->insert();

        //add info
        $post_message = trim(Request::post('message_'.$model->language));

        $message = $post_message;
        $message = str_replace('{login}', $user->login, $message);
        $message = str_replace('{password}', $user->password, $message);
        $message = str_replace('{time}', $model->getActivatedTimeTitle($model->language), $message);
        $result = trim($message);

        $message = $post_message;
        $message = str_replace('{login}', $user->login.' / ', $message);
        $message = str_replace('{password}', $user->password.' / ', $message);
        $message = str_replace('{time}', $model->getActivatedTimeTitle($model->language), $message);
        $csvResult = trim($message);

        //csv
        $csvResult = str_replace("
", " ", $csvResult);
        $csvResult = str_replace('  ', ' ', $csvResult);

        $csvResult = '"'.$csvResult.'"';
        $csvResult = iconv('utf-8', 'cp1251', $csvResult);
        file_put_contents(__DIR__.'/../files/generate.csv', $csvResult);
        //end csv
      }
    }

    $languagesData = $this->app->language->data();
    $languages = [];
    $messages = [];
    foreach($languagesData as $id=>$data){
      $languages[$data['language_title']] = $id;

      $messages['message_'.$id] = $data['generate_message'];
    }

    return $this->render('users/create', [
      'model' => $model,
      'result' => $result,
      'languages' => $languages,
      'messages' => $messages,
    ]);
  }

  public function actionGetNote(){
    $user = new Users();
    $user->eq('id', Request::get('id'))->find();

    die($user->note);
  }

  public function actionSaveNote(){
    $user = new Users();
    $user->eq('id', Request::post('id'))->find();

    if( empty($user->data) ) return;

    $user->note = Request::post('note');
    $user->update();
  }

  public function actionActivate(){
    $user = new Users();
    $user->eq('id', Request::post('id'))->find();

    if( empty($user->data) ) return;

    $user->activated_time = time();
    $user->update();
  }

  public function actionBan(){
    $user = new Users();
    $user->eq('id', Request::post('id'))->find();

    if( empty($user->data) ) return;

    if( Request::post('status') == 1 ){
      $user->banned = time();
    }else{
      $user->banned = 0;
    }

    $user->update();
  }

  public function actionEdit(){
    $user = new Users();
    $user->eq('id', Request::get('id'))->find();

    if( empty($user->data) ){
      return $this->redirect('users/index');
    }

    $model = new UsersEditForm();
    $model->load($user->data);

    if( Request::issetPost() ){
      $model->load($_POST);

      if( $model->validate() ){
        $model->setActivatedTime();

        $user->load($model->data);
        $user->tariff_time += $model->getActivatedSeconds();
        $user->update();

        return $this->redirect('users/index');
      }
    }

    return $this->render('users/edit', [
      'model' => $model,
    ]);
  }

  public function actionDelete(){
    $deleteList = Request::post('delete');

    if( !empty($deleteList) ){
      $user = new Users();
      $items = $user->in('id', array_values($deleteList))->findAll();
      foreach($items as $item){
        $item->delete();
      }
    }

    $total = $user->select('count(1) as count')
      ->find()
      ->count;
    $activated = $user->select('count(1) as count')
      ->greaterthan('activated_time', 0)
      ->find()
      ->count;

    return json_encode([
      'total' => $total,
      'activated' => $activated,
    ]);
  }

  public function actionGenerate(){
    $model = new UsersGenerateForm();
    $result = false;

    if( Request::issetPost() ){
      $model->load($_POST);

      if( $model->validate() ){
        $model->setActivatedTime();

        $result = [];
        $csvResult = [];
        $last_id = $model->select('max(id) as max')
          ->find()
          ->max;
        $loginFirstUpdated = false;

        $accounts_num = $model->accounts_num;

        for($i=0; $i<$accounts_num; $i++){
          if( $i >= 10000 ) break;
          $last_id ++;

          $user = new Users();
          $user->load($model->data);

          if( $model->login_type == 'ID+RANDOM' ){
            $user->login = self::generateLogin($model->login_prefix);
          }else{ //ID+LOGIN
            if( $loginFirstUpdated ){
              $user->id = $last_id;
            }
            $user->login = self::generateNumericLogin($model->login_prefix, $last_id);
          }
          $user->password = self::generatePassword();
          $user->users_limit = $model->users_limit;

          if( $user->validate() ){
            $user->insert();

            if( $model->login_type == 'ID+LOGIN' && !$loginFirstUpdated ){
              $last_id = $user->id;
              $loginFirstUpdated = true;

              $oldLogin = $user->login;
              $user->login = self::generateNumericLogin($model->login_prefix, $last_id);
              if( $user->validate() ){
                $user->update();
              }else{
                $user->login = $oldLogin;
              }
            }
          }else{
            $accounts_num ++;
            continue;
          }

          $post_message = trim(Request::post('message_'.$model->language));

          $message = $post_message;
          $message = str_replace('{login}', $user->login, $message);
          $message = str_replace('{password}', $user->password, $message);
          $message = str_replace('{time}', $model->getActivatedTimeTitle($model->language), $message);
          $result[] = trim($message);

          $message = $post_message;
          $message = str_replace('{login}', $user->login.' / ', $message);
          $message = str_replace('{password}', $user->password.' / ', $message);
          $message = str_replace('{time}', $model->getActivatedTimeTitle($model->language), $message);
          $csvResult[] = trim($message);
        }

        $result = implode(PHP_EOL.'================'.PHP_EOL, $result);

        //csv
        $csvResult = array_map(function($item){
          $item = str_replace("
", " ", $item);
          $item = str_replace('  ', ' ', $item);
          return $item;
        }, $csvResult);
        $csvResult = '"'.implode('"'.PHP_EOL.'"', $csvResult).'"';
        $csvResult = iconv('utf-8', 'cp1251', $csvResult);
        file_put_contents(__DIR__.'/../files/generate.csv', $csvResult);
        //end csv
      }
    }

    $languagesData = $this->app->language->data();
    $languages = [];
    $messages = [];
    foreach($languagesData as $id=>$data){
      $languages[$data['language_title']] = $id;

      $messages['message_'.$id] = $data['generate_message'];
    }

    return $this->render('users/generate', [
      'model' => $model,
      'result' => $result,
      'languages' => $languages,
      'messages' => $messages,
    ]);
  }

  public static function generateNumericLogin($prefix, $last_id){
    return $prefix.'-'.str_pad($last_id, 7, '0', STR_PAD_LEFT);
  }

  public static function generateLogin($prefix){
    $letters = implode("", [
      "ABCDEFGHIGKLMNOPQRSTUVWXYZ",
      "abcdefghigklmnopqrstuvwxyz",
      "1234567890",
    ]);
    $login = "";
    for($i=0; $i<7; $i++){
      $login.= $letters[rand(0, strlen($letters))];
    }
    return $prefix.'-'.$login;
  }

  public static function generatePassword(){
    $letters = implode("", [
      "ABCDEFGHIGKLMNOPQRSTUVWXYZ",
      "abcdefghigklmnopqrstuvwxyz",
      "1234567890",
    ]);
    $password = "";
    for($i=0; $i<12; $i++){
      $password.= $letters[rand(0, strlen($letters))];
    }
    return $password;
  }
}
