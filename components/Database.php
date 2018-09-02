<?php

class Database{
  public function __construct($app){
    $this->app = $app;

    $this->connect();
  }

  public function insert($table, $addRows){
    $rows = implode(", ", array_keys($addRows));
    $rowsP = ":".implode(", :", array_keys($addRows));
    $stmt = $db->prepare("INSERT INTO $table ($rows) VALUES ($rowsP)");

    foreach( $addRows as $key=>$value ){
      $stmt->bindParam(':'.$key, $value);
    }

    return $stmt->execute();
  }

  public function delete($table, $searchRows){

  }

  public function count($table, $searchRows){

  }

  public function update($table, $searchRows, $updateRows){

  }

  public function select($table, $searchQuery){
    $rows = implode(", ", array_keys($addRows));
    $rowsP = ":".implode(", :", array_keys($addRows));
    $stmt = $db->prepare("INSERT INTO $table ($rows) VALUES ($rowsP)");

    foreach( $addRows as $key=>$value ){
      $stmt->bindParam(':'.$key, $value);
    }

    return $stmt->execute();
  }

  public function connect(){
    $driver = $this->app->config['DB_DRIVER'];
    $host = $this->app->config['DB_HOSTNAME'];
    $user = $this->app->config['DB_USERNAME'];
    $pass = $this->app->config['DB_PASSWORD'];
    $dbname = $this->app->config['DB_DATABASE'];
    try
    {
      $this->db = new PDO( "$driver:host=$host;dbname=$dbname", $user, $pass);
    }
    catch(  PDOException $e  )
    {
      echo "Database connect error!";
      echo "You have an error: ".$e->getMessage()."<br>";
      echo "On line: ".$e->getLine();
      exit;
    }
  }
}
