<?php
session_start();

// Protect page: redirect to login if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Include database and Alumni class
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Alumni.php';

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$alumni = new Alumni($db);
$totalAlumni = $alumni->getAllAlumni()->num_rows;
$userBalance = $alumni->getUserById($_SESSION['user_id']);
$remainingBalance = $userBalance['balance'] ?? 0.00;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alumni Dashboard - AlumniPanaon Hub</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Link to external JS -->
    <script src="../assets/js/script.js"></script>
</head>
<body>

<!-- =========================
     HEADER WITH LOGOS AND CENTERED NAVIGATION
========================= -->
<header>
    <div class="logo-left">
        <img src="../assets/images/USTP-LOGO.png" alt="Left Logo">
    </div>

    <!-- Centered navigation menu -->
        <nav class="navbar">
        <a href="dashboard_alumni.php">Home</a>
        <a href="profile.php">My Profile</a>
        <a href="events.php">Events/Announcements</a>
        <a href="contact.php">Contact Us</a>
        <a href="network.php">Alumni Network</a>
        <a href="../controller/LogoutController.php">Logout</a>
        </nav>

    <div class="logo-right">
        <img src="../assets/images/ustp-alumni-logo.jpg" alt="Right Logo">
    </div>
</header>

<!-- =========================
     HERO SECTION
========================= -->
<section class="hero">
    <div class="hero-content">
        <h1>Welcome Back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
        <p>Stay connected with your alma mater and fellow alumni.</p>
        <div class="hero-stats">
            <div class="stat-item">
                <i class="fas fa-users"></i>
                <h3>Total Alumni: <?php echo $totalAlumni; ?></h3>
            </div>
            <div class="stat-item">
                <i class="fas fa-wallet"></i>
                <h3>Remaining Balance: ₱<?php echo number_format($remainingBalance, 2); ?></h3>
            </div>
        </div>
    </div>
</section>

<!-- =========================
     ALUMNI ACTIONS SECTION
========================= -->
<section class="admin-actions-section">
    <div class="container">
        <div class="panel-title">
            <h2>Alumni Panel</h2>
        </div>
        <div class="admin-actions-grid">
            <div class="action-card">
                <i class="fas fa-user"></i>
                <h3>My Profile</h3>
                <p>View and update your personal information and contact details.</p>
                <a href="profile.php" class="btn-action">View Profile</a>
            </div>

            <div class="action-card">
                <i class="fas fa-calendar-alt"></i>
                <h3>Events & Announcements</h3>
                <p>Stay updated with upcoming events and latest announcements.</p>
                <a href="events.php" class="btn-action">View Events</a>
            </div>

            <div class="action-card">
                <i class="fas fa-address-book"></i>
                <h3>Contact Us</h3>
                <p>Get in touch with the alumni office and university staff.</p>
                <a href="contact.php" class="btn-action">Contact Us</a>
            </div>

            <div class="action-card">
                <i class="fas fa-users"></i>
                <h3>Alumni Network</h3>
                <p>Connect with fellow alumni and build professional relationships.</p>
                <a href="network.php" class="btn-action">Explore Network</a>
            </div>
        </div>
    </div>
</section>

<!-- =========================
     FOOTER
========================= -->
<footer>
    Punta, Panaon, Misamis Occidental, Philippines 7205 |
    <a href="https://www.facebook.com/ustp.alumni.panaon" target="_blank">Facebook</a> |
    <a href="mailto:ustpaf-panaon@ustp.edu.ph">ustpaf-panaon@ustp.edu.ph</a>
</footer>

</body>
</html>
