<?php
require 'autoload.php';

if(Csrf::request_is_get()){
  $arr = [];
  $arr['token'] = Csrf::create_csrf_token();

  echo json_encode($arr);
}
?>
