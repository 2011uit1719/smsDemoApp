<?php
require 'autoload.php';
$obj->get_all_students_in_class(json_decode(file_get_contents('php://input'), true));
?>
