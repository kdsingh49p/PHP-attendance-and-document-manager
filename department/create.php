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
  
// instantiate object
include_once '../objects/user.php';
include_once '../objects/department.php';

  
$database = new Database();
$db = $database->getConnection();
  
$user = new User($db);
$department = new Department($db);
  
// get posted data
$data = json_decode(file_get_contents("php://input"));
  
// make sure data is not empty
if(
    !empty($data->department_name) 
){
    
    if(isset($data->department_id) && !empty(isset($data->department_id))){
        $department->department_id = $data->department_id;
    }
    $department->department_name = $data->department_name;
    $department->created_at = date('Y-m-d');
    
    if(isset($data->department_id) && !empty(isset($data->department_id))){
        if($department->update()){

            // set response code - 201 created
            http_response_code(201);
            
            // tell the user
            echo json_encode(array("message" => "Department updated successfully.", "data" => $department, "status" => "success"));   
        }else{
  
            // set response code - 503 service unavailable
            http_response_code(503);
      
            // tell the user
            echo json_encode(array("message" => ["Unable to update department."], "data" => [], "status" => "fail"));
        }

    } else {
        if($department->checkExists()){
            http_response_code(200);
            // tell the user
            echo json_encode(array("data" => [], "message" => ["Department already exists."], "status" => "fail"));
            return false;
        }
        $new_id = $department->create();
        if($new_id){

        // set response code - 201 created
        http_response_code(201);
            
        // tell the user
        echo json_encode(array("message" => "Department created successfully.", "data" => $department, "status" => "success"));   
        }else{
  
            // set response code - 503 service unavailable
            http_response_code(503);
      
            // tell the user
            echo json_encode(array("message" => ["Unable to create department."], "data" => [], "status" => "fail"));
        }
    }

    
}
  
else{
  
    // set response code - 400 bad request
    http_response_code(400);
  
    // tell the user
    echo json_encode(array("message" => ["Unable to create department. Data is incomplete."], "data" => []));
}
?>