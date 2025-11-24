<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

function sanitize($data) {
    return htmlspecialchars(trim($data));
}

function handleFileUpload($file, $user_id) {
    if ($file['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($file['type'], $allowed_types)) {
            $upload_dir = '../assets/uploads/profile_pictures/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $filename = $user_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filepath = $upload_dir . $filename;
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                return $filepath;
            }
        }
    }
    return null;
}

function upsert($conn, $table, $data, $user_id) {
    $columns = array_keys($data);
    $placeholders = implode('=?, ', $columns) . '=?';

    $check = $conn->prepare("SELECT * FROM $table WHERE user_id=?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();
    $check->close();

    if ($result) {
        $sql = "UPDATE $table SET $placeholders WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $params = array_values($data);
        $params[] = $user_id;
        $stmt->bind_param(str_repeat('s', count($data)) . 'i', ...$params);
        $stmt->execute();
        $stmt->close();
    } else {
        $cols = implode(',', $columns);
        $vals = implode(',', array_fill(0, count($columns), '?'));
        $stmt = $conn->prepare("INSERT INTO $table ($cols, user_id) VALUES ($vals, ?)");
        $params = array_values($data);
        $params[] = $user_id;
        $stmt->bind_param(str_repeat('s', count($data)) . 'i', ...$params);
        $stmt->execute();
        $stmt->close();
    }
}

function fetch_data($conn, $table, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and upsert
$pds_data = [
    'first_name'=>sanitize($_POST['first_name']),
    'last_name'=>sanitize($_POST['last_name']),
    'middle_name'=>sanitize($_POST['middle_name']),
    'extension_name'=>sanitize($_POST['extension_name']),
    'nick_name'=>sanitize($_POST['nick_name']),
    'age'=>sanitize($_POST['age']),
    'gender'=>sanitize($_POST['gender']),
    'civil_status'=>sanitize($_POST['civil_status']),
    'religion'=>sanitize($_POST['religion']),
    'birth_date'=>sanitize($_POST['birth_date']),
    'blood_type'=>sanitize($_POST['blood_type']),
    'course'=>sanitize($_POST['course']),
    'course_code'=>sanitize($_POST['course_code']),
    'university'=>sanitize($_POST['university']),
    'year_graduated'=>sanitize($_POST['year_graduated']),
    'phone_number'=>sanitize($_POST['phone_number']),
    'email'=>sanitize($_POST['email'])
];

$address_data = [
    'house_number'=>sanitize($_POST['house_number']),
    'street'=>'',
    'barangay'=>sanitize($_POST['barangay']),
    'city_municipal'=>sanitize($_POST['city_municipal']),
    'province'=>sanitize($_POST['province']),
    'zip_code'=>sanitize($_POST['zip_code']),
    'country'=>'Philippines',
    'region'=>sanitize($_POST['region'])
];

$guardian_data = [
    'father_name'=>sanitize($_POST['father_name']),
    'father_occupation'=>sanitize($_POST['father_occupation']),
    'father_contact_number'=>sanitize($_POST['father_contact_number']),
    'mother_name'=>sanitize($_POST['mother_name']),
    'mother_occupation'=>sanitize($_POST['mother_occupation']),
    'mother_contact_number'=>sanitize($_POST['mother_contact_number']),
    'guardian_name'=>sanitize($_POST['guardian_name']),
    'guardian_occupation'=>sanitize($_POST['guardian_occupation']),
    'guardian_contact_number'=>sanitize($_POST['guardian_contact_number'])
];

$emergency_data = [
    'contact_name'=>sanitize($_POST['emergency_name']),
    'relationship'=>sanitize($_POST['emergency_relationship']),
    'contact_number'=>sanitize($_POST['emergency_contact_number']),
    'contact_address'=>sanitize($_POST['emergency_contact_address'])
];

$graduation_data = ['message'=>sanitize($_POST['graduation_message'])];

$additional_data = [
    'user_signature_path' => sanitize($_POST['user_signature_path'] ?? ''),
    'date_accomplished' => !empty($_POST['date_accomplished']) ? sanitize($_POST['date_accomplished']) : date('Y-m-d'),
    'control_number' => !empty($_POST['control_number']) ? sanitize($_POST['control_number']) : 'N/A'
];

// Handle profile picture upload
$profile_picture_path = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] != UPLOAD_ERR_NO_FILE) {
    $profile_picture_path = handleFileUpload($_FILES['profile_picture'], $user_id);
    if ($profile_picture_path) {
        $stmt = $conn->prepare("UPDATE users SET profile_picture=? WHERE user_id=?");
        $stmt->bind_param("si", $profile_picture_path, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle work experience
$conn->query("DELETE FROM WORK_EXPERIENCE WHERE user_id=$user_id"); // Clear existing work experiences
for ($i = 1; $i <= 3; $i++) {
    if (!empty($_POST["we_org_$i"]) || !empty($_POST["we_position_$i"]) || !empty($_POST["we_dates_$i"])) {
        $dates = explode(' - ', sanitize($_POST["we_dates_$i"] ?? ''));
        $start_date = $dates[0] ?? '';
        $end_date = $dates[1] ?? '';
        $we_data = [
            'organization_business_name' => sanitize($_POST["we_org_$i"] ?? ''),
            'owner_proprietor_name' => sanitize($_POST["we_owner_$i"] ?? ''),
            'position' => sanitize($_POST["we_position_$i"] ?? ''),
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        $cols = implode(',', array_keys($we_data));
        $vals = implode(',', array_fill(0, count($we_data), '?'));
        $stmt = $conn->prepare("INSERT INTO WORK_EXPERIENCE ($cols, user_id) VALUES ($vals, ?)");
        $params = array_values($we_data);
        $params[] = $user_id;
        $stmt->bind_param(str_repeat('s', count($we_data)) . 'i', ...$params);
        $stmt->execute();
        $stmt->close();
    }
}

// Handle skills
$conn->query("DELETE FROM USER_SKILLS WHERE user_id=$user_id"); // Clear existing skills
for ($i = 1; $i <= 8; $i++) {
    if (!empty($_POST["skill_$i"])) {
        $skill_data = ['skill_name' => sanitize($_POST["skill_$i"])];
        $cols = implode(',', array_keys($skill_data));
        $vals = implode(',', array_fill(0, count($skill_data), '?'));
        $stmt = $conn->prepare("INSERT INTO USER_SKILLS ($cols, user_id) VALUES ($vals, ?)");
        $params = array_values($skill_data);
        $params[] = $user_id;
        $stmt->bind_param(str_repeat('s', count($skill_data)) . 'i', ...$params);
        $stmt->execute();
        $stmt->close();
    }
}

// Optional: Transaction to ensure atomic save
$conn->begin_transaction();
try {
    upsert($conn, 'PDS_CONTACT', $pds_data, $user_id);
    upsert($conn, 'HOME_ADDRESS', $address_data, $user_id);
    upsert($conn, 'PARENT_LEGAL_GUARDIAN', $guardian_data, $user_id);
    upsert($conn, 'EMERGENCY_CONTACT', $emergency_data, $user_id);
    upsert($conn, 'GRADUATION_MESSAGE', $graduation_data, $user_id);
    upsert($conn, 'ADDITIONAL_INFORMATION', $additional_data, $user_id);
    $conn->commit();
} catch(Exception $e) {
    $conn->rollback();
    die("Error saving profile: ".$e->getMessage());
}
}

if (isset($_GET['action']) && $_GET['action'] == 'download_pds') {
    require_once '../lib/fpdf/fpdf.php'; // Use the local FPDF library

    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $_SESSION['user_id'];

    // Fetch all data
    $user = fetch_data($conn, 'users', $user_id);
    $pds = fetch_data($conn, 'PDS_CONTACT', $user_id);
    $address = fetch_data($conn, 'HOME_ADDRESS', $user_id);
    $guardian = fetch_data($conn, 'PARENT_LEGAL_GUARDIAN', $user_id);
    $emergency = fetch_data($conn, 'EMERGENCY_CONTACT', $user_id);
    $message = fetch_data($conn, 'GRADUATION_MESSAGE', $user_id);

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

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetMargins(15, 15, 15);

    // Header with background color
    $pdf->SetFillColor(0, 102, 204); // Blue background
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 15, 'University of Science and Technology of Southern Philippines - Panaon', 0, 1, 'C', true);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 12, 'USTP ALUMNI ASSOCIATION PANAON.', 0, 1, 'C', true);
    $pdf->Ln(5);

    // Title
    $pdf->SetFillColor(255, 255, 255); // White background
    $pdf->SetTextColor(0, 0, 0); // Black text
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 15, 'Personal Data Sheet - Alumni Member', 0, 1, 'C');
    $pdf->Ln(10);

    // Profile Picture
    if (!empty($user['profile_picture']) && file_exists('../' . $user['profile_picture'])) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Profile Picture', 0, 1, 'L');
        $pdf->Image('../' . $user['profile_picture'], 15, $pdf->GetY(), 30, 30);
        $pdf->Ln(35);
    }

    // Personal Data and Contact Details
    $pdf->SetFillColor(173, 216, 230); // Light blue
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 12, 'Personal Data and Contact Details', 0, 1, 'L', true);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 11);

    // Name Table (45mm each, total 180mm)
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(45, 10, 'Last Name', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'First Name', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Middle Name', 1, 0, 'C', true);
    $pdf->Cell(45, 10, 'Extension', 1, 1, 'C', true);
    $pdf->Cell(45, 10, $pds['last_name'] ?? '', 1, 0, 'C');
    $pdf->Cell(45, 10, $pds['first_name'] ?? '', 1, 0, 'C');
    $pdf->Cell(45, 10, $pds['middle_name'] ?? '', 1, 0, 'C');
    $pdf->Cell(45, 10, $pds['extension_name'] ?? '', 1, 1, 'C');
    $pdf->Ln(5);

    // Personal Details Table (45mm each, total 180mm)
    $pdf->SetFillColor(173, 216, 230);
    $pdf->Cell(45, 8, 'Nickname', 1, 0, 'L', true);
    $pdf->Cell(45, 8, 'Age', 1, 0, 'L', true);
    $pdf->Cell(45, 8, 'Gender', 1, 0, 'L', true);
    $pdf->Cell(45, 8, 'Civil Status', 1, 1, 'L', true);
    $pdf->Cell(45, 8, $pds['nick_name'] ?? '', 1, 0, 'L');
    $pdf->Cell(45, 8, $pds['age'] ?? '', 1, 0, 'L');
    $pdf->Cell(45, 8, $pds['gender'] ?? '', 1, 0, 'L');
    $pdf->Cell(45, 8, $pds['civil_status'] ?? '', 1, 1, 'L');

    $pdf->Cell(60, 8, 'Religion', 1, 0, 'L', true);
    $pdf->Cell(60, 8, 'Birthday', 1, 0, 'L', true);
    $pdf->Cell(60, 8, 'Blood Type', 1, 1, 'L', true);
    $pdf->Cell(60, 8, $pds['religion'] ?? '', 1, 0, 'L');
    $pdf->Cell(60, 8, $pds['birth_date'] ?? '', 1, 0, 'L');
    $pdf->Cell(60, 8, $pds['blood_type'] ?? '', 1, 1, 'L');

    $pdf->Cell(90, 8, 'Course', 1, 0, 'L', true);
    $pdf->Cell(90, 8, 'Course Code', 1, 1, 'L', true);
    $pdf->Cell(90, 8, $pds['course'] ?? '', 1, 0, 'L');
    $pdf->Cell(90, 8, $pds['course_code'] ?? '', 1, 1, 'L');

    $pdf->Cell(90, 8, 'University', 1, 0, 'L', true);
    $pdf->Cell(90, 8, 'Year Graduated', 1, 1, 'L', true);
    $pdf->Cell(90, 8, $pds['university'] ?? '', 1, 0, 'L');
    $pdf->Cell(90, 8, $pds['year_graduated'] ?? '', 1, 1, 'L');

    $pdf->Cell(90, 8, 'Phone Number', 1, 0, 'L', true);
    $pdf->Cell(90, 8, 'Email', 1, 1, 'L', true);
    $pdf->Cell(90, 8, $pds['phone_number'] ?? '', 1, 0, 'L');
    $pdf->Cell(90, 8, $pds['email'] ?? '', 1, 1, 'L');
    $pdf->Ln(10);

    // Home Address
    $pdf->SetFillColor(173, 216, 230);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 12, 'Home Address', 0, 1, 'L', true);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(90, 8, 'House/Street', 1, 0, 'L', true);
    $pdf->Cell(90, 8, 'Barangay', 1, 1, 'L', true);
    $pdf->Cell(90, 8, $address['house_number'] ?? '', 1, 0, 'L');
    $pdf->Cell(90, 8, $address['barangay'] ?? '', 1, 1, 'L');

    $pdf->Cell(60, 8, 'City/Municipality', 1, 0, 'L', true);
    $pdf->Cell(30, 8, 'Zip Code', 1, 0, 'L', true);
    $pdf->Cell(90, 8, 'Province', 1, 1, 'L', true);
    $pdf->Cell(60, 8, $address['city_municipal'] ?? '', 1, 0, 'L');
    $pdf->Cell(30, 8, $address['zip_code'] ?? '', 1, 0, 'L');
    $pdf->Cell(90, 8, $address['province'] ?? '', 1, 1, 'L');

    $pdf->Cell(90, 8, 'Region', 1, 0, 'L', true);
    $pdf->Cell(90, 8, 'Country', 1, 1, 'L', true);
    $pdf->Cell(90, 8, $address['region'] ?? '', 1, 0, 'L');
    $pdf->Cell(90, 8, $address['country'] ?? 'Philippines', 1, 1, 'L');
    $pdf->Ln(10);

    // Parents / Legal Guardians
    $pdf->SetFillColor(173, 216, 230);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 12, 'Parents / Legal Guardians', 0, 1, 'L', true);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 8, 'Please indicate if deceased, separated, OFW/Working abroad or outside the province', 0, 1);
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(60, 8, 'Father\'s Name', 1, 0, 'L', true);
    $pdf->Cell(60, 8, 'Occupation', 1, 0, 'L', true);
    $pdf->Cell(60, 8, 'Contact No.', 1, 1, 'L', true);
    $pdf->Cell(60, 8, $guardian['father_name'] ?? '', 1, 0, 'L');
    $pdf->Cell(60, 8, $guardian['father_occupation'] ?? '', 1, 0, 'L');
    $pdf->Cell(60, 8, $guardian['father_contact_number'] ?? '', 1, 1, 'L');

    $pdf->Cell(60, 8, 'Mother\'s Name', 1, 0, 'L', true);
    $pdf->Cell(60, 8, 'Occupation', 1, 0, 'L', true);
    $pdf->Cell(60, 8, 'Contact No.', 1, 1, 'L', true);
    $pdf->Cell(60, 8, $guardian['mother_name'] ?? '', 1, 0, 'L');
    $pdf->Cell(60, 8, $guardian['mother_occupation'] ?? '', 1, 0, 'L');
    $pdf->Cell(60, 8, $guardian['mother_contact_number'] ?? '', 1, 1, 'L');

    $pdf->Cell(60, 8, 'Guardian\'s Name', 1, 0, 'L', true);
    $pdf->Cell(60, 8, 'Occupation', 1, 0, 'L', true);
    $pdf->Cell(60, 8, 'Contact No.', 1, 1, 'L', true);
    $pdf->Cell(60, 8, $guardian['guardian_name'] ?? '', 1, 0, 'L');
    $pdf->Cell(60, 8, $guardian['guardian_occupation'] ?? '', 1, 0, 'L');
    $pdf->Cell(60, 8, $guardian['guardian_contact_number'] ?? '', 1, 1, 'L');
    $pdf->Ln(10);

    // Emergency Contact
    $pdf->SetFillColor(173, 216, 230);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 12, 'Emergency Contact', 0, 1, 'L', true);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(60, 8, 'Name', 1, 0, 'L', true);
    $pdf->Cell(60, 8, 'Relationship', 1, 0, 'L', true);
    $pdf->Cell(60, 8, 'Contact No.', 1, 1, 'L', true);
    $pdf->Cell(60, 8, $emergency['contact_name'] ?? '', 1, 0, 'L');
    $pdf->Cell(60, 8, $emergency['relationship'] ?? '', 1, 0, 'L');
    $pdf->Cell(60, 8, $emergency['contact_number'] ?? '', 1, 1, 'L');
    $pdf->Cell(180, 8, 'Address', 1, 1, 'L', true);
    $pdf->MultiCell(180, 8, $emergency['contact_address'] ?? '', 1, 'L');
    $pdf->Ln(10);

    // Work Experience
    $pdf->SetFillColor(173, 216, 230);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 12, 'Work Experience', 0, 1, 'L', true);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(45, 8, 'Organization/Business', 1, 0, 'C', true);
    $pdf->Cell(45, 8, 'Owner/Proprietor', 1, 0, 'C', true);
    $pdf->Cell(45, 8, 'Position', 1, 0, 'C', true);
    $pdf->Cell(45, 8, 'Dates (from - to)', 1, 1, 'C', true);
    foreach ($work_experience as $we) {
        $dates = ($we['start_date'] ?? '') . ' - ' . ($we['end_date'] ?? '');
        $pdf->Cell(45, 8, $we['organization_business_name'] ?? '', 1, 0, 'L');
        $pdf->Cell(45, 8, $we['owner_proprietor_name'] ?? '', 1, 0, 'L');
        $pdf->Cell(45, 8, $we['position'] ?? '', 1, 0, 'L');
        $pdf->Cell(45, 8, $dates, 1, 1, 'L');
    }
    $pdf->Ln(10);

    // Talents and Skills
    $pdf->SetFillColor(173, 216, 230);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 12, 'Talents and Skills', 0, 1, 'L', true);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 8, 'List all of your expertise', 0, 1);
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetFillColor(255, 255, 255);
    for ($i = 0; $i < 8; $i++) {
        $skill = $skills[$i] ?? '';
        $pdf->Cell(20, 8, ($i + 1) . '.', 1, 0, 'C', true);
        $pdf->Cell(160, 8, $skill, 1, 1, 'L');
    }
    $pdf->Ln(10);

    // Graduation Message
    $pdf->SetFillColor(173, 216, 230);
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 12, 'Graduation Message', 0, 1, 'L', true);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 8, 'Motto, Sayings, Jokes, Principles in life, Short message to inspire others.', 0, 1);
    $pdf->Ln(2);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(180, 8, $message['message'] ?? '', 1, 'L');
    $pdf->Ln(10);

    // Certification
    $pdf->SetFillColor(173, 216, 230);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 12, 'Certification', 0, 1, 'L', true);
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->MultiCell(180, 8, 'I hereby certify that all information I have provided is accurate, to the best of my knowledge.', 0, 'L');
    $pdf->Ln(5);
    $pdf->Cell(90, 8, 'Date Accomplished: ' . date('m/d/Y'), 0, 0, 'L');
    $pdf->Cell(90, 8, 'Control Number: ' . ($additional_data['control_number'] ?? 'N/A'), 0, 1, 'L');

    // Output PDF
    $pdf->Output('D', 'PDS_' . $user_id . '.pdf');
    exit;
}

header("Location: ../view/profile.php?success=1");
exit;
