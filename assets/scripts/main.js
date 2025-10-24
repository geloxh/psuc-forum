// Improved dropdown functionality with error handling
function toggleDropdown(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const dropdown = event.currentTarget.nextElementSibling;
    if (!dropdown || !dropdown.classList.contains('dropdown')) {
        return; // Exit if dropdown element not found
    }
    
    const isOpen = dropdown.classList.contains('show');
    
    // Close all dropdowns
    document.querySelectorAll('.dropdown.show').forEach(d => d.classList.remove('show'));
    
    // Toggle current dropdown
    if (!isOpen) {
        dropdown.classList.add('show');
    }
}


// Apply theme on initial load to prevent FOUC (Flash of Unstyled Content)
document.addEventListener('DOMContentLoaded', function() {
    // Responsive navigation toggle
    const navToggle = document.querySelector('.nav-toggle');
    const nav = document.querySelector('.nav');

    navToggle?.addEventListener('click', () => {
        nav?.classList.toggle('nav--visible');
    });
    
    // Event delegation for dropdown toggles
    document.addEventListener('click', function(event) {
        const dropdownToggle = event.target.closest('.dropdown-toggle');
        if (dropdownToggle) {
            // Create a new event object with the correct currentTarget
            const newEvent = {
                preventDefault: () => event.preventDefault(),
                stopPropagation: () => event.stopPropagation(),
                currentTarget: dropdownToggle
            };
            toggleDropdown(newEvent);
        }
    });
});

// Global click handler to close dropdowns
document.addEventListener('click', function(event) {
    if (!event.target.closest('.user-menu')) {
        document.querySelectorAll('.dropdown.show').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
});

// Category Sidebar Functionality
function toggleCategory(categoryId) {
    const forums = document.getElementById('forums-' + categoryId);
    const arrow = document.getElementById('arrow-' + categoryId);
    
    if (forums && arrow) {
        forums.classList.toggle('open');
        arrow.classList.toggle('rotated');
        
        // Close other open categories for accordion effect
        document.querySelectorAll('.forums-dropdown.open').forEach(dropdown => {
            if (dropdown.id !== 'forums-' + categoryId) {
                dropdown.classList.remove('open');
                const otherArrow = document.getElementById(dropdown.id.replace('forums-', 'arrow-'));
                if (otherArrow) otherArrow.classList.remove('rotated');
            }
        });
    }
}

function navigateToForum(forumId) {
    window.location.href = 'forum.php?id=' + forumId;
}

// Password Confirmation
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm_password');

if (password && confirmPassword) {
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
        confirmPassword.setCustomValidity("Passwords do not match.");
        } else {
            confirmPassword.setCustomValidity('');
        }
    }

    password.onchange = validatePassword;
    confirmPassword.onkeyup = validatePassword;
}