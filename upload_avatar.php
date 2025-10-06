<?php
    require_once 'includes/auth.php';

    $auth = new Auth();
    $user = $auth -> getCurrentUser();

    if(!$user) {
        header('Location: login.php');
        exit;
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $target_dir = __DIR__ . "/assets/avatars/";

        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_filename = $user['id'] . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        $uploadOk = 1;
        $imageFileType = strtolower($file_extension);
        $errorMessage = '';

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES['avatar']['tmp_name']);
        if($check === false) {
            $errorMessage = "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if($_FILES['avatar']['size'] > 500000) {
            $errorMessage = "The file exceeds the maximum allowed size of 500KB.";
            $uploadOk = 0;
        }

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if(!in_array($imageFileType, $allowed_types)) {
            $errorMessage = "Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";
            $uploadOk = 0;
        }

        if($uploadOk == 0) {
            $_SESSION['upload_error'] = $errorMessage;
        } else {
            if ($user['avatar'] && $user['avatar'] != 'default.png' && file_exists($target_dir . $user['avatar'])) {
                unlink($target_dir . $user['avatar']);
            }

            if(move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                $auth -> updateAvatar($user['id'], $new_filename);
                $_SESSION['upload_success'] = "File Uploaded Successfully.";
            } else {
                $_SESSION['upload_error'] = "Oops, there was an error uploading your file.";
            }
        }

    } else {
        $_SESSION['upload_error'] = "No file was uploaded or an error occurred during upload.";
    }

    header('Location: profile.php');
    exit;
?>