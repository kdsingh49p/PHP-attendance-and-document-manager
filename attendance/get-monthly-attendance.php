<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

header("Access-Control-Allow-Methods: 'POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

// instantiate database and user object
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$user = new User($db);
$attendance = new Attendance($db);


   // check if more than 0 record found
$data = json_decode(file_get_contents("php://input"));
$attendance_arr=array();
$attendance_arr["records"]=array();
$attendance_arr["paging"]=array();
$keywords = isset($data->search) ? $data->search : "";
$months = [
    "01" => "January",
    "02" => "February",
    "03" => "March",
    "04" => "April",
    "05" => "May",
    "06" => "June",
    "07" => "July",
    "08" => "August",
    "09" => "September",
    "10" => "October",
    "11" => "November",
    "12" => "December",
];   
   

    $monthName = '';
    $year = '';
    if (!empty($data->month) && !empty($data->year)){
        $from_date = date('Y-m-d', strtotime($data->year.'-'.$data->month.'-01'));
        $to_date = date('Y-m-d', strtotime($data->year.'-'.$data->month.'-31'));
        $monthName = $months[$data->month];
        $year = $data->year;
    }else{
        $from_date = date('Y-m').'-01';
        $to_date = date('Y-m').'-31';
        $monthName = $months[date('m')];
        $year = date('Y');
    }

    $order_field        = 'first_name';
    $order_direction    = 'ASC';
    if(isset($data->orderBy)){
        $data_array         = (array) $data->orderBy;
        if(count($data_array) > 0){
            foreach($data->orderBy as $orderByCol => $orderByStatus){
                $order_field        = $orderByCol;
                $order_direction    = (($orderByStatus=='true' || $orderByStatus==true) ? 'ASC' : 'DESC');
            }
        }
    }

    $stmt = $attendance->read($from_date, $to_date);
    $stmt_user = $user->readPaging($from_record_num, $records_per_page, $order_field, $order_direction, $keywords);

    $num = $stmt->rowCount();
    $num_user = $stmt_user->rowCount();
 

    $is_auth = $user->isAuthenticate();

    if($is_auth && $is_auth['user_type']=='admin') {
       
        if($num_user > 0){
            $attendance = $stmt->fetchAll();
            while ($row_user = $stmt_user->fetch(PDO::FETCH_ASSOC)){
                // if($row_user['user_type']=='user'){
                    $user_item=array(
                        "user_id" => $row_user['user_id'],
                        "last_name" => $row_user['last_name'],
                        "first_name" => $row_user['first_name'],
                        "email" => $row_user['email'],
                        "user_type" => $row_user['user_type'],
                        "created_at" => $row_user['created_at'],
                        'from_date' => $from_date,
                        'to_date' =>$to_date,
                        "monthName" => $monthName,
                        "year" => $year
                    );
                    $total_hours_worked_month = [];
 
                    if($num > 0){
                        foreach ($attendance as $key => $row) {
                            if($row_user['user_id']==$row['user_id']){
                                array_push($total_hours_worked_month, $row['total_hours_worked']);
                            }
                        }

                            
                    }
                    $total_h_month = 0;
                    if(count($total_hours_worked_month) > 0){
                        $total_h_month = sumAllTime($total_hours_worked_month);
                    }
                    $user_item["total_hours_worked"] = $total_h_month;
                    array_push($attendance_arr["records"], $user_item);

                // }
            }
        }

         // include paging
         $row__user_count = $user->count($keywords);

         $page_url="{$home_url}product/read_paging.php?";
         $paging=$utilities->getPaging($page, $row__user_count, $records_per_page, $page_url);
         $attendance_arr["paging"]=$paging;

        $resp["message"] = "Data fetched successfully";
        http_response_code(200);
 
    }else{
        http_response_code(401);
        $resp["message"] = "Unauthenticate";        
    }

echo json_encode($attendance_arr);

function sumAllTime($time){
    
    $sum = strtotime('00:00:00');
    
    $totaltime = 0;
    
    foreach( $time as $element ) {
        
        // Converting the time into seconds
        $timeinsec = strtotime($element) - $sum;
        
        // Sum the time with previous value
        $totaltime = $totaltime + $timeinsec;
    }
    
    // Totaltime is the summation of all
    // time in seconds
    
    // Hours is obtained by dividing
    // totaltime with 3600
    $h = intval($totaltime / 3600);
    
    $totaltime = $totaltime - ($h * 3600);
    
    // Minutes is obtained by dividing
    // remaining total time with 60
    $m = intval($totaltime / 60);
    
    // Remaining value is seconds
    $s = $totaltime - ($m * 60);
    
    // Printing the result
    return ("$h hours, $m minutes");
}
