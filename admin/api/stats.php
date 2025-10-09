<?php
    require_once '../../includes/auth.php';

    $auth = new Auth();
    $user = $auth -> getCurrentUser();

    if(!$user || $user['role'] != 'admin') {
        http_response_code(403);
        exit;
    }

    $database = new Database();
    $conn = $database -> getConnection();

    $stats_query = "SELECT
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM topics) as total_topics,
        (SELECT COUNT(*) FROM posts) as total_posts";
    $stmt = $conn -> prepare($stats_query);
    $stmt -> execute();
    $stats = $stmt -> fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($stats);
?>