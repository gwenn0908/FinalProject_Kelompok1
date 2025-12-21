<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Get current user data
$stmt = $conn->prepare("SELECT username, email, full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    
    if (!empty($full_name) && !empty($email)) {
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Email already taken by another user.";
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $full_name, $email, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "Profile updated successfully!";
                $user['full_name'] = $full_name;
                $user['email'] = $email;
            } else {
                $error_message = "Failed to update profile.";
            }
        }
        $stmt->close();
    } else {
        $error_message = "All fields are required.";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (password_verify($current_password, $result['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Password changed successfully!";
                    } else {
                        $error_message = "Failed to change password.";
                    }
                } else {
                    $error_message = "New password must be at least 6 characters.";
                }
            } else {
                $error_message = "New passwords do not match.";
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
        $stmt->close();
    } else {
        $error_message = "All password fields are required.";
    }
}

$page_title = 'Settings';
include '../includes/header.php';
?>

<div class="settings-container">
    <div class="page-header">
        <h1><i class="fas fa-cog"></i> Settings</h1>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="settings-grid">
        <!-- Profile Settings -->
        <div class="settings-card">
            <div class="card-header">
                <h2><i class="fas fa-user"></i> Profile Information</h2>
            </div>
            <div class="card-content">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        <small style="color: var(--text-tertiary); font-size: 13px; margin-top: 4px; display: block;">Username cannot be changed</small>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Settings -->
        <div class="settings-card">
            <div class="card-header">
                <h2><i class="fas fa-lock"></i> Change Password</h2>
            </div>
            <div class="card-content">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small style="color: var(--text-tertiary); font-size: 13px; margin-top: 4px; display: block;">Minimum 6 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Information -->
        <div class="settings-card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Account Information</h2>
            </div>
            <div class="card-content">
                <div class="info-group">
                    <div class="info-label">User ID</div>
                    <div class="info-value">#<?php echo $user_id; ?></div>
                </div>
                <div class="info-group">
                    <div class="info-label">Username</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div class="info-group">
                    <div class="info-label">Account Status</div>
                    <div class="info-value">
                        <span class="badge badge-completed">Active</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preferences -->
        <div class="settings-card">
            <div class="card-header">
                <h2><i class="fas fa-palette"></i> Preferences</h2>
            </div>
            <div class="card-content">
                <div class="preference-item">
                    <div class="preference-info">
                        <h4>Email Notifications</h4>
                        <p>Receive email reminders for upcoming tasks and events</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" checked disabled>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="preference-item">
                    <div class="preference-info">
                        <h4>Dark Mode</h4>
                        <p>Switch to dark theme</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" disabled>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <small style="color: var(--text-tertiary); font-size: 13px; display: block; margin-top: 16px;">
                    <i class="fas fa-info-circle"></i> Advanced preferences coming soon
                </small>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

