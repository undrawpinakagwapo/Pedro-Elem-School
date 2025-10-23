<?php
// include('config.php');

// require  'vendor/autoload.php';

class DatabaseClass{	
	
    

    private $connection = null;

    private $dbhost = ""; // Ip Address of database if external connection.
    private $dbuser = ""; // Username for DB
    private $dbpass = ""; // Password for DB
    private $dbname = "db_elementary_school_pedro"; // DB Name
    private $lastMethod = null;

    private function track($methodName) {
        $this->lastMethod = $methodName;
    }
    // this function is called everytime this class is instantiated		
    public function __construct(){
        $this->track(__FUNCTION__);
        try{
            
            $this->dbhost = $_ENV['DBHOST']; // Ip Address of database if external connection.
            $this->dbuser = $_ENV['DBUSER']; // Username for DB
            $this->dbpass = $_ENV['DBPWD']; // Password for DB
            $this->dbname = $_ENV['DBNAME']; // DB Name
            
            $this->connection = new PDO("mysql:host={$this->dbhost};dbname={$this->dbname};", $this->dbuser, $this->dbpass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->exec("SET time_zone = '+08:00'");
            
        }catch(Exception $e){
            throw new Exception($e->getMessage());   
        }			
        
    }

    

    
    public function __destruct() {

        
        $logs = [
            'user_id' => isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null,
            'action' =>  'User '.$this->lastMethod. ' Transaction'
        ];
        $this->insertRequestBatchRquest($logs,'logs');
        
    }

    
    // Insert a row/s in a Database Table
    public function Insert( $statement = "" , $parameters = [] ){
        $this->track(__FUNCTION__);
        try{
            
            $this->executeStatement( $statement , $parameters );
            return $this->connection->lastInsertId();
            
        }catch(Exception $e){
            throw new Exception($e->getMessage());   
        }		
    }

    // Select a row/s in a Database Table
    public function Select( $statement = "" , $parameters = [] ){
        $this->track(__FUNCTION__);
        try{
            
            $stmt = $this->executeStatement( $statement , $parameters );
            return $stmt->fetchAll();
            
        }catch(Exception $e){
            throw new Exception($e->getMessage());   
        }		
    }
    
    // Update a row/s in a Database Table
    public function Update( $statement = "" , $parameters = [] ){
        $this->track(__FUNCTION__);
        try{
            
            $this->executeStatement( $statement , $parameters );
            
        }catch(Exception $e){
            throw new Exception($e->getMessage());   
        }		
    }		
    
    // Remove a row/s in a Database Table
    public function Remove( $statement = "" , $parameters = [] ){
        $this->track(__FUNCTION__);
        try{
            
            $this->executeStatement( $statement , $parameters );
            
        }catch(Exception $e){
            throw new Exception($e->getMessage());   
        }		
    }		
    
    // execute statement
    private function executeStatement( $statement = "" , $parameters = [] ){
        // $this->track(__FUNCTION__);
        try{
        
            $stmt = $this->connection->prepare($statement);
            $stmt->execute($parameters);
            return $stmt;
            
        }catch(Exception $e){
            throw new Exception($e->getMessage());   
        }		
    }

    public function insertRequestBatchRquest($request, $table, $folder =false) {
        $this->track(__FUNCTION__);
        // Initialize field and value arrays
        $fields = [];
        $placeholders = [];
        $values = [];
    
        // Loop through the request array
        foreach ($request as $key => $value) {
            if (is_array($value)) {
                // Handle multiple file uploads
                if (isset($value['name']) && is_array($value['name'])) {
                    $uploadedPaths = $this->handleMultipleFileUpload($value, $folder);
                    if ($uploadedPaths) {
                        $fields[] = $key; // Add field for the file paths
                        $placeholders[] = '?';
                        $values[] = implode('|', $uploadedPaths); // Concatenate paths with "|"
                    }
                }
            } else {
                // Handle normal fields
                if (!empty($value)) {
                    $fields[] = $key;
                    $placeholders[] = '?';
                    $values[] = $value;
                }
            }
        }
    
        // Construct the SQL query dynamically
        $fieldList = implode(', ', $fields);
        $placeholderList = implode(', ', $placeholders);
        $sql = "INSERT INTO ".$table." ($fieldList) VALUES ($placeholderList)";
    
        // Execute the query
        $this->Insert($sql, $values);
    }

     // Helper function to handle multiple file uploads
     public function handleMultipleFileUpload($fileArray, $folder = false) {
        $this->track(__FUNCTION__);
        if(!$folder) {
            return false;
        }

            // Check if the directory exists; if not, create it
        if (!is_dir($folder)) {
            if (!mkdir($folder, 0777, true)) {
                die("Failed to create directory: $folder");
            }
        }
        
        $uploadDir =  $folder; // Ensure this directory exists and is writable
        $uploadedPaths = [];
    
        foreach ($fileArray['name'] as $index => $fileName) {
            $tmpName = $fileArray['tmp_name'][$index];
            $error = $fileArray['error'][$index];
    
            if ($error === 0) {
                $uniqueFileName = uniqid() . '_' . $fileName;
                $destinationPath = $uploadDir . $uniqueFileName;
    
                if (move_uploaded_file($tmpName, $destinationPath)) {
                    $uploadedPaths[] = $destinationPath; // Collect the uploaded file path
                }
            }
        }
    
        return $uploadedPaths;
    }

    public function updateField($table, $data, $where) {
        $this->track(__FUNCTION__);
        // Validate the inputs
        if (empty($table) || empty($data) || empty($where)) {
            throw new Exception("Table, data, and where clause are required.");
        }
    
        // Dynamically build the update query
        $fields = [];
        $values = [];
        foreach ($data as $field => $value) {
            $fields[] = "$field = ?";
            $values[] = $value;
        }
    
        // Add the WHERE clause values
        foreach ($where as $key => $value) {
            $values[] = $value;
        }
    
        // Construct the query
        $query = "UPDATE $table SET " . implode(', ', $fields) . " WHERE " . implode(' AND ', array_map(fn($key) => "$key = ?", array_keys($where)));
    
        // Execute the query using the database instance
        return $this->update($query, $values);
    }


 
    
}



?>