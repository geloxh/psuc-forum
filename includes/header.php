<header class="header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" class="logo">
                <img src="assets/imgs/suc-logo.jpg" alt="SUC Forum Logo" style="height: 40px;">
            </a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php"><i class="fas fa-home"></i>Home</a></li>
                    <li><a href="about.php"><i class="fas fa-info-circle"></i>About</a></li>
                    <li><a href="search.php"><i class="fas fa-search"></i> Search</a></li>
                    <li>
                        <button onclick="toggleTheme()" class="btn" style="background: rgba(255,255,255,0.1); border: none; color: white;">
                            <i class="fas fa-moon" id="themeIcon"></i>
                        </button>
                    </li>
                    <?php if($user): ?>
                        <li><a href="messages.php"><i class="fas fa-envelope"></i>Messages</a></li>
                        <li><a href="notifications.php"><i class="fas fa-bell"></i>Notifications</a></li>
                        <li class="user-menu">
                            <a href="#" onclick="toggleDropdown()">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <div class="dropdown" id="userDropdown">
                                <a href="profile.php"><i class="fas fa-user-circle"></i>Profile</a>
                                <a href="settings.php"><i class="fas fa-cog"></i>Settings</a>
                                <a href="notifications.php"><i class="fas fa-bell"></i>Notifications</a>
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