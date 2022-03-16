<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: 'POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// include database and object files
include_once '../config/core.php';
include_once '../shared/utilities.php';
include_once '../config/database.php';
include_once '../objects/attendance.php';
include_once '../objects/user.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // The request is using the POST method
    return true;
    exit;
}
// utilities
$utilities = new Utilities();
  
$database = new Database();
$db = $database->getConnection();
  
$attendance = new Attendance($db);
$user = new User($db);
$is_auth = $user->isAuthenticate();
$data = json_decode(file_get_contents("php://input"));
$attendance_arr=array();
$attendance_arr["records"]=array();
$attendance_arr["paging"]=array();
$keywords = isset($data->search) ? $data->search : "";

if($is_auth) {
    if(isset($data->date)){
        $date       = date('Y-m-d', strtotime(substr($data->date ,0,10)));
    }else{
        $date       = date('Y-m-d');
    }
    $order_field        = 'user_id';
    $order_direction    = 'DESC';
    if(isset($data->orderBy)){
        $data_array         = (array) $data->orderBy;
        if(count($data_array) > 0){
            foreach($data->orderBy as $orderByCol => $orderByStatus){
                $order_field        = $orderByCol;
                $order_direction    = (($orderByStatus=='true' || $orderByStatus==true) ? 'ASC' : 'DESC');
            }
        }
    }

    $filter_user_id = NULL;
    if($is_auth['user_type']=='user'){
        $filter_user_id = $is_auth['user_id'];
    }

    // query
    $stmt = $attendance->readPaging($from_record_num, $records_per_page, $date, $filter_user_id, $order_field, $order_direction, $keywords);
    $num = $stmt->rowCount();
    
    if($num>0){
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

            extract($row);
    
            $attendance_item=array(
                "user_id" => $user_id,
                "first_name" => $first_name,
                "last_name" => $last_name,
                "date" => $date,
                "date2" => date('M d, Y', strtotime($date)),
                "checkin_time" => $checkin_time,
                "checkout_time" => $checkout_time,
                "total_hours_worked" => $total_hours_worked,
                "total_hours_worked2" => $total_hours_worked2,
                "searc_date" => $date 
            );
    
            array_push($attendance_arr["records"], $attendance_item);
        }
    
    
        $total_rows=$attendance->count($date, $filter_user_id, $keywords);
        $page_url="{$home_url}product/read_paging.php?";
        $paging=$utilities->getPaging($page, $total_rows, $records_per_page, $page_url);
        $attendance_arr["paging"]=$paging;
        $attendance_arr['orerby'] = $order_field;        
        http_response_code(200);
    
    }
    
    else{
    
        // set response code - 404 Not found
        http_response_code(200);
    
        $attendance_arr["message"] ="No attendances found.";

    }
}else{
    http_response_code(401);
    
    $attendance_arr["message"] ="user un atendicated.";
}
echo json_encode($attendance_arr);

?>