<?php
    require_once 'includes/auth.php';
    require_once 'config/database.php';

    $auth = new Auth();
    $user = $auth -> getCurrentUser();
    $database = new Database();
    $conn = $database -> getConnection();

    // Handle event creation
    if ($_POST && $user && in_array($user['role'], ['admin', 'faculty'])) {
        $stmt = $conn -> prepare("INSERT INTO academic_calendar (title, description, event_date, event_type, university, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt -> execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['event_date'],
            $_POST['event_type'],
            $_POST['university'],
            $user['id']
        ]);
        header('Location: academic_calendar.php');
        exit;
    }

    // Get calendar events
    $calendar_query = "SELECT ac.*, u.full_name AS creator_name
                       FROM academic_calendar ac
                       LEFT JOIN users u ON ac.created_by = u.id
                       WHERE ac.event_date >= CURDATE()
                       ORDER BY ac.event_date ASC";
    $stmt =  $conn -> prepare($calendar_query);
    $stmt -> execute();
    $events = $stmt -> fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lane="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Academic Calendar - PSUC Forum</title>
        <link href="assets/stylesheets/main.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <?php include 'includes/header.php'; ?>

        <main class="container">
            <div class="main-content">
                <div class="forum-content">
                    <div class="p-3">
                        <h1><i class="fas fa-calendar-alt"></i>Academic Calendar</h1>
                        <p class="text-secondary">Important dates and events for PSUC institutions</p>

                        <?php if ($user && in_array($user['role'], ['admin', 'faculty'])): ?>
                        <button onclick="toggleEventForm()" class="btn btn-primary mb-3">
                            <i class="fas fa-plus"></i>Add Event
                        </button>

                        <div id="eventForm" class="card mb-4" style="display: none;">
                            <div class="card-header">
                                <h3>Add New Event</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-group">
                                        <label>Event Title</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Event Date</label>
                                    <input type="date" name="event_date" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Event Type</label>
                                    <select name="event_type" class="form-control" required>
                                        <option value="exam">Examination</option>
                                        <option value="enrollment">Enrollment</option>
                                        <option value="holiday">Holiday</option>
                                        <option value="semester_start">Semester Start</option>
                                        <option value="semester_end">Semester End</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>University (Optional)</label>
                                    <input type="text" name="university" class="form-control" placeholder="Leave blank for all universities">
                                </div>
                                <button type="submit" class="btn btn-primary">Add Event</button>
                                <button type="button" onclick="toggleEventForm()" class="btn btn-secondary">Cancel</button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="calendar-events">
                        <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <div class="event-date">
                                <div class="month"><?php echo date('M', strtotime($event['event_date'])); ?></div>
                                <div class="day"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                            </div>
                            <div class="event-details">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                                <div class="event-meta">
                                    <span class="event-type <?php echo $event['event_type']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $event['event_type'])); ?>
                                    </span>
                                    <?php if ($event['university']): ?>
                                    <span class="event-university"><?php echo htmlspecialchars($event['university']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function toggleEventForm() {
            const form = document.getElementById('eventForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
    <script src="assets/scripts/main.js"></script>
</body>
</html>