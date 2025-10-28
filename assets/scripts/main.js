document.addEventListener('DOMContentLoaded', function() {
    // Dropdown toggle for user menus
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            // Close other open dropdowns
            dropdownToggles.forEach(otherToggle => {
                if (otherToggle !== toggle) {
                    otherToggle.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            toggle.classList.toggle('active');
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown-toggle')) {
            dropdownToggles.forEach(toggle => toggle.classList.remove('active'));
        }
    });

    // Mobile navigation toggle
    const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
    const body = document.querySelector('body');

    if (mobileNavToggle) {
        mobileNavToggle.addEventListener('click', () => {
            body.classList.toggle('nav-active');
        });
    }
});