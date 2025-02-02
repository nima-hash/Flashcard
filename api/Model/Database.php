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
    public function __construct() {
        parent::__construct(); 
    }
    // protected $connection = null;
    // public function __construct()
    // {
    //     $DB_HOST = getenv("DB_HOST");
    //     $DB_USERNAME = getenv("DB_USERNAME");
    //     $DB_PASSWORD = getenv("DB_PASSWORD");
    //     $DB_DATABASE_NAME = getenv("DB_DATABASE_NAME");
    //     $DB_PORT = getenv("DB_PORT");
    //     try {
    //         $dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE_NAME;charset=utf8mb4"; 
    //         $this->connection = new PDO(
    //             $dsn,
    //             $DB_USERNAME,
    //             $DB_PASSWORD,
    //             [
    //                 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    //                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    //                 PDO::ATTR_EMULATE_PREPARES => false,
    //             ]);
    //     } catch (PDOException $e) {
    //         // throw new Exception($e->getMessage()); 
    //         die("Database connection failed: " . $e->getMessage());
    //     }			
    // }

    // public function testselect($query = "" , $params = [])
    // {
        
    //     try {
            
    //         $stmt = $this->executeStatement( $query , $params );
    //         $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);	
            			
    //         $stmt->close();
    //         return $result;
    //     } catch(Exception $e) {
    //         throw New Exception( $e->getMessage() );
    //     }
    //     return false;
    // }

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

        // try {
            
        //     $stmt = $this->executePostStatement( $query , $params);
        //     // $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);	
           
        //     if ($stmt)
        //     {
        //     $stmt->close();
        //     return true;

        //     }			
        //     // return $result;
        //     } catch(Exception $e) {
        //     throw New Exception( $e->getMessage() );
        // }
        // return false;
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
                throw new Exception("SQL Prepare Error: " . implode(" ", $this->connection->errorInfo()));
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
                            throw new Exception("Unacceptable type for parameter: " . $param);
                    }
                    
                    $stmt->bindValue($index,$param, $pdoType);
                    
                    $index += 1;
                }

                if (!$stmt->execute()) {
                    throw new Exception("Execution Error: " . implode(" ", $stmt->errorInfo()));
                }
                
                return $stmt;
            }

          
          } catch(PDOException $e) {
              throw New Exception("Database Query Error: " . $e->getMessage() );
          }	
    }
    
    // private function executePostStatement($query = "" , $params)
    // {
        
    //     try {
    //         $stmt = $this->connection->prepare( $query );
            
    //         if($stmt === false) {
    //             throw New Exception("Unable to do prepared statement: " . $query);
    //         }
            
    //         if( $params ) {
    //             $paramsArray = [];
    //             $paramsType = '';
                
    //             foreach($params as $key =>$value){
    //                array_push($paramsArray, $value);
    //                $type = gettype($value);
    //                switch ($type) {
    //                 case 'string':
    //                     $type = 's';
    //                     break;
    //                 case 'integer':
    //                     $type = 'i';
    //                     break;
    //                 case 'boolean':
    //                     $type = 'b';
    //                     break;
    //                 case 'double':
    //                     $type = 'd';
    //                     break;
    //                 default:
    //                     $type = false;
    //               }
    //               if (!$type){
    //                 throw New Exception("Unacceptable input value of: " . $value);
    //               }
    //               $paramsType .= $type;

    //             }
                
    //             $stmt->bind_param($paramsType, ...$paramsArray);
                
    //         }
    //           $stmt->execute();
    //           return $stmt;

    //     }catch( mysqli_sql_exception $e ){

    //             echo json_encode($e->getMessage());
    //             die;
    //       } catch(Exception $e) {
    //           throw New Exception( $e->getMessage() );
    //           return false;
    //       }	
    // }
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