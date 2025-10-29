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
    <title>Document Library - PSUC Forum</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #fafafa; }
        .header { background: white; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .container { max-width: 800px; margin: 0 auto; padding: 0 1rem; }
        .logo { display: flex; align-items: center; gap: 1rem; text-decoration: none; color: #333; }
        .logo img { height: 50px; border-radius: 8px; }
        .content { padding: 3rem 0; }
        h1 { font-size: 2rem; margin-bottom: 2rem; color: #333; }
        .doc-grid { display: grid; gap: 1rem; }
        .doc-item { background: white; padding: 1.5rem; border-radius: 8px; display: flex; align-items: center; gap: 1rem; }
        .doc-icon { width: 40px; height: 40px; background: #4299e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; }
        .doc-info h3 { margin-bottom: 0.5rem; }
        .doc-info p { color: #666; font-size: 0.9rem; }
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
            <h1>Document Library</h1>
            <div class="doc-grid">
                <div class="doc-item">
                    <div class="doc-icon">📄</div>
                    <div class="doc-info">
                        <h3>Student Handbook</h3>
                        <p>Complete guide for students - policies, procedures, and academic requirements</p>
                    </div>
                </div>
                <div class="doc-item">
                    <div class="doc-icon">📋</div>
                    <div class="doc-info">
                        <h3>Enrollment Forms</h3>
                        <p>Registration and enrollment forms for all academic programs</p>
                    </div>
                </div>
                <div class="doc-item">
                    <div class="doc-icon">📊</div>
                    <div class="doc-info">
                        <h3>Research Guidelines</h3>
                        <p>Guidelines and templates for thesis and research projects</p>
                    </div>
                </div>
                <div class="doc-item">
                    <div class="doc-icon">🎓</div>
                    <div class="doc-info">
                        <h3>Graduation Requirements</h3>
                        <p>Complete checklist and requirements for graduation</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>