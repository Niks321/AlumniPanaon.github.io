<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require_once '../config/config.php';

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    header("Location: manage_alumni.php");
    exit;
}

// Fetch user basic info
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: manage_alumni.php");
    exit;
}

// Fetch PDS contact info
$stmt = $conn->prepare("SELECT * FROM pds_contact WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pds = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch home address
$stmt = $conn->prepare("SELECT * FROM home_address WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$address = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch parent/guardian
$stmt = $conn->prepare("SELECT * FROM parent_legal_guardian WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$guardian = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch emergency contact
$stmt = $conn->prepare("SELECT * FROM emergency_contact WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$emergency = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch work experience
$stmt = $conn->prepare("SELECT * FROM work_experience WHERE user_id = ? ORDER BY start_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$work_experience = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch skills
$stmt = $conn->prepare("SELECT * FROM user_skills WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch graduation message
$stmt = $conn->prepare("SELECT * FROM graduation_message WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$message = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch additional info
$stmt = $conn->prepare("SELECT * FROM additional_information WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$additional = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Alumni Profile - AlumniPanaon Hub</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
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
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="dashboard_admin.php">Home</a>
            <a href="manage_alumni.php">Manage Alumni</a>
            <a href="events.php">Events/Announcements</a>
            <a href="contact.php">Contact Us</a>
        <?php else: ?>
            <a href="dashboard_alumni.php">Home</a>
            <a href="profile.php">My Profile</a>
            <a href="events.php">Events/Announcements</a>
            <a href="contact.php">Contact Us</a>
            <a href="network.php">Alumni Network</a>
        <?php endif; ?>
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
        <h1>Alumni Profile: <?php echo htmlspecialchars($user['name']); ?></h1>
        <p>Personal Data Sheet (PDS) for <?php echo htmlspecialchars($user['email']); ?></p>
    </div>
</section>

<!-- =========================
     PROFILE CONTENT
========================= -->
<div class="profile-container">
    <div class="profile-content">
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <!-- Full Profile for Admin -->
            <!-- Personal Information -->
            <div class="section">
                <h2><i class="fas fa-user"></i> Personal Information</h2>
                <div class="row">
                    <div class="cell w-25"><label>Full Name</label><span><?php echo htmlspecialchars(($pds['first_name'] ?? '') . ' ' . ($pds['last_name'] ?? '')); ?></span></div>
                    <div class="cell w-25"><label>Age</label><span><?php echo htmlspecialchars($pds['age'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Gender</label><span><?php echo htmlspecialchars($pds['gender'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Civil Status</label><span><?php echo htmlspecialchars($pds['civil_status'] ?? ''); ?></span></div>
                </div>
                <div class="row">
                    <div class="cell w-33"><label>Religion</label><span><?php echo htmlspecialchars($pds['religion'] ?? ''); ?></span></div>
                    <div class="cell w-33"><label>Blood Type</label><span><?php echo htmlspecialchars($pds['blood_type'] ?? ''); ?></span></div>
                    <div class="cell w-33"><label>Birth Date</label><span><?php echo htmlspecialchars($pds['birth_date'] ?? ''); ?></span></div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="section">
                <h2><i class="fas fa-graduation-cap"></i> Academic Information</h2>
                <div class="row">
                    <div class="cell w-50"><label>Course</label><span><?php echo htmlspecialchars($pds['course'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Course Code</label><span><?php echo htmlspecialchars($pds['course_code'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Year Graduated</label><span><?php echo htmlspecialchars($pds['year_graduated'] ?? ''); ?></span></div>
                </div>
                <div class="row">
                    <div class="cell w-100"><label>University</label><span><?php echo htmlspecialchars($pds['university'] ?? ''); ?></span></div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="section">
                <h2><i class="fas fa-envelope"></i> Contact Information</h2>
                <div class="row">
                    <div class="cell w-50"><label>Email</label><span><?php echo htmlspecialchars($pds['email'] ?? ''); ?></span></div>
                    <div class="cell w-50"><label>Phone Number</label><span><?php echo htmlspecialchars($pds['phone_number'] ?? ''); ?></span></div>
                </div>
            </div>

            <!-- Home Address -->
            <div class="section">
                <h2>Home Address</h2>
                <div class="row">
                    <div class="cell w-25"><label>House Number</label><span><?php echo htmlspecialchars($address['house_number'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Street</label><span><?php echo htmlspecialchars($address['street'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Barangay</label><span><?php echo htmlspecialchars($address['barangay'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>City/Municipal</label><span><?php echo htmlspecialchars($address['city_municipal'] ?? ''); ?></span></div>
                </div>
                <div class="row">
                    <div class="cell w-25"><label>Province</label><span><?php echo htmlspecialchars($address['province'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Zip Code</label><span><?php echo htmlspecialchars($address['zip_code'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Region</label><span><?php echo htmlspecialchars($address['region'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Country</label><span><?php echo htmlspecialchars($address['country'] ?? ''); ?></span></div>
                </div>
            </div>

            <!-- Parent/Legal Guardian -->
            <div class="section">
                <h2><i class="fas fa-users"></i> Parent/Legal Guardian</h2>
                <div class="row">
                    <div class="cell w-33"><label>Father's Name</label><span><?php echo htmlspecialchars($guardian['father_name'] ?? ''); ?></span></div>
                    <div class="cell w-33"><label>Occupation</label><span><?php echo htmlspecialchars($guardian['father_occupation'] ?? ''); ?></span></div>
                    <div class="cell w-33"><label>Contact Number</label><span><?php echo htmlspecialchars($guardian['father_contact_number'] ?? ''); ?></span></div>
                </div>
                <div class="row">
                    <div class="cell w-33"><label>Mother's Name</label><span><?php echo htmlspecialchars($guardian['mother_name'] ?? ''); ?></span></div>
                    <div class="cell w-33"><label>Occupation</label><span><?php echo htmlspecialchars($guardian['mother_occupation'] ?? ''); ?></span></div>
                    <div class="cell w-33"><label>Contact Number</label><span><?php echo htmlspecialchars($guardian['mother_contact_number'] ?? ''); ?></span></div>
                </div>
                <div class="row">
                    <div class="cell w-33"><label>Guardian's Name</label><span><?php echo htmlspecialchars($guardian['guardian_name'] ?? ''); ?></span></div>
                    <div class="cell w-33"><label>Occupation</label><span><?php echo htmlspecialchars($guardian['guardian_occupation'] ?? ''); ?></span></div>
                    <div class="cell w-33"><label>Contact Number</label><span><?php echo htmlspecialchars($guardian['guardian_contact_number'] ?? ''); ?></span></div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="section">
                <h2><i class="fas fa-phone"></i> Emergency Contact</h2>
                <div class="row">
                    <div class="cell w-33"><label>Name</label><span><?php echo htmlspecialchars($emergency['contact_name'] ?? ''); ?></span></div>
                    <div class="cell w-33"><label>Relationship</label><span><?php echo htmlspecialchars($emergency['relationship'] ?? ''); ?></span></div>
                    <div class="cell w-33"><label>Contact Number</label><span><?php echo htmlspecialchars($emergency['contact_number'] ?? ''); ?></span></div>
                </div>
                <div class="row">
                    <div class="cell w-100"><label>Address</label><span><?php echo htmlspecialchars($emergency['contact_address'] ?? ''); ?></span></div>
                </div>
            </div>

            <!-- Work Experience -->
            <div class="section">
                <h2><i class="fas fa-briefcase"></i> Work Experience</h2>
                <?php if (count($work_experience) > 0): ?>
                    <?php foreach ($work_experience as $work): ?>
                        <div class="work-item">
                            <div class="row">
                                <div class="cell w-50"><label>Organization</label><span><?php echo htmlspecialchars($work['organization_business_name'] ?? ''); ?></span></div>
                                <div class="cell w-25"><label>Position</label><span><?php echo htmlspecialchars($work['position'] ?? ''); ?></span></div>
                                <div class="cell w-25"><label>Start Date</label><span><?php echo htmlspecialchars($work['start_date'] ?? ''); ?></span></div>
                            </div>
                            <div class="row">
                                <div class="cell w-50"><label>Owner/Proprietor</label><span><?php echo htmlspecialchars($work['owner_proprietor_name'] ?? ''); ?></span></div>
                                <div class="cell w-50"><label>End Date</label><span><?php echo htmlspecialchars($work['end_date'] ?? 'Present'); ?></span></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No work experience recorded.</p>
                <?php endif; ?>
            </div>

            <!-- Skills -->
            <div class="section">
                <h2><i class="fas fa-star"></i> Skills/Talents</h2>
                <div class="skills-list">
                    <?php if (count($skills) > 0): ?>
                        <?php foreach ($skills as $skill): ?>
                            <span class="skill-tag"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No skills recorded.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Graduation Message -->
            <div class="section">
                <h2><i class="fas fa-quote-left"></i> Graduation Message</h2>
                <p><?php echo htmlspecialchars($message['message'] ?? 'No message recorded.'); ?></p>
            </div>

            <!-- Additional Information -->
            <div class="section">
                <h2><i class="fas fa-info-circle"></i> Additional Information</h2>
                <div class="row">
                    <div class="cell w-50"><label>Date Accomplished</label><span><?php echo htmlspecialchars($additional['date_accomplished'] ?? ''); ?></span></div>
                    <div class="cell w-50"><label>Control Number</label><span><?php echo htmlspecialchars($additional['control_number'] ?? ''); ?></span></div>
                </div>
            </div>
        <?php else: ?>
            <!-- Limited Profile for Alumni -->
            <!-- Personal Information -->
            <div class="section">
                <h2><i class="fas fa-user"></i> Personal Information</h2>
                <div class="row">
                    <div class="cell w-50"><label>Full Name</label><span><?php echo htmlspecialchars(($pds['first_name'] ?? '') . ' ' . ($pds['last_name'] ?? '')); ?></span></div>
                    <div class="cell w-25"><label>Age</label><span><?php echo htmlspecialchars($pds['age'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Gender</label><span><?php echo htmlspecialchars($pds['gender'] ?? ''); ?></span></div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="section">
                <h2><i class="fas fa-graduation-cap"></i> Academic Information</h2>
                <div class="row">
                    <div class="cell w-50"><label>Course</label><span><?php echo htmlspecialchars($pds['course'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Course Code</label><span><?php echo htmlspecialchars($pds['course_code'] ?? ''); ?></span></div>
                    <div class="cell w-25"><label>Year Graduated</label><span><?php echo htmlspecialchars($pds['year_graduated'] ?? ''); ?></span></div>
                </div>
                <div class="row">
                    <div class="cell w-100"><label>University</label><span><?php echo htmlspecialchars($pds['university'] ?? ''); ?></span></div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="section">
                <h2><i class="fas fa-envelope"></i> Contact Information</h2>
                <div class="row">
                    <div class="cell w-50"><label>Email</label><span><?php echo htmlspecialchars($pds['email'] ?? ''); ?></span></div>
                    <div class="cell w-50"><label>Phone Number</label><span><?php echo htmlspecialchars($pds['phone_number'] ?? ''); ?></span></div>
                </div>
            </div>

            <!-- Work Experience Summary -->
            <div class="section">
                <h2><i class="fas fa-briefcase"></i> Work Experience</h2>
                <?php if (count($work_experience) > 0): ?>
                    <?php foreach ($work_experience as $work): ?>
                        <div class="work-item">
                            <div class="row">
                                <div class="cell w-50"><label>Organization</label><span><?php echo htmlspecialchars($work['organization_business_name'] ?? ''); ?></span></div>
                                <div class="cell w-50"><label>Position</label><span><?php echo htmlspecialchars($work['position'] ?? ''); ?></span></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No work experience recorded.</p>
                <?php endif; ?>
            </div>

            <!-- Skills -->
            <div class="section">
                <h2><i class="fas fa-star"></i> Skills/Talents</h2>
                <div class="skills-list">
                    <?php if (count($skills) > 0): ?>
                        <?php foreach ($skills as $skill): ?>
                            <span class="skill-tag"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No skills recorded.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

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
