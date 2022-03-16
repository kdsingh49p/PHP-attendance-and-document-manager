<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: 'POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // The request is using the POST method
    return true;
    exit;
}


// get database connection
include_once '../config/database.php';

  
// instantiate user object
include_once '../objects/attendance.php';
include_once '../objects/user.php';

  
$database = new Database();
$db = $database->getConnection();

$user = new User($db);
  
$attendance = new Attendance($db);
  
// get posted data
$data = json_decode(file_get_contents("php://input"));
  
$is_auth = $user->isAuthenticate();
  if($is_auth) {
    date_default_timezone_set("Africa/Casablanca");
    $attendance->date = date('Y-m-d');
    $attendance->user_id = $is_auth['user_id'];

    $get_today_attendance = $attendance->getTodayAttendance();
    if($get_today_attendance){
        http_response_code(200);
        echo json_encode(array("data" => $get_today_attendance, "message" => "User Login Successfully", "status" => "success"));
        return false;
    }else{
        echo json_encode(array("data" => [], "message" => ["No Data Found"], "status" => "no_data_found"));
        return false;
    }
  }

 

     
    
   

?>