<?php
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard_alumni.php");
    exit;
}

// Capture messages
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - AlumniPanaon Hub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/script.js" defer></script>
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
    <h1>Alumni Login</h1>

    <?php if($error): ?>
        <div class="error-message" style="color:red; margin-bottom:10px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="success-message" style="color:green; margin-bottom:10px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form action="../controller/LoginController.php" method="POST">
        <input type="text" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>

    <a href="register.php">Don't have an account? Register here</a>
</div>

<footer>
    Punta, Panaon, Misamis Occidental, Philippines 7205 |
    <a href="https://www.facebook.com/ustp.alumni.panaon">Facebook</a> |
    <a href="mailto:ustpaf-panaon@ustp.edu.ph">ustpaf-panaon@ustp.edu.ph</a>
</footer>

</body>
</html>
