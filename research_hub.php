<?php

    require_once 'includes/auth.php';
    $auth = new Auth();
    $user = $auth->getCurrentUser();
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Hub - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #fafafa; }
        .container { max-width: 800px; margin: 0 auto; padding: 0 1rem; }
        .content { padding: 3rem 0; text-align: center; }
        h1 { font-size: 2rem; margin-bottom: 1rem; color: #333; }
        .coming-soon { background: white; padding: 3rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .coming-soon h2 { font-size: 1.5rem; color: #4299e1; margin-bottom: 1rem; }
        .coming-soon p { color: #666; font-size: 1rem; line-height: 1.5; }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="content">
            <h1>Research Hub</h1>
            <div class="coming-soon">
                <h2>Coming Soon</h2>
                <p>The Research Hub Page is currently under development.
            </div>
        </div>
    </main>
</body>
</html>