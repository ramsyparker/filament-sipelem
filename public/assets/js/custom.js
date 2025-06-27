document.addEventListener('DOMContentLoaded', function () {
    // Handle validation errors
    const errors = JSON.parse(document.querySelector('meta[name="errors"]')?.content || '{}');
    if (Object.keys(errors).length > 0) {
        const modal = new bootstrap.Modal(document.getElementById(errors.hasOwnProperty('email') ? 'loginModal' : 'registerModal'));
        modal.show();
        
        // Show error notification
        if (errors.email && errors.email.includes('belum diverifikasi')) {
            showNotification('Email Anda belum diverifikasi. Silakan cek email Anda dan klik link verifikasi.', 'warning');
        } else if (errors.email) {
            showNotification(errors.email[0], 'error');
        }
    }

    // Show success message
    const success = document.querySelector('meta[name="success"]')?.content;
    if (success) {
        showNotification(success, 'success');
    }
});

// Function to show notifications
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'success' ? 'bi-check-circle' : 
                 type === 'error' ? 'bi-x-circle' : 
                 type === 'warning' ? 'bi-exclamation-triangle' : 'bi-info-circle';
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="bi ${icon} notification-icon"></i>
            <div class="notification-message">${message}</div>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye-slash';
    }
} 