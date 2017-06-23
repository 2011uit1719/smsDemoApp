<?php

class User
{

	function __construct($conn)
	{
		$this->conn = $conn;
		$this->error = "";
		$this->collection = $this->conn->users;
	}

	public function login()
	{
		$msg = "";
		if(!Csrf::request_is_post() ){
			Result::error("Please Login");
			die();
		}
		// elseif (!Csrf::request_is_same_domain()) {
		// Result::error("Request is not from same domain");
		// die();
		// }
		elseif(!Csrf::csrf_token_is_valid()){
			Result::error("Your Session has been expired");
			die();
		}
		elseif (!Csrf::csrf_token_is_recent()) {
			Result::error("Your Session not recent");
			die();
		}
		elseif ($_POST['username']=="" || $_POST['password']=="") {
			Result::error("Fill all the fields");
			die();
		}
		elseif (!Form::is_valid_email($_POST['username'])) {
			Result::error("Please enter a valid email id");
			die();
		}
		else{
			$email = strtolower($_POST['username']);
			$pass = $_POST['password'];

			$query = array('email' => $email, 'password' => $pass);
			$user = $this->collection->findOne($query);

			if(	($this->collection->count($query) === 1) && array_key_exists("teacher_id", $user)){
				
				$_SESSION['login'] = true;
				$_SESSION['user_id'] = $user['teacher_id'];
				
				$identifier = Token::get();
				$token = Token::get();

				$data = [];
				$data['remember_code'] = $identifier."___".$token;
				$data['teacher_id'] = $user['teacher_id'];
				$data['auth_token'] = Token::get();

				$this->setAuthToken($data['auth_token']);




				$user["remember_identifier"] = $identifier;
				$user["remember_token"] = md5($token);

				$this->collection->update(
					$query,
					['$set'=> $user]	
				);

				Result::success($data);

			}
			else{
				Result::error("Email and Password does not match");
				die();
			}
		}
	}

	public function logout()
	{
		session_destroy();
		Result::success();
	}

	public function reset_password($data, $dbObj)
	{	
		$query = ["teacher_id"=>$data['id']];
		$conn = $dbObj->getUserDbCon();

		if(!Csrf::request_is_post()){
			Result::error("Not a valid request type");
			die();
		}
		elseif (!isset($data['auth_token'])) {
			Result::error("Auth token not found");
			die();
		}
		elseif (!User::authTokenValid($data['auth_token'])) {
			Result::error("You are not authorized");
			die();
		}
		elseif($data['newPassword'] !== $data['reNewPassword']){
			Result::error("Passwords does not match");
			die();
		}
		elseif( !($this->conn->teachers->count($query) && $conn->users->count($query)) ){
			Result::error("User not found");
			die();
		}
		elseif( strlen($data['newPassword']) <8 ){
			Result::error("New password length cannot be less then 8");
			die();
		}
		else {
			$t = $conn->users->findOne($query)	;
			
			if( $t['password'] !== $data['oldPassword'] ){
				Result::error("Old Password does not match");
				die();
			}
			else{
				$t['password'] = $data['newPassword'];
				unset($t['_id']);

				$conn->users->update(
					$query,
					['$set'=>$t]
				);

				Result::success([],"Password change successfully");
			}
		}
	}

	public function validate_rememberme($data, $dbObj)
	{
		if(!Csrf::request_is_post()){
			Result::error("Not authorize");
			die();
		}
		else{
			$rem = explode("___", $data['remember_code']);
			$conn = $dbObj->getUserDbCon();


			$query = [
				"remember_identifier"=> $rem[0],
				'remember_token'=> md5($rem[1])
			];
			
			if($conn->users->count($query)){
				$user = $conn->users->findOne($query);

				$data = [
					"teacher_id"=>$user['teacher_id'],
					"auth_token"=>Token::get()
				];

				$_SESSION['login']=true;

				$this->setAuthToken($data['auth_token']);

				Result::success($data);

			}
			else{
				Result::error("Not authorize");
			}
		}
	}

	private function setAuthToken($token)
	{
		$_SESSION['auth_token'] = $token;
	}

	public static function authTokenValid($token){
		if($token === $_SESSION['auth_token']){
			return true;
		}
		return false;
	}


}
?>
