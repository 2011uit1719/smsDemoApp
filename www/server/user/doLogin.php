<?php
session_start();

$_SESSION['login'] = true;
$_SESSION['user_id'] = $_GET['teacher_id'];
?>
