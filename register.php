<?php
    require_once 'includes/auth.php';

    $auth = new Auth();
    $error = '';
    $success = '';

    if($_POST) {
        // --- Basic Server-Side Validation ---
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $full_name = trim($_POST['full_name']);
        $university = $_POST['university'];
        $role = $_POST['role'];

        if ($_POST['password'] !== $_POST['confirm_password']) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif (empty($username) || empty($full_name) || empty($university) || empty($role)) {
            $error = "Please fill out all required fields.";
        } else {
            if($auth->register($username, $email, $password, $full_name, $university, $role)) {
                $success = 'Registration successful! You can now login.';
                // Clear POST data on success to not repopulate the form
                $_POST = [];
            } else {
                $error = 'Registration failed. Username or email may already exist.';
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
        'Bicol University',
        'University of the Philippines in the Visayas'
    ],
    'Regional State Universities' => [
        'Bataan Peninsula State University',
        'Bulacan State University',
        'Cavite State University',
        'Laguna State Polytechnic University',
        'Nueva Ecija University of Science and Technology',
        'Pangasinan State University',
        'Tarlac State University',
        'Aurora State College of Technology',
        'Batangas State University',
        'Rizal Technological University'
    ],
    'Mindanao State Universities' => [
        'Mindanao State University - Main Campus',
        'Mindanao State University - Iligan Institute of Technology',
        'Mindanao State University - Tawi-Tawi',
        'Western Mindanao State University',
        'Southern Philippines Agribusiness and Marine and Aquatic School of Technology',
        'Surigao State College of Technology'
    ],
    'Visayas State Universities' => [
        'Visayas State University',
        'Central Philippines State University',
        'Negros Oriental State University',
        'Silliman University',
        'West Visayas State University',
        'Aklan State University',
        'Capiz State University'
    ]
    ];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin-top: 3rem;">
        <div class="forum-content">
            <div class="p-3">
                <div class="text-center mb-3">
                    <h1><i class="fas fa-graduation-cap"></i> PSUC Forum</h1>
                    <p>Join the community</p>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>University/College</label>
                        <select name="university" class="form-control" required>
                            <option value="">Select your institution</option>
                            <?php foreach($universities as $group => $unis): ?>
                                <optgroup label="<?php echo htmlspecialchars($group); ?>">
                                    <?php foreach($unis as $uni): ?>
                                        <option value="<?php echo htmlspecialchars($uni); ?>" <?php echo (isset($_POST['university']) && $_POST['university'] == $uni) ? 'selected' : ''; ?>><?php echo htmlspecialchars($uni); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="college student" <?php echo (isset($_POST['role']) && $_POST['role'] == 'college student') ? 'selected' : ''; ?>>College Student</option>
                            <option value="faculty" <?php echo (isset($_POST['role']) && $_POST['role'] == 'faculty') ? 'selected' : ''; ?>>Faculty</option>
                            <option value="other" <?php echo (isset($_POST['role']) && $_POST['role'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
                </form>
                
                <div class="text-center mt-2">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                    <p><a href="index.php">Back to Forum</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/scripts/main.js"></script>
</body>
</html>