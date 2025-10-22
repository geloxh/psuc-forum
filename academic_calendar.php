<?php
require_once 'includes/auth.php';

$auth = new Auth();
$user = $auth->getCurrentUser();

$database = new Database();
$conn = $database->getConnection();

// Get calendar events
$events_query = "SELECT * FROM academic_calendar ORDER BY event_date ASC";
$stmt = $conn->prepare($events_query);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Calendar - PSUC Forum</title>
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
                    <h1><i class="fas fa-calendar-alt"></i> Academic Calendar</h1>
                    <p class="text-secondary">Important dates and events for PSUC institutions</p>
                    
                    <?php if(count($events) > 0): ?>
                        <div style="margin-top: 2rem;">
                            <?php foreach($events as $event): ?>
                                <div class="widget" style="margin-bottom: 1rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div>
                                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                            <p><?php echo htmlspecialchars($event['description']); ?></p>
                                            <?php if($event['university']): ?>
                                                <small class="text-secondary">
                                                    <i class="fas fa-university"></i> <?php echo htmlspecialchars($event['university']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div style="text-align: right;">
                                            <div class="badge" style="background: var(--primary-color);">
                                                <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                            </div>
                                            <div style="margin-top: 0.5rem;">
                                                <span class="badge" style="background: var(--success-color);">
                                                    <?php echo ucfirst($event['event_type']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center" style="padding: 3rem;">
                            <i class="fas fa-calendar" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                            <h3>No events scheduled</h3>
                            <p class="text-secondary">Check back later for upcoming academic events and important dates.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="sidebar">
                <div class="widget">
                    <h3><i class="fas fa-info-circle"></i> Event Types</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div><span class="badge" style="background: #007bff;">Exam</span> Examination periods</div>
                        <div><span class="badge" style="background: #28a745;">Enrollment</span> Registration dates</div>
                        <div><span class="badge" style="background: #dc3545;">Holiday</span> Academic holidays</div>
                        <div><span class="badge" style="background: #ffc107;">Semester</span> Term dates</div>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <script src="assets/scripts/main.js"></script>
</body>
</html>