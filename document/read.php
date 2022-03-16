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
  
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$document = new Document($db);
$user = new User($db);
$is_auth = $user->isAuthenticate();
$data = json_decode(file_get_contents("php://input"));
$document_arr=array();
$document_arr["records"]=array();
$document_arr["paging"]=array();
$keywords = isset($data->search) ? $data->search : "";

if($is_auth) {


    $order_field        = 'document_id';
    $order_direction    = 'DESC';
    if(isset($data->orderBy)){
        $data_array         = (array) $data->orderBy;
        if(count($data_array) > 0){
            foreach($data->orderBy as $orderByCol => $orderByStatus){
                $order_field        = $orderByCol;
                $order_direction    = (($orderByStatus=='true' || $orderByStatus==true) ? 'ASC' : 'DESC');
            }
        }
    }
    $filter_department_id = NULL;
    if(isset($data->department_id) && !empty($data->department_id)){
        $filter_department_id = $data->department_id;
    }


    if(isset($data->employee_id) && !empty($data->employee_id)){
        $filter_employee_id = $data->employee_id;
    }else{
        $filter_employee_id = NULL;
    }


    $stmt = $document->readPaging($from_record_num, $records_per_page, $order_field, $order_direction, $keywords, $filter_department_id, $filter_employee_id);
    $num = $stmt->rowCount();
    
    if($num>0){
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
    
            $document_item=array(
                "document_id" => $document_id,
                "document_name" => $document_name,
                "document_path" => $document_path,
                "document_ext" => $document_ext,
                "employee_id" => $employee_id,
                "department_id" => $department_id,
                "created_at" => $created_at 
            );
    
            array_push($document_arr["records"], $document_item);
        }
    
    
        // include paging
        $total_rows=$document->count($keywords, $filter_department_id, $filter_employee_id);
        $page_url="{$home_url}product/read_paging.php?";
        $paging=$utilities->getPaging($page, $total_rows, $records_per_page, $page_url);
        $document_arr["paging"]=$paging;
        $document_arr['orerby'] = $order_field;        
        // set response code - 200 OK
        http_response_code(200);
    
    }
    
    else{
    
        // set response code - 404 Not found
        http_response_code(200);
    

        $document_arr["message"] ="No department found.";

    }
}else{
    // set response code - 401 Not found
    http_response_code(401);
    
    $document_arr["message"] ="user un atendicated.";
}
echo json_encode($document_arr);

?>