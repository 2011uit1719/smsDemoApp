<?php

class Connection
{
  protected function getConn()
  {
    // usefull on local system
    // when we want to connect to remote server instead of local
    $connectToRemoteServer = true;
    $m = "";

    if ($this->is_remote_server() || $connectToRemoteServer) {
      $m = $this->get_server_conn();
    }
    else {
      $m = $this->get_local_conn();
    }
    return $m;
  }
  protected function get_server_conn()
  {
    try{
        $username = "appUser";
        $password = "useratriddhi123";
        $m = new MongoClient("mongodb://$username:$password@35.154.180.155:27017/admin");
        return $m;
    }
    catch(Exception $e){
      die($e->getMessage());
    }
  }

  protected function is_remote_server()
  {
    return $_SERVER['SERVER_NAME'] != "localhost";
  }

  protected function get_local_conn()
  {
    try{
      $username = "admin";
      $password = "password";
      $m = new MongoClient("mongodb://$username:$password@127.0.0.1:27017/admin");
      return $m;
    }
    catch(Exception $e){
      die($e->getMessage());
    }
  }
}


?>
