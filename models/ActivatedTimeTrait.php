<?php
trait ActivatedTimeTrait{
  public function getActivatedTypes(){
    return [
      App::t('Дни') 		=> 'D',
      App::t('Часы') 		=> 'H',
      App::t('Минуты') 	=> 'M',
    ];
  }

  /**
   * Добавляет время активации
   * Применять только к экземплярам классов проверки форм
   */
  public function setActivatedTime($seconds = 'no-value'){
    if( $seconds == 'no-value' ){
      $seconds = $this->getActivatedSeconds();
    }
    if(
      $this->activated_time > 0 &&
      $this->activated_time + $this->activated_add_time < time()
    ){
      $this->activated_add_time += time() - ($this->activated_time + $this->activated_add_time);
    }
    $this->activated_add_time += $seconds;
  }

  /**
   * Определяет добавленное/отнятое время активации
   * Применять только к экземплярам классов проверки форм
   */
  public function getActivatedSeconds(){
    switch( $this->activated_type ){
      case "D":
        return $this->activated_num * 86400;
      break;
      case "H":
        return $this->activated_num * 3600;
      break;
      case "M":
        return $this->activated_num * 60;
      break;
      default:
        return $this->activated_num;
      break;
    }
  }



  /**
   * Выводит время в удобном виде
   */
  public function getTimeTitle($time, $language = ''){
    if( $time / 86400 >= 1 ){
      $number = $time / 86400;
      $floorNumber = floor($number);
      $remainder = $time - ($floorNumber * 86400);
      $value = $floorNumber.' '.App::t('дн.', $language);
      if( $remainder >= 3600 ){
        $value.= ' '.self::getTimeTitle($remainder, $language);
      }else
      if( $remainder >= 60 ){
        $value.= ' 0 '.App::t('ч.', $language);
        $value.= ' '.self::getTimeTitle($remainder, $language);
      }
      return $value;
    }else
    if( $time / 3600 >= 1 ){
      $number = $time / 3600;
      $floorNumber = floor($number);
      $remainder = $time - ($floorNumber * 3600);
      $value = $floorNumber.' '.App::t('ч.', $language);
      if( $remainder >= 60 ){
        $value.= ' '.self::getTimeTitle($remainder, $language);
      }
      return $value;
    }else
    if( $time > 0 ){
      $number = $time / 60;
      return ceil($number).' '.App::t('мин.', $language);
    }else{
      return '-';
    }
  }

  /**
   * Выводит время активации в удобном виде
   */
  public function getActivatedTimeTitle($language = ''){
    $time = $this->activated_add_time;
    if( $this->activated_time > 0 ){
      if( $this->activated_time + $this->activated_add_time > time() ){
        $time = ($this->activated_time + $this->activated_add_time) - time();
      }else{
        return '-';
      }
    }

    return $this->getTimeTitle($time, $language);
  }
}
