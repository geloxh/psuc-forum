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
    <title>About - PSUC Forum</title>
    <link rel="stylesheet" href="assets/stylesheets/main.css">
    <link rel="stylesheet" href="assets/stylesheets/dark-theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-info-circle"></i> About PSUC Forum</h1>
                    
                    <div style="margin: 2rem 0;">
                        <h2>Welcome to the Philippine State Universities and Colleges Forum</h2>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-secondary);">
                            A modern, responsive forum platform connecting PSUC communities nationwide. 
                            Our mission is to foster collaboration, knowledge sharing, and community building 
                            among students, faculty, and staff from Philippine State Universities and Colleges.
                        </p>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin: 2rem 0;">
                        <div class="widget">
                            <h3><i class="fas fa-graduation-cap"></i> Our Mission</h3>
                            <p>To create a unified platform where members of the PSUC community can connect, collaborate, and share knowledge across different institutions and disciplines.</p>
                        </div>

                        <div class="widget">
                            <h3><i class="fas fa-users"></i> Community</h3>
                            <p>Join thousands of students, faculty, and staff from state universities and colleges across the Philippines in meaningful discussions and collaborations.</p>
                        </div>

                        <div class="widget">
                            <h3><i class="fas fa-lightbulb"></i> Innovation</h3>
                            <p>Share research, academic resources, and innovative ideas that contribute to the advancement of education and research in the Philippines.</p>
                        </div>
                    </div>

                    <div style="margin: 2rem 0;">
                        <h2>Features</h2>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                            <div style="padding: 1rem; border-left: 4px solid var(--primary-color);">
                                <h4><i class="fas fa-comments"></i> Discussion Forums</h4>
                                <p>Organized categories for academic discussions, research collaboration, and general community topics.</p>
                            </div>
                            <div style="padding: 1rem; border-left: 4px solid var(--success-color);">
                                <h4><i class="fas fa-envelope"></i> Private Messaging</h4>
                                <p>Connect directly with other members through our secure messaging system.</p>
                            </div>
                            <div style="padding: 1rem; border-left: 4px solid var(--warning-color);">
                                <h4><i class="fas fa-search"></i> Advanced Search</h4>
                                <p>Find relevant discussions, resources, and members quickly with our powerful search functionality.</p>
                            </div>
                            <div style="padding: 1rem; border-left: 4px solid var(--info-color);">
                                <h4><i class="fas fa-mobile-alt"></i> Mobile Responsive</h4>
                                <p>Access the forum seamlessly from any device - desktop, tablet, or mobile phone.</p>
                            </div>
                        </div>
                    </div>

                    <div style="margin: 2rem 0;">
                        <h2>Get Started</h2>
                        <p>Ready to join our community? Here's how to get started:</p>
                        <ol style="line-height: 2;">
                            <li><strong>Register:</strong> Create your account by selecting your university and role</li>
                            <li><strong>Explore:</strong> Browse through different categories and forums</li>
                            <li><strong>Participate:</strong> Start discussions, reply to posts, and engage with the community</li>
                            <li><strong>Connect:</strong> Use private messaging to collaborate with other members</li>
                            <li><strong>Contribute:</strong> Share your knowledge, resources, and experiences</li>
                        </ol>
                    </div>

                    <?php if(!$user): ?>
                        <div style="text-align: center; margin: 2rem 0; padding: 2rem; background: var(--card-bg); border-radius: 8px;">
                            <h3>Join Our Community Today!</h3>
                            <p>Connect with fellow students and faculty from Philippine State Universities and Colleges.</p>
                            <div style="margin-top: 1rem;">
                                <a href="register.php" class="btn btn-primary" style="margin-right: 1rem;">
                                    <i class="fas fa-user-plus"></i> Register Now
                                </a>
                                <a href="login.php" class="btn btn-secondary">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-chart-line"></i> Forum Statistics</h3>
                    <?php
                    $database = new Database();
                    $conn = $database->getConnection();
                    $stats_query = "SELECT 
                        (SELECT COUNT(*) FROM users) as total_users,
                        (SELECT COUNT(*) FROM topics) as total_topics,
                        (SELECT COUNT(*) FROM posts) as total_posts";
                    $stmt = $conn->prepare($stats_query);
                    $stmt->execute();
                    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <strong><?php echo number_format($stats['total_users']); ?></strong>
                            <span>Members</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo number_format($stats['total_topics']); ?></strong>
                            <span>Topics</span>
                        </div>
                        <div class="stat-item">
                            <strong><?php echo number_format($stats['total_posts']); ?></strong>
                            <span>Posts</span>
                        </div>
                    </div>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-envelope"></i> Contact</h3>
                    <p>Have questions or suggestions? We'd love to hear from you!</p>
                    <p><i class="fas fa-envelope"></i> admin@psucforum.edu.ph</p>
                    <p><i class="fas fa-phone"></i> +63 (2) 123-4567</p>
                </div>
            </aside>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
</body>
</html>