<?php

class Hero
{
	private $conn;
  private $heors;

	function __construct($conn)
	{
		$this->conn = $conn;
		$this->heros = $this->conn->heros;
	}

  public function get_all( )
  {
		if(Csrf::request_is_get()){
	    $documents = [];
			$cursor = $this->heros->find();
			foreach ($cursor as $doc) {
				array_push($documents, $doc);
			}
	    Result::success($documents);
		}
  }

	public function get_one()
  {
		if(Csrf::request_is_get()){
			$arr = array('heroId'=>$_GET['id']);
			if($this->heros->count($arr)){
				$doc = $this->heros->findOne($arr);
				unset($doc['_id']);
				Result::success($doc);
			}
			else{
				Result::error("Person not found");
			}
		}
  }

  public function save($post, $files = NULL, $image = NULL)
  {
		$imagePresent = false;
		if (sizeof($files) != 0) {
			$imagePresent = true;
			$image->set_path("heros/".$post['heroId']);
			$uploadResult = $image->upload($files);
		}

		if($imagePresent && $uploadResult['valid']){
			unset($post['files']);
			$post['heroImg'] = $uploadResult['uploadedImages'];
			$this->heros->insert($post);
			Result::success([], "Person Save Successfully");
		}
		elseif(!$imagePresent) {
			$post['heroImg']="";
			$this->heros->insert($post);
			Result::success([], "Person Save Successfully");
		}
		else {
			Result::error("Error in uploading following files. ".implode(", ", $uploadResult['failedImages']));
		}
  }

	public function delete($post, $image)
	{
		$heroId = $post['id'];
		if($this->is_valid_heroId($heroId)){

			$image->set_path("heros/".$heroId);
			if ($image->delete_dir()) {
				$this->heros->remove(array('heroId'=>$heroId));
				Result::success([], "Person Deleted Successfully");
			}
		}
		else {
			Result::error("Person not found");
		}
	}

	public function delete_img($post, $image)
	{
		$heroId = $post['id'];
		$image->set_path("heros/".$heroId);
		if ($this->is_valid_heroId($heroId)) {
			if ($image->delete_file($post['file'])) {

				$arr = array('heroId' => $heroId );
				$doc = $this->heros->findOne($arr);
				$doc['heroImg']="";
				// $doc['heroImg'] = array_values($doc['heroImg']);

				unset($doc['_id']);
				$this->heros->update(
					$arr,
					array('$set'=>$doc)
				);
				Result::success([], "Person image deleted successfully");
			}
		}
	}

	private function is_valid_heroId($id)
	{
		if($this->heros->count(array('heroId' => $id ))){
			return true;
		}
		return false ;
	}

	public function update($post, $files = NULL, $image = NULL)
	{
		$heroId = $post['heroId'];
		if ($this->is_valid_heroId($heroId)) {
			$arr = array('heroId'=>$heroId);
			$doc = $this->heros->findOne($arr);
			unset($post['_id']);

			$imagePresent = false;
			if (sizeof($files) != 0) { // is hero image index is present
				if ($doc['heroImg'] == "") { // upload only if hero image is empty
					$imagePresent = true;
					$image->set_path("heros/".$post['heroId']);
					$uploadResult = $image->upload($files);
				}
				else{ Result::error("Cannot upload multiple images."); }
			}

			if($imagePresent && $uploadResult['valid']){
				$post['heroImg'] = $uploadResult['uploadedImages'];
				$this->heros->update($arr,array('$set'=>$post));
				Result::success([], "Person Details Updated Successfully");
			}
			elseif(!$imagePresent) {
				$this->heros->update($arr,array('$set'=>$post));
				Result::success([], "Person Details Updated Successfully");
			}
			else {
				Result::error("Error in uploading following files. ".implode(", ", $uploadResult['failedImages']));
			}
		}
	}
}
?>
