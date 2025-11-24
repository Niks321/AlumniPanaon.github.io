<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit;
}

require_once '../config/config.php';

// Create contact_content table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS contact_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    address VARCHAR(255) DEFAULT 'Punta, Panaon, Misamis Occidental, Philippines 7205',
    email VARCHAR(100) DEFAULT 'ustp.panaon@ustp.edu.ph',
    phone VARCHAR(20) DEFAULT '+63 123 456 7890',
    facebook VARCHAR(255) DEFAULT 'https://www.facebook.com/ustp.alumni.panaon'
) ENGINE=InnoDB");

// Insert default if not exists
$conn->query("INSERT IGNORE INTO contact_content (id) VALUES (1)");

// Create org_chart table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS org_chart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    display_order INT NOT NULL
) ENGINE=InnoDB");

// Fetch current contact content
$stmt = $conn->prepare("SELECT * FROM contact_content WHERE id = 1");
$stmt->execute();
$content = $stmt->get_result()->fetch_assoc();
$stmt->close();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_contact'])) {
        $address = sanitize($_POST['address']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $facebook = sanitize($_POST['facebook']);

        $stmt = $conn->prepare("UPDATE contact_content SET address=?, email=?, phone=?, facebook=? WHERE id=1");
        $stmt->bind_param("ssss", $address, $email, $phone, $facebook);
        if ($stmt->execute()) {
            $message = "Contact information updated successfully!";
            // Refresh content
            $refresh_stmt = $conn->prepare("SELECT * FROM contact_content WHERE id = 1");
            $refresh_stmt->execute();
            $content = $refresh_stmt->get_result()->fetch_assoc();
            $refresh_stmt->close();
        } else {
            $message = "Error updating contact information.";
        }
        $stmt->close();
    } elseif (isset($_POST['add_position'])) {
        $name = sanitize($_POST['name']);
        $position = sanitize($_POST['position']);
        $display_order = (int)$_POST['display_order'];

        $stmt = $conn->prepare("INSERT INTO org_chart (name, position, display_order) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $position, $display_order);
        if ($stmt->execute()) {
            $message = "Position added successfully!";
        } else {
            $message = "Error adding position.";
        }
        $stmt->close();
    } elseif (isset($_POST['delete_position'])) {
        $id = (int)$_POST['id'];

        $stmt = $conn->prepare("DELETE FROM org_chart WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Position deleted successfully!";
        } else {
            $message = "Error deleting position.";
        }
        $stmt->close();
    }
}

// Fetch org chart for display
$stmt = $conn->prepare("SELECT * FROM org_chart ORDER BY display_order");
$stmt->execute();
$org_chart = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function sanitize($data) {
    return htmlspecialchars(trim($data));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Contact Us - AlumniPanaon Hub</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Link to external JS -->
    <script src="../assets/js/script.js"></script>
    <style>
        .edit-form {
            max-width: 800px;
            margin: 0 auto;
            background: var(--background-color);
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--primary-color);
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .btn-submit {
            background-color: var(--primary-color);
            color: var(--background-color);
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .btn-submit:hover {
            background-color: var(--secondary-color);
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        .success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
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
        <h1>Edit Contact Us</h1>
        <p>Update the contact information displayed on the Contact Us page.</p>
    </div>
</section>

<!-- =========================
     EDIT FORM SECTION
========================= -->
<section class="edit-form-section">
    <div class="container">
        <div class="edit-form">
            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <h3>Contact Information</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($content['address']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($content['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($content['phone']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="facebook">Facebook Link</label>
                    <input type="url" id="facebook" name="facebook" value="<?php echo htmlspecialchars($content['facebook']); ?>" required>
                </div>

                <button type="submit" name="update_contact" class="btn-submit">Update Contact Information</button>
            </form>

            <hr style="margin: 40px 0;">

            <h3>Organization Chart Management</h3>

            <h4>Add New Position</h4>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="position">Position</label>
                    <input type="text" id="position" name="position" required>
                </div>

                <div class="form-group">
                    <label for="display_order">Display Order</label>
                    <input type="number" id="display_order" name="display_order" required>
                </div>

                <button type="submit" name="add_position" class="btn-submit">Add Position</button>
            </form>

            <h4>Current Positions</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 10px; border: 1px solid #ddd;">Name</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Position</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Order</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($org_chart as $person): ?>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($person['name']); ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($person['position']); ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $person['display_order']; ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $person['id']; ?>">
                                    <button type="submit" name="delete_position" class="btn-submit" style="background: #dc3545; padding: 5px 10px; font-size: 12px;" onclick="return confirm('Are you sure you want to delete this position?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
