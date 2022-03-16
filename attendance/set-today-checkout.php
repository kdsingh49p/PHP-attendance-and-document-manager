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
    $attendance->checkout_time = date("H:i:s");
    $attendance->user_id = $is_auth['user_id'];

    if($attendance->getTodayAttendance()) {
        $time1 = new DateTime($attendance->getTodayAttendance()['date']." ".$attendance->getTodayAttendance()['checkin_time']);
        $time2 = new DateTime(date("Y-m-d H:i:s"));
        $timediff = $time1->diff($time2);
    
        $attendance->total_hours_worked = $timediff->h.":".$timediff->i.":".$timediff->s;
        $attendance->total_hours_worked2 = $timediff->h." hours, ".$timediff->i." minutes";
    

        $attendance->id = $attendance->getTodayAttendance()['id'];
        $set_checkin_attendance = $attendance->setCheckout();
        if($set_checkin_attendance){
            $get_today_attendance = $attendance->getTodayAttendance();
            http_response_code(200);
            echo json_encode(array("data" => $get_today_attendance, "message" => "Checkout Set Successfully", "status" => "success"));
            return false;
        }else{
            echo json_encode(array("data" => [], "message" => ["No Data Found"], "status" => "no_data_found"));
            return false;
        }
    }else{
        echo json_encode(array("data" => [], "message" => ["Checkin Not Found"], "status" => "fail"));
        return false;
    }
    
  }

   

?>