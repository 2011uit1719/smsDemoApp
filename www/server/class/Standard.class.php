<?php
class Standard
{

	private $id = "class_id";
	function __construct($conn)
	{
		$this->conn = $conn;
		$this->collection = $this->conn->classes;
	}

	public function get_all_students_in_class()
	{
		if(!Csrf::request_is_get()){
			Result::error("Not a valid request type");
		}
		elseif (!isset($_GET['auth_token'])) {
			Result::error("Auth token not found");
		}
		elseif (!isset($_GET["$this->id"])) {
			Result::error("Please provide class id");
		}
		elseif (!User::authTokenValid($_GET['auth_token'])) {
			Result::error("You are not authorized");
		}
		else{
			$query = array($this->id=>$_GET["$this->id"]);
			if($this->collection->count($query)){
				

				$documents = [];
				$cursor = $this->conn->students->find($query);
				foreach ($cursor as $doc) {
					array_push($documents, $doc);
				}
				
				$class = $this->collection->findOne($query);

				$arr = [];
				$arr['students'] = $documents;
				$arr['totalStudents'] = count($documents);
				$arr['class_name'] = $class['class_name'];

				Result::success($arr);
			}
			else{
				Result::error("Class not found");
			}
		}

 	 }



	private function get_sms_data($type, $query)
	{
		if($type == "absent"){
			return iterator_to_array( $this->conn->absent->find($query), false );
		}
		else{
			return iterator_to_array( $this->conn->custom->find($query), false );
		}
	}

	public function get_report()
	{
		if(!Csrf::request_is_get()){
			Result::error("Not a valid request type");
		}
		elseif (!isset($_GET['auth_token'])) {
			Result::error("Auth token not found");
		}
		elseif (!isset($_GET["smsType"])) {
			Shout::error("Please select sms type");
		}
		elseif (!isset($_GET["selectedClass"])) {
			Result::error("Please provide class id");
		}
		elseif (!User::authTokenValid($_GET['auth_token'])) {
			Result::error("You are not authorized");
		}
		else{

			$query = [];
			$docs = [];

			if($_GET['selectedStudent'] != "all"){
				$query["student_id"] = $_GET['selectedStudent'];
			}

			if($_GET['type'] === "day"){
				$query['date'] = $_GET['selectedDate'];	
			}
			elseif($_GET['type'] === "month"){
				$q = "-". $_GET['selectedMonth'] ."-";
				$regex = array('$regex' => new MongoRegex("/$q/i"));
				$query['date'] = $regex;
			}

			$query['class_id'] = 	$_GET['selectedClass'];
			$query['teacher_id'] =	$_GET['teacher_id'];

			
			if($_GET['type'] == "week"){
				$weekDates = $this->get_week_dates($_GET['selectedDate']);

				foreach ($weekDates as $date) {
					$query['date'] = $date;
					$docs = array_merge(
						$docs,
						$this->get_sms_data($_GET['smsType'], $query)
					);
				}

			}
			else {
				$docs = $this->get_sms_data($_GET['smsType'], $query);
			}
			
			
			$result = [];
			if($_GET['selectedStudent'] == "all"){
				$studentArray = [];

				foreach ($docs as $absent) {
					if( !isset($studentArray[$absent['student_id']]) ){
						$studentArray[$absent['student_id']] = [];
					}
					array_push($studentArray[$absent['student_id']], $absent);
				}

				
				foreach ($studentArray as $key => $value) {
					$student = $this->conn->students->findOne(["student_id"=>$key]);

					if(count($student)){
						array_push(
							$result,
							[
								"student_id"=>$student['student_id'],
								"rollNo"=>$student['rollNo'],
								"name"=>$student['name'],
								"total_sms_sent"=> count( $studentArray[$key] )
							]
						);
					}

				}

				
			}
			else{
				$s = $docs[0];
				$student = $this->conn->students->findOne(["student_id"=>$s['student_id']]);

				array_push(
					$result , 
					[
						"student_id"=>$student['student_id'],
						"rollNo"=>$student['rollNo'],
						"name"=>$student['name'],
						"total_sms_sent"=>count($docs)
					]
				);
			}
			Result::success($result);
			
		}
	}

	public function get_week_dates($keyDate)
	{
		$weekDates = [];
		$dateArr =  explode( "-", $keyDate );
				
		$totalDays = cal_days_in_month(CAL_GREGORIAN, (int) $dateArr[1] , (int) $dateArr[2]);				
		$sDay = (int)$dateArr[0];
		$eDay = $sDay + 7;

		$day = $sDay;

		while($day != $eDay) {
			if($day <= 9){
				$date = "0".$day."-".$dateArr[1]."-".$dateArr[2];
			}
			else{
				$date = $day."-".$dateArr[1]."-".$dateArr[2];
			}
			$query['date'] = $date;

			array_push($weekDates, $date);
			
			$day = $day + 1;
			if($day > $totalDays){
				$left = $eDay - $totalDays;
				$day = 01;
				$eDay = $left;
			}
		}

		return $weekDates;
	}

	function getc_date()
	{
		date_default_timezone_set("Asia/Kolkata");
		$t=time();
		return(date("d-m-Y",$t));
	}



}
?>
