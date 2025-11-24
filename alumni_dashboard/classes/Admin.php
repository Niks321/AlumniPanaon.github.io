<?php
require_once 'User.php';

class Admin extends User {
    public function __construct($dbConn) {
        parent::__construct($dbConn, '', '', '');
    }

    // Admin login
    public function login($email, $password) {
        return $this->authenticate($email, $password, 'admin');
    }

    // Get user by email for admin
    public function getUserByEmail($email, $role = 'admin') {
        return parent::getUserByEmail($email, $role);
    }

    // Get profile (not used for admin, but required by abstract)
    public function getProfile($userId) {
        // Admins don't have profiles like alumni
        return null;
    }

    // Get all alumni
    public function getAllAlumni() {
        $stmt = $this->conn->prepare("SELECT u.user_id, u.name, u.email, u.created_at, p.first_name, p.last_name, p.course, p.year_graduated FROM users u LEFT JOIN pds_contact p ON u.user_id = p.user_id WHERE u.role = 'alumni'");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get all events
    public function getAllEvents() {
        $stmt = $this->conn->prepare("SELECT * FROM events ORDER BY date DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get all announcements
    public function getAllAnnouncements() {
        $stmt = $this->conn->prepare("SELECT * FROM announcements ORDER BY date DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
