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
        $content = sanitize_input($_POST['content']);
        $category = sanitize_input($_POST['category']);
        
        if (!empty($title)) {
            $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content, category) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $title, $content, $category);
            
            if ($stmt->execute()) {
                $message = 'Note created successfully!';
            } else {
                $error = 'Failed to create note';
            }
            $stmt->close();
        }
    } elseif ($action === 'update') {
        $note_id = intval($_POST['note_id']);
        $title = sanitize_input($_POST['title']);
        $content = sanitize_input($_POST['content']);
        $category = sanitize_input($_POST['category']);
        
        $stmt = $conn->prepare("UPDATE notes SET title=?, content=?, category=? WHERE note_id=? AND user_id=?");
        $stmt->bind_param("sssii", $title, $content, $category, $note_id, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Note updated successfully!';
        } else {
            $error = 'Failed to update note';
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        $note_id = intval($_POST['note_id']);
        
        $stmt = $conn->prepare("DELETE FROM notes WHERE note_id=? AND user_id=?");
        $stmt->bind_param("ii", $note_id, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Note deleted successfully!';
        } else {
            $error = 'Failed to delete note';
        }
        $stmt->close();
    } elseif ($action === 'toggle_favorite') {
        $note_id = intval($_POST['note_id']);
        $is_favorite = intval($_POST['is_favorite']);
        
        $stmt = $conn->prepare("UPDATE notes SET is_favorite=? WHERE note_id=? AND user_id=?");
        $stmt->bind_param("iii", $is_favorite, $note_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Get all notes
$category_filter = $_GET['category'] ?? 'all';
$query = "SELECT * FROM notes WHERE user_id = ?";

if ($category_filter !== 'all') {
    $query .= " AND category = '" . $conn->real_escape_string($category_filter) . "'";
}

$query .= " ORDER BY is_favorite DESC, updated_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notes = $stmt->get_result();
$stmt->close();

// Get categories
$stmt = $conn->prepare("SELECT DISTINCT category FROM notes WHERE user_id = ? AND category IS NOT NULL AND category != ''");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$categories_result = $stmt->get_result();
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row['category'];
}
$stmt->close();

$page_title = 'Notes';
$extra_js = '<script src="../assets/js/notes.js"></script>';
include '../includes/header.php';
?>

<div class="notes-container">
    <div class="page-header">
        <h1><i class="fas fa-sticky-note"></i> My Notes</h1>
        <button class="btn btn-primary" onclick="openNoteModal()">
            <i class="fas fa-plus"></i> New Note
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

    <!-- Category Filters -->
    <div class="note-filters">
        <a href="?category=all" class="filter-btn <?php echo $category_filter === 'all' ? 'active' : ''; ?>">
            All Notes
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="?category=<?php echo urlencode($cat); ?>" 
               class="filter-btn <?php echo $category_filter === $cat ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($cat); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Notes Grid -->
    <div class="notes-grid">
        <?php if ($notes->num_rows > 0): ?>
            <?php while ($note = $notes->fetch_assoc()): ?>
                <div class="note-card" data-note-id="<?php echo $note['note_id']; ?>">
                    <div class="note-header">
                        <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                        <div class="note-actions">
                            <button class="btn-icon <?php echo $note['is_favorite'] ? 'favorite' : ''; ?>" 
                                    onclick="toggleFavorite(<?php echo $note['note_id']; ?>, <?php echo $note['is_favorite'] ? 0 : 1; ?>)" 
                                    title="Favorite">
                                <i class="<?php echo $note['is_favorite'] ? 'fas' : 'far'; ?> fa-star"></i>
                            </button>
                            <button class="btn-icon" onclick="editNote(<?php echo $note['note_id']; ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon" onclick="deleteNote(<?php echo $note['note_id']; ?>)" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="note-content">
                        <?php 
                        $content = htmlspecialchars($note['content']);
                        echo strlen($content) > 200 ? substr($content, 0, 200) . '...' : $content;
                        ?>
                    </div>
                    
                    <div class="note-footer">
                        <?php if ($note['category']): ?>
                            <span class="badge badge-category">
                                <i class="fas fa-folder"></i> <?php echo htmlspecialchars($note['category']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="note-date">
                            <i class="fas fa-clock"></i> 
                            <?php echo date('M d, Y', strtotime($note['updated_at'])); ?>
                        </span>
                    </div>
                    
                    <!-- Hidden data for editing -->
                    <div class="note-data" style="display: none;">
                        <span class="data-id"><?php echo $note['note_id']; ?></span>
                        <span class="data-title"><?php echo htmlspecialchars($note['title']); ?></span>
                        <span class="data-content"><?php echo htmlspecialchars($note['content']); ?></span>
                        <span class="data-category"><?php echo htmlspecialchars($note['category']); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-sticky-note"></i>
                <h2>No notes found</h2>
                <p>Create your first note to get started!</p>
                <button class="btn btn-primary" onclick="openNoteModal()">
                    <i class="fas fa-plus"></i> Create Note
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Note Modal -->
<div id="noteModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2 id="modalTitle">New Note</h2>
            <span class="close" onclick="closeNoteModal()">&times;</span>
        </div>
        <form method="POST" action="" id="noteForm">
            <input type="hidden" name="action" id="noteAction" value="create">
            <input type="hidden" name="note_id" id="noteId">
            
            <div class="form-group">
                <label for="title">Note Title *</label>
                <input type="text" id="title" name="title" required placeholder="Untitled">
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" placeholder="e.g., Math, Science, History" list="categoryList">
                <datalist id="categoryList">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            
            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="15" placeholder="Start writing..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeNoteModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Note
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

