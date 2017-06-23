<?php
if(!isset($_SESSION))
{
    session_start();
}
require_once '../ini.php';


$conn = new Connection();
$conn = $conn->getDbConn();

$user = new User($conn);
$user->get_user_details();
?>
