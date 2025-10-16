<?php
    require_once 'includes/auth.php';
    require_once 'includes/forum.php';

    $auth = new Auth();
    $forum_class = new Forum();
    $user = $auth->getCurrentUser();

    if (!$user) {
        header('Location: login.php');
        exit;
    }

    $topic_id = $_GET['id'] ?? 0;

    // Fetch the topic to get its details before deleting
    $topic = $forum_class->getTopic($topic_id);

    // Security check: ensure user is the author or an admin
    if (!$topic || ($topic['user_id'] != $user['id'] && !$auth->isAdmin())) {
        header('Location: index.php');
        exit;
    }

    // Perform the deletion
    if ($forum_class->deleteTopic($topic_id)) {
        // Redirect to the forum the topic was in
        header('Location: forum.php?id=' . $topic['forum_id']);
        exit;
    } else {
        // Handle error, maybe redirect back with an error message
        header('Location: topic.php?id=' . $topic_id . '&error=delete_failed');
        exit;
    }
?>