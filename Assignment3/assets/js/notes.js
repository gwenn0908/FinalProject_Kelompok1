/**
 * EduTrack - Notes JavaScript
 * Handles note-taking functionality
 */

// Open modal for creating new note
function openNoteModal() {
    document.getElementById('modalTitle').textContent = 'New Note';
    document.getElementById('noteAction').value = 'create';
    document.getElementById('noteId').value = '';
    document.getElementById('title').value = '';
    document.getElementById('content').value = '';
    document.getElementById('category').value = '';
    
    showModal('noteModal');
}

// Close note modal
function closeNoteModal() {
    hideModal('noteModal');
}

// Edit note
function editNote(noteId) {
    const noteCard = document.querySelector(`.note-card[data-note-id="${noteId}"]`);
    if (!noteCard) return;
    
    const noteData = noteCard.querySelector('.note-data');
    const title = noteData.querySelector('.data-title').textContent;
    const content = noteData.querySelector('.data-content').textContent;
    const category = noteData.querySelector('.data-category').textContent;
    
    document.getElementById('modalTitle').textContent = 'Edit Note';
    document.getElementById('noteAction').value = 'update';
    document.getElementById('noteId').value = noteId;
    document.getElementById('title').value = title;
    document.getElementById('content').value = content;
    document.getElementById('category').value = category;
    
    showModal('noteModal');
}

// Delete note
function deleteNote(noteId) {
    if (!confirmDelete('Are you sure you want to delete this note?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'delete';
    
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'note_id';
    idInput.value = noteId;
    
    form.appendChild(actionInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
}

// Toggle favorite status
function toggleFavorite(noteId, isFavorite) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'toggle_favorite';
    
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'note_id';
    idInput.value = noteId;
    
    const favoriteInput = document.createElement('input');
    favoriteInput.type = 'hidden';
    favoriteInput.name = 'is_favorite';
    favoriteInput.value = isFavorite;
    
    form.appendChild(actionInput);
    form.appendChild(idInput);
    form.appendChild(favoriteInput);
    document.body.appendChild(form);
    form.submit();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Auto-grow textarea
    const textarea = document.getElementById('content');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    }
});

