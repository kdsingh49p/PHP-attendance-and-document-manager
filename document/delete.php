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
include_once '../objects/document.php';
include_once '../objects/user.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // The request is using the POST method
    return true;
    exit;
}
// utilities
$utilities = new Utilities();
  
// instantiate database
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$document = new Document($db);
$user = new User($db);
$is_auth = $user->isAuthenticate();
$data = json_decode(file_get_contents("php://input"));
// set ID property of record to read
$document->document_id = isset($data->id) ? $data->id : null;

 
if($is_auth) {
    $document_read = $document->readOne();
    // check if more than 0 record found
    if($document->delete()){

        unlink('../uploads/'.$document_read['document_path']);
        // include paging
        // set response code - 200 OK
        http_response_code(200);
        $document_arr["message"] ="Document deleted successfully.";
    }
    
    else{
    
        // set response code - 404 Not found
        http_response_code(200);
    
        // tell the user attendance does not exist

        $document_arr["message"] ="No Document found.";

    }
}else{
    // set response code - 401 Not found
    http_response_code(401);
    
    // tell the user attendance does not exist
    $document_arr["message"] ="user un atendicated.";
}
echo json_encode($document_arr);

?>