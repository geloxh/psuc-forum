<header class="header">
    <div class="container">
        <div class="header-content" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="../index.php" class="logo">
                    <i class="fas fa-shield-alt"></i>
                    <span>Admin Panel</span>
                </a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                        <li><a href="user.php"><i class="fas fa-users"></i>Users</a></li>
                        <li><a href="settings.php"><i class="fas fa-cog"></i>Settings</a></li>
                    </ul>
                </nav>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem;">
                <button onclick="toggleTheme()" style="background: none; border: 1px solid var(--border-color); padding: 0.5rem; border-radius: 0.5rem;">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                <span style="color; var(--secondary-color);">
                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                </span>
                <a href="../logout.php" style="color: var(--danger-color); text-decoration: none;">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </div>
</header>

<script src="../assets/scripts/main.js"></script>