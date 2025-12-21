<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $title = sanitize_input($_POST['title']);
        $description = sanitize_input($_POST['description']);
        $event_date = sanitize_input($_POST['event_date']);
        $event_time = sanitize_input($_POST['event_time']);
        $event_type = sanitize_input($_POST['event_type']);
        $location = sanitize_input($_POST['location']);
        
        if (!empty($title) && !empty($event_date)) {
            $stmt = $conn->prepare("INSERT INTO events (user_id, title, description, event_date, event_time, event_type, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $user_id, $title, $description, $event_date, $event_time, $event_type, $location);
            
            if ($stmt->execute()) {
                $message = 'Event created successfully!';
            } else {
                $error = 'Failed to create event';
            }
            $stmt->close();
        }
    } elseif ($action === 'update') {
        $event_id = intval($_POST['event_id']);
        $title = sanitize_input($_POST['title']);
        $description = sanitize_input($_POST['description']);
        $event_date = sanitize_input($_POST['event_date']);
        $event_time = sanitize_input($_POST['event_time']);
        $event_type = sanitize_input($_POST['event_type']);
        $location = sanitize_input($_POST['location']);
        
        $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, event_time=?, event_type=?, location=? WHERE event_id=? AND user_id=?");
        $stmt->bind_param("ssssssii", $title, $description, $event_date, $event_time, $event_type, $location, $event_id, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Event updated successfully!';
        } else {
            $error = 'Failed to update event';
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        $event_id = intval($_POST['event_id']);
        
        $stmt = $conn->prepare("DELETE FROM events WHERE event_id=? AND user_id=?");
        $stmt->bind_param("ii", $event_id, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Event deleted successfully!';
        } else {
            $error = 'Failed to delete event';
        }
        $stmt->close();
    }
}

// Get current month and year
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get all events for the user
$stmt = $conn->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY event_date ASC, event_time ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$all_events = $stmt->get_result();
$events_array = [];
while ($event = $all_events->fetch_assoc()) {
    $event['item_type'] = 'event';
    $events_array[] = $event;
}
$stmt->close();

// Get all todos with due dates for the user
$stmt = $conn->prepare("SELECT todo_id, title, description, due_date, priority, status FROM todos WHERE user_id = ? AND due_date IS NOT NULL ORDER BY due_date ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$all_todos = $stmt->get_result();
$todos_array = [];
while ($todo = $all_todos->fetch_assoc()) {
    $todo['item_type'] = 'todo';
    $todo['event_date'] = $todo['due_date']; // For consistency in calendar display
    $todos_array[] = $todo;
}
$stmt->close();

// Combine events and todos
$calendar_items = array_merge($events_array, $todos_array);

$page_title = 'Calendar';
include '../includes/header.php';
?>

<div class="calendar-container">
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt"></i> Academic Calendar</h1>
        <button class="btn btn-primary" onclick="openEventModal()">
            <i class="fas fa-plus"></i> New Event
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="calendar-layout">
        <!-- Calendar View -->
        <div class="calendar-view">
            <div class="calendar-header">
                <button class="btn-icon" onclick="changeMonth(-1)" title="Previous Month">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <h2 id="currentMonth"><?php echo date('F Y', mktime(0, 0, 0, $current_month, 1, $current_year)); ?></h2>
                    <button class="btn btn-sm btn-secondary" onclick="goToToday()" title="Go to current month">
                        <i class="fas fa-calendar-day"></i> Today
                    </button>
                </div>
                <button class="btn-icon" onclick="changeMonth(1)" title="Next Month">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <!-- Calendar Legend -->
            <div class="calendar-legend">
                <div class="legend-item">
                    <div class="legend-box event-class-legend"></div>
                    <span>Class</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box event-exam-legend"></div>
                    <span>Exam</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box event-assignment-legend"></div>
                    <span>Assignment</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box event-meeting-legend"></div>
                    <span>Meeting</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box todo-legend"></div>
                    <span>Task</span>
                </div>
            </div>

            <div class="calendar-grid">
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>

                <?php
                $first_day = date('w', mktime(0, 0, 0, $current_month, 1, $current_year));
                $days_in_month = date('t', mktime(0, 0, 0, $current_month, 1, $current_year));
                $today = date('Y-m-d');
                
                // Empty cells before first day
                for ($i = 0; $i < $first_day; $i++) {
                    echo '<div class="calendar-day empty"></div>';
                }
                
                // Calendar days
                for ($day = 1; $day <= $days_in_month; $day++) {
                    $date = sprintf('%04d-%02d-%02d', $current_year, $current_month, $day);
                    $is_today = ($date === $today) ? 'today' : '';
                    $day_of_week = date('w', mktime(0, 0, 0, $current_month, $day, $current_year));
                    $is_weekend = ($day_of_week == 0 || $day_of_week == 6) ? 'weekend' : '';
                    
                    // Get all items (events + todos) for this day
                    $day_items = array_filter($calendar_items, function($item) use ($date) {
                        return $item['event_date'] === $date;
                    });
                    
                    echo '<div class="calendar-day ' . $is_today . ' ' . $is_weekend . '" data-date="' . $date . '" onclick="showDayEvents(\'' . $date . '\')">';
                    echo '<div class="day-number">' . $day . '</div>';
                    
                    if (count($day_items) > 0) {
                        echo '<div class="day-events-container">';
                        $count = 0;
                        $max_visible = 3;
                        
                        foreach ($day_items as $item) {
                            if ($count < $max_visible) {
                                if ($item['item_type'] === 'event') {
                                    $event_class = 'event-' . htmlspecialchars($item['event_type']);
                                    echo '<div class="calendar-event-item ' . $event_class . '" onclick="editEvent(' . $item['event_id'] . '); event.stopPropagation();" title="' . htmlspecialchars($item['title']) . '">';
                                    if ($item['event_time']) {
                                        echo '<span class="event-time">' . date('g:i A', strtotime($item['event_time'])) . '</span> ';
                                    }
                                    echo '<span class="event-title">' . htmlspecialchars(strlen($item['title']) > 20 ? substr($item['title'], 0, 20) . '...' : $item['title']) . '</span>';
                                    echo '</div>';
                                } else {
                                    // Todo item
                                    $todo_class = 'todo-' . htmlspecialchars($item['priority']);
                                    $status_icon = $item['status'] === 'completed' ? '✓ ' : '';
                                    echo '<div class="calendar-event-item calendar-todo-item ' . $todo_class . '" onclick="window.location.href=\'todos.php\'; event.stopPropagation();" title="Task: ' . htmlspecialchars($item['title']) . '">';
                                    echo '<span class="todo-icon">📝</span> ';
                                    echo '<span class="event-title">' . $status_icon . htmlspecialchars(strlen($item['title']) > 18 ? substr($item['title'], 0, 18) . '...' : $item['title']) . '</span>';
                                    echo '</div>';
                                }
                            }
                            $count++;
                        }
                        
                        if ($count > $max_visible) {
                            echo '<div class="more-events-link" onclick="showDayEvents(\'' . $date . '\'); event.stopPropagation();">';
                            echo '+' . ($count - $max_visible) . ' more';
                            echo '</div>';
                        }
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <!-- Events List -->
        <div class="events-sidebar">
            <h3>Upcoming Events & Tasks</h3>
            <div class="events-list">
                <?php
                $upcoming_items = array_filter($calendar_items, function($item) {
                    return $item['event_date'] >= date('Y-m-d');
                });
                
                if (count($upcoming_items) > 0):
                    usort($upcoming_items, function($a, $b) {
                        return strcmp($a['event_date'], $b['event_date']);
                    });
                    
                    foreach ($upcoming_items as $item):
                        if ($item['item_type'] === 'event'):
                            // Display Event
                ?>
                    <div class="event-card" data-event-id="<?php echo $item['event_id']; ?>">
                        <div class="event-header">
                            <span class="badge badge-<?php echo $item['event_type']; ?>">
                                <?php echo ucfirst($item['event_type']); ?>
                            </span>
                            <div class="event-actions">
                                <button class="btn-icon" onclick="editEvent(<?php echo $item['event_id']; ?>)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon" onclick="deleteEvent(<?php echo $item['event_id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                        <?php if ($item['description']): ?>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php endif; ?>
                        <div class="event-meta">
                            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($item['event_date'])); ?></span>
                            <?php if ($item['event_time']): ?>
                                <span><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($item['event_time'])); ?></span>
                            <?php endif; ?>
                            <?php if ($item['location']): ?>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['location']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Hidden data for editing -->
                        <div class="event-data" style="display: none;">
                            <span class="data-id"><?php echo $item['event_id']; ?></span>
                            <span class="data-title"><?php echo htmlspecialchars($item['title']); ?></span>
                            <span class="data-description"><?php echo htmlspecialchars($item['description']); ?></span>
                            <span class="data-date"><?php echo $item['event_date']; ?></span>
                            <span class="data-time"><?php echo $item['event_time']; ?></span>
                            <span class="data-type"><?php echo $item['event_type']; ?></span>
                            <span class="data-location"><?php echo htmlspecialchars($item['location']); ?></span>
                        </div>
                    </div>
                <?php
                        else:
                            // Display Todo
                ?>
                    <div class="event-card todo-card" data-todo-id="<?php echo $item['todo_id']; ?>" style="border-left: 3px solid var(--<?php echo $item['priority'] === 'high' ? 'error' : ($item['priority'] === 'medium' ? 'warning' : 'primary'); ?>-color);">
                        <div class="event-header">
                            <span class="badge badge-<?php echo $item['priority']; ?>">
                                <i class="fas fa-tasks"></i> <?php echo ucfirst($item['priority']); ?> Task
                            </span>
                            <div class="event-actions">
                                <a href="todos.php" class="btn-icon" title="View in To-Do">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                        <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                        <?php if ($item['description']): ?>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php endif; ?>
                        <div class="event-meta">
                            <span><i class="fas fa-calendar"></i> Due: <?php echo date('M d, Y', strtotime($item['due_date'])); ?></span>
                            <span class="badge badge-<?php echo $item['status']; ?>" style="font-size: 11px;">
                                <?php echo ucfirst(str_replace('_', ' ', $item['status'])); ?>
                            </span>
                        </div>
                    </div>
                <?php
                        endif;
                    endforeach;
                else:
                ?>
                    <div class="empty-state-small">
                        <i class="fas fa-calendar-alt"></i>
                        <p>No upcoming events or tasks</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Event Modal -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">New Event</h2>
            <span class="close" onclick="closeEventModal()">&times;</span>
        </div>
        <form method="POST" action="" id="eventForm">
            <input type="hidden" name="action" id="eventAction" value="create">
            <input type="hidden" name="event_id" id="eventId">
            
            <div class="form-group">
                <label for="title">Event Title *</label>
                <input type="text" id="title" name="title" required placeholder="Enter event title">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" placeholder="Enter event description"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="event_date">Date *</label>
                    <input type="date" id="event_date" name="event_date" required>
                </div>
                
                <div class="form-group">
                    <label for="event_time">Time</label>
                    <input type="time" id="event_time" name="event_time">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="event_type">Type</label>
                    <select id="event_type" name="event_type">
                        <option value="class">Class</option>
                        <option value="exam">Exam</option>
                        <option value="assignment">Assignment</option>
                        <option value="meeting">Meeting</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="Enter location">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEventModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Event
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize calendar variables BEFORE loading calendar.js
var currentMonth = <?php echo $current_month; ?>;
var currentYear = <?php echo $current_year; ?>;
const calendarData = <?php echo json_encode($calendar_items); ?>;
console.log('Calendar loaded - Month:', currentMonth, 'Year:', currentYear);
</script>

<script src="../assets/js/calendar.js"></script>

<?php include '../includes/footer.php'; ?>

