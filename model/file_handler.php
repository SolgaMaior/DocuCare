<?php

$main_path = "C:\\xampp\htdocs\PatientFolder";


function makePatientDirectory($firstname, $lastname)
{
    global $main_path;
    $dir_create = $main_path . '\\' . $lastname . $firstname;

    if (!is_dir($dir_create)) {

        if (mkdir($dir_create)) {
            return;
        } else {
            return;
        }
    } else {
        return $dir_create;
    }
}

function handleImageUpload($imageFile, $firstname, $lastname)
{
    // Validate the upload
    if (!isset($imageFile) || $imageFile['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error occurred'];
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = $imageFile['type'];

    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only images are allowed'];
    }

    // Validate file size (e.g., max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB in bytes
    if ($imageFile['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File is too large. Maximum size is 5MB'];
    }

    // Generate unique filename
    $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
    $newFilename = $firstname . $lastname . '_prof' . '.' . $extension;


    $uploadDir = makePatientDirectory($firstname, $lastname) . '\\' . 'profile_image\\';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }


    $targetPath = $uploadDir . $newFilename;



    if (is_dir($uploadDir)) {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        foreach ($imageExtensions as $ext) {
            $files = glob($uploadDir . '*.' . $ext);
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }



    if (move_uploaded_file($imageFile['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'message' => 'Image uploaded successfully',
            'filename' => $newFilename,
            'path' => $targetPath
        ];
    } else {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }
}

function get_profile_image_path($firstname, $lastname)
{

    $relativePath = '/PatientFolder/' . $lastname . $firstname . '/profile_image/';
    $absolutePath = $_SERVER['DOCUMENT_ROOT'] . '/' . $relativePath;

    if (is_dir($absolutePath)) {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        foreach ($imageExtensions as $ext) {
            $files = glob($absolutePath . '*.' . $ext);
            if (!empty($files)) {
                $filename = basename($files[0]);
                return $relativePath . $filename;
            }
        }
    }
    return null;
}
