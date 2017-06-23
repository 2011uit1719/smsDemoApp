<?php
if(!isset($_SESSION))
{
    session_start();
}
require_once '../ini.php';

if ($_SERVER['SERVER_NAME'] != "localhost") {
	$m = new MongoClient("mongodb://139.59.183.156");
}
else {
	$m = new MongoClient();
}
$conn = $m->admin;;

$user = new User($conn);
$user->get_login_details();
?>
