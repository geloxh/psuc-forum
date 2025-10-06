<?php
    require_once 'includes/auth.php';

    $auth = new Auth();
    $user = $auth->getCurrentUser();

    if(!$user) {
        header('Location: login.php');
        exit;
    }

    $success = '';
    $error = '';

    if($_POST) {
        $database = new Database();
        $conn = $database->getConnection();
    
        if(isset($_POST['update_profile'])) {
            $query = "UPDATE users SET full_name = ?, university = ? WHERE id = ?";
            $stmt = $conn -> prepare($query);
            if($stmt -> execute([$_POST['full_name'], $_POST['university'], $user['id']])) {
                $success = 'Profile updated successfully!';
            } else {
                $error = 'Failed to update profile.';
            }
        }
    
        if(isset($_POST['change_password'])) {
            if(password_verify($_POST['current_password'], $user['password'])) {
                if($_POST['new_password'] === $_POST['confirm_password']) {
                    $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    $query = "UPDATE users SET password = ? WHERE id = ?";
                    $stmt = $conn->prepare($query);

                    if($stmt -> execute([$hashed_password, $user['id']])) {
                    $success = 'Password changed successfully!';
                    } else {
                        
                        $error = 'Failed to change password.';
                    }
                } else {
                    $error = 'New passwords do not match.';
                }
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }

    $universities = [
        'University of the Philippines',
        'Polytechnic University of the Philippines',
        'Technological University of the Philippines',
        'Philippine Normal University',
        'Mindanao State University',
        'Central Luzon State University',
        'Visayas State University',
        'Bataan Peninsula State University',
        'Bulacan State University',
        'Cavite State University'
    ];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="assets/stylesheets/dark-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-cog"></i> Account Settings</h1>
                    <p class="text-secondary">Manage your account preferences and security</p>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                        <!-- Profile Settings -->
                        <div>
                            <h3><i class="fas fa-user"></i> Profile Information</h3>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="full_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                    <small class="text-secondary">Username cannot be changed</small>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    <small class="text-secondary">Email cannot be changed</small>
                                </div>
                                <div class="form-group">
                                    <label>University/College</label>
                                    <select name="university" class="form-control" required>
                                        <?php foreach($universities as $uni): ?>
                                            <option value="<?php echo $uni; ?>" <?php echo $user['university'] == $uni ? 'selected' : ''; ?>>
                                                <?php echo $uni; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </form>
                        </div>
                        
                        <!-- Security Settings -->
                        <div>
                            <h3><i class="fas fa-lock"></i> Security Settings</h3>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="6">
                                </div>
                                <div class="form-group">
                                    <label>Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                                </div>
                                <button type="submit" name="change_password" class="btn btn-danger">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Notification Preferences -->
                    <div style="margin-top: 3rem;">
                        <h3><i class="fas fa-bell"></i> Notification Preferences</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; margin-top: 1rem;">
                            <div style="padding: 1rem; background: var(--light-color); border-radius: 8px;">
                                <h4>Email Notifications</h4>
                                <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <input type="checkbox" checked> New replies to my topics
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <input type="checkbox" checked> Private messages
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="checkbox"> Weekly digest
                                </label>
                            </div>
                            
                            <div style="padding: 1rem; background: var(--light-color); border-radius: 8px;">
                                <h4>Privacy Settings</h4>
                                <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <input type="checkbox" checked> Show online status
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <input type="checkbox" checked> Allow private messages
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="checkbox"> Show email to other users
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Theme Preferences -->
                    <div style="margin-top: 3rem;">
                        <h3><i class="fas fa-palette"></i> Appearance</h3>
                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <button onclick="setTheme('light')" class="btn btn-secondary">
                                <i class="fas fa-sun"></i> Light Theme
                            </button>
                            <button onclick="setTheme('dark')" class="btn btn-secondary">
                                <i class="fas fa-moon"></i> Dark Theme
                            </button>
                            <button onclick="setTheme('auto')" class="btn btn-secondary">
                                <i class="fas fa-adjust"></i> Auto (System)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-info-circle"></i> Account Info</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <strong><?php echo ucfirst($user['role']); ?></strong>
                            <span>Account Type</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $user['reputation']; ?></strong>
                            <span>Reputation</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo date('M Y', strtotime($user['created_at'])); ?></strong>
                            <span>Member Since</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo ucfirst($user['status']); ?></strong>
                            <span>Status</span>
                        </div>
                    </div>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-shield-alt"></i> Security Tips</h3>
                    <ul style="padding-left: 1.5rem; line-height: 1.8;">
                        <li>Use a strong, unique password</li>
                        <li>Don't share your account credentials</li>
                        <li>Log out from public computers</li>
                        <li>Report suspicious activity</li>
                        <li>Keep your profile information updated</li>
                    </ul>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-question-circle"></i> Need Help?</h3>
                    <p style="font-size: 0.9rem; line-height: 1.6;">
                        If you need assistance with your account settings, please contact our support team or visit the Help forum.
                    </p>
                    <a href="forum.php?id=3" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-life-ring"></i> Get Help
                    </a>
                </div>
            </aside>
        </div>
    </main>

    <script>
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

        function setTheme(theme) {
            const body = document.body;
            const icon = document.getElementById('themeIcon');
            
            if (theme === 'dark') {
                body.classList.add('dark-theme');
                if(icon) icon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            } else if (theme === 'light') {
                body.classList.remove('dark-theme');
                if(icon) icon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                // Auto theme based on system preference
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    body.classList.add('dark-theme');
                    if(icon) icon.className = 'fas fa-sun';
                } else {
                    body.classList.remove('dark-theme');
                    if(icon) icon.className = 'fas fa-moon';
                }
                localStorage.setItem('theme', 'auto');
            }
        }

        function toggleDropdown() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const icon = document.getElementById('themeIcon');
            
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-theme');
                if(icon) icon.className = 'fas fa-sun';
            } else if (savedTheme === 'auto') {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.body.classList.add('dark-theme');
                    if(icon) icon.className = 'fas fa-sun';
                }
            }
        });

        window.onclick = function(event) {
            if (!event.target.matches('.user-menu a')) {
                var dropdown = document.getElementById('userDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }
    </script>
</body>
</html>