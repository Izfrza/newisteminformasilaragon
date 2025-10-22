<?php
// config/sms_config.php
function ensureSMSLogsDir() {
    $log_dir = 'sms_logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    return $log_dir;
}

function sendOTP($phone, $otp) {
    // Simulasi pengiriman SMS
    // Dalam implementasi real, gunakan API SMS seperti Twilio, Nexmo, dll.
    
    $log_dir = ensureSMSLogsDir();
    
    $log_file = $log_dir . '/otp_logs.txt';
    $message = "[" . date('Y-m-d H:i:s') . "] OTP untuk $phone: $otp - Valid 10 menit\n";
    file_put_contents($log_file, $message, FILE_APPEND | LOCK_EX);
    
    return true;
}

function generateOTP($length = 6) {
    return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function saveOTP($phone, $otp) {
    $log_dir = ensureSMSLogsDir();
    $otp_file = $log_dir . '/otp_tokens.json';
    $otps = [];
    
    if (file_exists($otp_file)) {
        $otps = json_decode(file_get_contents($otp_file), true);
        if ($otps === null) {
            $otps = [];
        }
    }
    
    // Hapus OTP lama untuk nomor yang sama
    foreach ($otps as $key => $existing_otp) {
        if ($existing_otp['phone'] === $phone) {
            unset($otps[$key]);
        }
    }
    
    $otps[$otp] = [
        'phone' => $phone,
        'created_at' => time(),
        'expires' => time() + 600, // 10 menit
        'used' => false
    ];
    
    file_put_contents($otp_file, json_encode($otps, JSON_PRETTY_PRINT));
    return true;
}

function verifyOTP($phone, $otp) {
    $log_dir = ensureSMSLogsDir();
    $otp_file = $log_dir . '/otp_tokens.json';
    
    if (!file_exists($otp_file)) {
        return false;
    }
    
    $otps = json_decode(file_get_contents($otp_file), true);
    if ($otps === null) {
        return false;
    }
    
    if (isset($otps[$otp]) && 
        $otps[$otp]['phone'] === $phone &&
        !$otps[$otp]['used'] && 
        $otps[$otp]['expires'] > time()) {
        
        return true;
    }
    
    return false;
}

function markOTPUsed($otp) {
    $log_dir = ensureSMSLogsDir();
    $otp_file = $log_dir . '/otp_tokens.json';
    
    if (file_exists($otp_file)) {
        $otps = json_decode(file_get_contents($otp_file), true);
        if ($otps === null) {
            return;
        }
        
        if (isset($otps[$otp])) {
            $otps[$otp]['used'] = true;
            file_put_contents($otp_file, json_encode($otps, JSON_PRETTY_PRINT));
        }
    }
}

// Fungsi untuk membersihkan OTP yang sudah expired
function cleanExpiredOTPs() {
    $log_dir = ensureSMSLogsDir();
    $otp_file = $log_dir . '/otp_tokens.json';
    
    if (!file_exists($otp_file)) {
        return;
    }
    
    $otps = json_decode(file_get_contents($otp_file), true);
    if ($otps === null) {
        return;
    }
    
    $current_time = time();
    $cleaned_otps = [];
    
    foreach ($otps as $otp => $otp_data) {
        if ($otp_data['expires'] > $current_time && !$otp_data['used']) {
            $cleaned_otps[$otp] = $otp_data;
        }
    }
    
    file_put_contents($otp_file, json_encode($cleaned_otps, JSON_PRETTY_PRINT));
}

// Jalankan pembersihan OTP expired setiap kali file di-load
cleanExpiredOTPs();
?>