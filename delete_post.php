<?php
    require_once 'includes/auth.php';
    require_once ' includes/forum.php';

    $auth = new Auth();
    $forum = new Forum();
    $user = $auth -> getCurentUser();

    if (!user) {
        header('Location: login.php');
        exit;
    }

    $post_id = $_GET['id'] ?? 0;
    $post = $forum -> getPost($post_id);

    if (!$post || ($user['id'] != $post['user_id'] && !$auth -> isAdmin())) {
        header('Location: index.php');
        exit;
    }

    if ($forum -> deletePost($post_id)) {
        header('Location: topic.php?id=' . $post['topic_id']);
    } else {
        header('Location: topic.php?id=' . $post['topic_id'] .'&error=delete_failed');
    }
    exit;
?>