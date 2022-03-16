<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: 'POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


// include database and object files
include_once '../config/database.php';
include_once '../objects/user.php';
  
// instantiate database and user object
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$user = new User($db);
  

// query users
$stmt = $user->read();
$num = $stmt->rowCount();
  
$resp  = [
    'data'=> [],
    'message'=> ""
];
if($num > 0){
  
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        extract($row);
        $user_item=array(
            "user_id" => $user_id,
            "last_name" => $last_name,
            "first_name" => $first_name,
            "email" => $email,
            "api_token" => $api_token,
            "user_type" => $user_type,
            "created_at" => $created_at
        );
  
        array_push($resp["data"], $user_item);
    }
  
    $resp["message"] = "Data Fetched Successfully";
   
} 
http_response_code(200);
  
echo json_encode($resp);