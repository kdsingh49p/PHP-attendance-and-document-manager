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
  
include_once '../objects/user.php';
  
$database = new Database();
$db = $database->getConnection();
  
$user = new User($db);
  
// get posted data
$data = json_decode(file_get_contents("php://input"));
  
// make sure data is not empty
if(
    !empty($data->email) &&
    !empty($data->password)

){
  
    $user->email = $data->email;
    $user->password = md5($data->password);

    $get_user = $user->checkLogin();
    if($get_user){
        http_response_code(200);
        // tell the user
        echo json_encode(array("data" => $get_user, "message" => "User Login Successfully", "status" => "success"));
        return false;
    }else{
        echo json_encode(array("data" => [], "message" => ["Invalid email and password or email not verified"], "status" => "fail"));
        return false;
    }
 
}
  
// tell the user data is incomplete
else{
  
    // set response code - 400 bad request
    http_response_code(400);
  
    // tell the user
    echo json_encode(array("message" => ["Unable to create user. Data is incomplete."], "data" => []));
}
?>