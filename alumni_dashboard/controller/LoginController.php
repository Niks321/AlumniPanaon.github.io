<?php
session_start();
require_once '../config/database.php';
require_once '../classes/Alumni.php';
require_once '../classes/Admin.php';

class LoginController {
    private $alumni;
    private $admin;

    public function __construct($dbConn) {
        $this->alumni = new Alumni($dbConn);
        $this->admin = new Admin($dbConn);
    }

    // Handle login
    public function handleLogin() {
        if (!isset($_POST['login'])) {
            header("Location: ../view/login.php");
            exit;
        }

        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Try alumni login first
        $user = $this->alumni->login($email, $password);

        if (!$user) {
            // Try admin login
            $user = $this->admin->login($email, $password);
        }

        if ($user && is_array($user)) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../view/dashboard_admin.php");
            } else {
                header("Location: ../view/dashboard_alumni.php");
            }
            exit;
        } else {
            // Check if email exists in either alumni or admin
            $existsAlumni = $this->alumni->getUserByEmail($email);
            $existsAdmin = $this->admin->getUserByEmail($email);
            if ($existsAlumni || $existsAdmin) {
                $_SESSION['error'] = "Incorrect email or password. Please try again.";
            } else {
                $_SESSION['error'] = "Email or user cannot be found.";
            }
            header("Location: ../view/login.php");
            exit;
        }
    }
}

// Initialize database and controller
$db = new Database();
$loginController = new LoginController($db->conn);
$loginController->handleLogin();
?>
