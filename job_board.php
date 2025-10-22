<?php
require_once 'includes/auth.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

$database = new Database();
$conn = $database->getConnection();

// Get job postings
$jobs_query = "SELECT j.*, u.username as posted_by_name FROM job_board j 
               LEFT JOIN users u ON j.posted_by = u.id 
               WHERE j.status = 'active' 
               ORDER BY j.created_at DESC";
$stmt = $conn->prepare($jobs_query);
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Board - PSUC Forum</title>
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
                    <h1><i class="fas fa-briefcase"></i> Job Board</h1>
                    <p class="text-secondary">Career opportunities for PSUC community members</p>
                    
                    <?php if(count($jobs) > 0): ?>
                        <div style="margin-top: 2rem;">
                            <?php foreach($jobs as $job): ?>
                                <div class="widget" style="margin-bottom: 1.5rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                        <div>
                                            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                            <p style="color: var(--primary-color); font-weight: 600; margin: 0.5rem 0;">
                                                <i class="fas fa-building"></i> <?php echo htmlspecialchars($job['company']); ?>
                                            </p>
                                        </div>
                                        <div style="text-align: right;">
                                            <span class="badge" style="background: var(--success-color);">
                                                <?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?>
                                            </span>
                                            <?php if($job['salary_range']): ?>
                                                <div style="margin-top: 0.5rem; font-weight: 600;">
                                                    <?php echo htmlspecialchars($job['salary_range']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <p><?php echo nl2br(htmlspecialchars(substr($job['description'], 0, 300))); ?>
                                    <?php if(strlen($job['description']) > 300): ?>...<?php endif; ?></p>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                        <div style="display: flex; gap: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                                            <?php if($job['location']): ?>
                                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                            <?php endif; ?>
                                            <span><i class="fas fa-clock"></i> Posted <?php echo date('M j, Y', strtotime($job['created_at'])); ?></span>
                                            <?php if($job['application_deadline']): ?>
                                                <span><i class="fas fa-calendar"></i> Deadline: <?php echo date('M j, Y', strtotime($job['application_deadline'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if($job['contact_email']): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($job['contact_email']); ?>" class="btn btn-primary">
                                                <i class="fas fa-envelope"></i> Apply
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center" style="padding: 3rem;">
                            <i class="fas fa-briefcase" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                            <h3>No job postings available</h3>
                            <p class="text-secondary">Check back later for new career opportunities.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-filter"></i> Job Types</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div><span class="badge" style="background: #007bff;">Full-time</span> Permanent positions</div>
                        <div><span class="badge" style="background: #28a745;">Part-time</span> Flexible hours</div>
                        <div><span class="badge" style="background: #ffc107;">Internship</span> Learning opportunities</div>
                        <div><span class="badge" style="background: #17a2b8;">Freelance</span> Project-based work</div>
                    </div>
                </div>

                <div class="widget">
                    <h3><i class="fas fa-tips"></i> Application Tips</h3>
                    <ul style="padding-left: 1.5rem; line-height: 1.8;">
                        <li>Tailor your resume to the job</li>
                        <li>Write a compelling cover letter</li>
                        <li>Research the company</li>
                        <li>Follow application instructions</li>
                        <li>Apply before the deadline</li>
                    </ul>
                </div>
            </aside>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
</body>
</html>