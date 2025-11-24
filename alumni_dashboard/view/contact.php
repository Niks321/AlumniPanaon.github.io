<?php
session_start();

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$homeLink = $isAdmin ? 'dashboard_admin.php' : 'dashboard_alumni.php';
$manageAlumniLink = $isAdmin ? '<a href="manage_alumni.php">Manage Alumni</a>' : '<a href="profile.php">My Profile</a>';

require_once '../config/config.php';

// Create org_chart table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS org_chart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    display_order INT NOT NULL
) ENGINE=InnoDB");

// Fetch contact content
$stmt = $conn->prepare("SELECT * FROM contact_content WHERE id = 1");
$stmt->execute();
$content = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch org chart
$stmt = $conn->prepare("SELECT * FROM org_chart ORDER BY display_order");
$stmt->execute();
$org_chart = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - AlumniPanaon Hub</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Link to external JS -->
    <script src="../assets/js/script.js"></script>
    <style>
        .org-chart {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 60px 0;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 15px;
            box-shadow: var(--shadow);
        }
        .org-level {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .org-person {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: none;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            width: 250px;
            margin: 0 15px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .org-person::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        .org-person:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        .org-person i {
            font-size: 40px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        .org-person h3 {
            margin: 0;
            color: var(--text-color);
            font-weight: 600;
            font-size: 18px;
        }
        .org-person p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: var(--text-color);
            font-weight: 400;
        }
        .org-line {
            width: 3px;
            height: 30px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }
        .org-connector {
            display: flex;
            align-items: center;
            flex-direction: column;
            margin-bottom: 10px;
        }
    </style>
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
        <a href="<?php echo $homeLink; ?>">Home</a>
        <?php echo $manageAlumniLink; ?>
        <a href="events.php">Events/Announcements</a>
        <a href="contact.php">Contact Us</a>
        <?php if (!$isAdmin): ?>
            <a href="network.php">Alumni Network</a>
        <?php endif; ?>
        <a href="../controller/LogoutController.php">Logout</a>
        </nav>

    <div class="logo-right">
        <img src="../assets/images/ustp-alumni-logo.jpg" alt="Right Logo">
    </div>
</header>

<?php if ($isAdmin): ?>
    <div style="text-align: center; margin: 10px;">
        <a href="edit_contact.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Edit Contact Us</a>
    </div>
<?php endif; ?>

<!-- =========================
     HERO SECTION
========================= -->
<section class="hero">
    <div class="hero-content">
        <h1>Contact Us</h1>
        <p>Get in touch with the USTP Panaon Alumni Association.</p>
    </div>
</section>

<!-- =========================
     ORGANIZATION CHART SECTION
========================= -->
<section class="org-chart-section">
    <div class="container">
        <h2>Organization Chart</h2>
        <div class="org-chart">
            <?php foreach ($org_chart as $index => $person): ?>
                <div class="org-level">
                    <div class="org-person">
                        <h3><?php echo htmlspecialchars($person['position']); ?></h3>
                        <p><?php echo htmlspecialchars($person['name']); ?></p>
                    </div>
                </div>
                <?php if ($index < count($org_chart) - 1): ?>
                    <div class="org-connector">
                        <div class="org-line"></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =========================
     CONTACT INFO SECTION
========================= -->
<section class="contact-info-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 60px 0; margin-top: 40px;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 40px; font-size: 2.5em; font-weight: 300;">Contact Information</h2>
        <div style="display: flex; justify-content: space-around; flex-wrap: wrap;">
            <div style="text-align: center; margin: 20px;">
                <i class="fas fa-map-marker-alt" style="font-size: 50px; margin-bottom: 15px;"></i>
                <h3>Address</h3>
                <p><?php echo htmlspecialchars($content['address']); ?></p>
            </div>
            <div style="text-align: center; margin: 20px;">
                <i class="fas fa-envelope" style="font-size: 50px; margin-bottom: 15px;"></i>
                <h3>Email</h3>
                <p><a href="mailto:<?php echo htmlspecialchars($content['email']); ?>" style="color: white; text-decoration: none;"><?php echo htmlspecialchars($content['email']); ?></a></p>
            </div>
            <div style="text-align: center; margin: 20px;">
                <i class="fas fa-phone" style="font-size: 50px; margin-bottom: 15px;"></i>
                <h3>Phone</h3>
                <p><?php echo htmlspecialchars($content['phone']); ?></p>
            </div>
            <div style="text-align: center; margin: 20px;">
                <i class="fab fa-facebook" style="font-size: 50px; margin-bottom: 15px;"></i>
                <h3>Facebook</h3>
                <p><a href="<?php echo htmlspecialchars($content['facebook']); ?>" target="_blank" style="color: white; text-decoration: none;">USTP Alumni Panaon</a></p>
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
