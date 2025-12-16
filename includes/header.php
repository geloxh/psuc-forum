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
@media (max-width: 768px) {
    .mobile-nav-toggle {
        display: block;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--text-primary);
        cursor: pointer;
        z-index: 1002;
    }

    .nav {
        position: fixed;
        top: 0;
        right: -100%;
        width: 280px;
        height: 100vh;
        background: white;
        box-shadow: -2px 0 20px rgba(0, 0, 0, 0.1);
        transition: right 0.3s ease;
        z-index: 1001;
        padding-top: 80px;
    }

    .nav.active {
        right: 0;
    }

    .nav-menu {
        flex-direction: column;
        padding: 1rem 0;
        gap: 0;
    }

    .nav-menu li {
        width: 100%;
    }

    .nav-menu > li > a {
        padding: 1rem 1.5rem;
        display: block;
        border-bottom: 1px solid #f0f0f0;
    }

    .dropdown {
        position: static;
        box-shadow: none;
        border: none;
        background: #f8f9fa;
        margin: 0;
        padding: 0;
    }

    .dropdown a {
        padding: 0.75rem 2rem;
        border-bottom: 1px solid #e9ecef;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileNavToggle = document.getElementById('mobileNavToggle');
    const navMenu = document.getElementById('navMenu');

    if (mobileNavToggle && navMenu) {
        mobileNavToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            
            const icon = this.querySelector('i');
            if (navMenu.classList.contains('active')) {
                icon.className = 'fas fa-times';
            } else {
                icon.className = 'fas fa-bars';
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav') && !e.target.closest('.mobile-nav-toggle')) {
                navMenu.classList.remove('active');
                const icon = mobileNavToggle.querySelector('i');
                icon.className = 'fas fa-bars';
            }
        });
    }
});
</script>

<script src="assets/scripts/main.js"></script>
