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
  
$product = new User($db);
  
// get posted data
$data = json_decode(file_get_contents("php://input"));
  
// make sure data is not empty
if(
    !empty($data->last_name) &&
    !empty($data->first_name) &&
    !empty($data->email) &&
    !empty($data->password)
){
  
    // set  property values
    $product->last_name = $data->last_name;
    $product->first_name = $data->first_name;
    $product->email = $data->email;
    $product->password = md5($data->password);
    $product->api_token = md5(uniqid($data->email, true));
    $product->created_at = date('Y-m-d');
    
    if($product->checkUserExists()){
        http_response_code(200);
        // tell the user
        echo json_encode(array("data" => [], "message" => ["User already exists."], "status" => "fail"));
        return false;
    }
    $product_id = $product->create();
    if($product_id){

        $decode_id = base64_encode($product_id);

        $message = "
        <p>Hi,<p> 

        <br>
        <p>Click on this <a style='color:blue;' href='http://localhost:3000/verify_email/".$decode_id."'>link</a> to verify your email account.</p>

        <br>
        <p>From,</p>
        <p>Attendance Management System</p>
        ";

        $headers  = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Reply-To: Attendance Management <kuldeepmailtest@gmail.com\r\n";
        $headers .= "Return-Path: Attendance Management <kuldeepmailtest@gmail.com>\r\n";
        $headers .= "From: Attendance Management <kuldeepmailtest@gmail.com>\r\n";
        $headers .= "X-Priority: 3\r\n";
        $headers .= "X-Mailer: PHP". phpversion() ."\r\n";        
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

          mail($data->email, "Email Verification | Attendance Management", $message, $headers);

        // set response code - 201 created
        http_response_code(201);
        
        echo json_encode(array("message" => "User created successfully. $decode_id", "data" => $product, "status" => "success"));
    }
  
    else{
  
        // set response code - 503 service unavailable
        http_response_code(503);
  
        // tell the user
        echo json_encode(array("message" => ["Unable to create user."], "data" => [], "status" => "fail"));
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