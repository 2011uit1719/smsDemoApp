<?php
$path = "/var/www/html/modernDefence/admin/app/server/process";
$arr = [
"8954111731 'hello ak' 'student-102'", "9509415782 'hello hk' 'student-106'"
];

foreach ($arr as $value) {
 shell_exec("nohup php ".$path."/sendSmsProcess.php ".$value." >>  ".$path."/process.log 2>&1 </dev/null &");   
}



?>