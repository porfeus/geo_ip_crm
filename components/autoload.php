<?php
spl_autoload_register(function ($class) {
    $component_file = __DIR__.'/../components/' . $class . '.php';
    $controllers_file = __DIR__.'/../controllers/' . $class . '.php';
    $models_file = __DIR__.'/../models/' . $class . '.php';

    if( is_file($component_file) ) include $component_file;
    elseif( is_file($controllers_file) ) include $controllers_file;
    elseif( is_file($models_file) ) include $models_file;

    else{
      if( strpos($class, 'Controller') ){
        header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
        $app = App::get();
        $app->controller = new MainController($app);
        die($app->controller->renderPartial('404'));
      }else{
        die('Класс '.$class.' не найден!');
      }
    }
});
