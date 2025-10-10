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

    // Handle AJAX requests
    if(isset($_POST['ajax'])) {
        header('Content-Type: application/json');

        if($_POST['action'] == 'update_role') {
            $stmt = $conn -> prepare("UPDATE users SET role = ? WHERE id = ?");
            $result = $stmt -> execute([$_POST['role'], $_POST['user_id']]);
            echo json_encode(['success' => $result]);
            exit;
        }

        if($_POST['action'] == 'delete_user') {
            $stmt = $conn -> prepare('DELETE FROM users WHERE id = ?');
            $result = $stmt -> execute([$_POST['user_id']]);
            echo json_encode(['success' => $result]);
            exit;
        }
    }

    // Pagination and search
    $page = isset($_GET['page']) ?  (int)$_GET['page'] : 1;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $role_filter = isset($_GET['role']) ? $_GET['role'] : '';
    $per_page = 20;
    $offset = ($page - 1) * $per_page;

    $where_conditions = [];
    $params  = [];

    if($search) {
        $where_conditions[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if($role_filter) {
        $where_conditions[] = "role = ?";
        $params[] = $role_filter;
    }

    $where_clause = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Get total count
    $count_query = "SELECT COUNT(*) FROM users $where_clause";
    $stmt = $conn -> prepare($count_query);
    $stmt -> execute($params);
    $total_users = $stmt -> fetchColumn();
    $total_pages = ceil($total_users / $per_page);

    // Get users
    $query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
    $stmt = $conn -> prepare($query);
    $stmt -> execute($params);
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
    <?php
        include __DIR__ . '/includes/header.php';
    
    ?>
    <main class="container">
        <div class="main-content" style="grid-template-columns: 1fr;">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-users"></i> Manage Users</h1>
                    <p class="text-secondary">Total users: <?php echo $total_users; ?></p>
                </div>

                <!-- Filters -->
                <div class="p-3">
                    <form method="GET" class="filters">
                        <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                        <select name="role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="moderator" <?php echo $role_filter == 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                            <option value="faculty" <?php echo $role_filter == 'faculty' ? 'selected' : ''; ?>>Faculty</option>
                            <option value="student" <?php echo $role_filter == 'student' ? 'selected' : ''; ?>>Student</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="users.php" class="btn btn-secondary">Clear</a>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="p-3">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                                <tr data-user-id="<?php echo $u['id']; ?>">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <img src="../assets/avatars/<?php echo $u['avatar'] ?: 'default.png'; ?>" 
                                                 alt="Avatar" class="user-avatar">
                                            <div>
                                                <strong><?php echo htmlspecialchars($u['username']); ?></strong>
                                                <br><small><?php echo htmlspecialchars($u['full_name']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <select class="role-select" data-user-id="<?php echo $u['id']; ?>">
                                            <option value="admin" <?php echo $u['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="moderator" <?php echo $u['role'] == 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                                            <option value="faculty" <?php echo $u['role'] == 'faculty' ? 'selected' : ''; ?>>Faculty</option>
                                            <option value="student" <?php echo $u['role'] == 'student' ? 'selected' : ''; ?>>Student</option>
                                        </select>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-danger btn-sm delete-user" data-user-id="<?php echo $u['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if($total_pages > 1): ?>
                        <div class="pagination">
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>" 
                                   class="<?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
</body>
</html>