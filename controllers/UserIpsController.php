<?php
class UserIpsController extends AdminIpsController{

  public function accessRules(){
    return [
        [
            'allow' => true,
            'actions' => ['index', 'get-ips-by-country', 'import', 'check', 'clear'],
            'roles' => ['user'],
        ],
    ];
  }
}
