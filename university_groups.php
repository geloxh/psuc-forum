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
    <title>University Groups - PSUC Forum</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #fafafa; }
        .header { background: white; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .container { max-width: 800px; margin: 0 auto; padding: 0 1rem; }
        .logo { display: flex; align-items: center; gap: 1rem; text-decoration: none; color: #333; }
        .logo img { height: 50px; border-radius: 8px; }
        .content { padding: 3rem 0; }
        h1 { font-size: 2rem; margin-bottom: 2rem; color: #333; }
        .group-grid { display: grid; gap: 1.5rem; }
        .group-card { background: white; padding: 1.5rem; border-radius: 8px; }
        .group-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .group-avatar { width: 50px; height: 50px; background: #4299e1; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        .group-info h3 { margin-bottom: 0.5rem; color: #333; }
        .group-info p { color: #666; font-size: 0.9rem; }
        .group-desc { color: #666; font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem; }
        .group-stats { display: flex; gap: 1rem; font-size: 0.8rem; color: #666; }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="suc-logo.jpg" alt="PSUC Logo">
                <span>PSUC Forum</span>
            </a>
        </div>
    </header>
    
    <main class="container">
        <div class="content">
            <h1>University Groups</h1>
            <div class="group-grid">
                <div class="group-card">
                    <div class="group-header">
                        <div class="group-avatar">UP</div>
                        <div class="group-info">
                            <h3>University of the Philippines System</h3>
                            <p>Official group for all UP campuses</p>
                        </div>
                    </div>
                    <div class="group-desc">Connect with students, faculty, and alumni from all University of the Philippines campuses nationwide.</div>
                    <div class="group-stats">
                        <span>2,450 members</span>
                        <span>•</span>
                        <span>Active discussions</span>
                    </div>
                </div>
                <div class="group-card">
                    <div class="group-header">
                        <div class="group-avatar">PUP</div>
                        <div class="group-info">
                            <h3>Polytechnic University of the Philippines</h3>
                            <p>PUP community forum</p>
                        </div>
                    </div>
                    <div class="group-desc">Discussion space for PUP students, faculty, and staff to share academic resources and campus updates.</div>
                    <div class="group-stats">
                        <span>1,890 members</span>
                        <span>•</span>
                        <span>Daily posts</span>
                    </div>
                </div>
                <div class="group-card">
                    <div class="group-header">
                        <div class="group-avatar">MSU</div>
                        <div class="group-info">
                            <h3>Mindanao State University</h3>
                            <p>MSU community network</p>
                        </div>
                    </div>
                    <div class="group-desc">Platform for MSU community members to collaborate on research, share opportunities, and stay connected.</div>
                    <div class="group-stats">
                        <span>1,234 members</span>
                        <span>•</span>
                        <span>Research focused</span>
                    </div>
                </div>
                <div class="group-card">
                    <div class="group-header">
                        <div class="group-avatar">BSU</div>
                        <div class="group-info">
                            <h3>Batangas State University</h3>
                            <p>BSU official community</p>
                        </div>
                    </div>
                    <div class="group-desc">Hub for BSU students and faculty to discuss academic programs, events, and campus life.</div>
                    <div class="group-stats">
                        <span>987 members</span>
                        <span>•</span>
                        <span>Campus updates</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>