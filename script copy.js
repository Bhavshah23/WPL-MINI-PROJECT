document.addEventListener('DOMContentLoaded', function() {
    // Password validation
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.length < 8) {
                this.setCustomValidity('Password must be at least 8 characters long');
            } else {
                this.setCustomValidity('');
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const password = this.querySelector('input[name="password"]');
            if (password && password.value.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
            }
        });
    });
});