<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($auth)) {
    require_once __DIR__ . '/auth.php';
    $auth = new Auth();
    $user = $auth->getCurrentUser();
}
?>

<header class="header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" class="logo">
                <img src="assets/imgs/suc-logo.jpg" alt="SUC Forum Logo" style="height: 60px;">
            </a>

            <button class="mobile-nav-toggle" id="mobileNavToggle">
                <i class="fas fa-bars"></i>
            </button>

            <nav class="nav" id="navMenu">
                <ul class="nav-menu">
                    <li><a href="index.php"><i class="fas fa-home"></i>Home</a></li>
                    <li class="user-menu dropdown-toggle">
                        <a href="#"><i class="fas fa-graduation-cap"></i>Academic <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown">
                            <a href="academic_calendar.php"><i class="fas fa-calendar-alt"></i>Academic Calendar</a>
                            <a href="document_library.php"><i class="fas fa-file-alt"></i>Document Library</a>
                            <a href="research_hub.php"><i class="fas fa-microscope"></i>Research Hub</a>
                        </div>
                    </li>
                    <li class="user-menu dropdown-toggle">
                        <a href="#"><i class="fas fa-users"></i>Community <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown">
                            <a href="events.php"><i class="fas fa-calendar"></i>Events</a>
                            <a href="job_board.php"><i class="fas fa-briefcase"></i>Job Board</a>
                            <a href="university_groups.php"><i class="fas fa-university"></i>University Groups</a>
                        </div>
                    </li>
                    <li><a href="about.php"><i class="fas fa-info-circle"></i>About</a></li>
                    <li><a href="search.php"><i class="fas fa-search"></i>Search</a></li>
                    <?php if($user): ?>
                        <li><a href="messages.php"><i class="fas fa-envelope"></i>Messages</a></li>
                        <li><a href="notifications.php"><i class="fas fa-bell"></i>Notifications</a></li>
                        <li class="user-menu dropdown-toggle">
                            <a href="#">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="dropdown">
                                <a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
                                <a href="settings.php"><i class="fas fa-cog"></i>Settings</a>
                                <?php if($user['role'] == 'admin'): ?>
                                    <a href="admin/"><i class="fas fa-shield-alt"></i>Admin Panel</a>
                                <?php endif; ?>
                                <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i>Login</a></li>
                        <li><a href="register.php"><i class="fas fa-user-plus"></i>Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</header>

<style>
.mobile-nav-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 1.2rem;
    color: var(--text-primary);
    cursor: pointer;
    z-index: 1002;
    transition: transform 0.2s ease;
}

.mobile-nav-toggle:hover {
    transform: scale(1.1);
}

.dropdown {
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
}

.dropdown-toggle.active .dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-toggle .fa-chevron-down {
    transition: transform 0.2s ease;
}

.dropdown-toggle.active .fa-chevron-down {
    transform: rotate(180deg);
}

@media (max-width: 768px) {
    .mobile-nav-toggle {
        display: block;
    }

    .nav {
        position: fixed;
        top: 0;
        right: -100%;
        width: 260px;
        height: 100vh;
        background: white;
        box-shadow: -4px 0 15px rgba(0,0,0,0.1);
        transition: right 0.25s ease;
        z-index: 1001;
        padding-top: 70px;
        overflow-y: auto;
    }

    .nav.active {
        right: 0;
    }

    .nav-menu {
        flex-direction: column;
        padding: 0;
        gap: 0;
    }

    .nav-menu li {
        width: 100%;
    }

    .nav-menu > li > a {
        padding: 1rem 1.5rem;
        display: block;
        border-bottom: 1px solid #f5f5f5;
        transition: background 0.2s ease;
    }

    .nav-menu > li > a:hover {
        background: #f8f9fa;
    }

    .dropdown {
        position: static;
        box-shadow: none;
        border: none;
        background: #f8f9fa;
        margin: 0;
        padding: 0;
        opacity: 1;
        visibility: visible;
        transform: none;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }

    .dropdown-toggle.active .dropdown {
        max-height: 200px;
    }

    .dropdown a {
        padding: 0.75rem 2.5rem;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.2s ease;
    }

    .dropdown a:hover {
        background: #e9ecef;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('mobileNavToggle');
    const nav = document.getElementById('navMenu');

    // Mobile menu toggle
    if (toggle && nav) {
        toggle.addEventListener('click', function() {
            nav.classList.toggle('active');
            const icon = this.querySelector('i');
            icon.className = nav.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav') && !e.target.closest('.mobile-nav-toggle')) {
                nav.classList.remove('active');
                toggle.querySelector('i').className = 'fas fa-bars';
            }
        });
    }

    // Dropdown functionality for both desktop and mobile
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(dropdown => {
        const link = dropdown.querySelector('a[href="#"]');
        if (link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Close other dropdowns
                dropdowns.forEach(other => {
                    if (other !== dropdown) {
                        other.classList.remove('active');
                    }
                });
                
                // Toggle current dropdown
                dropdown.classList.toggle('active');
            });
        }
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-toggle')) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
});
</script>

<script src="assets/scripts/main.js"></script>
