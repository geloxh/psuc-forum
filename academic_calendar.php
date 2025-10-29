<?php

    require_once 'includes/auth.php';
    require_once 'config/database.php';

    $auth = new Auth();
    $user = $auth->getCurrentUser();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Calendar - PSUC Forum</title>
    <link href="assets/stylesheets/main.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
       #calendar {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 10px;
       }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="main-content">
            <div class="forum-content">
                <div class="p-3">
                    <h1><i class="fas fa-calendar-alt"></i>Academic Calendar</h1>
                    <p class="text-secondary">Important dates and events for PSUC institutions</p>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="assets/scripts/main.js"></script>
</body>
</html>