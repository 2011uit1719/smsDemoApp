<?php

class Csrf
{
  public static function request_is_get() {
  	if($_SERVER['REQUEST_METHOD'] === 'GET'){
      return true;
    }
    return false;
  }

  public static function request_is_post() {
  	if($_SERVER['REQUEST_METHOD'] === 'POST'){
      return true;
    }
    return false;
  }

  // Generate a token for use with CSRF protection.
  // Does not store the token.
  public static function csrf_token() {
  	return md5(uniqid(rand(), TRUE));
  }

  // Generate and store CSRF token in user session.
  // Requires session to have been started already.
  public static function create_csrf_token() {
  	$token = Csrf::csrf_token();
    $_SESSION['csrf_token'] = $token;
   	$_SESSION['csrf_token_time'] = time();
  	return $token;
  }

  // Destroys a token by removing it from the session.
  public static function destroy_csrf_token() {
    $_SESSION['csrf_token'] = null;
   	$_SESSION['csrf_token_time'] = null;
  	return true;
  }

  // Return an HTML tag including the CSRF token
  // for use in a form.
  // Usage: echo csrf_token_tag();
  public static function csrf_token_tag() {
  	$token = Csrf::create_csrf_token();
  	return "<input type=\"hidden\" name=\"csrf_token\" value=\"".$token."\">";
  }

  // Returns true if user-submitted POST token is
  // identical to the previously stored SESSION token.
  // Returns false otherwise.
  public static function csrf_token_is_valid() {
  	if(isset($_POST['csrf_token'])) {
  		$user_token = $_POST['csrf_token'];
  		$stored_token = $_SESSION['csrf_token'];
  		return $user_token === $stored_token;
  	} else {
  		return false;
  	}
  }

  // You can simply check the token validity and
  // handle the failure yourself, or you can use
  // this "stop-everything-on-failure" function.
  public static function die_on_csrf_token_failure() {
  	if(!csrf_token_is_valid()) {
  		die("CSRF token validation failed.");
  	}
  }

  // Optional check to see if token is also recent
  public static function csrf_token_is_recent() {
  	$max_elapsed = 60 * 60 * (1/12); // 1 day
  	if(isset($_SESSION['csrf_token_time'])) {
  		$stored_time = $_SESSION['csrf_token_time'];
  		return ($stored_time + $max_elapsed) >= time();
  	} else {
  		// Remove expired token
  		destroy_csrf_token();
  		return false;
  	}
  }

  public static function request_is_same_domain() {
  	if(!isset($_SERVER['HTTP_REFERER'])) {
  		// No refererer sent, so can't be same domain
  		return false;
  	} else {
  		$referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
  		$server_host = $_SERVER['HTTP_HOST'];

  		// Uncomment for debugging
  		// echo 'Request from: ' . $referer_host . "<br />";
  		// echo 'Request to: ' . $server_host . "<br />";

  		return ($referer_host == $server_host) ? true : false;
  	}
  }
}


?>
