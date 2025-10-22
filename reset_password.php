<?php
// reset_password.php
session_start();
require_once 'config/database.php';
require_once 'config/sms_config.php';

$message = '';
$error = '';
$valid_phone = false;
$phone = '';

if (isset($_GET['phone'])) {
    $phone = $_GET['phone'];
    
    // Verifikasi bahwa OTP sudah divalidasi
    if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true) {
        $valid_phone = true;
        
        // Handle form submission
        if ($_POST) {
            $new_password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            if ($new_password !== $confirm_password) {
                $error = "Password tidak cocok!";
            } elseif (strlen($new_password) < 6) {
                $error = "Password minimal 6 karakter!";
            } else {
                $database = new Database();
                $db = $database->getConnection();
                
                // Update password di database
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $query = "UPDATE admins SET password = :password WHERE phone = :phone";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':password' => $hashed_password,
                    ':phone' => $phone
                ]);
                
                if ($stmt->rowCount() > 0) {
                    // Clear session
                    unset($_SESSION['otp_verified']);
                    unset($_SESSION['reset_phone']);
                    $message = "Password berhasil direset! Silakan login dengan password baru.";
                } else {
                    $error = "Gagal mereset password. Silakan coba lagi.";
                }
            }
        }
    } else {
        $error = "Verifikasi OTP diperlukan!";
        header("Location: forgot_password.php");
        exit();
    }
} else {
    $error = "Nomor HP tidak ditemukan!";
    header("Location: forgot_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SMA Negeri 1 Maju Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .reset-password-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .reset-card {
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: none;
            border-radius: 15px;
        }
        .reset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card reset-card">
                        <div class="reset-header">
                            <h3><i class="fas fa-school me-2"></i>SMA Negeri 1 Maju Jaya</h3>
                            <p class="mb-0">Reset Password</p>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($message): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                                </div>
                                <div class="text-center">
                                    <a href="admin_login.php" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login Sekarang
                                    </a>
                                </div>
                            <?php else: ?>
                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-mobile-alt me-2"></i>
                                    <strong>Reset password untuk:</strong> <?php echo htmlspecialchars($phone); ?>
                                </div>
                                
                                <p class="text-muted mb-4">Masukkan password baru untuk akun admin Anda.</p>
                                
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password Baru</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                        </div>
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                                        </div>
                                    </div>
                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save me-2"></i>Reset Password
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>