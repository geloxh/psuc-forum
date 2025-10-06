<?php
    require_once 'includes/auth.php';

    $auth = new Auth();
    $error = '';
    $success = '';

    if($_POST) {
        if($auth->register($_POST['username'], $_POST['email'], $_POST['password'], $_POST['full_name'], $_POST['university'])) {
            $success = 'Registration successful! You can now login.';
        } else {
            $error = 'Registration failed. Username or email may already exist.';
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
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>University/College</label>
                        <select name="university" class="form-control" required>
                            <option value="">Select your institution</option>
                            <?php foreach($universities as $uni): ?>
                                <option value="<?php echo $uni; ?>"><?php echo $uni; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
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
</body>
</html>