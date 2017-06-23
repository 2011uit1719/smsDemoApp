<?php
require 'autoload.php';
$obj->save_absent_students(json_decode(file_get_contents('php://input'), true));
?>
