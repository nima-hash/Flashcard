<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$dotenv = file(__DIR__ . "/databankconfig.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($dotenv as $line) 
{
putenv(trim($line));
}


class Connection
{

  protected $connection = null;

  public function __construct()
  {

        $DB_HOST = getenv("DB_HOST");
        $DB_USERNAME = getenv("DB_USERNAME");
        $DB_PASSWORD = getenv("DB_PASSWORD");
        $DB_DATABASE_NAME = getenv("DB_DATABASE_NAME");
        $DB_PORT = getenv("DB_PORT");
        try {
            $dsn = "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE_NAME;charset=utf8mb4"; 
            $this->connection = new PDO(
                $dsn,
                $DB_USERNAME,
                $DB_PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }			
    }

    public function prepareStatement($query) {
      $stmt = $this -> connection -> prepare($query);
      return $stmt;
    }

    public function insertedId() {
      $id = $this -> connection -> lastInsertId();
      return $id;
    }

}

?>