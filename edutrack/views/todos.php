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
        $priority = sanitize_input($_POST['priority']);
        $due_date = sanitize_input($_POST['due_date']);
        
        if (!empty($title)) {
            $stmt = $conn->prepare("INSERT INTO todos (user_id, title, description, priority, due_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $title, $description, $priority, $due_date);
            
            if ($stmt->execute()) {
                $message = 'Task created successfully!';
            } else {
                $error = 'Failed to create task';
            }
            $stmt->close();
        }
    } elseif ($action === 'update') {
        $todo_id = intval($_POST['todo_id']);
        $title = sanitize_input($_POST['title']);
        $description = sanitize_input($_POST['description']);
        $status = sanitize_input($_POST['status']);
        $priority = sanitize_input($_POST['priority']);
        $due_date = sanitize_input($_POST['due_date']);
        
        $stmt = $conn->prepare("UPDATE todos SET title=?, description=?, status=?, priority=?, due_date=? WHERE todo_id=? AND user_id=?");
        $stmt->bind_param("sssssii", $title, $description, $status, $priority, $due_date, $todo_id, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Task updated successfully!';
        } else {
            $error = 'Failed to update task';
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        $todo_id = intval($_POST['todo_id']);
        
        $stmt = $conn->prepare("DELETE FROM todos WHERE todo_id=? AND user_id=?");
        $stmt->bind_param("ii", $todo_id, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Task deleted successfully!';
        } else {
            $error = 'Failed to delete task';
        }
        $stmt->close();
    } elseif ($action === 'toggle_status') {
        $todo_id = intval($_POST['todo_id']);
        $new_status = $_POST['new_status'];
        
        $stmt = $conn->prepare("UPDATE todos SET status=? WHERE todo_id=? AND user_id=?");
        $stmt->bind_param("sii", $new_status, $todo_id, $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'quick_update_status') {
        $todo_id = intval($_POST['todo_id']);
        $new_status = sanitize_input($_POST['status']);
        
        $stmt = $conn->prepare("UPDATE todos SET status=? WHERE todo_id=? AND user_id=?");
        $stmt->bind_param("sii", $new_status, $todo_id, $user_id);
        $stmt->execute();
        $stmt->close();
        
        header('Location: todos.php?filter=' . ($filter ?? 'all'));
        exit();
    } elseif ($action === 'quick_update_priority') {
        $todo_id = intval($_POST['todo_id']);
        $new_priority = sanitize_input($_POST['priority']);
        
        $stmt = $conn->prepare("UPDATE todos SET priority=? WHERE todo_id=? AND user_id=?");
        $stmt->bind_param("sii", $new_priority, $todo_id, $user_id);
        $stmt->execute();
        $stmt->close();
        
        header('Location: todos.php?filter=' . ($filter ?? 'all'));
        exit();
    }
}

// Get all todos
$filter = $_GET['filter'] ?? 'all';
$query = "SELECT * FROM todos WHERE user_id = ?";

if ($filter === 'pending') {
    $query .= " AND status = 'pending'";
} elseif ($filter === 'in_progress') {
    $query .= " AND status = 'in_progress'";
} elseif ($filter === 'completed') {
    $query .= " AND status = 'completed'";
}

$query .= " ORDER BY FIELD(priority, 'high', 'medium', 'low'), due_date ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$todos = $stmt->get_result();
$stmt->close();

$page_title = 'To-Do List';
$extra_js = '<script src="../assets/js/todos.js"></script>';
include '../includes/header.php';
?>

<div class="todos-container">
    <div class="page-header">
        <h1><i class="fas fa-tasks"></i> My Tasks</h1>
        <button class="btn btn-primary" onclick="openTodoModal()">
            <i class="fas fa-plus"></i> New Task
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

    <!-- Filters -->
    <div class="todo-filters">
        <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
            All Tasks
        </a>
        <a href="?filter=pending" class="filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>">
            Pending
        </a>
        <a href="?filter=in_progress" class="filter-btn <?php echo $filter === 'in_progress' ? 'active' : ''; ?>">
            In Progress
        </a>
        <a href="?filter=completed" class="filter-btn <?php echo $filter === 'completed' ? 'active' : ''; ?>">
            Completed
        </a>
    </div>

    <!-- To-Do List - Table View -->
    <div class="todos-table-container">
        <?php if ($todos->num_rows > 0): ?>
            <table class="todos-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th style="width: 35%;">
                            <i class="fas fa-font"></i> Name
                        </th>
                        <th style="width: 15%;">
                            <i class="fas fa-info-circle"></i> Status
                        </th>
                        <th style="width: 12%;">
                            <i class="fas fa-flag"></i> Priority
                        </th>
                        <th style="width: 15%;">
                            <i class="fas fa-calendar"></i> Due Date
                        </th>
                        <th style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($todo = $todos->fetch_assoc()): ?>
                        <tr class="todo-row <?php echo $todo['status'] === 'completed' ? 'completed-row' : ''; ?>" data-todo-id="<?php echo $todo['todo_id']; ?>">
                            <td class="todo-drag-handle">
                                <i class="fas fa-grip-vertical"></i>
                            </td>
                            <td class="todo-title-cell">
                                <div class="todo-title-wrapper">
                                    <input type="checkbox" class="todo-checkbox-table" 
                                           <?php echo $todo['status'] === 'completed' ? 'checked' : ''; ?>
                                           onchange="toggleTodoStatus(<?php echo $todo['todo_id']; ?>, this.checked)">
                                    <span class="todo-title-text <?php echo $todo['status'] === 'completed' ? 'completed' : ''; ?>">
                                        <?php echo htmlspecialchars($todo['title']); ?>
                                    </span>
                                </div>
                                <?php if ($todo['description']): ?>
                                    <div class="todo-description-preview">
                                        <?php echo htmlspecialchars(substr($todo['description'], 0, 100)); ?>
                                        <?php echo strlen($todo['description']) > 100 ? '...' : ''; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="todo-status-cell">
                                <form method="POST" class="quick-update-form" onchange="this.submit()">
                                    <input type="hidden" name="action" value="quick_update_status">
                                    <input type="hidden" name="todo_id" value="<?php echo $todo['todo_id']; ?>">
                                    <select name="status" class="status-select status-<?php echo $todo['status']; ?>">
                                        <option value="pending" <?php echo $todo['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in_progress" <?php echo $todo['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="completed" <?php echo $todo['status'] === 'completed' ? 'selected' : ''; ?>>Done</option>
                                    </select>
                                </form>
                            </td>
                            <td class="todo-priority-cell">
                                <form method="POST" class="quick-update-form" onchange="this.submit()">
                                    <input type="hidden" name="action" value="quick_update_priority">
                                    <input type="hidden" name="todo_id" value="<?php echo $todo['todo_id']; ?>">
                                    <select name="priority" class="priority-select priority-<?php echo $todo['priority']; ?>">
                                        <option value="low" <?php echo $todo['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                                        <option value="medium" <?php echo $todo['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                        <option value="high" <?php echo $todo['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                                    </select>
                                </form>
                            </td>
                            <td class="todo-date-cell">
                                <?php if ($todo['due_date']): ?>
                                    <span class="due-date-text">
                                        <?php echo date('M d', strtotime($todo['due_date'])); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="no-date">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="todo-actions-cell">
                                <button class="btn-icon-small" onclick="editTodo(<?php echo $todo['todo_id']; ?>)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon-small" onclick="deleteTodo(<?php echo $todo['todo_id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                            
                            <!-- Hidden data for editing -->
                            <td style="display: none;">
                                <div class="todo-data">
                                    <span class="data-id"><?php echo $todo['todo_id']; ?></span>
                                    <span class="data-title"><?php echo htmlspecialchars($todo['title']); ?></span>
                                    <span class="data-description"><?php echo htmlspecialchars($todo['description']); ?></span>
                                    <span class="data-status"><?php echo $todo['status']; ?></span>
                                    <span class="data-priority"><?php echo $todo['priority']; ?></span>
                                    <span class="data-due-date"><?php echo $todo['due_date']; ?></span>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                <h2>No tasks found</h2>
                <p>Create your first task to get started!</p>
                <button class="btn btn-primary" onclick="openTodoModal()">
                    <i class="fas fa-plus"></i> Create Task
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Todo Modal -->
<div id="todoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">New Task</h2>
            <span class="close" onclick="closeTodoModal()">&times;</span>
        </div>
        <form method="POST" action="" id="todoForm">
            <input type="hidden" name="action" id="todoAction" value="create">
            <input type="hidden" name="todo_id" id="todoId">
            
            <div class="form-group">
                <label for="title">Task Title *</label>
                <input type="text" id="title" name="title" required placeholder="Enter task title">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Enter task description"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                
                <div class="form-group" id="statusGroup" style="display: none;">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeTodoModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Task
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

