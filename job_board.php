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
    <title>Job Board - PSUC Forum</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #fafafa; }
        .header { background: white; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .container { max-width: 800px; margin: 0 auto; padding: 0 1rem; }
        .logo { display: flex; align-items: center; gap: 1rem; text-decoration: none; color: #333; }
        .logo img { height: 50px; border-radius: 8px; }
        .content { padding: 3rem 0; }
        h1 { font-size: 2rem; margin-bottom: 2rem; color: #333; }
        .job-grid { display: grid; gap: 1.5rem; }
        .job-card { background: white; padding: 1.5rem; border-radius: 8px; }
        .job-header { display: flex; justify-content: between; align-items: start; margin-bottom: 1rem; }
        .job-title { font-size: 1.2rem; color: #333; margin-bottom: 0.5rem; }
        .job-company { color: #4299e1; font-weight: 500; margin-bottom: 0.5rem; }
        .job-location { color: #666; font-size: 0.9rem; margin-bottom: 1rem; }
        .job-desc { color: #666; font-size: 0.9rem; line-height: 1.5; margin-bottom: 1rem; }
        .job-type { background: #e2e8f0; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #4a5568; display: inline-block; }
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
            <h1>Job Board</h1>
            <div class="job-grid">
                <div class="job-card">
                    <div class="job-title">Assistant Professor - Computer Science</div>
                    <div class="job-company">Polytechnic University of the Philippines</div>
                    <div class="job-location">Manila, Philippines</div>
                    <div class="job-desc">Seeking qualified faculty member to teach undergraduate computer science courses and conduct research in software engineering.</div>
                    <span class="job-type">Full-time</span>
                </div>
                <div class="job-card">
                    <div class="job-title">Research Assistant - Agricultural Sciences</div>
                    <div class="job-company">Central Luzon State University</div>
                    <div class="job-location">Nueva Ecija, Philippines</div>
                    <div class="job-desc">Research position focusing on sustainable farming practices and crop improvement technologies.</div>
                    <span class="job-type">Contract</span>
                </div>
                <div class="job-card">
                    <div class="job-title">Administrative Officer</div>
                    <div class="job-company">Batangas State University</div>
                    <div class="job-location">Batangas, Philippines</div>
                    <div class="job-desc">Administrative support for academic affairs and student services departments.</div>
                    <span class="job-type">Full-time</span>
                </div>
                <div class="job-card">
                    <div class="job-title">Laboratory Technician - Chemistry</div>
                    <div class="job-company">Mindanao State University</div>
                    <div class="job-location">Marawi, Philippines</div>
                    <div class="job-desc">Maintain laboratory equipment and assist in chemistry experiments and research activities.</div>
                    <span class="job-type">Part-time</span>
                </div>
            </div>
        </div>
    </main>
</body>
</html>