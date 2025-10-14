
// Dark - Light Mode Toggle
function toggleTheme() {
    const body = document.body;
    const icon = document.getElementById('themeIcon');

    if (body.classList.contains('dark-theme')) {
        body.classList.remove('dark-theme');
        icon.className = 'fas fa-moon';
        localStorage.setItem('theme', 'light');
    } else {
        body.classList.add('dark-theme');
        icon.className = 'fas fa-sun';
        localStorage.setItem('theme', 'dark');
    }
}

function toggleDropdown() {
    document.getElementById('userDropdown').classList.toggle('show');
}

// Load saved theme
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    const icon = document.getElementById('themeIcon');

    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        if(icon) icon.className = 'fas fa-sun';
    }
});

// Close dropdown when clicking outside
window.onclick = function(event) {
    if (!event.target.matches('.user-menu a')) {
        var dropdown = document.getElementById('userDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
}


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

function validatePassword() {
    if (password.value !== confirmPassword.value) {
        confirmPassword.setCustomValidity("Passwords do not match.");
    } else {
        confirmPassword.setCustomValidity('');
    }
}

password.onchange = validatePassword;
confirmPassword.onkeyup = validatePassword;