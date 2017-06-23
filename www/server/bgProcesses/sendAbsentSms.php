<?php
require_once '../autoloader.php';

$phone =        $argv[1]; 
$sms =          $argv[2];
$studentId =    $argv[3];
$teacherId =    $argv[4];

$url="http://198.24.149.4/API/pushsms.aspx?loginID=dkborana021&password=webflaxmoderndefence@123&mobile=".$phone."&text=".$sms."&senderid=SCHOOL&route_id=2&Unicode=1";
$url = str_replace(" ", '%20', $url);

$ch = curl_init();
$headers = array(
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8, image/gif, image/x-bitmap, image/jpeg, image/pjpeg',
    'Connection: Keep-Alive',
    'Content-type: application/x-www-form-urlencoded;charset=UTF-8'
);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Timeout in seconds
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$res = curl_exec($ch);

$response =  json_decode($res);






$conn = get_db_conn();

$s = $conn->students->findOne(array("student_id"=>$studentId));
$s = format_stuent($s);
$s['date'] = getc_date();
$s['sms'] = $sms;
$s['status'] = $response->LoginStatus;
$s['teacher_id'] = $teacherId;


$conn->absent->insert($s);

function format_stuent($student)
{
    
            unset($student['name']);
            unset($student['rollNo']);
            unset($student['_id']);
            unset($student['student_img']);
            unset($student['gender']);
            unset($student['dob']);
            unset($student['village']);
            unset($student['address']);

            return $student;

}

function getc_date()
{
    date_default_timezone_set("Asia/Kolkata");
    $t=time();
    return(date("d-m-Y",$t));
}

function get_server_conn()
{
    try{
        $username = "appUser";
        $password = "useratriddhi123";
        $m = new MongoClient("mongodb://$username:$password@35.154.180.155:27017/admin");
        return $m;
    }
    catch(Exception $e){
      die($e->getMessage());
    }
}

function get_db_conn(){

    $m = get_server_conn();
    $db_conn = $m->modernDefenceStaging;

    return($db_conn);
}

?>
