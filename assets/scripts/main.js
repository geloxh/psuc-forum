// Dark - Light Mode Toggle
function toggleTheme() {
    document.body.classList.toggle('dark-theme');
    const isDark = document.body.classList.contains('dark-theme');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateThemeIcon(isDark);
}

function updateThemeIcon(isDark) {
    const icon = document.getElementById('themeIcon');
    if (icon) {
        icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// Apply theme on initial load to prevent FOUC (Flash of Unstyled Content)
document.addEventListener('DOMContentLoaded', function() {
    const theme = localStorage.getItem('theme');
    const isDark = theme === 'dark';
    if (isDark) {
        document.body.classList.add('dark-theme');
    }
    updateThemeIcon(isDark);

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

// For Web Sidebar
function toggleCategory(categoryId) {
    const forums = document.getElementById('forums-' + categoryId);
    const arrow = document.getElementById('arrow-' + categoryId);
    
    forums.classList.toggle('open');
    arrow.classList.toggle('rotated');
}

function navigateToForum(forumName) {
    // Navigate to specific forum - adjust URL as needed
    window.location.href = 'forum.php?name=' + forumName;
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