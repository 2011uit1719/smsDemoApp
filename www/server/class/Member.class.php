<?php

class Member
{
	private $conn;
  private $members;
	private $members_bulk_upload;
	private $bloodGroup = ["A+", "A-", "B+", "B-", "O+", "O-", "AB+", "AB-"];

	function __construct($conn)
	{
		$this->conn = $conn;
		$this->members = $this->conn->members;
		$this->members_bulk_upload = $this->conn->members_bulk_upload;
	}

	public function bulk_upload($post)
	{
		if(Csrf::request_is_post()){
			$incorrectBloodGroupName = [];
      $incorrectRowFill = [];
			$members = $post['data'];

			$filedetails = array(
				'FILE_NAME' => $post['filename'],
				'TOTAL_MEMBERSS_ADDED' => count($post['data'])
			);

			$row = 1;
			foreach ($members as $mem) {
				$row++;
        if(array_search($mem['BLOOD_GROUP'], $this->bloodGroup) === false){
          $file = [];
          $file['member'] = $mem;
          $file['row'] = $row;
          array_push($incorrectBloodGroupName, $file);
					break;
        }
        if (!$this->member_data_correct($mem)) {
					$file = [];
          $file['member'] = $mem;
          $file['row'] = $row;
          array_push($incorrectRowFill, $file);
					break;
        }
			}

			if (count($incorrectBloodGroupName)) {
        $file = $incorrectBloodGroupName[0];
				$member = $file['member'];
        Result::error("Blood Group name ".$member['BLOOD_GROUP']." is incorrect on row ".$file['row']);
      }
      else if(count($incorrectRowFill)) {
        $file = $incorrectRowFill[0];
        Result::error("Please fill all details of row ".$file ['row']);
      }
			else{
				$this->members->batchInsert($members);
				$this->members_bulk_upload->insert($filedetails);
				Result::success([], "All Members Uploaded Successfully");
			}
		}

	}

	public function get_all_bulkUploadFile( )
  {
    if(Csrf::request_is_get()){
			$documents = [];
			$cursor = $this->members_bulk_upload->find();
			foreach ($cursor as $doc) {
				array_push($documents, $doc);
			}
	    Result::success($documents);
		}
  }

	public function delete_bulkUploadFile($post)
	{
		$item = $post['item'];
		$obj = $item['_id'];
		$arr = array('_id'=>new MongoId($obj['$id']));
		if($this->members_bulk_upload->count($arr)){
	    $this->members_bulk_upload->remove($arr);
			Result::success([], "File Details Deleted Successfully");
		}
		else {
			Result::error("File not found");
		}
	}

	public function get_all( )
  {
    if(Csrf::request_is_get()){
			$documents = [];
			$cursor = $this->members->find();
			foreach ($cursor as $doc) {
				array_push($documents, $doc);
			}
	    Result::success($documents);
		}
  }

	public function get_one($post)
  {
		if(Csrf::request_is_get()){
			$arr = array('memId'=>$post['id']);
			if($this->members->count($arr)){
				$doc = $this->members->findOne($arr);
				unset($doc['_id']);
				Result::success($doc);
			}
			else{
				Result::error("Event not found");
			}
		}
  }


	public function delete($post)
	{
		if(Csrf::request_is_post()){
			$arr = array('memId'=>$post['item']['memId']);
			if($this->members->count($arr)){
		    $this->members->remove($arr);
				Result::success([], "Member Deleted Successfully");
			}
			else {
				Result::error("Member not found");
			}
		}
	}

	private function member_data_correct($member)
	{
		if($member['MEMBER_NAME'] == "") return false;
    if($member['GENDER'] == "") return false;
    if($member['BLOOD_GROUP'] == "") return false;
    if($member['MOBILE_NUMBER'] == "") return false;
    if($member['CITY'] == "") return false;
    if($member['ADDRESS'] == "") return false;

    return true;
	}
}
?>
