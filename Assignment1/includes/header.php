<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dynamic base URL calculation
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_url = $protocol . '://' . $host . $script_dir;

// Remove /views if present (for pages inside views folder)
$base_url = preg_replace('#/views$#', '', $base_url);

// Clean up double slashes
$base_url = rtrim($base_url, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | EduTrack' : 'EduTrack - Learning Productivity System'; ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
    <?php if (is_logged_in()): ?>
        <!-- Top Header -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo">
                    <a href="dashboard.php">
                    <i class="fas fa-graduation-cap"></i> EduTrack
                </a>
            </div>
                    <div class="user-menu">
                    <span class="username">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </div>
        </header>

        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <div class="sidebar-main">
                    <a href="dashboard.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="todos.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'todos.php') ? 'active' : ''; ?>">
                        <i class="fas fa-tasks"></i>
                        <span>To-Do</span>
                    </a>
                </div>
                
                <div class="sidebar-footer">
                    <a href="settings.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <a href="logout.php" class="sidebar-link logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                    </div>
            </nav>
        </aside>

        <!-- Main Content with Sidebar -->
        <main class="main-content with-sidebar">
                <?php else: ?>
        <!-- Header for Non-Logged In Users -->
        <header class="main-header">
            <div class="header-content">
                <div class="logo">
                    <a href="../index.php">
                        <i class="fas fa-graduation-cap"></i> EduTrack
                    </a>
                </div>
                <nav class="main-nav">
                    <a href="signin.php">Sign In</a>
            </nav>
        </div>
    </header>
    <main class="main-content">
    <?php endif; ?>