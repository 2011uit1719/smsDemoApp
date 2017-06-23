<?php

    $stagingUrl =       "http://35.154.180.155/staging/modernDefence/app/www";
    $productionUrl =    "http://35.154.180.155/modernDefence/app/www";
    $localhostUrl =     "http://localhost/webflax/modernDefence/app/www";

    $stagingAppPath =    "/var/www/html/staging/modernDefence/app/www/server";
    $productionAppPath = "/var/www/html/modernDefence/app/www/server";

    $baseUrl = "";
    $appPath = "";

    if($_SERVER['SERVER_NAME'] != "localhost"){

      if(connToStagingDb()){
        $baseUrl = $stagingUrl;
        $appPath = $stagingAppPath;
      }
      else{
        $baseUrl = $productionUrl;
        $appPath = $productionAppPath;
      }
      
    }
    else{
      $baseUrl = $localhostUrl;
      $appPath = $stagingAppPath;
    }

define("BASEURL",$baseUrl);
define("APPPATH",$appPath);

?>