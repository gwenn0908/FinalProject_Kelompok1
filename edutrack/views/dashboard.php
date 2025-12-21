<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM todos WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_todos = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as completed FROM todos WHERE user_id = ? AND status = 'completed'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_todos = $stmt->get_result()->fetch_assoc()['completed'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as pending FROM todos WHERE user_id = ? AND status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_todos = $stmt->get_result()->fetch_assoc()['pending'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as upcoming FROM events WHERE user_id = ? AND event_date >= CURDATE()");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$upcoming_events = $stmt->get_result()->fetch_assoc()['upcoming'];
$stmt->close();

// Get recent todos
$stmt = $conn->prepare("SELECT * FROM todos WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_todos = $stmt->get_result();
$stmt->close();

// Get upcoming events
$stmt = $conn->prepare("SELECT * FROM events WHERE user_id = ? AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$upcoming_events_list = $stmt->get_result();
$stmt->close();

$page_title = 'Dashboard';
include '../includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome back, <?php echo htmlspecialchars($username); ?>! 👋</h1>
        <p class="dashboard-subtitle">Here's your learning progress overview</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $total_todos; ?></h3>
                <p>Total Tasks</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $completed_todos; ?></h3>
                <p>Completed</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $pending_todos; ?></h3>
                <p>Pending</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $upcoming_events; ?></h3>
                <p>Upcoming Events</p>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="dashboard-grid">
        <!-- Recent To-Dos -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-tasks"></i> Recent Tasks</h2>
                <a href="todos.php" class="btn btn-sm">View All</a>
            </div>
            <div class="card-content">
                <?php if ($recent_todos->num_rows > 0): ?>
                    <div class="todo-list-preview">
                        <?php while ($todo = $recent_todos->fetch_assoc()): ?>
                            <div class="todo-item-preview <?php echo $todo['status']; ?>">
                                <div class="todo-info">
                                    <h4><?php echo htmlspecialchars($todo['title']); ?></h4>
                                    <span class="badge badge-<?php echo $todo['priority']; ?>">
                                        <?php echo ucfirst($todo['priority']); ?>
                                    </span>
                                    <span class="badge badge-<?php echo $todo['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $todo['status'])); ?>
                                    </span>
                                </div>
                                <?php if ($todo['due_date']): ?>
                                    <div class="todo-date">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo date('M d, Y', strtotime($todo['due_date'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-tasks"></i>
                        <p>No tasks yet. Create your first task!</p>
                        <a href="todos.php" class="btn btn-primary">Create Task</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-calendar-alt"></i> Upcoming Events</h2>
                <a href="calendar.php" class="btn btn-sm">View Calendar</a>
            </div>
            <div class="card-content">
                <?php if ($upcoming_events_list->num_rows > 0): ?>
                    <div class="events-list-preview">
                        <?php while ($event = $upcoming_events_list->fetch_assoc()): ?>
                            <div class="event-item-preview">
                                <div class="event-date-badge">
                                    <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                    <span class="month"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                                </div>
                                <div class="event-info">
                                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p>
                                        <span class="badge badge-<?php echo $event['event_type']; ?>">
                                            <?php echo ucfirst($event['event_type']); ?>
                                        </span>
                                        <?php if ($event['event_time']): ?>
                                            <span class="event-time">
                                                <i class="fas fa-clock"></i> 
                                                <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-alt"></i>
                        <p>No upcoming events. Add one now!</p>
                        <a href="calendar.php" class="btn btn-primary">Add Event</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="actions-grid">
            <a href="todos.php?action=new" class="action-card">
                <i class="fas fa-plus-circle"></i>
                <span>New Task</span>
            </a>
            <a href="calendar.php?action=new" class="action-card">
                <i class="fas fa-calendar-plus"></i>
                <span>New Event</span>
            </a>
            <a href="notes.php?action=new" class="action-card">
                <i class="fas fa-sticky-note"></i>
                <span>New Note</span>
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

