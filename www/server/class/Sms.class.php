<?php

class Sms{
    public static function send($students)
	{
		$path = "/var/www/html/staging/modernDefence/app/www/server/bgProcesses";
		foreach ($students as $student) {
			$value = "'".$student['phone']."' '".$student['sms']."' '".$student['student_id']."'";
			shell_exec("nohup php ".$path."/sendSmsProcess.php ".$value." >>  ".$path."/process.log 2>&1 </dev/null &");   
		}

	}
}

?>