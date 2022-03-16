<?php
class User{
  
    // database connection and table name
    private $conn;
    private $table_name = "users";
  
    // object properties
    public $user_id;
    public $last_name;
    public $first_name;
    public $email;
    public $password;
    public $api_token;
    public $is_verified;
    public $created_at;
  
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // read products with pagination
    public function count($keywords){
    

        $search_sql = '';
        if($keywords!=""){
            $search_sql = " AND (CONCAT( u.first_name,  ' ', u.last_name ) LIKE :search_keyword OR u.first_name LIKE :search_keyword OR u.last_name LIKE :search_keyword ) ";
        }
            
        // select query
        $query = "SELECT
                     COUNT(*) as total_rows  
                    FROM
                    " . $this->table_name . " u
                      
                     WHERE 1=1   ".$search_sql;
    
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
    
        // bind variable values
        
        if($keywords){
            // sanitize
            $keywords=htmlspecialchars(strip_tags($keywords));
            $keywords = "%{$keywords}%";
    
            $stmt->bindParam(':search_keyword', $keywords);

        }

        // execute query
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $row['total_rows'];
        // return values from database
    }

    // read  with pagination
    public function readPaging($from_record_num, $records_per_page, $orderField, $orderDirection, $keywords){
    

        $search_sql = '';
        if($keywords!=""){
            $search_sql = " AND (CONCAT( u.first_name,  ' ', u.last_name ) LIKE :search_keyword OR u.first_name LIKE :search_keyword OR u.last_name LIKE :search_keyword ) ";
        }
        // select query
        $query = "SELECT
                     * 
                    FROM
                    " . $this->table_name . " u
                     
                     WHERE 1=1   ".$search_sql." 
                ORDER BY ".$orderField." ".$orderDirection." 
                LIMIT :from_record_num, :records_per_page";
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
        // bind variable values
        $stmt->bindParam(':from_record_num', $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
        
        if($keywords){
            // sanitize
            $keywords=htmlspecialchars(strip_tags($keywords));
            $keywords = "%{$keywords}%";
            $stmt->bindParam(':search_keyword', $keywords);
        }

        // execute query
        $stmt->execute();
    
        // return values from database
        return $stmt;
    }
    // read products
    function read(){
    
        // select all query
        $query = "SELECT * FROM
                    " . $this->table_name . "";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);
    
        // execute query
        $stmt->execute();
    
        return $stmt;
    }
    function create(){
  
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    last_name=:last_name, first_name=:first_name, email=:email, password=:password, api_token=:api_token, created_at=:created_at";
      
        // prepare query
        $stmt = $this->conn->prepare($query);
      
        // sanitize
        $this->last_name=htmlspecialchars(strip_tags($this->last_name));
        $this->first_name=htmlspecialchars(strip_tags($this->first_name));

        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->password=htmlspecialchars(strip_tags($this->password));
        $this->api_token=htmlspecialchars(strip_tags($this->api_token));
        $this->created_at=htmlspecialchars(strip_tags($this->created_at));
      
        // bind values
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":email", $this->email);

        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":api_token", $this->api_token);
        $stmt->bindParam(":created_at", $this->created_at);
      
        // execute query
        if($stmt->execute()){
            $insertid = $this->conn->lastInsertId();
            $this->user_id = $insertid;
            return $insertid;

        }
      
        return false;
          
    }
    // used when filling up the update  form
    function get_user_by_token(){
        
        // query to read single record
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE
                    api_token = ? 
                LIMIT
                    0,1";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind id of product to be updated
        $stmt->bindParam(1, $this->api_token);

        // execute query
        $stmt->execute();

        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // set values to object properties
        if($row){
            return $row;
        }else{
            return false;
        }
    }

    // used when filling up the update product form
    function get_user(){
    
        // query to read single record
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE
                    user_id = ? AND is_verified=0 
                LIMIT
                    0,1";
    
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
        // bind id of product to be updated
        $stmt->bindParam(1, $this->user_id);
    
        // execute query
        $stmt->execute();
    
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // set values to object properties
       if($row){
           return $row;
       }else{
           return false;
       }
    }

    function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    /**
     * get access token from header
     * */
    function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    function isAuthenticate(){
        $token = $this->getBearerToken();
        if($token){
            $this->api_token = $token;
            return $this->get_user_by_token();
            
        }
        return false;
    }
    // used when filling up the update product form
    function checkLogin(){
    
        // query to read single record
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE
                    email = :email AND password = :password  AND is_verified=1 
                LIMIT
                    0,1";
    
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
          // sanitize
         $this->email=htmlspecialchars(strip_tags($this->email));
         $this->password=htmlspecialchars(strip_tags($this->password));
 
         // bind new values
         $stmt->bindParam(':email', $this->email);
         $stmt->bindParam(':password', $this->password);
    
        // execute query
        $stmt->execute();
    
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // set values to object properties
       if($row){
           return $row;
       }else{
           return false;
       }
    }

    // used when filling up the update product form
    function checkUserExists(){
    
        // query to read single record
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE
                    email = ?
                LIMIT
                    0,1";
    
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
        // bind id of product to be updated
        $stmt->bindParam(1, $this->email);
    
        // execute query
        $stmt->execute();
    
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // set values to object properties
       if($row){
           return true;
       }else{
           return false;
       }
    }
    // update the product
function setUserVerify(){
  
    // update query
    $query = "UPDATE
                " . $this->table_name . "
            SET
                is_verified = :is_verified
            WHERE
                user_id = :user_id";
  
    // prepare query statement
    $stmt = $this->conn->prepare($query);
  
    // sanitize
    $this->is_verified=htmlspecialchars(strip_tags($this->is_verified));
    $this->user_id=htmlspecialchars(strip_tags($this->user_id));
  
    // bind new values
    $stmt->bindParam(':user_id', $this->user_id);
    $stmt->bindParam(':is_verified', $this->is_verified);
  
    // execute the query
    if($stmt->execute()){
        return true;
    }
  
    return false;
}
}
?>