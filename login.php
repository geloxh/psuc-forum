<?php
require_once 'includes/auth.php';

$auth = new Auth();
$error = '';

if($_POST) {
    if($auth->login($_POST['username'], $_POST['password'])) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin-top: 5rem;">
        <div class="forum-content">
            <div class="p-3">
                <div class="text-center mb-3">
                    <h1><i class="fas fa-graduation-cap"></i> PSUC Forum</h1>
                    <p>Sign in to your account</p>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Username or Email</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                </form>
                
                <div class="text-center mt-2">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                    <p><a href="index.php">Back to Forum</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>