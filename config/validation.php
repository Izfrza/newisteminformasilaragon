<?php
function validateImage($file) {
    $errors = [];
    
    // Check if file is uploaded
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading file.";
        return $errors;
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5000000) {
        $errors[] = "File size too large. Maximum 5MB allowed.";
    }
    
    // Check file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        $errors[] = "Only JPG, PNG, and GIF files are allowed.";
    }
    
    return $errors;
}
?>