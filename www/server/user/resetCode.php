<?php
require 'autoload.php';

$user->reset_password(json_decode(file_get_contents('php://input'), true), new Database());

?>
