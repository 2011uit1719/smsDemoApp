<?php
class Image
{
	private $uploadedImages = [];
	private $failled = [];
	private $module = "";
	private $imageUploadResult = ["valid"=>true, "error"=>""];

	public function upload($files)
	{
		$files = $files['files'];
		$dirPath = $this->get_path();

		if(!is_dir($dirPath)){
			mkdir($dirPath);
		}

		if (is_array($files['name'])) {
			foreach ($files['name'] as $key => $file_name) {
				$this->upload_image($dirPath, $files, $file_name, $key);
			}
		}
		else{
			$this->upload_image($dirPath, $files, $files['name'], 0);
		}


		if( !$this->is_img_uploaded() )
		{
			$this->delete_dir($dirPath);
			$this->imageUploadResult["valid"] = false;
			$this->imageUploadResult["failedImages"] = $this->failed;
		}
		else {
			$this->imageUploadResult["uploadedImages"] = $this->uploadedImages;
		}
		return $this->imageUploadResult;
	}

	private function is_img_uploaded()
	{
		if( isset($this->failed) ) return false;
		return true;
	}

	public function set_path($path)
	{
		if ($_SERVER['SERVER_NAME'] != "localhost"){
			$this->imageUploadPath = "/var/www/html/images/{$path}/";
		}
		else {
			$this->imageUploadPath = "../../img/{$path}/";
		}
	}

	public function get_path()
	{
		return $this->imageUploadPath;
	}

	public function delete_dir()
	{
		$path = $this->get_path();
		// echo "$path";
    if (is_dir($path) === true)
    {
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file)
        {
            $this->delete_file($file);
        }

        return rmdir($path);
    }
    elseif (is_file($path) === true)
    {
        return unlink($path);
    }

    return false;
	}

	public function delete_file($file)
	{
		$path = $this->get_path().$file;

		if (is_file($path) === true)
	    {
	        return unlink($path);
	    }
	  return false;
	}

	public function uploaded_images()
	{
		return $this->uploadedImages;
	}

	public function failedImages()
	{
		return $this->failed;
	}

	private function upload_image($dirPath, $files, $file_name, $key)
	{
		$allowed = ['jpg', 'png', 'jpeg', 'gif'];

		if(is_array($files['name'])){
			$file_tmp = $files['tmp_name'][$key];
			$file_size = $files['size'][$key];
			$file_error = $files['error'][$key];
		}
		else{
			$file_tmp = $files['tmp_name'];
			$file_size = $files['size'];
			$file_error = $files['error'];
		}

		// getimagesize($file_tmp);

		$file_ext = explode('.', $file_name);
		$file_ext = strtolower(end($file_ext));

		if (in_array($file_ext, $allowed)) {

			if($file_error === 0){

				if($file_size <= 2097152){

					if(is_dir($dirPath)){

						if(is_writable($dirPath)){

							$new_file_name = uniqid('', true) . '.' . $file_ext;
		          $file_destination = $dirPath . $new_file_name;
							if(move_uploaded_file($file_tmp, $file_destination)){
								$this->uploadedImages[$key] = $new_file_name;
							}
							else{
								$this->failed[$key] = "Cannot upload Image:- {$file_destination} location not found";
							}
						}
						else{
							$this->failed[$key] = $dirPath." directory is not writable ";
						}
					}
					else{
						$this->failed[$key] = $dirPath." directory Not found ";
					}
				}
				else
					$this->failed[$key] = $file_name . " is too large, image size should less then 2MB  ";
			}
			else
				$this->failed[$key] = $file_name . " errored with code {$file_error}";
		}
		else
			$this->failed[$key] = $file_name . " file extension '{$file_ext}' is not allowed";
	}
}

?>
