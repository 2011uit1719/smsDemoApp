<?php
require 'autoload.php';
$obj->send_custom_sms(json_decode(file_get_contents('php://input'), true));
?>
