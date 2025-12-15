<?php
/**
 * Authentication Functions
 * Handles user registration, login, and session management
 */

require_once 'db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Register a new user
 */
function register_user($username, $email, $full_name, $password, $gender) {
    global $conn;
    
    // Sanitize inputs
    $username = sanitize_input($username);
    $email = sanitize_input($email);
    $full_name = !empty($full_name) ? sanitize_input($full_name) : null;
    $gender = sanitize_input($gender);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    // Validate password strength
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
    }
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Username already exists'];
    }
    $stmt->close();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return ['success' => false, 'message' => 'Email already registered'];
    }
    $stmt->close();
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, password, gender) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $full_name, $hashed_password, $gender);
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Registration successful'];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

/**
 * Login user
 */
function login_user($username, $password) {
    global $conn;
    
    // Sanitize input
    $username = sanitize_input($username);
    
    // Get user from database
    $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            
            // Update last login
            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $update_stmt->bind_param("i", $user['user_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            $stmt->close();
            return ['success' => true, 'message' => 'Login successful'];
        }
    }
    
    $stmt->close();
    return ['success' => false, 'message' => 'Invalid username or password'];
}

/**
 * Logout user
 */
function logout_user() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit();
}
?>

