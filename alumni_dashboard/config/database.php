<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db = "alumni_system";
    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
        if ($this->conn->connect_error) {
            die("Database Connection Failed: " . $this->conn->connect_error);
        }
    }
}
?>
