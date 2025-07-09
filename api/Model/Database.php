<?php
require_once "/Applications/MAMP/htdocs/Flashcards/config/database.php";

// use function PHPSTORM_META\type;
// $dotenv = file(__DIR__ . "/databankconfig.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
// foreach ($dotenv as $line) {
//     putenv(trim($line));
// }



class Database extends Connection
{
    // Ensures the connection is established
    public function __construct() 
    {
        parent::__construct(); 
    }

    public function select($query , $params = [])
    {
        
        try {
            
            $stmt = $this->executeStatement( $query , $params );
            return $stmt->fetchAll();	
        } catch(Exception $e) {
            throw New Exception( $e->getMessage() );
        }
        // return false;
    }

    public function post($query = "" , $params=[])
    {
        return $this->executeStatement($query, $params);
    }

    public function update($query ="", $params=[])
    {
        return $this->executeStatement($query, $params);

        // try {
           
            
        //     $stmt = $this->executeStatement( $query , $params);
            
        //     // $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);	
            
        //     if (!$stmt)
        //     { 
        //         throw new Exception("can not update");
        //     }
        //     $stmt->close();
        //     return true;

            			
        //     // return $result;
        //     } catch(Exception $e) {
        //     throw New Exception( $e->getMessage() );
        // }
    }

    public function delete($query ="", $params=[])
    {
        
        return $this->executeStatement($query, $params);

        // try {

            
        //     $stmt = $this->executeStatement( $query , $params);
            
        //     // $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);	
            
        //     if (!$stmt)
        //     { 
        //         throw new Exception("can not update");
        //     }
        //     $stmt->close();
        //     return true;

            			
        //     // return $result;
        //     } catch(Exception $e) {
        //     throw New Exception( $e->getMessage() );
        // }
    }

    private function executeStatement($query = "" , $params = [])
    {
        
        try {

            $stmt = $this->connection->prepare( $query );
            if (!$stmt) {
                throw new Exception("SQL Prepare Error: " . implode(" ", $this->connection->errorInfo()), 500);
            }
             // Debug: Log the query and parameters
        // error_log("Executing Query: " . $query);
        // error_log("With Parameters: " . print_r($params, true));
            // Bind parameters dynamically
            if ($params) {
                $index = 1;
                // Loop through each parameter and determine the type
                foreach ($params as $param) {
                    // Determine the type of each parameter (i = integer, d = double, s = string, b = blob)
                    $type = gettype($param);
                    switch ($type) {
                        case 'string':
                            $pdoType = PDO::PARAM_STR;
                            break;
                        case 'integer':
                            $pdoType = PDO::PARAM_INT;
                            break;
                        case 'double':
                            $pdoType = PDO::PARAM_STR;
                            break;
                        case 'boolean':
                            $pdoType = PDO::PARAM_BOOL;
                            break;
                        default:
                            throw new Exception("Unacceptable type for parameter: " . $param, 400);
                    }
                    
                    $stmt->bindValue($index,$param, $pdoType);
                    
                    $index += 1;
                }

                if (!$stmt->execute()) {
                    throw new Exception("Execution Error: " . implode(" ", $stmt->errorInfo()), 500);
                }
                
                return $stmt;
            }

          
          } catch(PDOException $e) {
              throw New Exception("Database Query Error: " . $e->getMessage(), 500);
          }	
    }
    
    private function test ($query = "" , $params = [])
    {
        
        try {

            $stmt = $this->connection->prepare( $query );
            if (strpos($query, 'INSER')) {
                print_r($query);
                die;
            }
            // Bind parameters dynamically
            if ($params) {
                $index = 1;
                // Loop through each parameter and determine the type
                foreach ($params as $param) {
                    
                    // Determine the type of each parameter (i = integer, d = double, s = string, b = blob)
                    $type = gettype($param);
                    switch ($type) {
                        case 'string':
                            $pdoType = PDO::PARAM_STR;
                            break;
                        case 'integer':
                            $pdoType = PDO::PARAM_INT;
                            break;
                        case 'double':
                            $pdoType = PDO::PARAM_STR;
                            break;
                        case 'boolean':
                            $pdoType = PDO::PARAM_BOOL;
                            break;
                        default:
                            throw new Exception("Unacceptable type for parameter: " . $param);
                    }
                    
                    $stmt->bindValue($index,$param, $pdoType);
                    $index += 1;
                }
                
                $stmt->execute();
                return $stmt;
            }

            // if($stmt === false) {
            //     throw New Exception("Unable to do prepared statement: " . $query);
            // }
           
            
            //   $stmt->execute();
             
            //   return $stmt;
          } catch(PDOException $e) {
              throw New Exception("Database Query Error: " . $e->getMessage() );
          }	
    }
  }