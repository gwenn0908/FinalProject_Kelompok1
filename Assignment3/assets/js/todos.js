/**
 * EduTrack - To-Do List JavaScript
 * Handles to-do list CRUD operations
 */

// Open modal for creating new todo
function openTodoModal() {
    document.getElementById('modalTitle').textContent = 'New Task';
    document.getElementById('todoAction').value = 'create';
    document.getElementById('todoId').value = '';
    document.getElementById('title').value = '';
    document.getElementById('description').value = '';
    document.getElementById('priority').value = 'medium';
    document.getElementById('status').value = 'pending';
    document.getElementById('due_date').value = '';
    document.getElementById('statusGroup').style.display = 'none';
    
    showModal('todoModal');
}

// Close todo modal
function closeTodoModal() {
    hideModal('todoModal');
}

// Edit todo
function editTodo(todoId) {
    const todoRow = document.querySelector(`.todo-row[data-todo-id="${todoId}"]`);
    if (!todoRow) return;
    
    const todoData = todoRow.querySelector('.todo-data');
    const title = todoData.querySelector('.data-title').textContent;
    const description = todoData.querySelector('.data-description').textContent;
    const status = todoData.querySelector('.data-status').textContent;
    const priority = todoData.querySelector('.data-priority').textContent;
    const dueDate = todoData.querySelector('.data-due-date').textContent;
    
    document.getElementById('modalTitle').textContent = 'Edit Task';
    document.getElementById('todoAction').value = 'update';
    document.getElementById('todoId').value = todoId;
    document.getElementById('title').value = title;
    document.getElementById('description').value = description;
    document.getElementById('priority').value = priority;
    document.getElementById('status').value = status;
    document.getElementById('due_date').value = dueDate;
    document.getElementById('statusGroup').style.display = 'block';
    
    showModal('todoModal');
}

// Delete todo
function deleteTodo(todoId) {
    if (!confirmDelete('Are you sure you want to delete this task?')) {
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
    idInput.name = 'todo_id';
    idInput.value = todoId;
    
    form.appendChild(actionInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
}

// Toggle todo status
function toggleTodoStatus(todoId, isChecked) {
    const newStatus = isChecked ? 'completed' : 'pending';
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'toggle_status';
    
    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'todo_id';
    idInput.value = todoId;
    
    const statusInput = document.createElement('input');
    statusInput.type = 'hidden';
    statusInput.name = 'new_status';
    statusInput.value = newStatus;
    
    form.appendChild(actionInput);
    form.appendChild(idInput);
    form.appendChild(statusInput);
    document.body.appendChild(form);
    form.submit();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for due date to today
    const dueDateInput = document.getElementById('due_date');
    if (dueDateInput) {
        dueDateInput.min = getTodayDate();
    }
});

