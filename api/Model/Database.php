<?php
require_once __DIR__ . "/../../config/database.php";

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
            throw New Exception( "Database SELECT Error: " . $e->getMessage() );
        }
    }

    public function post($query = "" , $params=[])
    {
       try {
            $this->executeStatement($query, $params);
            return $this -> insertedId();
        } catch(Exception $e) {
            throw New Exception( "Database POST Error: " . $e->getMessage() );
        }

    }

    public function update($query ="", $params=[])
    {
        try {
        $stmt = $this->executeStatement($query, $params);   
        return  $stmt -> rowCount() > 0;
        } catch(Exception $e) {
            throw New Exception( "Database Error: " . $e->getMessage() );
        }
    }

    public function delete($query ="", $params=[])
    {     
        try {
            $stmt = $this->executeStatement($query, $params);
            return  $stmt -> rowCount() > 0;
        } catch(Exception $e) {
            throw New Exception( "Database Error: " . $e->getMessage() );
        }
    }

    private function executeStatement($query = "" , $params = [])
    {
        
        try {

            $stmt = $this->connection->prepare( $query );
            if (!$stmt) {
                throw new Exception("SQL Prepare Error: " . implode(" ", $this->connection->errorInfo()), 500);
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
                        case 'NULL':
                            $pdoType = PDO::PARAM_NULL;
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
              throw New Exception("Database Query Error: " . $e->getMessage(), $e->getCode());
          }	
    }
    
    public function startTransaction(): void
    {
        if (!$this->connection->inTransaction()) {
            $this->connection->beginTransaction();
        }
    }

        public function commitTransaction(): void
    {
        if ($this->connection->inTransaction()) {
            $this->connection->commit();
        }
    }

        public function rollBackTransaction(): void
    {
        if ($this->connection->inTransaction()) {
            $this->connection->rollBack();
        }
    }
    
    public function isInTransaction(): bool
    {
        return $this->connection->inTransaction();
    }
  }