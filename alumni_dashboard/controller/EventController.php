<?php
session_start();

// Protect page: redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/login.php");
    exit;
}

require_once '../config/database.php';
require_once '../classes/UpcomingEvent.php';
require_once '../classes/Announcement.php';

$db = new Database();
$conn = $db->conn;

$eventObj = new UpcomingEvent($conn);
$announcementObj = new Announcement($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_event') {
        $title = $_POST['title'] ?? '';
        $date = $_POST['date'] ?? '';
        $location = $_POST['location'] ?? '';
        $organizer = $_POST['organizer'] ?? '';
        $description = $_POST['description'] ?? '';

        if ($eventObj->addEvent($title, $date, $description, $location, $organizer)) {
            header("Location: ../view/events.php");
            exit;
        } else {
            echo "Error adding event.";
        }
    } elseif ($action === 'add_announcement') {
        $title = $_POST['title'] ?? '';
        $date = $_POST['date'] ?? '';
        $description = $_POST['description'] ?? '';

        if ($announcementObj->addAnnouncement($title, $date, $description)) {
            header("Location: ../view/events.php");
            exit;
        } else {
            echo "Error adding announcement.";
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'delete_event') {
        $id = $_GET['id'] ?? '';
        if ($eventObj->deleteEvent($id)) {
            header("Location: ../view/events.php");
            exit;
        } else {
            echo "Error deleting event.";
        }
    } elseif ($action === 'delete_announcement') {
        $id = $_GET['id'] ?? '';
        if ($announcementObj->deleteAnnouncement($id)) {
            header("Location: ../view/events.php");
            exit;
        } else {
            echo "Error deleting announcement.";
        }
    }
}
?>
