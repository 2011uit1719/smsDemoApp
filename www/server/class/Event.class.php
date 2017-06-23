<?php

class Event
{
	private $conn;
  private $events;

	function __construct($conn)
	{
		$this->conn = $conn;
		$this->events = $this->conn->events;
	}

  public function get_all( )
  {
		if(Csrf::request_is_get()){
	    $documents = [];
			$cursor = $this->events->find();
			foreach ($cursor as $doc) {
				array_push($documents, $doc);
			}
	    Result::success($documents);
		}
  }

	public function get_one()
  {
		if(Csrf::request_is_get()){
			$arr = array('eventId'=>$_GET['id']);
			if($this->events->count($arr)){
				$doc = $this->events->findOne($arr);
				unset($doc['_id']);
				Result::success($doc);
			}
			else{
				Result::error("Event not found");
			}
		}
  }

  public function save($post, $files = NULL, $image = NULL)
  {
		if(Csrf::request_is_post()){
			$imagePresent = false;
			if (sizeof($files) != 0) {
				$imagePresent = true;
				$image->set_path("events/".$post['eventId']);
				$uploadResult = $image->upload($files);
			}

			if($imagePresent && $uploadResult['valid']){
				unset($post['files']);
				$post['eventImg'] = $uploadResult['uploadedImages'];
				$this->events->insert($post);
				Result::success([], "Event Save Successfully");
			}
			elseif(!$imagePresent) {
				$this->events->insert($post);
				Result::success([], "Event Save Successfully");
			}
			else {
				Result::error("Error in uploading following files. ".implode(", ", $uploadResult['failedImages']));
			}
		}
  }

	public function delete($post, $image)
	{
		if(Csrf::request_is_post()){
			$eventId = $post['id'];
			if($this->is_valid_eventId($eventId)){

				$image->set_path("events/".$eventId);
				if ($image->delete_dir()) {
					$this->events->remove(array('eventId'=>$eventId));
					Result::success([], "Event Deleted Successfully");
				}
			}
			else {
				Result::error("Event not found");
			}
		}
	}

	public function delete_img($post, $image)
	{
		if(Csrf::request_is_post()){
			$eventId = $post['id'];
			$image->set_path("events/".$eventId);
			if ($this->is_valid_eventId($eventId)) {
				if ($image->delete_file($post['file'])) {

					$arr = array('eventId' => $eventId );
					$doc = $this->events->findOne($arr);
					$key = array_search($post['file'], $doc['eventImg']);
					unset($doc['eventImg'][$key]);
					$doc['eventImg'] = array_values($doc['eventImg']);

					unset($doc['_id']);
					$this->events->update(
						$arr,
						array('$set'=>$doc)
					);
					Result::success([], "Event image deleted successfully");
				}
			}
		}
	}

	private function is_valid_eventId($id)
	{
		if($this->events->count(array('eventId' => $id ))){
			return true;
		}
		return false ;
	}

	public function update($post, $files = NULL, $image = NULL)
	{
		if(Csrf::request_is_post()){
			$eventId = $post['eventId'];
			if ($this->is_valid_eventId($eventId)) {
				$arr = array('eventId'=>$eventId);
				$doc = $this->events->findOne($arr);
				unset($post['_id']);

				$imagePresent = false;
				if (sizeof($files) != 0) {
					$imagePresent = true;
					$image->set_path("events/".$post['eventId']);
					$uploadResult = $image->upload($files);
				}

				if($imagePresent && $uploadResult['valid']){
					$post['eventImg'] = array_merge( $doc['eventImg'], $uploadResult['uploadedImages'] );
					$this->events->update($arr,array('$set'=>$post));
					Result::success([], "Event Details Updated Successfully");
				}
				elseif(!$imagePresent) {
					$this->events->update($arr,array('$set'=>$post));
					Result::success([], "Event Details Updated Successfully");
				}
				else {
					Result::error("Error in uploading following files. ".implode(", ", $uploadResult['failedImages']));
				}
			}
		}
	}
}
?>
