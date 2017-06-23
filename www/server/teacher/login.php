<?php
require 'autoload.php';

$obj->login(json_decode(file_get_contents('php://input'), true));
?>
