<?php
class Result
{
	public static function success($data = [], $msg = "Operation Successfull")
	{
		echo json_encode(['valid'=>true, 'data'=>$data, "msg"=>$msg]);
	}

	public static function error($error)
	{
		echo json_encode(['valid'=>false, 'error'=>$error]);
	}
}
