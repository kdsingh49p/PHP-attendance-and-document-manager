<?php
class Attendance{
  
    // database connection and table name
    private $conn;
    private $table_name = "attendance";
  
    // object properties
    public $id;

    public $user_id;
    public $date;
    public $checkin_time;
    public $checkout_time;
    public $total_hours_worked;
    public $total_hours_worked2;
    public $created_at;
  
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    function read($from_date, $to_date){
    
        // select all query
        $query = "SELECT u.first_name, u.last_name, a.date, a.checkin_time, a.checkout_time, a.total_hours_worked, a.total_hours_worked2, a.user_id FROM
                    " . $this->table_name . " a JOIN
                        users u
                            ON u.user_id = a.user_id WHERE a.date BETWEEN :from_date AND :to_date ;";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // sanitize
        $from_date=htmlspecialchars(strip_tags($from_date));
        $to_date=htmlspecialchars(strip_tags($to_date));
    
        // bind values
        $stmt->bindParam(":from_date", $from_date);
        $stmt->bindParam(":to_date", $to_date);

        // execute query
        $stmt->execute();
    
        return $stmt;
    }
    
    function setCheckout(){
  
        // query to insert record
        $query = "UPDATE " . $this->table_name . "
                SET checkout_time=:checkout_time, total_hours_worked=:total_hours_worked,total_hours_worked2=:total_hours_worked2 WHERE id=:id";

        // prepare query
        $stmt = $this->conn->prepare($query);
      
        // sanitize
        $this->checkout_time=htmlspecialchars(strip_tags($this->checkout_time));
        $this->total_hours_worked=htmlspecialchars(strip_tags($this->total_hours_worked));
        $this->total_hours_worked2=htmlspecialchars(strip_tags($this->total_hours_worked2));

        
        $this->id=htmlspecialchars(strip_tags($this->id));
      
        // bind values
        $stmt->bindParam(":checkout_time", $this->checkout_time);
        $stmt->bindParam(":total_hours_worked", $this->total_hours_worked);
        $stmt->bindParam(":total_hours_worked2", $this->total_hours_worked2);

        $stmt->bindParam(":id", $this->id);
        $params = $this->id."=".$this->checkout_time."=".$this->total_hours_worked;
        // execute query
        if($stmt->execute()){
            return true;
        }
      
        return false;
          
    }
    function setCheckin(){
  
        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    date=:date, user_id=:user_id, checkin_time=:checkin_time, created_at=:created_at";
      
        // prepare query
        $stmt = $this->conn->prepare($query);
      
        // sanitize
        $this->date=htmlspecialchars(strip_tags($this->date));
        $this->user_id=htmlspecialchars(strip_tags($this->user_id));
        $this->checkin_time=htmlspecialchars(strip_tags($this->checkin_time));
        $this->created_at=htmlspecialchars(strip_tags($this->created_at));
      
        // bind values
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":checkin_time", $this->checkin_time);
        $stmt->bindParam(":created_at", $this->created_at);
      
        // execute query
        if($stmt->execute()){
            $insertid = $this->conn->lastInsertId();
            $this->id = $insertid;
            return $insertid;

        }
      
        return false;
          
    }
    

    // used when filling up the update product form
    function getTodayAttendance(){
    
        // query to read single record
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE
                    user_id = :user_id AND date = :date
                 ORDER BY id DESC 
                LIMIT
                    0,1";
    
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
          // sanitize
         $this->user_id=htmlspecialchars(strip_tags($this->user_id));
         $this->date=htmlspecialchars(strip_tags($this->date));
 
         // bind new values
         $stmt->bindParam(':user_id', $this->user_id);
         $stmt->bindParam(':date', $this->date);
    
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

    // used for paging attendance
    public function count($date, $filter_user_id=NULL, $keywords){

        $user_filter_sql = '';
        if($filter_user_id){
            $user_filter_sql = " AND u.user_id= :user_id ";
        }

        $search_sql = '';
        if($keywords!=""){
            $search_sql = " AND (CONCAT( u.first_name,  ' ', u.last_name ) LIKE :search_keyword OR u.first_name LIKE :search_keyword OR u.last_name LIKE :search_keyword ) ";
        }
         
        // select query
        $query = "SELECT 
                    COUNT(*) as total_rows 
                 FROM
                    " . $this->table_name . " a
                     JOIN
                        users u
                            ON u.user_id = a.user_id
                             WHERE date = :date ".$user_filter_sql." ".$search_sql;
    
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
    
        $date=htmlspecialchars(strip_tags($date));
        // bind variable values
        $stmt->bindParam(':date', $date);
        
        if($filter_user_id){
            $stmt->bindParam(':user_id', $filter_user_id);
        }

        if($keywords){
            $keywords=htmlspecialchars(strip_tags($keywords));
            $keywords = "%{$keywords}%";
            $stmt->bindParam(':search_keyword', $keywords);
        }
    
        // execute query
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $row['total_rows'];
    }

    public function readPaging($from_record_num, $records_per_page, $date, $filter_user_id=NULL, $orderField, $orderDirection, $keywords){
        
        $user_filter_sql = '';
        if($filter_user_id){
            $user_filter_sql = " AND u.user_id= :user_id ";
        }

        $search_sql = '';
        if($keywords!=""){
            $search_sql = " AND (CONCAT( u.first_name,  ' ', u.last_name ) LIKE :search_keyword OR u.first_name LIKE :search_keyword OR u.last_name LIKE :search_keyword ) ";
        }
         
        // select query
        $query = "SELECT
                    u.first_name, u.last_name, a.date, a.checkin_time, a.checkout_time, a.total_hours_worked, a.total_hours_worked2, a.user_id
                 FROM
                    " . $this->table_name . " a
                     JOIN
                        users u
                            ON u.user_id = a.user_id
                             WHERE date = :date ".$user_filter_sql." ".$search_sql." 
                ORDER BY ".$orderField." ".$orderDirection." 
                LIMIT :from_record_num, :records_per_page";
    
        // prepare query statement
        $stmt = $this->conn->prepare( $query );
        
        $date=htmlspecialchars(strip_tags($date));
        // bind variable values
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':from_record_num', $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
        
        if($filter_user_id){
            $stmt->bindParam(':user_id', $filter_user_id);
        }

        if($keywords){
            $keywords=htmlspecialchars(strip_tags($keywords));
            $keywords = "%{$keywords}%";
            // echo $keywords;
            $stmt->bindParam(':search_keyword', $keywords);
        }

        // execute query
        $stmt->execute();
    
        // return values from database
        return $stmt;
    }

    

}
?>