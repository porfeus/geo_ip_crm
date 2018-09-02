<?php
//error_reporting(E_ALL);

define('PAGE_TYPE', 'admin');

$config = require '../config/config.php';
require '../components/autoload.php';

$config['urlManager'] = [
  'adminpanel' => 'main/login',
];

$app = new App($config);
$app->run();
