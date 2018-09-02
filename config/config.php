<?php

return [
  //admin
  'admin_login' => 'admin', //Логин админа
  'admin_password' => '12345', //Пароль админа
  'admin_email' => 'gugavaeezd@mail.ru', //E-mail админа
  'show_captcha_admin' => true, //Запрашивать капчу у админа (true - да, false - нет)

  //logo and Bg
  'bg_img' => 'bg.jpg', //Фоновая картинка на странице входа (картинка в папке img)
  'logo_img' => 'logo.png', //Логотип на странице входа (картинка в папке img)
  'bg_img-panel' => 'bg_in.jpg', //Фоновая картинка в панели пользователя (картинка в папке img)

  //GeoLite2
  'GeoLite2_IPv4_download' =>
  'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz', //Ссылка на скачивание базы GeoLite2 IPv4
  'GeoLite2_IPv4_path' => __DIR__.'/../files/GeoLite2/GeoLite2-City.mmdb', //Путь к файлу базы GeoLite2 IPv4

  //GeoLite2 (Если GeoLite2_IPv6_download пусто = временно отключено)
  'GeoLite2_IPv6_download' => '', //Ссылка на скачивание базы GeoLite2 IPv6
  'GeoLite2_IPv6_path' => __DIR__.'/../files/GeoLite2/GeoLite2-City_ipv6.mmdb', //Путь к файлу базы GeoLite2 IPv6

  //users
  'default_language' => 'ru', //Язык пользователей по-умолчанию
  'need_agree' => true, //Требовать согласие с условиями (true - да, false - нет)
  'show_captcha_user' => true, //Запрашивать капчу у пользователя (true - да, false - нет)

  //database
  'DB_DRIVER'   => 'mysql', //Драйвер БД
  'DB_HOSTNAME' => 'localhost', //Сервер БД
  'DB_USERNAME' => 'user12345', //Логин пользователя БД
  'DB_PASSWORD' => 'wFNf768', //Пароль пользователя БД
  'DB_DATABASE' => 'sites3_db', //База данных

  //users
  'inactive_delete_days' => 0, //Через сколько дней удалять пользователей по истечении активации
  'ips_delete_min' => 50, //Через сколько минут удалять список IP-адресов пользователя после последней его проверки

  //ips
  'ips_import_speed' => 500, //Количество импортируемых IP-адресов за один тик
  'ips_ckecking_speed' => 250, //Количество проверяемых IP-адресов за один тик

  //pages
  'users_show_num' => 100, //Число записей на странице пользователей
  'ips_show_num' => 50, //Число записей на странице определенных ip-адресов

  //info block
  'show_info_block' => true, //Показывать информационный блок (true - да, false - нет)

  //notification
  'notification_days' => [1,2,3,4,5], //За сколько дней уведомлять о завершении активации
  'notification_limit' => 5, //Лимит отправки писем за один раз
];
