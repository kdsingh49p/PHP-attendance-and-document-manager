<?php
class Department{
  
    // database connection and table name
    private $conn;
    private $table_name = "departments";
  
    // object properties
    public $department_id;
    public $department_name;
    public $created_at;
  
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // read products with pagination
    public function count($keywords){
    

        $search_sql = '';
        if($keywords!=""){
            $search_sql = " AND u.department_name LIKE :search_keyword ";
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

    public function readPaging($from_record_num, $records_per_page, $orderField, $orderDirection, $keywords){
    
        $search_sql = '';
        if($keywords!=""){
            $search_sql = " AND u.department_name LIKE :search_keyword ";
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
                    department_name=:department_name,  created_at=:created_at";
      
        // prepare query
        $stmt = $this->conn->prepare($query);
      
        // sanitize
        $this->department_name=htmlspecialchars(strip_tags($this->department_name));
        $this->created_at=htmlspecialchars(strip_tags($this->created_at));
      
        // bind values
        $stmt->bindParam(":department_name", $this->department_name);
        $stmt->bindParam(":created_at", $this->created_at);
      
        // execute query
        if($stmt->execute()){
            $insertid = $this->conn->lastInsertId();
            $this->user_id = $insertid;
            return $insertid;
        }
        return false;
          
    }

    function update(){
  
        // query to insert record
        $query = "UPDATE " . $this->table_name . "
                SET
                    department_name=:department_name,  created_at=:created_at WHERE department_id=:department_id";
      
        // prepare query
        $stmt = $this->conn->prepare($query);
      
        // sanitize
        $this->department_name=htmlspecialchars(strip_tags($this->department_name));
        $this->created_at=htmlspecialchars(strip_tags($this->created_at));
        $this->department_id=htmlspecialchars(strip_tags($this->department_id));

        // bind values
        $stmt->bindParam(":department_name", $this->department_name);
        $stmt->bindParam(":department_id", $this->department_id);
        $stmt->bindParam(":created_at", $this->created_at);
      
        // execute query
        if($stmt->execute()){
            return true;
        }
        return false;
          
    }

    // used when filling up the update product form
function readOne(){
  
    // query to read single record
    $query = "SELECT
                d.department_name, d.created_at, d.department_id
            FROM
                " . $this->table_name . " d
                
            WHERE
                d.department_id = ?
            LIMIT
                0,1";
  
    // prepare query statement
    $stmt = $this->conn->prepare( $query );
  
    // bind id of product to be updated
    $stmt->bindParam(1, $this->department_id);
  
    // execute query
    $stmt->execute();
  
    // get retrieved row
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
  
    // set values to object properties
    $this->department_name = $row['department_name'];
    $this->department_id = $row['department_id'];
    $this->created_at = $row['created_at'];

}

// delete the product
function delete(){
  
    // delete query
    $query = "DELETE FROM " . $this->table_name . " WHERE department_id = ?";
  
    // prepare query
    $stmt = $this->conn->prepare($query);
  
    // sanitize
    $this->department_id=htmlspecialchars(strip_tags($this->department_id));
  
    // bind id of record to delete
    $stmt->bindParam(1, $this->department_id);
  
    // execute query
    if($stmt->execute()){
        return true;
    }
  
    return false;
}

    // used when filling up the update product form
    function checkExists(){
    
        // query to read single record
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE
                    department_name = ?
                LIMIT
                    0,1";
    
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
        // bind id of product to be updated
        $stmt->bindParam(1, $this->department_name);
    
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

    
}
?>