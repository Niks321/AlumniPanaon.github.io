<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Admin';
$user_email = $_SESSION['user_email'] ?? '';

// Include database and classes for stats
require_once '../config/database.php';
require_once '../classes/Alumni.php';
require_once '../classes/UpcomingEvent.php';
require_once '../classes/Announcement.php';

$db = new Database();
$conn = $db->conn;

$alumni = new Alumni($conn);
$events = new UpcomingEvent($conn);
$announcements = new Announcement($conn);

$totalAlumni = $alumni->getAllAlumni()->num_rows;
$totalEvents = $events->getAllEvents()->num_rows;
$totalAnnouncements = $announcements->getAllAnnouncements()->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - AlumniPanaon Hub</title>
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
        <a href="dashboard_admin.php">Home</a>
        <a href="manage_alumni.php">Manage Alumni</a>
        <a href="events.php">Events/Announcements</a>
        <a href="contact.php">Contact Us</a>
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
        <h1>Welcome Back, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <p>Manage the AlumniPanaon Hub as an administrator.</p>
    </div>
</section>

<!-- =========================
     STATS SECTION
========================= -->
<section class="stats-section">
    <div class="container">
        <h2>Dashboard Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h3><?php echo $totalAlumni; ?></h3>
                <p>Total Alumni</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-alt"></i>
                <h3><?php echo $totalEvents; ?></h3>
                <p>Upcoming Events</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-bullhorn"></i>
                <h3><?php echo $totalAnnouncements; ?></h3>
                <p>Announcements</p>
            </div>
        </div>
    </div>
</section>

<!-- =========================
     ADMIN ACTIONS SECTION
========================= -->
<section class="admin-actions-section">
    <div class="container">
        <div class="admin-panel-wrapper">
            <h2>Admin Panel</h2>
            <div class="admin-actions-grid">
                <div class="action-card">
                    <i class="fas fa-users"></i>
                    <h3>Alumni List</h3>
                    <p>View and manage alumni profiles and registrations.</p>
                    <a href="manage_alumni.php" class="btn-action">Manage Alumni</a>
                </div>

                <div class="action-card">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Events & Announcements</h3>
                    <p>Manage upcoming events and latest announcements.</p>
                    <a href="events.php" class="btn-action">Edit Events</a>
                </div>

                <div class="action-card">
                    <i class="fas fa-address-book"></i>
                    <h3>Contact Us</h3>
                    <p>Update organization chart and contact information.</p>
                    <a href="edit_contact.php" class="btn-action">Edit Contact</a>
                </div>
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
