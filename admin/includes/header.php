<header class="header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" class="logo">
                <img src="../assets/imgs/suc-logo.jpg" alt="PSUC Admin Logo" style="height: 40px;"> PSUC Admin
            </a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt"></i>Dashboard
                    </a></li>
                    <li><a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>Users
                    </a></li>
                    <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                        <i class="fas fa-folder"></i>Categories
                    </a></li>
                    <li><a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i>Reports
                    </a></li>
                    <li><a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>Settings
                    </a></li>
                    <li><a href="../index.php"><i class="fas fa-arrow-left"></i>Back to Forum</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>
