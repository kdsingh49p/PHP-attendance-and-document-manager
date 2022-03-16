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
include_once '../objects/document.php';

  
$database = new Database();
$db = $database->getConnection();
  
$user = new User($db);
$document = new Document($db);
  
// get posted data
$data = $_REQUEST;
$employee_id = "";
$is_auth = $user->isAuthenticate();

$employee_id = $is_auth['user_id'];

// make sure data is not empty
if(
    !empty($employee_id)  
){
    
    // set product property values
    $ext = explode(".", $_FILES["document"]["name"]);

    $document->department_id = $data['department_id'];
    
    $document->employee_id = $employee_id;

    $document->created_at = date('Y-m-d');


    $target_dir = "../uploads/";
    $newfilename = round(microtime(true)) . '.' . end($ext);

    $target_file = $target_dir . $newfilename;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["document"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
    }

    // Check if file already exists
    if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["document"]["size"] > 9000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" && $imageFileType != "pdf" && $imageFileType != "docx" && $imageFileType != "mp4" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
    if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {

        $document->document_name = htmlspecialchars( basename( $_FILES["document"]["name"]));
        $document->document_path = $newfilename;
        $document->document_ext = $imageFileType;
        
        $new_id = $document->create();
        if($new_id){

            // set response code - 201 created
            http_response_code(201);
            
            // tell the user
            echo json_encode(array("message" => "Document uploaded successfully.", "data" => $document, "status" => "success"));   
        }else{

            // set response code - 503 service unavailable
            http_response_code(503);
        
            // tell the user
            echo json_encode(array("message" => ["Unable to upload document."], "data" => [], "status" => "fail"));
        }
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
    }
    
    
}
  
// tell the user data is incomplete
else{
  
    // set response code - 400 bad request
    http_response_code(400);
  
    // tell the user
    echo json_encode(array("message" => ["Unable to upload document. Data is incomplete."], "data" => []));
}


?>