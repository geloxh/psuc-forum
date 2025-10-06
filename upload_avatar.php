<?php
    require_once 'includes/auth.php';

    $auth = new Auth();
    $user = $auth -> getCurrentUser();

    if(!$user) {
        header('Location: login.php');
        exit;
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
        $target_dir = "assets/avatars/";
        $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_filename = $user['id'] . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        $uploadOk = 1;
        $imageFileType = strtolower($file_extension);

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES['avatar']['tmp_name']);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            echo "Invalid file type. Only JPG, JPEG, and PNG files are allowed.";
            $uploadOk = 0;
        }

        // Check file size
        if($_FILES['avatar']['size'] > 500000) {
            echo "The file exceeds the maximum allowed size of 500KB.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != 'jpg' && $imageFileType != 'png' && $imageFileType != 'jpeg' && $imageFileType != 'gif'
            && $imageFileType != 'webp') {
                echo "Please use JPG, JPEG, PNG, GIF, or WEBP file format.";
                $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            echo "File not uploaded.";
        } else {
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                $auth -> updateAvatar($user['id'], $new_filename);
                header('Location: profile.php');
            } else {
                echo "Oops, there was an error uploading your file.";
            }
        }
    }
?>