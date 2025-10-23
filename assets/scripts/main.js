// Apply theme on initial load to prevent FOUC (Flash of Unstyled Content)
document.addEventListener('DOMContentLoaded', function() {
    // Responsive navigation toggle
    const navToggle = document.querySelector('.nav-toggle');
    const nav = document.querySelector('.nav');

    navToggle?.addEventListener('click', () => {
        nav?.classList.toggle('nav--visible');
    });
});

// Consolidated Dropdown Logic
document.addEventListener('click', function(event) {
    const dropdownToggle = event.target.closest('.user-menu > a');
    const clickedDropdownMenu = dropdownToggle?.nextElementSibling;
    const isAlreadyOpen = clickedDropdownMenu?.classList.contains('show');

    // Always close all open dropdowns first
    document.querySelectorAll('.user-menu .dropdown.show').forEach(dropdown => {
        dropdown.classList.remove('show');
    });

    // If a dropdown toggle was clicked and it wasn't already open, open it.
    // This creates the toggle effect because we've already closed it above.
    if (dropdownToggle && !isAlreadyOpen) {
        event.preventDefault(); // Prevent navigation only when opening a dropdown
        clickedDropdownMenu.classList.add('show');
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