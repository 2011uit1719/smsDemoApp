<?php
class Teacher
{

	private $id = "teacher_id";
	function __construct($conn)
	{
		$this->conn = $conn;
		$this->collection = $this->conn->teachers;
	}

	public function get_login_teacher_id()
	{
		if(isset($_SESSION['user_id'])){
			Result::success($_SESSION['user_id']);
		}
	}

	public function get_details()
	{
    	if(!Csrf::request_is_get()){
			Result::error("Not a valid request type");
			die();
		}
		elseif (!isset($_GET['auth_token'])) {
			Result::error("Auth token not found");
			die();
		}
		elseif (!isset($_GET["$this->id"])) {
			Result::error("Please provide teacher id");
			die();
		}
		elseif (!User::authTokenValid($_GET['auth_token'])) {
			Result::error("You are not authorized");
			die();
		}
		else{
			$arr = array($this->id=>$_GET["$this->id"]);
			if($this->collection->count($arr)){

        		$doc['userDetails'] = $this->collection->findOne($arr);
				$doc['userDetails']['classes'] = [];

				// print_r($doc);
				
				$ids = explode(",", $doc['userDetails']['class_id']);
				foreach ($ids as $id) {
					$className = $this->get_class_name($id);	
					$t = $this->conn->students->count(array("class_id"=>$id));
					array_push($doc['userDetails']['classes'], ["id"=>$id, "name"=>$className, "totalStudents"=>$t]);
				}

				unset($doc['userDetails']['_id']);

				Result::success($doc);
			}
			else{
				Result::error("Teacher not found");
				die();
			}
		}
	}

	public function get_teacher_id()
	{
		$arr = array("email"=>$_GET['email']);
		if($this->conn->teachers->count($arr)){
			$doc = $this->collection->findOne($arr);
			Result::success($doc['teacher_id']);
		}
		else {
			Result::error("Login plz");
			die();
		}
	}

	public function get_class_name($classId)
	{
		$class = $this->conn->classes->findOne(array("class_id"=>$classId));
		return $class['class_name'];
	}
  private function get_student_details_of_teacher()
  {
		if(Csrf::request_is_get()){
			$arr = array($this->id=>$_GET["$this->id"]);
			if($this->collection->count($arr)){

				$teacher = $this->collection->findOne($arr);
				$class = $this->get_class_name($teacher['class_id']);



				$documents = [];
				$cursor = $this->conn->students->find(array("class_id"=>$class));
				foreach ($cursor as $doc) {
					array_push($documents, $doc);
				}
				return($documents);

				Result::success($documents);
			}
			else{
				Result::error("Teacher not found");
				die();
			}
		}

  }

}
?>
