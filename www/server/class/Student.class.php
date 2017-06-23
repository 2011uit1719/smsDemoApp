<?php
class Student
{

	private $id_attr = "student_id";
	private $req_id_attr = "student_id";
	private $alreadyAbsentMarkedStd = [];

	function __construct($conn)
	{
		$this->conn = $conn;
		$this->collection = $this->conn->students;
	}

	private function is_valid_data($data)
	{
		if (!isset($data['auth_token'])) {
			Result::error("Auth token not found");
			return false;
		}
		elseif (!isset($data['teacher_id'])) {
			Result::error("Teacher id not found");
			return false;
		}
		elseif (!isset($data["students"])) {
			Result::error("Please provide students list");
			return false;
		}
		elseif (!User::authTokenValid($data['auth_token'])) {
			Result::error("You are not authorized");
			return false;
		}
		return true;
	}
	
	public function save_absent_students($data)
	{
		if(!Csrf::request_is_post()){
			Result::error("Not a valid request type");
			die();
		}
		elseif (!$this->is_valid_data($data)) {
			die();
		}
		elseif (count($data["students"]) == 0) {
			Result::success("", "No absent marked");
			die();
		}
		else{
			Result::success("", "Attendence marked successfully");
			$formatedStudentsArr = $this->format_stuents_arr($data['students']);
			// print_r($formatedStudentsArr);
			$path = APPPATH."/bgProcesses";
			foreach ($formatedStudentsArr as $student) {
				$value = "'".$student['phone']."' '".$student['sms']."' '".$student['student_id']."' '".$data['teacher_id']."'";
				shell_exec("nohup php ".$path."/sendAbsentSms.php ".$value." >>  ".$path."/process.log 2>&1 </dev/null &");   
			}
		}
	}

	public function send_custom_sms($data)
	{
		if(!Csrf::request_is_post()){
			Result::error("Not a valid request type");
			die();
		}
		elseif (!$this->is_valid_data($data)) {
			die();
		}
		elseif (count($data["students"]) == 0) {
			Result::success("", "No student selected");
			die();
		}
		else{
			Result::success("", "Message sent successfully");
			$formatedStudentsArr = $this->format_stuents_arr($data['students']);
			
			$path = APPPATH."/bgProcesses";
			foreach ($formatedStudentsArr as $student) {
				$value = "'".$student['phone']."' '".$student['sms']."' '".$student['student_id']."' '".$data['teacher_id']."'";
				shell_exec("nohup php ".$path."/sendCustomSms.php ".$value." >>  ".$path."/process.log 2>&1 </dev/null &");   
			}
		}
	}

	private function format_stuents_arr($students)
	{
		return array_map(function ($abs)
			{
				unset($abs['name']);
				unset($abs['rollNo']);
				unset($abs['_id']);
				if(isset($abs['student_img'])){unset($abs['student_img']);}
				if(isset($abs['gender'])){unset($abs['gender']);}
				if(isset($abs['dob'])){unset($abs['dob']);}
				if(isset($abs['village'])){unset($abs['village']);}
				if(isset($abs['address'])){unset($abs['address']);}

				return $abs;
			}, $students);

	}
	private function query($arr)
	{
		return array(
		"$this->id_attr"=>$arr[$this->req_id_attr],
		"class_id"=>$arr['class_id'],
		"date"=>$arr['date']
		);
	}

	private function is_absent_already_marked($students)
	{
		$list = array_filter($students, function($student)
			{
				$query = $this->query($student);
				return $this->conn->absent->findOne($query);
			});
		$this->set_absent_marked_students($list);
		return count($list);
	}

	private function set_absent_marked_students($students)
	{
		$this->alreadyAbsentMarkedStd = $students;
	}

	private function get_absent_marked_students()
	{
		return $this->alreadyAbsentMarkedStd;
	}

	private function compose_absent_marked_error($students)
	{
		$error = "";
		for ($i=0; $i < count($students) ; $i++) {
			$name = $students[$i]['name'];
			if ($i === 0) {
				$error = $name;
			}

			elseif($i == count($students)-1){
				$error = $error . " and $name";
			}

			else {
				$error = $error. ", $name";
			}
		}
		return "$error absent is already marked";
	}
}
?>
