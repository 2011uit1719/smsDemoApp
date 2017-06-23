<?php
require 'autoload.php';

$user->validate_rememberme(json_decode(file_get_contents('php://input'), true), new Database());

?>
