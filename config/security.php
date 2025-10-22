<?php
// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input sanitization
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Check admin permissions
function checkAdminPermission($required_role = 'admin') {
    if (!isset($_SESSION['admin']) || 
        ($required_role === 'superadmin' && $_SESSION['admin']['role'] !== 'superadmin')) {
        header("Location: admin_login.php");
        exit();
    }
}
?>