<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$page_title = 'Dashboard';
include '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome back, <?php echo htmlspecialchars($username); ?>! 👋</h1>
        <p class="dashboard-subtitle">Here's your learning progress overview</p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

