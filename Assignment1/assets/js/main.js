/**
 * EduTrack - Main JavaScript File
 * General functionality and utilities
 */


// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Format date to readable string
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Get today's date in YYYY-MM-DD format
function getTodayDate() {
    const today = new Date();
    return today.toISOString().split('T')[0];
}

// Highlight active nav link
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.main-nav a');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
            link.style.backgroundColor = 'var(--bg-secondary)';
            link.style.color = 'var(--text-primary)';
        }
    });
});

