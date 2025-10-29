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
    <title>Events - PSUC Forum</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #fafafa; }
        .header { background: white; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .container { max-width: 800px; margin: 0 auto; padding: 0 1rem; }
        .logo { display: flex; align-items: center; gap: 1rem; text-decoration: none; color: #333; }
        .logo img { height: 50px; border-radius: 8px; }
        .content { padding: 3rem 0; }
        h1 { font-size: 2rem; margin-bottom: 2rem; color: #333; }
        .event-grid { display: grid; gap: 1.5rem; }
        .event-card { background: white; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #10b981; }
        .event-date { background: #10b981; color: white; padding: 0.5rem; border-radius: 4px; display: inline-block; font-size: 0.9rem; margin-bottom: 1rem; }
        .event-title { font-size: 1.2rem; margin-bottom: 0.5rem; color: #333; }
        .event-location { color: #666; margin-bottom: 0.5rem; }
        .event-desc { color: #666; font-size: 0.9rem; line-height: 1.5; }
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
            <h1>Upcoming Events</h1>
            <div class="event-grid">
                <div class="event-card">
                    <div class="event-date">March 15, 2024</div>
                    <div class="event-title">PSUC Research Conference 2024</div>
                    <div class="event-location">University of the Philippines Diliman</div>
                    <div class="event-desc">Annual research conference showcasing innovative studies from state universities and colleges across the Philippines.</div>
                </div>
                <div class="event-card">
                    <div class="event-date">April 8-10, 2024</div>
                    <div class="event-title">Inter-SUC Sports Festival</div>
                    <div class="event-location">Batangas State University</div>
                    <div class="event-desc">Three-day sports competition featuring various athletic events among Philippine state universities.</div>
                </div>
                <div class="event-card">
                    <div class="event-date">May 20, 2024</div>
                    <div class="event-title">Digital Innovation Summit</div>
                    <div class="event-location">Technological University of the Philippines</div>
                    <div class="event-desc">Summit focusing on digital transformation and innovation in higher education institutions.</div>
                </div>
                <div class="event-card">
                    <div class="event-date">June 5, 2024</div>
                    <div class="event-title">Environmental Sustainability Forum</div>
                    <div class="event-location">Central Luzon State University</div>
                    <div class="event-desc">Forum discussing sustainable practices and environmental conservation initiatives in academic institutions.</div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>