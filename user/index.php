<?php
//error_reporting(E_ALL);

define('PAGE_TYPE', 'user');

$config = require '../config/config.php';
require '../components/autoload.php';

$config['urlManager'] = [
  'userpanel' => 'main/login',
  'ips' => 'user-ips/index',
  'ips/import' => 'user-ips/import',
  'ips/check' => 'user-ips/check',
  'ips/clear' => 'user-ips/clear',
  'ips/get-ips-by-country' => 'user-ips/get-ips-by-country',
];

$app = new App($config);
$app->run();
