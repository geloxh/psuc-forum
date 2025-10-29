<?php

    require_once '../config/database.php';
    
    header('Content-Type: application/json');

    try {
        $database = new Database();
        $conn = $database -> getConnection();

        $stmt = $conn -> prepare("SELECT id, title, description, event_date AS START FROM academic_calendar");
        $stmt -> execute();

        $events = $stmt -> fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($events);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e -> getMessage()]);
    }