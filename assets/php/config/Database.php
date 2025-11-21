<?php
// assets/php/config/Database.php
// Connexion MySQLi centralisée 

class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "facturation";
    public $conn;

    public function __construct()
    {
        $this->connectDB();
    }

    public function connectDB()
    {
        $this->conn = mysqli_connect($this->host, $this->username, $this->password, $this->dbname);
        if (!$this->conn) {
            die("Erreur de connexion MySQLi: " . mysqli_connect_error());
        }
        mysqli_set_charset($this->conn, "utf8");
    }

    public function close()
    {
        if ($this->conn) {
            mysqli_close($this->conn);
        }
    }
}

// Usage :
// $db = new Database();
// $conn = $db->conn;
