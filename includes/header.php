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
                <img src="assets/imgs/suc-logo.jpg" alt="SUC Forum Logo" style="height: 40px;">
            </a>
            <button class="nav-toggle" aria-label="toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="nav">
                <ul class="nav-menu">
                    <li><a href="index.php"><i class="fas fa-home"></i>Home</a></li>
                    <li class="user-menu">
                        <a href="#" onclick="toggleDropdown(event)"><i class="fas fa-graduation-cap"></i>Academic <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown">
                            <a href="academic_calendar.php"><i class="fas fa-calendar-alt"></i>Academic Calendar</a>
                            <a href="document_library.php"><i class="fas fa-file-alt"></i>Document Library</a>
                            <a href="research_hub.php"><i class="fas fa-microscope"></i>Research Hub</a>
                        </div>
                    </li>
                    <li class="user-menu">
                        <a href="#" onclick="toggleDropdown(event)"><i class="fas fa-users"></i>Community <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown">
                            <a href="events.php"><i class="fas fa-calendar"></i>Events</a>
                            <a href="job_board.php"><i class="fas fa-briefcase"></i>Job Board</a>
                            <a href="university_groups.php"><i class="fas fa-university"></i>University Groups</a>
                        </div>
                    </li>
                    <li><a href="about.php"><i class="fas fa-info-circle"></i>About</a></li>
                    <li><a href="search.php"><i class="fas fa-search"></i> Search</a></li>
                    <li>
                        <button onclick="toggleTheme()" class="btn btn-theme-toggle">
                            <i class="fas fa-moon" id="themeIcon"></i>
                        </button>
                    </li>
                    <?php if($user): ?>
                        <li><a href="messages.php"><i class="fas fa-envelope"></i>Messages</a></li>
                        <li><a href="notifications.php"><i class="fas fa-bell"></i>Notifications</a></li>
                        <li class="user-menu"> 
                            <a href="#" onclick="toggleDropdown(event)">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="dropdown" id="userDropdown">
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