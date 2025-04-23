document.addEventListener('DOMContentLoaded', function () {
    // Handle validation errors
    const errors = JSON.parse(document.querySelector('meta[name="errors"]')?.content || '{}');
    if (Object.keys(errors).length > 0) {
        const modal = new bootstrap.Modal(document.getElementById(errors.hasOwnProperty('email') ? 'loginModal' : 'registerModal'));
        modal.show();
    }

    // Show success message
    const success = document.querySelector('meta[name="success"]')?.content;
    if (success) {
        alert(success);
    }
});

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggleBtn = input.nextElementSibling.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        toggleBtn.classList.remove('bi-eye-slash');
        toggleBtn.classList.add('bi-eye');
    } else {
        input.type = 'password';




    }
} 