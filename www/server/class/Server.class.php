<?php
class Server
{
  public static function is_remote()
  {
    return $_SERVER['SERVER_NAME'] != "localhost";
  }

  public static function is_139()
  {
    return $_SERVER['REMOTE_ADDR'] == "139.59.183.156";
  }
}
 ?>
