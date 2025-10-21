// job_board.php
<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$auth = new Auth();
$user = $auth->getCurrentUser();
$database = new Database();
$conn = $database->getConnection();

// Handle job posting
if ($_POST && $user) {
    $stmt = $conn->prepare("INSERT INTO job_board (title, company, description, requirements, location, job_type, salary_range, application_deadline, contact_email, posted_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['title'], $_POST['company'], $_POST['description'], $_POST['requirements'],
        $_POST['location'], $_POST['job_type'], $_POST['salary_range'],
        $_POST['application_deadline'], $_POST['contact_email'], $user['id']
    ]);
    header('Location: job_board.php');
    exit;
}

// Get job listings
$jobs_query = "SELECT jb.*, u.full_name as poster_name 
               FROM job_board jb 
               JOIN users u ON jb.posted_by = u.id 
               WHERE jb.status = 'active' AND (jb.application_deadline IS NULL OR jb.application_deadline >= CURDATE())
               ORDER BY jb.created_at DESC";
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-briefcase"></i> Job Board</h1>
                    <p class="text-secondary">Career opportunities for PSUC students and graduates</p>
                    
                    <?php if ($user): ?>
                    <button onclick="toggleJobForm()" class="btn btn-primary mb-3">
                        <i class="fas fa-plus"></i> Post Job
                    </button>
                    
                    <div id="jobForm" class="card mb-4" style="display: none;">
                        <div class="card-header">
                            <h3>Post New Job</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label>Job Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Company</label>
                                    <input type="text" name="company" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Job Description</label>
                                    <textarea name="description" class="form-control" rows="4" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Requirements</label>
                                    <textarea name="requirements" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="location" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Job Type</label>
                                    <select name="job_type" class="form-control" required>
                                        <option value="full-time">Full-time</option>
                                        <option value="part-time">Part-time</option>
                                        <option value="internship">Internship</option>
                                        <option value="freelance">Freelance</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Salary Range</label>
                                    <input type="text" name="salary_range" class="form-control" placeholder="e.g., ₱25,000 - ₱35,000">
                                </div>
                                <div class="form-group">
                                    <label>Application Deadline</label>
                                    <input type="date" name="application_deadline" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Contact Email</label>
                                    <input type="email" name="contact_email" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Post Job</button>
                                <button type="button" onclick="toggleJobForm()" class="btn btn-secondary">Cancel</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="job-listings">
                        <?php foreach ($jobs as $job): ?>
                        <div class="job-card">
                            <div class="job-header">
                                <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                                <span class="job-type <?php echo $job['job_type']; ?>">
                                    <?php echo ucfirst(str_replace('-', ' ', $job['job_type'])); ?>
                                </span>
                            </div>
                            <div class="job-company">
                                <i class="fas fa-building"></i>
                                <?php echo htmlspecialchars($job['company']); ?>
                                <?php if ($job['location']): ?>
                                <span class="job-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($job['location']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="job-description">
                                <?php echo nl2br(htmlspecialchars(substr($job['description'], 0, 200))); ?>
                                <?php if (strlen($job['description']) > 200): ?>...<?php endif; ?>
                            </div>
                            <div class="job-footer">
                                <div class="job-meta">
                                    <?php if ($job['salary_range']): ?>
                                    <span class="salary">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <?php echo htmlspecialchars($job['salary_range']); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if ($job['application_deadline']): ?>
                                    <span class="deadline">
                                        <i class="fas fa-clock"></i>
                                        Apply by <?php echo date('M j, Y', strtotime($job['application_deadline'])); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <a href="mailto:<?php echo htmlspecialchars($job['contact_email']); ?>" class="btn btn-primary">
                                    <i class="fas fa-envelope"></i> Apply Now
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function toggleJobForm() {
            const form = document.getElementById('jobForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
