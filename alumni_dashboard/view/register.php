<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard_alumni.php");
    exit;
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - AlumniPanaon Hub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header>
    <div class="logo-left">
        <img src="../assets/images/USTP-LOGO.png" alt="Left Logo">
    </div>

    <div class="text-center">
        <h1>University of Science and Technology of Southern Philippines</h1>
        <h2>AlumniPanaon Hub</h2>
    </div>

    <div class="logo-right">
        <img src="../assets/images/ustp-alumni-logo.jpg" alt="Right Logo">
    </div>
</header>

<div class="login-container">
    <h1>Alumni Register</h1>

    <?php if($error): ?>
        <div class="error-message" style="color:red; margin-bottom:10px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="success-message" style="color:green; margin-bottom:10px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form action="../controller/RegisterController.php" method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit" name="register">Register</button>
    </form>

    <a href="login.php">Already have an account? Login here</a>
</div>

<footer>
    Punta, Panaon, Misamis Occidental, Philippines 7205 |
    <a href="https://www.facebook.com/ustp.alumni.panaon">Facebook</a> |
    <a href="mailto:ustpaf-panaon@ustp.edu.ph">ustpaf-panaon@ustp.edu.ph</a>
</footer>

</body>
</html>
