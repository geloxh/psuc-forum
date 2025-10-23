<?php
require_once 'includes/auth.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

if(!$user) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$success = '';
$error = '';

if($_POST) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $university = $_POST['university'];
    
    if(empty($full_name) || empty($email) || empty($university)) {
        $error = 'Please fill out all required fields.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $query = "UPDATE users SET full_name = ?, email = ?, university = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        if($stmt->execute([$full_name, $email, $university, $user['id']])) {
            $success = 'Profile updated successfully!';
            // Refresh user data
            $user = $auth->getCurrentUser();
        } else {
            $error = 'Failed to update profile. Email may already be in use.';
        }
    }
}

$universities = [
    'University of the Philippines System' => [
        'University of the Philippines Diliman',
        'University of the Philippines Manila',
        'University of the Philippines Los Baños',
        'University of the Philippines Visayas',
        'University of the Philippines Mindanao',
        'University of the Philippines Open University',
        'University of the Philippines Baguio',
        'University of the Philippines Cebu'
    ],
    'Major State Universities' => [
        'Polytechnic University of the Philippines',
        'Technological University of the Philippines',
        'Philippine Normal University',
        'Mindanao State University',
        'Central Luzon State University',
        'Visayas State University',
        'Bicol University'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-cog"></i> Account Settings</h1>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required 
                                   value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        <div class="form-group">
                            <label>University/College</label>
                            <select name="university" class="form-control" required>
                                <?php foreach($universities as $group => $unis): ?>
                                    <optgroup label="<?php echo htmlspecialchars($group); ?>">
                                        <?php foreach($unis as $uni): ?>
                                            <option value="<?php echo htmlspecialchars($uni); ?>" 
                                                    <?php echo $user['university'] == $uni ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($uni); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small class="text-secondary">Username cannot be changed</small>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                            <small class="text-secondary">Role is assigned by administrators</small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-user"></i> Account Info</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <strong><?php echo date('M j, Y', strtotime($user['created_at'])); ?></strong>
                            <span>Member Since</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo $user['reputation']; ?></strong>
                            <span>Reputation</span>
                        </div>
                    </div>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-shield-alt"></i> Privacy & Security</h3>
                    <p style="font-size: 0.9rem; line-height: 1.6;">
                        Your account information is kept secure and private. Only your username and university are visible to other users.
                    </p>
                </div>
            </aside>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
</body>
</html>