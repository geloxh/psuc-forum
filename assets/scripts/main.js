document.addEventListener('DOMContentLoaded', function() {
    // Dropdown toggle for user menus
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    dropdownToggles.forEach(toggle => {
        const toggleLink = toggle.querySelector('a');
        
        toggleLink.addEventListener('click', function(event) {
            if (this.getAttribute('href') === '#') {
                event.preventDefault();
                event.stopPropagation();

                const parentToggle = this.parentElement;

                // Close other open dropdowns
                document.querySelectorAll('.dropdown-toggle').forEach(otherToggle => {
                    if (otherToggle !== parentToggle) {
                        otherToggle.classList.remove('active');
                    }
                });

                // Toggle current dropdown
                parentToggle.classList.toggle('active');
            }
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