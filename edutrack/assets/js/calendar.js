/**
 * EduTrack - Calendar JavaScript
 * Handles calendar and event management
 */

// Note: currentMonth, currentYear, and calendarData are initialized in calendar.php
// before this script is loaded

// Open modal for creating new event
function openEventModal(prefilledDate = null) {
    document.getElementById('modalTitle').textContent = 'New Event';
    document.getElementById('eventAction').value = 'create';
    document.getElementById('eventId').value = '';
    document.getElementById('title').value = '';
    document.getElementById('description').value = '';
    document.getElementById('event_time').value = '';
    document.getElementById('event_type').value = 'class';
    document.getElementById('location').value = '';
    
    // Set date field (will be overwritten if prefilledDate is provided)
    if (prefilledDate) {
        document.getElementById('event_date').value = prefilledDate;
        console.log('Modal opened with prefilled date:', prefilledDate);
    } else {
        document.getElementById('event_date').value = '';
    }
    
    showModal('eventModal');
}

// Close event modal
function closeEventModal() {
    hideModal('eventModal');
}

// Edit event
function editEvent(eventId) {
    const eventCard = document.querySelector(`.event-card[data-event-id="${eventId}"]`);
    if (!eventCard) return;
    
    const eventData = eventCard.querySelector('.event-data');
    const title = eventData.querySelector('.data-title').textContent;
    const description = eventData.querySelector('.data-description').textContent;
    const date = eventData.querySelector('.data-date').textContent;
    const time = eventData.querySelector('.data-time').textContent;
    const type = eventData.querySelector('.data-type').textContent;
    const location = eventData.querySelector('.data-location').textContent;
    
    document.getElementById('modalTitle').textContent = 'Edit Event';
    document.getElementById('eventAction').value = 'update';
    document.getElementById('eventId').value = eventId;
    document.getElementById('title').value = title;
    document.getElementById('description').value = description;
    document.getElementById('event_date').value = date;
    document.getElementById('event_time').value = time;
    document.getElementById('event_type').value = type;
    document.getElementById('location').value = location;
    
    showModal('eventModal');
}

// Delete event
function deleteEvent(eventId) {
    if (!confirmDelete('Are you sure you want to delete this event?')) {
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
    idInput.name = 'event_id';
    idInput.value = eventId;
    
    form.appendChild(actionInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
}

// Change month in calendar
function changeMonth(delta) {
    // Make sure variables are defined
    if (typeof currentMonth === 'undefined' || typeof currentYear === 'undefined') {
        console.error('Calendar variables not initialized!');
        return;
    }
    
    console.log('Before change - Month:', currentMonth, 'Year:', currentYear, 'Delta:', delta);
    
    let newMonth = currentMonth + delta;
    let newYear = currentYear;
    
    if (newMonth > 12) {
        newMonth = 1;
        newYear++;
    } else if (newMonth < 1) {
        newMonth = 12;
        newYear--;
    }
    
    console.log('After change - Month:', newMonth, 'Year:', newYear);
    window.location.href = `calendar.php?month=${newMonth}&year=${newYear}`;
}

// Go to current month (today)
function goToToday() {
    const today = new Date();
    const todayMonth = today.getMonth() + 1;
    const todayYear = today.getFullYear();
    window.location.href = `calendar.php?month=${todayMonth}&year=${todayYear}`;
}

// Show events for a specific day
function showDayEvents(date) {
    console.log('Calendar day clicked:', date);
    
    // Log existing items for this date
    if (typeof calendarData !== 'undefined') {
        const dayItems = calendarData.filter(item => item.event_date === date);
        if (dayItems.length > 0) {
            console.log(`Found ${dayItems.length} existing item(s) on ${date}`);
        } else {
            console.log(`No existing items on ${date} - opening new event modal`);
        }
    }
    
    // Open modal with date pre-filled
    openEventModal(date);
}

// Format time for display
function formatTime(timeString) {
    if (!timeString) return '';
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour);
    return `${displayHour}:${minutes} ${ampm}`;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Verify calendar variables are loaded
    console.log('DOM loaded - Calendar vars:', {
        month: typeof currentMonth !== 'undefined' ? currentMonth : 'undefined',
        year: typeof currentYear !== 'undefined' ? currentYear : 'undefined',
        data: typeof calendarData !== 'undefined' ? 'loaded' : 'undefined'
    });
    
    // Set minimum date for event date to today
    const eventDateInput = document.getElementById('event_date');
    if (eventDateInput) {
        eventDateInput.min = getTodayDate();
    }
});

