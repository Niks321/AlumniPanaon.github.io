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

// Get all alumni except the current user
$currentUserId = $_SESSION['user_id'];
$allAlumni = $alumni->getAllAlumni();
$alumniList = [];
while ($row = $allAlumni->fetch_assoc()) {
    if ($row['user_id'] != $currentUserId) {
        $alumniList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alumni Network - AlumniPanaon Hub</title>
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
        <a href="network.php" class="active">Alumni Network</a>
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
        <h1>Alumni Network</h1>
        <p>Connect with fellow alumni and build professional relationships.</p>
    </div>
</section>

<!-- =========================
     ALUMNI NETWORK SECTION
========================= -->
<section class="admin-actions-section">
    <div class="container">
        <div class="panel-title">
            <h2>Explore Alumni</h2>
        </div>
        <div class="network-grid">
            <?php if (count($alumniList) > 0): ?>
                <?php foreach ($alumniList as $alum): ?>
                    <div class="network-card">
                        <i class="fas fa-user-circle"></i>
                        <h3><?php echo htmlspecialchars($alum['name']); ?></h3>
                        <p><?php echo htmlspecialchars($alum['email']); ?></p>
                        <a href="view_profile.php?user_id=<?php echo $alum['user_id']; ?>" class="btn-action">View Profile</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No other alumni found.</p>
            <?php endif; ?>
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
