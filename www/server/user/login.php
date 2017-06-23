<?php
require 'autoload.php';

$data = json_decode(file_get_contents('php://input'), true);

$_POST['username'] = $data['username'];
$_POST['password'] = $data['password'];
$_POST['csrf_token'] = $data['csrf_token'];

$user->login();
?>
