<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $agree_terms = isset($_POST['agree_terms']);
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($gender)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!$agree_terms) {
        $error = 'You must agree to the terms and conditions';
    } else {
        $result = register_user($username, $email, $full_name, $password, $gender);
        if ($result['success']) {
            $success = $result['message'] . '. Redirecting to sign in...';
            header('refresh:2;url=signin.php');
        } else {
            $error = $result['message'];
        }
    }
}

$page_title = 'Sign Up';
include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>Create Your Account</h2>
            <p>Start tracking your learning progress today!</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Username
                </label>
                <input type="text" id="username" name="username" required 
                       placeholder="Choose a username"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" id="email" name="email" required 
                       placeholder="Enter your email"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="full_name">
                    <i class="fas fa-id-card"></i> Full Name
                </label>
                <input type="text" id="full_name" name="full_name" 
                       placeholder="Enter your full name (optional)"
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-venus-mars"></i> Gender
                </label>
                <div class="radio-group">
                    <label class="radio-label">
                        <input type="radio" name="gender" value="male" required 
                               <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'male') ? 'checked' : ''; ?>>
                        <span>Male</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="gender" value="female" required
                               <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'female') ? 'checked' : ''; ?>>
                        <span>Female</span>
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="gender" value="other" required
                               <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'other') ? 'checked' : ''; ?>>
                        <span>Other</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" id="password" name="password" required 
                       placeholder="Create a password (min 6 characters)">
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <i class="fas fa-lock"></i> Confirm Password
                </label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Re-enter your password">
            </div>

            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="agree_terms" id="agree_terms" required>
                    <span>I agree to the Terms and Conditions</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="signin.php">Sign In</a></p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

