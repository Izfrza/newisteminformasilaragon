<?php
// config/helpers.php

if (!function_exists('formatDate')) {
    function formatDate($dateString) {
        if (empty($dateString) || $dateString == '0000-00-00') {
            return '-';
        }
        return date('d F Y', strtotime($dateString));
    }
}

if (!function_exists('showAlert')) {
    function showAlert($message, $type = 'success') {
        echo "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}

if (!function_exists('uploadImage')) {
    function uploadImage($file, $target_dir = "uploads/") {
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . time() . '_' . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is actual image
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            return ["success" => false, "message" => "File bukan gambar."];
        }
        
        // Check file size (5MB max)
        if ($file["size"] > 5000000) {
            return ["success" => false, "message" => "Ukuran file terlalu besar."];
        }
        
        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            return ["success" => false, "message" => "Hanya format JPG, JPEG, PNG & GIF yang diizinkan."];
        }
        
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return ["success" => true, "file_path" => $target_file];
        } else {
            return ["success" => false, "message" => "Terjadi error saat upload file."];
        }
    }
}

if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}
?>