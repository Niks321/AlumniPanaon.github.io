<?php
session_start();

// Protect page: redirect to login if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
require_once '../classes/UpcomingEvent.php';
require_once '../classes/Announcement.php';

$db = new Database();
$conn = $db->conn;

$eventObj = new UpcomingEvent($conn);
$announcementObj = new Announcement($conn);

// Fetch events from database
$eventsResult = $eventObj->getAllEvents();
$events = [];
if ($eventsResult->num_rows > 0) {
    while ($row = $eventsResult->fetch_assoc()) {
        $events[] = new UpcomingEvent($conn, $row['title'], $row['date'], $row['description'], 'fa-calendar-alt', $row['location'], $row['organizer'], $row['event_id']);
    }
}

// Fetch announcements from database
$announcementsResult = $announcementObj->getAllAnnouncements();
$announcements = [];
if ($announcementsResult->num_rows > 0) {
    while ($row = $announcementsResult->fetch_assoc()) {
        $announcements[] = new Announcement($conn, $row['title'], $row['date'], $row['description'], $row['announcement_id']);
    }
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$homeLink = $isAdmin ? 'dashboard_admin.php' : 'dashboard_alumni.php';
$manageAlumniLink = $isAdmin ? '<a href="manage_alumni.php">Manage Alumni</a>' : '<a href="profile.php">My Profile</a>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events & Announcements - AlumniPanaon Hub</title>
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

<!-- =========================
     HERO SECTION
========================= -->
<section class="hero">
    <div class="hero-content">
        <h1>Events & Announcements</h1>
        <p>Stay updated with the latest events and announcements from USTP Panaon Campus.</p>
    </div>
</section>

<!-- =========================
     EVENTS SECTION
========================= -->
<section class="events-section">
    <div class="events-content">
        <h2>Upcoming Events</h2>
        <?php if ($isAdmin): ?>
            <div class="admin-form-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0; color: #333;">Add New Event or Announcement</h3>
                <form id="adminForm" action="../controller/EventController.php" method="POST" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: end;">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Type</label>
                        <div style="display: flex; gap: 10px;">
                            <label><input type="radio" name="type" value="event" checked> Event</label>
                            <label><input type="radio" name="type" value="announcement"> Announcement</label>
                        </div>
                    </div>
                    <input type="hidden" name="action" id="actionInput" value="add_event">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Title</label>
                        <input type="text" name="title" placeholder="Title" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Date</label>
                        <input type="date" name="date" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    <div id="locationField" style="flex: 1; min-width: 200px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Location</label>
                        <input type="text" name="location" placeholder="Location" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    <div id="organizerField" style="flex: 1; min-width: 200px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Organizer</label>
                        <input type="text" name="organizer" placeholder="Organizer" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    </div>
                    <div style="flex: 2; min-width: 300px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Description</label>
                        <textarea name="description" placeholder="Description" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"></textarea>
                    </div>
                    <button type="submit" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;">Add</button>
                </form>
            </div>
            <script>
                document.querySelectorAll('input[name="type"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        const isEvent = this.value === 'event';
                        document.getElementById('locationField').style.display = isEvent ? 'block' : 'none';
                        document.getElementById('organizerField').style.display = isEvent ? 'block' : 'none';
                        document.getElementById('actionInput').value = isEvent ? 'add_event' : 'add_announcement';
                        document.querySelector('input[name="location"]').required = isEvent;
                        document.querySelector('input[name="organizer"]').required = isEvent;
                    });
                });
                // Trigger on load for default
                document.querySelector('input[name="type"]:checked').dispatchEvent(new Event('change'));
            </script>
        <?php endif; ?>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
                <div class="event-card">
                    <?php echo $event->display(); ?>
                    <?php if ($isAdmin): ?>
                        <a href="../controller/EventController.php?action=delete_event&id=<?php echo $event->id; ?>" onclick="return confirm('Are you sure you want to delete this event?')" style="display: inline-block; margin-top: 10px; color: #dc3545; text-decoration: none; font-weight: 500;">Delete</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =========================
     ANNOUNCEMENTS SECTION
========================= -->
<section class="announcements-section">
    <div class="announcements-content">
        <h2>Latest Announcements</h2>

        <div class="announcements-list">
            <?php foreach ($announcements as $announcement): ?>
                <div class="announcement-item">
                    <?php echo $announcement->display(); ?>
                    <?php if ($isAdmin): ?>
                        <a href="../controller/EventController.php?action=delete_announcement&id=<?php echo $announcement->id; ?>" onclick="return confirm('Are you sure you want to delete this announcement?')">Delete</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =========================
     FOOTER
========================= -->
<footer>
    Punta, Panaon, Misamis Occidental, Philippines 7205 |
    <a href="https://www.facebook.com/ustp.alumni.panaon" target="_blank">Facebook</a> |
    <a href="mailto:ustp.panaon@ustp.edu.ph">ustpaf.panaon@ustp.edu.ph</a>
</footer>

</body>
</html>
