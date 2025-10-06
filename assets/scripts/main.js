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