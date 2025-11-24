<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

function fetch_data($conn, $table, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result ?: [];
}

$user = fetch_data($conn, 'users', $user_id);
$pds       = fetch_data($conn, 'PDS_CONTACT', $user_id);
$address   = fetch_data($conn, 'HOME_ADDRESS', $user_id);
$guardian  = fetch_data($conn, 'PARENT_LEGAL_GUARDIAN', $user_id);
$emergency = fetch_data($conn, 'EMERGENCY_CONTACT', $user_id);
$message   = fetch_data($conn, 'GRADUATION_MESSAGE', $user_id);

// Fetch work experience
$work_experience = [];
$stmt = $conn->prepare("SELECT * FROM WORK_EXPERIENCE WHERE user_id=? ORDER BY work_id ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $work_experience[] = $row;
}
$stmt->close();

// Fetch skills
$skills = [];
$stmt = $conn->prepare("SELECT skill_name FROM USER_SKILLS WHERE user_id=? ORDER BY skill_id ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $skills[] = $row['skill_name'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Alumni Profile - AlumniPanaon Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-pic-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        function validateForm() {
            const requiredFields = document.querySelectorAll('input[required]');
            let isValid = true;
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = 'red';
                    isValid = false;
                } else {
                    field.style.borderColor = '#ccc';
                }
            });
            if (!isValid) {
                alert('Please fill in all required fields.');
            }
            return isValid;
        }
    </script>
</head>
<body>

<!-- HEADER WITH LOGOS AND NAVIGATION -->
<header class="profile-header">
    <div class="logo-left">
        <img src="../assets/images/USTP-LOGO.png" alt="Left Logo">
    </div>
    <div class="header-center">
        <nav class="navbar">
        <a href="dashboard_alumni.php">Home</a>
        <a href="profile.php">My Profile</a>
        <a href="events.php">Events/Announcements</a>
        <a href="contact.php">Contact Us</a>
        <a href="network.php">Alumni Network</a>
        <a href="../controller/LogoutController.php">Logout</a>
        </nav>
    </div>
    <div class="logo-right">
        <img src="../assets/images/ustp-alumni-logo.jpg" alt="Right Logo">
    </div>
</header>

<!-- MAIN FORM -->
<div class="pds-form">

    <div class="title-container main-title-container">
        <h1 class="main-title"><i class="fas fa-user-graduate"></i> Alumni Personal Data Sheet</h1>
    </div>

    <form action="../controller/ProfileController.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">

    <!-- Profile Picture Section -->
    <div class="profile-picture-section">
        <h2 class="form-title"><i class="fas fa-camera"></i> Profile Picture</h2>
        <div class="profile-pic-container" style="display: flex; flex-direction: column; align-items: center;">
            <img id="profile-pic-preview" src="<?= $user['profile_picture'] ?? '../assets/images/default-profile.png' ?>" alt="Profile Picture" class="profile-pic">
            <label for="profile_picture" class="upload-btn"><i class="fas fa-upload"></i> Choose File</label>
            <input type="file" name="profile_picture" id="profile_picture" accept="image/*" onchange="previewImage(event)">
        </div>
    </div>

        <!-- PERSONAL DATA AND CONTACT DETAILS -->
        <div class="form-section">
            <div class="form-title-container">
                <h2 class="section-title"><i class="fas fa-id-card"></i> Personal Data and Contact Details</h2>
            </div>
            <div class="row">
                <div class="cell w-25"><label>Last Name *</label><input type="text" name="last_name" required value="<?= $pds['last_name'] ?? '' ?>"></div>
                <div class="cell w-25"><label>First Name *</label><input type="text" name="first_name" required value="<?= $pds['first_name'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Middle Name</label><input type="text" name="middle_name" value="<?= $pds['middle_name'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Extension Name</label><input type="text" name="extension_name" value="<?= $pds['extension_name'] ?? '' ?>"></div>
            </div>
            <div class="row">
                <div class="cell w-25"><label>Nickname</label><input type="text" name="nick_name" value="<?= $pds['nick_name'] ?? '' ?>"></div>
                <div class="cell w-15"><label>Age</label><input type="text" name="age" value="<?= $pds['age'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Gender</label><input type="text" name="gender" value="<?= $pds['gender'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Civil Status</label><input type="text" name="civil_status" value="<?= $pds['civil_status'] ?? '' ?>"></div>
            </div>
            <div class="row">
                <div class="cell w-25"><label>Religion</label><input type="text" name="religion" value="<?= $pds['religion'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Birthday (mm/dd/yyyy)</label><input type="date" name="birth_date" value="<?= $pds['birth_date'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Blood Type</label><input type="text" name="blood_type" value="<?= $pds['blood_type'] ?? '' ?>"></div>
            </div>
            <div class="row">
                <div class="cell w-25"><label>Course</label><input type="text" name="course" value="<?= $pds['course'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Course Code</label><input type="text" name="course_code" value="<?= $pds['course_code'] ?? '' ?>"></div>
                <div class="cell w-25"><label>University Name</label><input type="text" name="university" value="<?= $pds['university'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Year/Batch</label><input type="text" name="year_graduated" value="<?= $pds['year_graduated'] ?? '' ?>"></div>
            </div>
            <div class="row">
                <div class="cell w-50"><label>Phone / Mobile Number</label><input type="text" name="phone_number" value="<?= $pds['phone_number'] ?? '' ?>"></div>
                <div class="cell w-50"><label>Email Address *</label><input type="email" name="email" required value="<?= $pds['email'] ?? '' ?>"></div>
            </div>
        </div>

        <!-- HOME ADDRESS -->
        <div class="form-section">
            <div class="form-title-container">
                <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Home Address</h2>
            </div>
            <div class="row">
                <div class="cell w-50"><label>House # / Block-Lot / Street Name</label><input type="text" name="house_number" value="<?= $address['house_number'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Barangay</label><input type="text" name="barangay" value="<?= $address['barangay'] ?? '' ?>"></div>
            </div>
            <div class="row">
                <div class="cell w-25"><label>Municipality / City</label><input type="text" name="city_municipal" value="<?= $address['city_municipal'] ?? '' ?>"></div>
                <div class="cell w-15"><label>Zip Code</label><input type="text" name="zip_code" value="<?= $address['zip_code'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Province</label><input type="text" name="province" value="<?= $address['province'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Region</label><input type="text" name="region" value="<?= $address['region'] ?? '' ?>"></div>
            </div>
        </div>

        <!-- PARENTS / GUARDIANS -->
        <div class="form-section">
            <div class="form-title-container">
                <h2 class="section-title"><i class="fas fa-users"></i> Parents / Legal Guardians</h2>
            </div>
            <p class="note">Please indicate if deceased, separated, OFW, abroad, or outside the province.</p>
            <div class="row">
                <div class="cell w-33"><label>Father's Name</label><input type="text" name="father_name" value="<?= $guardian['father_name'] ?? '' ?>"></div>
                <div class="cell w-33"><label>Occupation</label><input type="text" name="father_occupation" value="<?= $guardian['father_occupation'] ?? '' ?>"></div>
            <div class="cell w-33"><label>Contact Number</label><input type="text" name="father_contact_number" value="<?= $guardian['father_contact_number'] ?? '' ?>"></div>
            </div>
            <div class="row">
                <div class="cell w-33"><label>Mother's Name</label><input type="text" name="mother_name" value="<?= $guardian['mother_name'] ?? '' ?>"></div>
                <div class="cell w-33"><label>Occupation</label><input type="text" name="mother_occupation" value="<?= $guardian['mother_occupation'] ?? '' ?>"></div>
                <div class="cell w-33"><label>Contact No.</label><input type="text" name="mother_contact_number" value="<?= $guardian['mother_contact_number'] ?? '' ?>"></div>
            </div>
            <div class="row">
                <div class="cell w-33"><label>Guardian's Name</label><input type="text" name="guardian_name" value="<?= $guardian['guardian_name'] ?? '' ?>"></div>
                <div class="cell w-33"><label>Occupation</label><input type="text" name="guardian_occupation" value="<?= $guardian['guardian_occupation'] ?? '' ?>"></div>
                <div class="cell w-33"><label>Contact Number</label><input type="text" name="guardian_contact_number" value="<?= $guardian['guardian_contact_number'] ?? '' ?>"></div>
            </div>
        </div>

        <!-- EMERGENCY CONTACT -->
        <div class="form-section">
            <div class="form-title-container">
                <h2 class="section-title"><i class="fas fa-exclamation-triangle"></i> In Case of Emergency (Important!)</h2>
            </div>
            <div class="row">
                <div class="cell w-33"><label>Name</label><input type="text" name="emergency_name" value="<?= $emergency['contact_name'] ?? '' ?>"></div>
                <div class="cell w-33"><label>Contact Number</label><input type="text" name="emergency_contact_number" value="<?= $emergency['contact_number'] ?? '' ?>"></div>
                <div class="cell w-33"><label>Relationship</label><input type="text" name="emergency_relationship" value="<?= $emergency['relationship'] ?? '' ?>"></div>
            </div>
            <div class="row">
                <div class="cell w-100"><label>Address</label><input type="text" name="emergency_contact_address" value="<?= $emergency['contact_address'] ?? '' ?>"></div>
            </div>
        </div>

        <!-- WORK EXPERIENCE -->
        <div class="form-section">
            <div class="form-title-container">
                <h2 class="section-title"><i class="fas fa-briefcase"></i> Work Experience (Latest)</h2>
            </div>
            <?php for ($i=1; $i<=3; $i++): ?>
            <div class="row">
                <div class="cell w-25"><label>Organization / Business Name</label><input type="text" name="we_org_<?= $i ?>" value="<?= $work_experience[$i-1]['organization_business_name'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Owner / Proprietor</label><input type="text" name="we_owner_<?= $i ?>" value="<?= $work_experience[$i-1]['owner_proprietor_name'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Position</label><input type="text" name="we_position_<?= $i ?>" value="<?= $work_experience[$i-1]['position'] ?? '' ?>"></div>
                <div class="cell w-25"><label>Inclusive Dates (From - To)</label><input type="text" name="we_dates_<?= $i ?>" value="<?= ($work_experience[$i-1]['start_date'] ?? '') . ' - ' . ($work_experience[$i-1]['end_date'] ?? '') ?>"></div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- TALENTS & SKILLS -->
        <div class="form-section">
            <div class="form-title-container">
                <h2 class="section-title"><i class="fas fa-star"></i> Talents and Skills</h2>
            </div>
            <div class="row">
                <div class="cell w-25"><label>Skill 1</label><input type="text" name="skill_1" value="<?= $skills[0] ?? '' ?>"></div>
                <div class="cell w-25"><label>Skill 2</label><input type="text" name="skill_2" value="<?= $skills[1] ?? '' ?>"></div>
                <div class="cell w-25"><label>Skill 3</label><input type="text" name="skill_3" value="<?= $skills[2] ?? '' ?>"></div>
                <div class="cell w-25"><label>Skill 4</label><input type="text" name="skill_4" value="<?= $skills[3] ?? '' ?>"></div>
            </div>
            <div class="row">
                <div class="cell w-25"><label>Skill 5</label><input type="text" name="skill_5" value="<?= $skills[4] ?? '' ?>"></div>
                <div class="cell w-25"><label>Skill 6</label><input type="text" name="skill_6" value="<?= $skills[5] ?? '' ?>"></div>
                <div class="cell w-25"><label>Skill 7</label><input type="text" name="skill_7" value="<?= $skills[6] ?? '' ?>"></div>
                <div class="cell w-25"><label>Skill 8</label><input type="text" name="skill_8" value="<?= $skills[7] ?? '' ?>"></div>
            </div>
        </div>

        <!-- GRADUATION MESSAGE -->
        <div class="form-section">
            <div class="title-container">
                <h2 class="section-title"><i class="fas fa-graduation-cap"></i> Graduation Message</h2>
            </div>
            <div class="row">
                <div class="cell w-100">
                    <label>Graduation Message</label>
                    <textarea class="message-box" name="graduation_message" placeholder="Share your graduation message..."><?= $message['message'] ?? '' ?></textarea>
                </div>
            </div>
        </div>

        <!-- CERTIFICATION + SIGNATURE -->
        <div class="form-section">
            <div class="form-title-container">
                <h2 class="section-title"><i class="fas fa-signature"></i> Certification and Signature</h2>
            </div>
            <p class="cert-text" style="text-align: center; font-style: italic; margin: 20px 0; font-size: 16px; color: #555;">
                I hereby certify that all information I have provided is accurate, to the best of my knowledge.
            </p>
            <div class="row">
                <div class="cell w-25">
                    <label for="certify" class="certify-label">
                        <input type="checkbox" id="certify" name="certify" required>
                        <strong>I certify that the information provided is true and correct.</strong>
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="cell w-50">
                    <label>Date Accomplished</label>
                    <input type="date" name="date_accomplished">
                </div>
                <div class="cell w-50">
                    <label>Control Number</label>
                    <input type="text" name="control_number">
                </div>
            </div>
        </div>

        <button type="submit" class="submit-btn"><i class="fas fa-save"></i> UPDATE PROFILE</button>

    </form>

    <!-- Download PDS Button -->
    <div class="download-section">
        <a href="../controller/ProfileController.php?action=download_pds" class="download-btn"><i class="fas fa-download"></i> DOWNLOAD PDS</a>
        <a href="../assets/form/Alumni Registration Form.docx" class="download-btn" download><i class="fas fa-download"></i> DOWNLOAD REGISTRATION FORM</a>
    </div>

</div>

<!-- FOOTER -->
<footer class="profile-footer">
    <p>Punta, Panaon, Misamis Occidental, Philippines 7205 | 
    <a href="https://www.facebook.com/ustp.alumni.panaon" target="_blank">Facebook</a> | 
    <a href="mailto:ustpaf-panaon@ustp.edu.ph">ustpaf-panaon@ustp.edu.ph</a></p>
</footer>

</body>
</html>
