<?php
class Shareable{
  
    // database connection and table name
    private $conn;
    private $table_name = "shareable";
  
    // object properties
    public $id;
    public $share_document_name;
    public $shareable_id;
    public $valid_till;
    public $employee_id;
    public $document_id;
    public $created_at;
  
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // read products with pagination
    public function count($keywords){
    

        $search_sql = '';
        if($keywords!=""){
            $search_sql = " AND u.document_name LIKE :search_keyword ";
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

    // read products with pagination
    public function readPaging($from_record_num, $records_per_page, $orderField, $orderDirection, $keywords, $filter_department_id, $filter_employee_id){
        $filter_department_sql = '';
        if($filter_department_id){
            $filter_department_sql = " AND u.department_id= :department_id ";
        }

        $filter_employee_sql = '';
        if($filter_employee_id){
            $filter_employee_sql = " AND u.employee_id= :employee_id ";
        }

        
        $search_sql = '';
        if($keywords!=""){
            $search_sql = " AND u.document_name LIKE :search_keyword ";
        }
        // select query
        $query = "SELECT
                     * 
                    FROM
                    " . $this->table_name . " u
                     
                     WHERE 1=1   ".$search_sql." ".$filter_department_sql." ".$filter_employee_sql."  
                ORDER BY ".$orderField." ".$orderDirection." 
                LIMIT :from_record_num, :records_per_page";
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
        // bind variable values
        $stmt->bindParam(':from_record_num', $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
        
        if($filter_department_id){
            $stmt->bindParam(':department_id', $filter_department_id);
        }

        if($filter_employee_id){
            $stmt->bindParam(':employee_id', $filter_employee_id);
        }

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
                share_document_name=:share_document_name, shareable_id=:shareable_id, valid_till=:valid_till, 
                employee_id=:employee_id, created_at=:created_at, document_id=:document_id";
      
        // prepare query
        $stmt = $this->conn->prepare($query);
      
        // sanitize
        $this->share_document_name=htmlspecialchars(strip_tags($this->share_document_name));
        $this->shareable_id=htmlspecialchars(strip_tags($this->shareable_id));
        $this->valid_till=htmlspecialchars(strip_tags($this->valid_till));
        $this->employee_id=htmlspecialchars(strip_tags($this->employee_id));
        $this->created_at=htmlspecialchars(strip_tags($this->created_at));
        $this->document_id=htmlspecialchars(strip_tags($this->document_id));

      
        // bind values
        $stmt->bindParam(":share_document_name", $this->share_document_name);
        $stmt->bindParam(":shareable_id", $this->shareable_id);
        $stmt->bindParam(":valid_till", $this->valid_till);
        $stmt->bindParam(":employee_id", $this->employee_id);
        $stmt->bindParam(":document_id", $this->document_id);

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
                    document_name=:document_name,  
                    employee_id=:employee_id,  
                    department_id=:department_id WHERE document_id=:document_id";
      
        // prepare query
        $stmt = $this->conn->prepare($query);
      
        // sanitize
        $this->document_name=htmlspecialchars(strip_tags($this->document_name));
        $this->employee_id=htmlspecialchars(strip_tags($this->employee_id));
        $this->department_id=htmlspecialchars(strip_tags($this->department_id));

        $this->document_id=htmlspecialchars(strip_tags($this->document_id));

        
      
        // bind values
        $stmt->bindParam(":document_name", $this->document_name);
        $stmt->bindParam(":employee_id", $this->employee_id);
        $stmt->bindParam(":department_id", $this->department_id);
        $stmt->bindParam(":document_id", $this->document_id);

        
        // execute query
        if($stmt->execute()){
            return true;
        }
        return false;
          
    }
    
    function verifyShareable(){
  
        // query to read single record
        $query = "SELECT
                     * 
                FROM
                    " . $this->table_name . " s
                    JOIN documents d on d.document_id=s.document_id
                WHERE
                    s.shareable_id = ?
                LIMIT
                    0,1";
      
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
      
        // bind id of product to be updated
        $stmt->bindParam(1, $this->shareable_id);
      
        // execute query
        $stmt->execute();
      
        // get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // set values to object properties
        
        return $row;
    }
    // used when filling up the update product form
function readOne(){
  
    // query to read single record
    $query = "SELECT
                 * 
            FROM
                " . $this->table_name . " d
                
            WHERE
                d.document_id = ?
            LIMIT
                0,1";
  
    // prepare query statement
    $stmt = $this->conn->prepare( $query );
  
    // bind id of product to be updated
    $stmt->bindParam(1, $this->document_id);
  
    // execute query
    $stmt->execute();
  
    // get retrieved row
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // set values to object properties
    $this->document_name = $row['document_name'];
    $this->document_id = $row['document_id'];
    $this->created_at = $row['created_at'];
    return $row;
}

// delete 
function delete(){
  
    // delete query
    $query = "DELETE FROM " . $this->table_name . " WHERE document_id = ?";
  
    // prepare query
    $stmt = $this->conn->prepare($query);
  
    // sanitize
    $this->document_id=htmlspecialchars(strip_tags($this->document_id));
  
    // bind id of record to delete
    $stmt->bindParam(1, $this->document_id);
  
    // execute query
    if($stmt->execute()){
        return true;
    }
  
    return false;
}

  

    
}
?>