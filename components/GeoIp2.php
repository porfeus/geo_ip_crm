<?php

class GeoIp2{
  public static $reader = false;

  public static function getIpInfo($ip){
    try{
      if( !self::$reader ){
        require_once '../vendor/autoload.php';

        if( filter_var($ip, FILTER_FLAG_IPV6) && App::get()->config['GeoLite2_IPv6_download'] ){ // Если IPv6
          self::$reader = new \GeoIp2\Database\Reader(App::get()->config['GeoLite2_IPv6_path']);
        }else{ // Если IPv4
          self::$reader = new \GeoIp2\Database\Reader(App::get()->config['GeoLite2_IPv4_path']);
        }
      }

      return self::$reader->city($ip);
    }catch(\Exception $e){
      return [];
    }
  }
}
