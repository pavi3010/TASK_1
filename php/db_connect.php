<?php

// Ideally, use a library like vlucas/phpdotenv for environment variables.
// For this standalone task, we'll use a simple configuration array.

class Database {
    private $mysql_host = 'localhost';
    private $mysql_user = 'root';
    private $mysql_pass = ''; // Default XAMPP password is empty
    private $mysql_db = 'guvi_task';

    private $mongo_host = '127.0.0.1'; // 127.0.0.1 is often safer than localhost for mongo drivers
    private $mongo_port = '27017';
    private $mongo_db = 'guvi_profile_db';

    private $redis_host = '127.0.0.1';
    private $redis_port = 6379;

    public function getMysqlConnection() {
        try {
            $conn = new mysqli($this->mysql_host, $this->mysql_user, $this->mysql_pass, $this->mysql_db);
            if ($conn->connect_error) {
                // Try to create the database if it doesn't exist (First run convenience)
                $conn = new mysqli($this->mysql_host, $this->mysql_user, $this->mysql_pass);
                if (!$conn->connect_error) {
                    $conn->query("CREATE DATABASE IF NOT EXISTS " . $this->mysql_db);
                    $conn->select_db($this->mysql_db);
                    
                    // Create users table if not exists
                    $table_sql = "CREATE TABLE IF NOT EXISTS users (
                        id INT(11) AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(50) NOT NULL,
                        email VARCHAR(100) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )";
                    $conn->query($table_sql);
                    return $conn;
                }
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            return $conn;
        } catch (Exception $e) {
            die(json_encode(["status" => "error", "message" => "MySQL Connection Error: " . $e->getMessage()]));
        }
    }

    public function getMongoConnection() {
        try {
            // Requires MongoDB PHP Driver (composer require mongodb/mongodb)
            require_once 'vendor/autoload.php'; // Assuming composer autoload is present
            // If not using composer, this part depends on the environment implementation.
            // For the purpose of this file submission, we assume the library is available.
            
            $client = new MongoDB\Client("mongodb://{$this->mongo_host}:{$this->mongo_port}");
            return $client->selectDatabase($this->mongo_db);
        } catch (Exception $e) {
            // If the user doesn't have the library, we can't do much, but we provide the code.
            // We'll fallback or error out gracefully.
            return null;
        }
    }

    public function getRedisConnection() {
        try {
            $redis = new Redis();
            $redis->connect($this->redis_host, $this->redis_port);
            return $redis;
        } catch (Exception $e) {
             die(json_encode(["status" => "error", "message" => "Redis Connection Error: " . $e->getMessage()]));
        }
    }
}
?>
