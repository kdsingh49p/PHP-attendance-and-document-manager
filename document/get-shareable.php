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
include_once '../objects/shareable.php';
include_once '../objects/user.php';

date_default_timezone_set("Africa/Casablanca");


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
$shareable = new Shareable($db);
$data = json_decode(file_get_contents("php://input"));
// set ID property of record to read
$shareable->shareable_id = isset($data->id) ? $data->id : null;

 
    // check if more than 0 record found
    if($shareable->shareable_id){
    
        // products array
        $found_data = $shareable->verifyShareable();
        if($found_data['valid_till'] !="" && $found_data['valid_till'] != NULL){
            if($found_data['valid_till'] < date('Y-m-d')){
                http_response_code(200);
                $shareable_arr["message"] ="shareable date expire.";
                $shareable_arr["data"] = [];
                $shareable_arr["status"] = 'fail';
                echo json_encode($shareable_arr);
                exit;
            }
        }
    
        // include paging
        // set response code - 200 OK
        http_response_code(200);
        $shareable_arr["message"] ="shareable found successfully.";
        $shareable_arr["data"] = $found_data;
        $shareable_arr["status"] = 'success';

    }
    
    else{
    
        // set response code - 404 Not found
        http_response_code(200);
    
        // tell the user attendance does not exist
        $shareable_arr["status"] = 'fail';
        $shareable_arr["message"] ="No department found.";

    }

echo json_encode($shareable_arr);

?>