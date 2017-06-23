<?php

// error_reporting(-1);
// ini_set('display_errors', 'On');

class Database  extends Connection{
  private $db_conn;

  public function getDbConn(){

    $m = $this->getConn();
    $db_conn = $this->getUserDbCon();
    if (isset($_SESSION['login'])) {

      if(connToStagingDb()){
        $db_conn = getStagingDbConn($m);
      }
      else{
        $db_conn = getProductionDbConn($m);
      }
      
    }

    return($db_conn);
  }

  public function getUserDbCon(){
    $m = $this->getConn();
    if(connToStagingDb()){
      return getStgAutDbUsersConn($m);
    }
    else{
      return getProdAutDbUsersConn($m);
    }

  }
}
?>
