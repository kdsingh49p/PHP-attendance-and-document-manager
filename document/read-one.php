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
  
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$department = new Department($db);
$user = new User($db);
$is_auth = $user->isAuthenticate();
$data = json_decode(file_get_contents("php://input"));
// set ID property of record to read
$department->department_id = isset($data->id) ? $data->id : null;

 
if($is_auth) {

    // check if more than 0 record found
    if($department->department_id){
    
        // products array
        $department->readOne();

        $department_arr = array(
            "department_id" =>  $department->department_id,
            "department_name" => $department->department_name,
            "created_at" => $department->created_at,
      
        );
    
    
        // include paging
        // set response code - 200 OK
        http_response_code(200);
        $department_arr["message"] ="department found successfully.";
        $department_arr["data"] =$department_arr;
    }
    
    else{
    
        // set response code - 404 Not found
        http_response_code(200);
        $department_arr["message"] ="No department found.";
    }
}else{
    // set response code - 401 Not found
    http_response_code(401);
    
    $department_arr["message"] ="user un atendicated.";
}
echo json_encode($department_arr);

?>