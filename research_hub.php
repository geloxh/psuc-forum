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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #fafafa; }
        .header { background: white; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .container { max-width: 800px; margin: 0 auto; padding: 0 1rem; }
        .logo { display: flex; align-items: center; gap: 1rem; text-decoration: none; color: #333; }
        .logo img { height: 50px; border-radius: 8px; }
        .content { padding: 3rem 0; }
        h1 { font-size: 2rem; margin-bottom: 2rem; color: #333; }
        .research-grid { display: grid; gap: 1.5rem; }
        .research-item { background: white; padding: 1.5rem; border-radius: 8px; }
        .research-title { font-size: 1.1rem; margin-bottom: 0.5rem; color: #333; }
        .research-author { color: #4299e1; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .research-desc { color: #666; font-size: 0.9rem; line-height: 1.5; }
        .research-tags { margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .tag { background: #e2e8f0; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #4a5568; }
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
            <h1>Research Hub</h1>
            <div class="research-grid">
                <div class="research-item">
                    <div class="research-title">Sustainable Agriculture Practices in Philippine SUCs</div>
                    <div class="research-author">Dr. Maria Santos, Central Luzon State University</div>
                    <div class="research-desc">A comprehensive study on implementing sustainable farming techniques across state universities to promote environmental conservation and food security.</div>
                    <div class="research-tags">
                        <span class="tag">Agriculture</span>
                        <span class="tag">Sustainability</span>
                        <span class="tag">Environment</span>
                    </div>
                </div>
                <div class="research-item">
                    <div class="research-title">Digital Transformation in Higher Education</div>
                    <div class="research-author">Prof. Juan Dela Cruz, Polytechnic University of the Philippines</div>
                    <div class="research-desc">Analyzing the impact of digital technologies on teaching methodologies and student learning outcomes in Philippine state universities.</div>
                    <div class="research-tags">
                        <span class="tag">Education</span>
                        <span class="tag">Technology</span>
                        <span class="tag">Digital Learning</span>
                    </div>
                </div>
                <div class="research-item">
                    <div class="research-title">Marine Biodiversity Conservation</div>
                    <div class="research-author">Dr. Ana Reyes, University of the Philippines Marine Science Institute</div>
                    <div class="research-desc">Research on protecting marine ecosystems and promoting sustainable fishing practices in Philippine coastal areas.</div>
                    <div class="research-tags">
                        <span class="tag">Marine Science</span>
                        <span class="tag">Conservation</span>
                        <span class="tag">Biodiversity</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>