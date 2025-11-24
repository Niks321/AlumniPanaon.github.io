<?php
require_once 'User.php';

class Alumni extends User { // Inheritance

    public function __construct($dbConn, $name = '', $email = '', $password = ''){
        parent::__construct($dbConn, $name, $email, $password);
    }

    // =========================
    // REGISTER USER
    // =========================
    public function register($name = '', $email = '', $password = ''){
        if(empty($name) && isset($_POST['register'])){
            $name = trim($_POST['name']);
            $email = trim(strtolower($_POST['email']));
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        if(empty($name) || empty($email) || empty($password)){
            return false;
        }

        // Check if email exists
        $check = $this->conn->prepare("SELECT user_id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();
        if($result->num_rows > 0){
            return "Email already exists";
        }

        // Insert user
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if($stmt->execute()){
            return $this->conn->insert_id; // success
        } else {
            return false; // failed
        }
    }

    // =========================
    // LOGIN USER
    // =========================
    public function login($email, $password){
        return $this->authenticate($email, $password, 'alumni');
    }

    // =========================
    // GET USER BY EMAIL
    // =========================
    public function getUserByEmail($email, $role = 'alumni'){
        return parent::getUserByEmail($email, $role);
    }

    // =========================
    // GET PROFILE
    // =========================
    public function getProfile($userId){
        $stmt = $this->conn->prepare("SELECT * FROM users u LEFT JOIN pds_contact p ON u.user_id = p.user_id WHERE u.user_id = ? AND u.role = 'alumni'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // =========================
    // GET USER BY ID
    // =========================
    public function getUserById($user_id){
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 1){
            return $result->fetch_assoc();
        }
        return false;
    }

    // =========================
    // GET ALL ALUMNI
    // =========================
    public function getAllAlumni(){
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE role = 'alumni'");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result;
    }

    // =========================
    // DELETE ALUMNI
    // =========================
    public function deleteAlumni($user_id){
        // Start transaction
        $this->conn->begin_transaction();

        try {
            // Delete from related tables first due to foreign keys
            $tables = ['pds_contact', 'home_address', 'parent_legal_guardian', 'emergency_contact', 'work_experience', 'user_skills', 'graduation_message', 'additional_information'];
            foreach ($tables as $table) {
                $stmt = $this->conn->prepare("DELETE FROM $table WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();
            }

            // Delete from users table
            $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'alumni'");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback on error
            $this->conn->rollback();
            return false;
        }
    }
}
?>
