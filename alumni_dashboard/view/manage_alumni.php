<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin'){
    header("Location: login.php");
    exit;
}

require_once '../config/config.php';

// Display message if set
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Fetch all alumni users with detailed info, ensuring no duplicates
$stmt = $conn->prepare("SELECT DISTINCT u.user_id, u.balance, p.first_name, p.last_name, p.middle_name, p.email FROM users u LEFT JOIN pds_contact p ON u.user_id = p.user_id WHERE u.role = 'alumni'");
$stmt->execute();
$result = $stmt->get_result();
$alumni = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Alumni - AlumniPanaon Hub</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
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
        <h1>Manage Alumni</h1>
        <p>View and manage alumni profiles and download PDS.</p>
    </div>
</section>

<!-- =========================
     ALUMNI LIST SECTION
========================= -->
<section class="alumni-list-section">
    <div class="container">
        <h1>ALUMNI LIST</h1>

        <div class="search-container">
            <div class="search-box">
                🔍 <input type="text" placeholder="Search here">
            </div>
        </div>

        <div class="table-wrapper">
            <table class="alumni-table">
                <thead>
                    <tr>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Email</th>
                        <th>Balance Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alumni as $alum): ?>
                        <?php $balance = $alum['balance'] ?? 0.00; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($alum['last_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($alum['first_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($alum['middle_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($alum['email'] ?? 'N/A'); ?></td>
                            <td><?php echo number_format($balance, 1); ?></td>
                            <td class="actions">
                                <a href="view_profile.php?user_id=<?php echo $alum['user_id']; ?>" class="view-btn">VIEW</a>
                                <a href="../controller/ProfileController.php?action=download_pds&user_id=<?php echo $alum['user_id']; ?>" class="download-btn">DOWNLOAD PDS</a>
                                <a href="#" class="update-balance-btn" data-user-id="<?php echo $alum['user_id']; ?>" data-current-balance="<?php echo $balance; ?>">UPDATE BALANCE</a>
                                <a href="../controller/DeleteAlumniController.php?user_id=<?php echo $alum['user_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this alumni?')">DELETE</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Empty rows for spacing -->
                    <tr><td colspan="6" style="height:40px;"></td></tr>
                    <tr><td colspan="6" style="height:40px;"></td></tr>
                    <tr><td colspan="6" style="height:40px;"></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- =========================
     BALANCE UPDATE MODAL
========================= -->
<div id="balanceModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Update Balance</h2>
        <form id="balanceForm">
            <input type="hidden" id="userId" name="user_id">
            <label for="newBalance">New Balance:</label>
            <input type="number" id="newBalance" name="balance" step="0.01" min="0" required>
            <button type="submit">Update Balance</button>
        </form>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-box input');
    const tableRows = document.querySelectorAll('.alumni-table tbody tr');
    const modal = document.getElementById('balanceModal');
    const closeBtn = document.querySelector('.close');
    const balanceForm = document.getElementById('balanceForm');
    const userIdInput = document.getElementById('userId');
    const newBalanceInput = document.getElementById('newBalance');

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();

        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let match = false;

            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(searchTerm)) {
                    match = true;
                }
            });

            if (match || searchTerm === '') {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Modal functionality
    document.querySelectorAll('.update-balance-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const currentBalance = this.getAttribute('data-current-balance');
            userIdInput.value = userId;
            newBalanceInput.value = currentBalance;
            modal.style.display = 'block';
        });
    });

    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Form submission
    balanceForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../controller/update_balance.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Balance updated successfully!');
                location.reload();
            } else {
                alert('Error updating balance: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the balance.');
        });
    });
});
</script>

</body>
</html>
