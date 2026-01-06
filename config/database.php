<?php
class Database {
    private static $instance = null;
    private $connection;
    private $host = "Localhost";
    private $name = "root";
    private $password = "";
    private $dbname = "kari";

    public function __construct(){
        try {
            $this->connection = new PDO("mysql:host=".$this->host.";dbname=".$this->dbname,$this->name,$this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new Database();
        }
        return self::$instance;
    }
    public function getConnection(){
        return $this->connection;
    }
}



