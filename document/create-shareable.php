<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: 'POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // The request is using the POST method
    return true;
    exit;
}


// get database connection
include_once '../config/database.php';
  
include_once '../objects/user.php';
include_once '../objects/shareable.php';

  
$database = new Database();
$db = $database->getConnection();
  
$user = new User($db);
$shareable = new Shareable($db);
  
$data = json_decode(file_get_contents("php://input"));
$employee_id = "";
$is_auth = $user->isAuthenticate();


$employee_id = $is_auth['user_id'];

// make sure data is not empty
if(
    !empty($employee_id) && !empty($data->share_document_name) && !empty($data->shareable_id) && !empty($data->document_id)   
)
{
  
    $shareable->share_document_name = $data->share_document_name;
    $shareable->shareable_id = $data->shareable_id;
    $shareable->document_id = $data->document_id;
    $shareable->employee_id = $employee_id;
    $shareable->created_at = date('Y-m-d');
    $shareable->valid_till = NULL;
    if ($data->valid_till!=""){
        $shareable->valid_till = date('Y-m-d', strtotime($data->valid_till));
    }

    $new_id = $shareable->create();
    if($new_id){

        // set response code - 201 created
        http_response_code(201);
        
        // tell the user
        echo json_encode(array("message" => "Share link created successfully.", "data" => $shareable, "status" => "success"));   
    }else{

        // set response code - 503 service unavailable
        http_response_code(503);
    
        // tell the user
        echo json_encode(array("message" => ["Unable to create share link."], "data" => [], "status" => "fail"));
    }
}
  
// tell the user data is incomplete
else{
  
    // set response code - 400 bad request
    http_response_code(400);
  
    // tell the user
    echo json_encode(array("message" => ["Unable to create sharelink. Data is incomplete."], "data" => []));
}


?>