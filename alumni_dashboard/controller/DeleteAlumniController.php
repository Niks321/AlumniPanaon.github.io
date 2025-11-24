<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../view/login.php");
    exit;
}

require_once '../config/config.php';
require_once '../classes/Alumni.php';

$alumniObj = new Alumni($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];

    if ($alumniObj->deleteAlumni($user_id)) {
        $_SESSION['message'] = "Alumni deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to delete alumni.";
        $_SESSION['message_type'] = "error";
    }

    header("Location: ../view/manage_alumni.php");
    exit;
} else {
    header("Location: ../view/manage_alumni.php");
    exit;
}
?>
