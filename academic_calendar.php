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
    <title>Academic Calendar - PSUC Forum</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #fafafa; }
        .header { background: white; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .container { max-width: 800px; margin: 0 auto; padding: 0 1rem; }
        .logo { display: flex; align-items: center; gap: 1rem; text-decoration: none; color: #333; }
        .logo img { height: 50px; border-radius: 8px; }
        .content { padding: 3rem 0; }
        h1 { font-size: 2rem; margin-bottom: 2rem; color: #333; }
        .calendar-grid { display: grid; gap: 1.5rem; }
        .event { background: white; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #4299e1; }
        .event-date { font-weight: 600; color: #4299e1; margin-bottom: 0.5rem; }
        .event-title { font-size: 1.1rem; margin-bottom: 0.5rem; }
        .event-desc { color: #666; font-size: 0.9rem; }
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
            <h1>Academic Calendar</h1>
            <div class="calendar-grid">
                <div class="event">
                    <div class="event-date">January 15, 2024</div>
                    <div class="event-title">First Semester Begins</div>
                    <div class="event-desc">Start of classes for the first semester</div>
                </div>
                <div class="event">
                    <div class="event-date">March 25-29, 2024</div>
                    <div class="event-title">Midterm Examinations</div>
                    <div class="event-desc">First semester midterm examination period</div>
                </div>
                <div class="event">
                    <div class="event-date">May 20-24, 2024</div>
                    <div class="event-title">Final Examinations</div>
                    <div class="event-desc">First semester final examination period</div>
                </div>
                <div class="event">
                    <div class="event-date">June 10, 2024</div>
                    <div class="event-title">Second Semester Begins</div>
                    <div class="event-desc">Start of classes for the second semester</div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>