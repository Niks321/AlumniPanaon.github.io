<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $balance = $_POST['balance'] ?? null;

    if (!$user_id || $balance === null) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    // Validate balance
    if (!is_numeric($balance) || $balance < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid balance value']);
        exit;
    }

    // Update balance in database
    $stmt = $conn->prepare("UPDATE users SET balance = ? WHERE user_id = ? AND role = 'alumni'");
    $stmt->bind_param('di', $balance, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Balance updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update balance']);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
