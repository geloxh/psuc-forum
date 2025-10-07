<?php
    require_once '../includes/auth.php';

    $auth = new Auth();
    $user = $auth -> getCurrentUser();

    if(!$user || $user['role'] != 'admin') {
        header('Location: ../login.php');
        exit;
    }

    $database = new Database();
    $conn = $database -> getConnection();

    // Handle user actions (e.g., delete, change role)
    if(isset($_GET['action']) && isset($_GET['id'])) {
        $action = $_GET['action'];
        $user_id = $_GET['id'];

        if($action == 'delete') {
            $stmt = $conn -> prepare("DELETE FROM users WHERE id = ?");
            $stmt -> execute([$user_id]);
        } elseif($action == 'role') {
            $new_role = $_GET['role'];
            $stmt = $conn -> prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt -> execute([$new_role, $user_id]);
        }
        header('Location: users.php');
        exit;
    }

    // Get all users
    $stmt = $conn -> prepare("SELECT * FROM users");
    $stmt -> execute();
    $users = $stmt -> fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Users - PSUC Admin</title>
        <link rel="stylesheet" href="assets/stylesheets/main.css">
        <link rel="stylesheet" href="assets/stylesheets/https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-shield-alt"></i> PSUC Admin
                </a>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
                        <li><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                        <li><a href="settings.php"><i class="fas fa-cog"></i> Forum Settings</a></li>
                        <li><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Forum</a></li>
                        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="main-content" style="grid-template-columns: 1fr;">
            <div class="p-3">
                <h1><i class="fas fa-users"></i>Manage Users</h1>
                <p class="text-secondary">Total users: <?php echo count($users); ?></p>
            </div>

            <div class="p-3">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</td>
                            <th>Username</td>
                            <th>Email</td>
                            <th>Role</td>
                            <th>Actions</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?> </td>
                                <td>
                                    <form action="users.php" method="GET" style="display: inline;">
                                        <input type="hidden" name="action" value="role">
                                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                        <select name="role" onchange="this.form.submit()">
                                            <option value="admin" <?php if($u['role'] == 'admin') echo 'selected'; ?>Admin</option>
                                            <option value="moderator" <?php if($u['role'] == 'moderator') echo 'selected'; ?>Moderator</option>
                                            <option value="faculty" <?php if($u['role'] == 'faculty') echo 'selected'; ?>Faculty</option>
                                            <option value="student" <?php if($u['role'] == 'student') echo 'selected'; ?>Student</option>
                                            <option value="other" <?php if($u['role'] == 'other') echo 'selected'; ?>Other</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <a href="users.php?action=delete&id=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php 
                            endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </main>
</body>
</html>