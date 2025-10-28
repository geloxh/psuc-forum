document.addEventListener('DOMContentLoaded', function() {
    // Dropdown Toggles
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation(); // Prevent the window click listener from firing immediately
            const dropdown = this.querySelector('.dropdown');
            
            // Close other open dropdowns
            document.querySelectorAll('.dropdown.show').forEach(openDropdown => {
                if (openDropdown !== dropdown) {
                    openDropdown.classList.remove('show');
                }
            });

            dropdown.classList.toggle('show');
        });
    });

    // Close dropdowns when clicking outside
    window.addEventListener('click', function(event) {
        // Check if the click is outside of a user-menu
        if (!event.target.closest('.user-menu')) {
            document.querySelectorAll('.dropdown.show').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });

    // Nav Toggle for mobile
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });
    }

    // Homepage enhancements
    setTimeout(() => {
        // Topic card animations
        document.querySelectorAll('.topic-card').forEach(card => {
            card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-2px)');
            card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
        });

        // Widget hover effects
        document.querySelectorAll('.widget').forEach(widget => {
            widget.addEventListener('mouseenter', () => widget.style.transform = 'translateY(-1px)');
            widget.addEventListener('mouseleave', () => widget.style.transform = 'translateY(0)');
        });

        // Action item hover effects
        document.querySelectorAll('.action-item').forEach(item => {
            item.addEventListener('mouseenter', () => item.style.transform = 'scale(1.02)');
            item.addEventListener('mouseleave', () => item.style.transform = 'scale(1)');
        });

        // Forum page animations
        document.querySelectorAll('.topic-row').forEach(row => {
            row.addEventListener('mouseenter', () => row.style.transform = 'translateX(4px)');
            row.addEventListener('mouseleave', () => row.style.transform = 'translateX(0)');
        });

        document.querySelectorAll('.page-btn, .page-number').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                if (!this.classList.contains('active')) this.style.transform = 'translateY(-1px)';
            });
            btn.addEventListener('mouseleave', () => btn.style.transform = 'translateY(0)');
        });
    }, 100);
});