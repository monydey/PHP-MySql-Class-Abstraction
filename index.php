<?php
  require 'config.php';
  

 class LaMarche{
 
    // Create connection to database() and return.
    public function connect(){
       $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
       
       // Check connection
       if($conn ->connect_error){
          die("Connection failed: " . $conn->connect_error);
       }else{
          echo 'Connection Successful <br>';
       }
       return $conn;
    }
    
    // General query that can be customized to fit desired purpose.
    public function query($query){
       $laMarche = $this->connect();
       $result = $laMarche->query($query);
       
       while( $row = $result->fetch_object() ){
          $results[] = $row;
       }
       
       // View results
       print_r($results);
       
       // Free result.
       $result->close();
       
       return $results;
    }
    
    public function insert($table, $data, $dataType){
       
       // Connect to the database
       $LaMarche = $this->connect();
       
       // Check if $table or $data are not set
       if( empty($table) || empty($data)){
          return false;
       }
       
       // Cast $data and $dataType(for implode()) to arrays
       $data = (array) $data;
       $dataType = (array) $dataType;
       
       // Build dataType string
       $dataType = implode('', $dataType); // Remove spaces
       $dataType = str_replace('%', '', $dataType); //Remove special characters
       
       // Create variables from $data array.
       list( $columnNames, $parameters, $columnValues ) = $this->prepareData($data);
       
       // Prepend the $dataType to the $columnValues
       array_unshift($columnValues, $dataType);
       
       // Prepare the query 
       $stmt = $laMarche->prepare("INSERT INTO {$table} ({$columnNames}) VALUES ({$parameters})");
       
       // bind_param requires the values to be passed by reference.
       $columnValues[] = $this->reference($columnValues);
       
       // Bind values 
       call_user_func_array( array($stmt, 'bind_param'), $columnValues);
       
       // Execute the query
       $stmt->execute();
       
       // Check for successful insertion by checking the number of affected_rows.
       if( $stmt->affected_rows ){
          return true;
       }
       
       return false;
        
    }
    public function update($table, $data, $dataType, $where, $where_dataType){
      
      // Connect to the database
       $laMarche = $this->connect();
       
       // Check if $table or $data are set
       if(empty($table) || empty($data)){
          return false;
       }
       
       // Cast $data, $where_dataType, $dataType(for implode()) to arrays
       $data = (array) $data;
       $dataType = (array) $dataType;
       $where_dataType = (array) $where_dataType;
       
       // format array
       $dataType = implode('', $dataType);
       $dataType = str_replace('%', '', $dataType);
       $where_dataType = implode('', $where_dataType);
       $where_dataType = str_replace('%', '', $where_dataType);
       $format .= $where_dataType; //echo $format;
       
       // Create variables from $data array.
       list( $columnNames, $parameters, $columnValues ) = $this->prepareData($data, 'update');
       
       // Create where clause, where values Instantiate to prevent error notice.
       $where_clause = '';
       $where_values = '';
       $count = 0;
       
       foreach ($where as $columnName => $columnValue){
          if($count > 0){
             $where_clause .= ' AND ';
          }
          
          $where_clause .= $columnName . '=?';
          $where_values[] = $columnValue;
          
          $count++;
       }
       
       // Prepend the $dataType onto $columnValues
       array_unshift($columnValues, $dataType);
       $columnValues = array_merge($columnValues, $where_values);
       
       // Prepare the query
       $stmt = $laMarche->prepare("UPDATE {$table} SET {$parameters} WHERE {$where_clause}");
       
       // bind the columnValues dynamically 
       call_user_func_array( array( $stmt, 'bind_param'), $this->reference($columnValues));
       
       // Execute query
       $stmt->execute();
        
       // Check for successful insertion by checking the number of affected_rows. 
       if( $stmt->affected_rows){
          return true;
       }
       
       return false;
    }
    
    // Customize your query.
    public function get_results($query){
       $result = $this->query($query);
       
       if($conn){
          return $result;
       }else{
          echo 'Failed to get the results.';
       }
    }
   
    public function delete($table, $id){
       // Connect to the database
       $laMarche = $this->connect();
       
       // Prepare our query for binding 
       $stmt = $db->prepare("DELETE FROM {$table} WHERE ID = ?");
       
       // Dynamically bind values 
       $stmt->bind_param('d', $id);
       
       // Execute the query
       $stmt->execute();
       
       // Check for successful insertion 
       if ($stmt->affected_rows){
          return true;
       }
    }
    public function prepareData($data, $type='update'){
       // Instantiate $fields and $placeholders to enable looping 
       $columnName = '';
       $parameters = '';
       $columnValues = array();
       
       // build $columnNames, $columnValues and parameters
       // Parse them out as columnNames and columnValues pair
       foreach( $data as $columnName => $columnValue ){
          $columnNames .= "{$columnName},"; // Comma separated list of columnNames
          $columnValues[] = $columnValue;  // Array of columnValues
          
          if( $type == 'update'){
             $parameters .= $columnNames . '=?,';
          }else{
             $parameters .= '?,';  // Comma separated list of parameters variable.
          }
       }
       
       // Remove comma by normalizing $columnNames and $parameters.
       $columnNames = substr($columnNames, 0, -1);
       $parameters = substr($parameters, 0, -1);
       
       return array($columnNames, $parameters, $columnValues );
    }
    
    // Convert values to references.
    public function reference($array){
       $refValues = array();
       
       foreach ($array as $key => $value){
          $refValues[$key] = &$array[$key];
       }
       
       return $refValues;
    }
 }
 
  $laMarche = new LaMarche;
?>