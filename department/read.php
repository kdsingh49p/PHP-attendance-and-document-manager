<?php
// required headers
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
include_once '../objects/department.php';
include_once '../objects/user.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // The request is using the POST method
    return true;
    exit;
}
// utilities
$utilities = new Utilities();
  
// instantiate database and Department object
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$department = new Department($db);
$user = new User($db);
$is_auth = $user->isAuthenticate();
$data = json_decode(file_get_contents("php://input"));
$department_arr=array();
$department_arr["records"]=array();
$department_arr["paging"]=array();
$keywords = isset($data->search) ? $data->search : "";

if($is_auth) {
    $order_field        = 'department_id';
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


    $stmt = $department->readPaging($from_record_num, $records_per_page, $order_field, $order_direction, $keywords);
    $num = $stmt->rowCount();
    
    // check if more than 0 record found
    if($num>0){
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
    
            $department_item=array(
                "department_id" => $department_id,
                "department_name" => $department_name,
                "created_at" => $created_at 
            );
    
            array_push($department_arr["records"], $department_item);
        }
    
    
        // include paging
        $total_rows=$department->count($keywords);
        $page_url="{$home_url}product/read_paging.php?";
        $paging=$utilities->getPaging($page, $total_rows, $records_per_page, $page_url);
        $department_arr["paging"]=$paging;
        $department_arr['orerby'] = $order_field;        
        // set response code - 200 OK
        http_response_code(200);
    
    }
    
    else{
    
        // set response code - 404 Not found
        http_response_code(200);
        $department_arr["message"] ="No department found.";

    }
}else{
    http_response_code(401);
    $department_arr["message"] ="user un atendicated.";
}
echo json_encode($department_arr);

?>