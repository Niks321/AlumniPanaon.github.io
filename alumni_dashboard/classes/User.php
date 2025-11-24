<?php
abstract class User { // Abstraction
    protected $conn;
    protected $id;
    protected $name;
    protected $email;
    protected $password; // Encapsulation

    public function __construct($conn, $name = '', $email = '', $password = ''){
        $this->conn = $conn;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    // Abstract method for login
    abstract public function login($email, $password);

    // Abstract method for profile
    abstract public function getProfile($userId);

    // Common login logic
    protected function authenticate($email, $password, $role) {
        $stmt = $this->conn->prepare("SELECT user_id, name, email, password_hash, role FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        } elseif ($user && $password === $user['password_hash']) {
            // Temporary check for plain text passwords (existing data)
            return $user;
        }
        return false;
    }

    // Get user by email
    protected function getUserByEmail($email, $role) {
        $stmt = $this->conn->prepare("SELECT user_id, name, email FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>
