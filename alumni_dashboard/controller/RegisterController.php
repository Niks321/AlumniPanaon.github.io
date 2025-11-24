<?php
session_start();
require_once "../config/config.php";
require_once "../classes/Alumni.php";

if(isset($_POST['register'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($password !== $confirm_password){
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../view/register.php");
        exit;
    }

    $alumni = new Alumni($conn);
    // Call register() and capture its return value
    $result = $alumni->register($name, $email, $password);

    if($result && is_numeric($result)){
        $_SESSION['success'] = "Successfully registered! You can now login.";
        header("Location: ../view/login.php");
        exit;
    } elseif($result === "Email already exists"){
        $_SESSION['error'] = "Email already registered.";
        header("Location: ../view/register.php");
        exit;
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: ../view/register.php");
        exit;
    }
}
?>
