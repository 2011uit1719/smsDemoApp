<?php

class Form{

  public static function is_valid_email($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

  public static function is_valid_number($number)
	{
		return preg_match('/^\d{10}$/', $number);
	}
}


?>
