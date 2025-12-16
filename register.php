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
        } elseif ($auth -> emailExists($email)) {
            $error = "Existing Email User Account.";
        } elseif (empty($username) || empty($full_name) || empty($university) || empty($role)) {
            $error = "Please fill out all required fields.";
        } else {
            if($auth -> register($username, $email, $password, $full_name, $university, $role)) {
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
    <style>
        @import url('https://fonts.cdnfonts.com/css/optima');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Optima', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .logo {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .logo-img {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            display: block;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0 0 0.5rem;
        }
        .subtitle {
            color: #718096;
            font-size: 0.95rem;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        input, select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            background: #f7fafc;
            transition: all 0.2s ease;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #4299e1;
            background: white;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }
        button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #4299e1, #3182ce);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin: 1.5rem 0;
            transition: all 0.2s ease;
        }
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(66, 153, 225, 0.3);
        }
        .error {
            background: #fed7d7;
            color: #c53030;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border-left: 4px solid #e53e3e;
        }
        .success {
            background: #c6f6d5;
            color: #2f855a;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            border-left: 4px solid #38a169;
        }
        .links {
            text-align: center;
            font-size: 0.9rem;
            color: #718096;
        }
        .links a {
            color: #4299e1;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        .links a:hover {
            color: #3182ce;
        }
        @media (max-width: 480px) {
            .container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="assets/imgs/suc-logo.jpg" alt="PSUC Logo" class="logo-img">
            <h1>PSUC Forum</h1>
            <p class="subtitle">Join the community</p>
        </div>
                
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
                
        <form method="POST">
            <div class="form-group">
                <input type="text" name="full_name" placeholder="Full Name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <select name="university" required>
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
                <select name="role" required>
                            <option value="college student" <?php echo (isset($_POST['role']) && $_POST['role'] == 'college student') ? 'selected' : ''; ?>>College Student</option>
                            <option value="faculty" <?php echo (isset($_POST['role']) && $_POST['role'] == 'faculty') ? 'selected' : ''; ?>>Faculty</option>
                            <option value="other" <?php echo (isset($_POST['role']) && $_POST['role'] == 'other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required minlength="6">
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="6">
            </div>
            <button type="submit">Register</button>
        </form>
        
        <div class="links">
            <a href="login.php">Already have an account?</a> • <a href="index.php">Back to forum</a>
        </div>
    </div>
</body>
</html>