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
include_once '../objects/document.php';
  
$database = new Database();
$db = $database->getConnection();
  
$user = new User($db);
$document = new Document($db);
  
$data = json_decode(file_get_contents("php://input"));
  
if(
    !empty($data->document_name) && !empty($data->employee_id)  && !empty($data->document_id) 
){
    

    $document->document_name = $data->document_name;
    $document->employee_id = $data->employee_id;
    $document->department_id = $data->department_id;
    $document->document_id = $data->document_id;

    if($document->update()){

        // set response code - 201 created
        http_response_code(201);
        $document = $document->readOne();
        // tell the user
        echo json_encode(array("message" => "Document updated successfully.", "data" => $document, "status" => "success"));   
    }else{

        // set response code - 503 service unavailable
        http_response_code(503);
    
        // tell the user
        echo json_encode(array("message" => ["Unable to update Document."], "data" => [], "status" => "fail"));
    }

}
  
// tell the user data is incomplete
else{
  
    // set response code - 400 bad request
    http_response_code(400);
  
    // tell the user
    echo json_encode(array("message" => ["Unable to create document. Data is incomplete."], "data" => []));
}
?>