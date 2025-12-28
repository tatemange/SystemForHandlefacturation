<?php
// assets/php/config/Database.php
// Connexion MySQLi centralisée 

class Database
{
    private $host;
    private $username;
    private $password;
    private $dbname;
    public $conn;

    public function __construct()
    {
        require_once __DIR__ . '/config.php';
        
        $this->host = DB_HOST;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->dbname = DB_NAME;

        $this->connectDB();
    }

    public function connectDB()
    {
        $this->conn = mysqli_connect($this->host, $this->username, $this->password, $this->dbname);
        if (!$this->conn) {
            die("Erreur de connexion a facturation (mysqli error): " . mysqli_connect_error());
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
