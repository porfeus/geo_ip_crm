<?php
class AdminIpsController extends BaseController{

  public function accessRules(){
    return [
        [
            'allow' => true,
            'roles' => ['admin'],
        ],
    ];
  }

  public function actionIndex(){
    $ips = new Ips();
    $total = $ips->select('count(1) as count')
      ->eq('owner_id', $this->app->user->identity->id)
      ->find()
      ->count;
    $total_admin = $ips->select('count(1) as count')
      ->eq('owner_id', 'admin') //!!!
      ->find()
      ->count;

    if( Request::get('action') == 'ajax' ){
      header('Content-type: application/json');

      $start = Request::post('start');
      $search = Request::post('search');
      $columns = Request::post('columns');

      $length = Request::post('length');
      if( $length > 1000 ) $length = 1000;

      //filter
      $whereAdd = ['owner_id = "'.$this->app->user->identity->id.'"'];
      if( Request::get('admin_table') ){
        $whereAdd = ['owner_id = "admin"'];
      }

      $whereAdd[] = 'and last_check_time > 0';

      $groupBy = 'country_name';
      if( Request::get('country') ){
        $groupBy = 'city_name';
        $whereAdd[] = 'and country_name = "'.Request::get('country').'"';
      }

      if( !empty($search['value']) ){
        array_push($whereAdd, ' and (
          '.$groupBy.' like "%'.addslashes($search['value']).'%"
        )');
      }
      $ips->select('count(1) as count, country_code, country_name, city_name');
      $ips->where(implode(' ', $whereAdd));
      $ips->groupby($groupBy);
      $ips->limit($start, $length);
      $items = $ips->findAll();
      //end filter

      //count filter
      $ips->select('count(1) as count');
      $ips->where(implode(' ', $whereAdd));
      $ips->groupby($groupBy);
      $filtered = count($ips->findAll());
      //end count filter

      //not-checked list
      $ips->select('count(1) as count');
      $ips->where(str_replace('last_check_time > 0', 'last_check_time = 0', implode(' ', $whereAdd)));
      $notCheckedList = $ips->find();
      //end not-checked list

      $data = [];
      foreach($items as $item){
        array_push($data, [
          'country_code' => $item->country_code,
          'name' => $item->{$groupBy},
          'country_name' => $item->country_name,
          'length' => $item->count,
          'last_check_time' => $item->last_check_time,
          'ips' => $this->returnIpsByCountry($item->country_name, ((Request::get('country'))? $item->city_name: 'none')),
        ]);
      }

      if( !Request::get('admin_table') && isset($notCheckedList) && $notCheckedList->count > 0 ){
        array_unshift($data, [
          'country_code' => '',
          'name' => 'not_checked',
          'country_name' => '',
          'length' => $notCheckedList->count,
          'last_check_time' => 0,
          'ips' => '',
        ]);
      }

      $json_data = array(
          "draw"            => intval( $_REQUEST['draw'] ),
          "recordsTotal"    => intval( $filtered ),
          "recordsFiltered" => intval( $filtered ),
          "data"            => $data
      );
      echo json_encode($json_data);
      exit;
    }

    //save email
    if( $this->app->user->role == 'user' ){
      $emailForm = new EmailForm();
      $userInfo = $this->app->user->identity;

      if( Request::issetPost() && Request::post('action') == 'save-email' ){
        $emailForm->load($_POST);

        if( $emailForm->validate() ){

          $userInfo->email = $emailForm->email;
          $userInfo->update();

          $data['email_success'] = 1;
          Request::session('modal_info', [
            'title' => App::t('Информация'),
            'message' => App::t('Системные сообщения подключены'),
          ]);
        }else{
          $data['email_error'] = 1;
          $data['email_message'] = $emailForm->getError('email');
        }
      }
    }
    //end. save email

    return $this->render($this->app->controllerName.'/index', array(
      'total' => $total,
      'total_admin' => $total_admin,
      'model' => $ips,
    ));

  }

  public function actionGetIpsByCountry(){
    if( isset($_REQUEST['save']) ){
      $filename = $_REQUEST['country_name'];
      if( isset($_REQUEST['city_name']) ){
        $filename = $_REQUEST['country_name'].'-'.$_REQUEST['city_name'];
      }
      header('Content-Disposition: attachment; filename="'.$filename.'.'.$_REQUEST['save']);
    }
    $ips = new Ips();
    $ips->eq('owner_id', $this->app->user->identity->id);
    $ips->eq('country_name', $_REQUEST['country_name']);
    if( isset($_REQUEST['city_name']) ){
      $ips->eq('city_name', $_REQUEST['city_name']);
    }
    $items = $ips->findAll();

    foreach($items as $item){
      if( isset($_REQUEST['save']) && $_REQUEST['save'] == 'csv' ) echo '"';
      echo $item->ip.':'.$item->port;
      if( isset($_REQUEST['save']) && $_REQUEST['save'] == 'csv' ) echo '"';
      echo "\r\n";
    }
  }

  public function returnIpsByCountry($country_name, $city_name = 'none'){
    $ips = new Ips();
    $ips->eq('owner_id', $this->app->user->identity->id);
    $ips->eq('country_name', $country_name);
    if( $city_name != 'none' ){
      $ips->eq('city_name', $city_name);
    }
    $items = $ips->findAll();

    $list = [];

    foreach($items as $item){
      $list[] = $item->ip.':'.$item->port;
    }

    return implode(PHP_EOL, $list);
  }

  public function actionImport(){

    $this->limitFilter(false); //Анализ наличия ограничения на проверку (начало импорта)

    $model = new IpsImportForm();

    if( Request::issetPost() ){
      $model->load(array_merge($_POST, $_FILES));

      if( $model->validate() ){

        $ipList = [];
        $ipStr = '';

        switch( $model->variant ){
          case "file":
            $file = $_FILES['file']['tmp_name'];
            if( !empty($file) && is_file($file) ){
              $ipStr = file_get_contents($file);
              $ipStr = str_replace('"', '', $ipStr);
            }
          break;
          case "field":
            $ipStr = $model->field;
          break;
        }

        $ipList = explode("\n", trim($ipStr, "\r\n"));
        $add = 0;
        $del = 0;
        $skipped = 0;
        $wrongFormat = 0;

        if( Request::session('next_step') ){
          $add = Request::session('import_result')['add'];
          $del = Request::session('import_result')['del'];
          $skipped = Request::session('import_result')['skipped'];
          $wrongFormat = Request::session('import_result')['wrongFormat'];
        }else{
          Request::session('import_result', [
            'add' => $add,
            'del' => $del,
            'skipped' => $skipped,
            'wrongFormat' => $wrongFormat,
          ]);
        }

        if( !empty($ipList) ){
          $checkedNum = 0;
          foreach($ipList as $i=>$item){
            if( Request::session('next_step') && Request::session('next_step') > $i ) continue;

            $item = trim(str_replace(' ', '', $item));

            $match = false;
            preg_match('@(.+):(.+)@', $item, $match);
            if( empty($match) || !filter_var($match[1], FILTER_VALIDATE_IP) ){
              $wrongFormat ++;

              Request::session('import_result', [
                'add' => $add,
                'del' => $del,
                'skipped' => $skipped,
                'wrongFormat' => $wrongFormat,
              ]);
              continue;
            }

            $currentIp = $match[1];
            $currentPort = $match[2];

            if( $checkedNum == $this->app->config['ips_import_speed'] ){
              Request::session('next_step', $i);
              $percent = floor(($i+1) / count($ipList) * 100);
              die('next_step:'.$percent);
            }
            $checkedNum ++;

            //Ограничиваем проверку только определенным списком IP
            if( $this->app->user->role == 'user' ){
              $userInfo = $this->app->user->identity;
              if( $userInfo->import_admin_list ){
                $ipExistsInAdminList = $model->select('count(1) as count')
                    ->eq('owner_id', 'admin') //!!!
                    ->eq('ip', $currentIp)
                    ->find()->count;
                if( !$ipExistsInAdminList ){
                  $skipped ++;

                  Request::session('import_result', [
                    'add' => $add,
                    'del' => $del,
                    'skipped' => $skipped,
                    'wrongFormat' => $wrongFormat,
                  ]);
                  continue;
                }
              }
            }

            $duplicatesNum = $model->select('count(1) as count')
                ->eq('owner_id', $this->app->user->identity->id)
                ->eq('ip', $currentIp)
                ->find()->count;

            if( $duplicatesNum ){
              $del ++;

              Request::session('import_result', [
                'add' => $add,
                'del' => $del,
                'skipped' => $skipped,
                'wrongFormat' => $wrongFormat,
              ]);
              continue;
            }

            //Ограничение на количество загружаемых ип, если установлено
            if( $this->app->user->role == 'user' ){
              if( $this->app->user->identity->addIpsLimited() ){
                break;
              }
            }
            //Конец. Ограничение на количество загружаемых ип, если установлено

            /*
            Скорость добавления: ~400 записей за 10 секунд
            */
            $ips = new Ips();
            $ips->ip = $currentIp;
            $ips->port = $currentPort;
            $ips->owner_id = $this->app->user->identity->id;
            $ips->insert();

            $add ++;

            Request::session('import_result', [
              'add' => $add,
              'del' => $del,
              'skipped' => $skipped,
              'wrongFormat' => $wrongFormat,
            ]);
          }
        }

        //Создаем отчет об импорте
        $result = Request::session('import_result');
        Request::session('import_result', '');

        $modal_message = '';

        $modal_message .= ''.
          str_replace('_NUM_', $result['add'], App::t('Добавлено IP: _NUM_ ед.')).
          '<br />';
        $modal_message .= ''.
          str_replace('_NUM_', $result['del'], App::t('Удалено дублей: _NUM_ ед.')).
          '<br />';
        if( $this->app->user->role == 'user' && $userInfo->import_admin_list ){
          $modal_message .= ''.
            str_replace('_NUM_', $result['skipped'], App::t('Нет в заданном списке: _NUM_  ед.')).
            '<br />';
        }
        $modal_message .= ''.
          str_replace('_NUM_', $result['wrongFormat'], App::t('Неподдерживаемый формат: _NUM_ ед.')).
          '<br />';

        Request::session('modal_info', [
          'title' => App::t('Загрузка завершена'),
          'message' => $modal_message,
        ]);
        //Конец. Создаем отчет об импорте

        Request::session('next_step', '');
        die('ok');
      }else{
        die(json_encode($model->errors));
      }
    }

    return $this->render('admin-ips/import', [
      'model' => $model,
    ]);
  }

  public function limitFilter($allLimits = true){
    //Анализ наличия ограничения на проверку (начало импорта)
    if( $this->app->user->role == 'user' && !Request::session('next_step') ){
      $userInfo = $this->app->user->identity;

      if( $allLimits ){
        //Определяет, действует ли задержка перед следующей проверкой списка ип-адресов
        if( $userInfo->checkIpsCountdown() ){
          $this->redirect('ips/index');
        }
      }

      //Закончилось количество проверок
      if( $userInfo->checkIpsLimited() ){
        $this->redirect('ips/index');
      }
    }
  }

  public function actionCheck(){

    $this->limitFilter(); //Анализ наличия ограничения на проверку (начало импорта)

    $model = new Ips();

    if( Request::issetPost() ){

      $userIps = $model
        ->eq('owner_id', $this->app->user->identity->id)
        ->orderby('id ASC')
        ->findAll();

      $scan = 0;

      if( Request::session('next_step') ){
        $scan = Request::session('import_result')['scan'];
      }else{

        //Импорт ip-адресов пользователем (форма заполнена верно, начало импорта)
        if( $this->app->user->role == 'user' ){
          $userInfo = $this->app->user->identity;
          $userInfo->last_check_time = time();
          if( $userInfo->check_limit_on && $userInfo->check_limit_num > 0 ){
            $userInfo->check_limit_num = $userInfo->check_limit_num - 1;
          }
          $userInfo->update();
        }
      }

      $checkedNum = 0;
      foreach($userIps as $i=>$item){
        if( Request::session('next_step') && Request::session('next_step') > $i ) continue;

        $currentIp = $item->ip;
        $currentPort = $item->port;

        if( $checkedNum == $this->app->config['ips_ckecking_speed'] ){
          Request::session('next_step', $i);
          $percent = floor(($i+1) / count($userIps) * 100);
          die('next_step:'.$percent);
        }
        $checkedNum ++;
        $scan ++;


        $item->setGeoInfo();
        $item->update();

        Request::session('import_result', [
          'scan' => $scan,
        ]);
      }

      //Создаем отчет о проверке
      $result = Request::session('import_result');
      Request::session('import_result', '');

      $modal_message = ''.
        str_replace('_NUM_', $result['scan'], App::t('Проверено записей: _NUM_ ед.'));

      Request::session('modal_info', [
        'title' => App::t('Проверка завершена'),
        'message' => $modal_message,
      ]);
      //Конец. Создаем отчет об импорте

      Request::session('next_step', '');
      die('ok');
    }

    return $this->render('admin-ips/check', [
      'model' => $model,
    ]);
  }

  public function actionClear(){

    $this->limitFilter(false); //Анализ наличия ограничения на проверку (начало импорта)

    $this->app->pdo->exec('DELETE FROM ips WHERE owner_id = "'.$this->app->user->identity->id.'"');

    return $this->redirect($this->app->controllerName.'/index');
  }

  /**
	 * Извлекает из папки архива скачанную базу и удаляет следы архива
	 */
  public function clearArchive($dir, $pathFile, $downloadFile){
    $od = opendir($dir);
    while($rd = readdir($od)){
      $f = $dir.'/'.$rd;
      if( $rd == '.' || $rd == '..' ) continue;
      if( is_dir($f) ){
        $this->clearArchive($f, $pathFile, $downloadFile);
        rmdir($f);
      }else{
        if( strstr($f, '.mmdb') ){
          if( $f != $pathFile ){
            copy($f, $pathFile);
            unlink($f);
          }
        }else{
          if( !strstr($f, basename($downloadFile) ) ){
            unlink($f);
          }
        }
      }
    }
  }

  /**
   * Скачивает архив IPv4
   */
  public function downloadIPv4Base(){
    if( empty($this->app->config['GeoLite2_IPv4_download']) ) return;

    $archive_dir = dirname($this->app->config['GeoLite2_IPv4_path']);
    $archive_name = basename($this->app->config['GeoLite2_IPv4_download']);
    $downloaded_archive = $archive_dir.'/'.$archive_name;
    copy($this->app->config['GeoLite2_IPv4_download'], $downloaded_archive);
    $phar = new PharData($downloaded_archive);
    $phar->extractTo($archive_dir);

    $this->clearArchive($archive_dir, $this->app->config['GeoLite2_IPv4_path'], $this->app->config['GeoLite2_IPv4_download']);
  }

  /**
   * Скачивает архив IPv6
   */
  public function downloadIPv6Base(){
    if( empty($this->app->config['GeoLite2_IPv6_download']) ) return;

    $archive_dir = dirname($this->app->config['GeoLite2_IPv6_path']);
    $archive_name = basename($this->app->config['GeoLite2_IPv6_download']);
    $downloaded_archive = $archive_dir.'/'.$archive_name;
    copy($this->app->config['GeoLite2_IPv6_download'], $downloaded_archive);
    $phar = new PharData($downloaded_archive);
    $phar->extractTo($archive_dir);

    $this->clearArchive($archive_dir, $this->app->config['GeoLite2_IPv6_path'], $this->app->config['GeoLite2_IPv6_download']);
  }

  /**
   * Скачивает архив базы и извлекает его
   */
  public function actionUpdateGeobase(){
    $this->downloadIPv4Base();
    $this->downloadIPv6Base();

    Request::session('modal_info', [
      'title' => App::t('Информация'),
      'message' => App::t('Геобаза успешно обновлена!'),
    ]);

    return $this->redirect($this->app->controllerName.'/index');
  }

  public static function baseStatus(){
    $base_archive_file =
      dirname(App::get()->config['GeoLite2_IPv4_path']) . '/' .
      basename(App::get()->config['GeoLite2_IPv4_download']);

    $md5_checksum_file = App::get()->config['GeoLite2_IPv4_download'].'.md5';

    if( !is_file($base_archive_file) ){
      return 1;
    }

    $base_archive_checksum = md5_file( $base_archive_file );
    $md5_checksum = file_get_contents($md5_checksum_file);

    if( $base_archive_checksum != $md5_checksum ){
      return 2;
    }

    return 0;
  }
}
