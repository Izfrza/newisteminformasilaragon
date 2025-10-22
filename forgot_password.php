<?php
// forgot_password.php
session_start();
require_once 'config/database.php';
require_once 'config/sms_config.php';

$message = '';
$error = '';
$show_otp_form = false;
$phone = '';

if ($_POST) {
    if (isset($_POST['phone'])) {
        // Step 1: Request OTP
        $phone = $_POST['phone'];
        
        // Validasi format nomor HP
        if (!preg_match('/^\+62[0-9]{9,13}$/', $phone)) {
            $error = "Format nomor HP tidak valid. Gunakan format: +628123456789";
        } else {
            $database = new Database();
            $db = $database->getConnection();
            
            // Cari admin dengan nomor HP
            $query = "SELECT * FROM admins WHERE phone = :phone";
            $stmt = $db->prepare($query);
            $stmt->execute([':phone' => $phone]);
            
            if ($stmt->rowCount() > 0) {
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Generate dan kirim OTP
                $otp = generateOTP();
                if (saveOTP($phone, $otp)) {
                    if (sendOTP($phone, $otp)) {
                        $_SESSION['reset_phone'] = $phone;
                        $show_otp_form = true;
                        $message = "OTP telah dikirim ke nomor Anda! Check file <code>sms_logs/otp_logs.txt</code> untuk melihat OTP.";
                    } else {
                        $error = "Gagal mengirim OTP. Silakan coba lagi.";
                    }
                } else {
                    $error = "Gagal menyimpan OTP. Silakan coba lagi.";
                }
            } else {
                $error = "Nomor HP tidak terdaftar dalam sistem.";
            }
        }
    } elseif (isset($_POST['otp'])) {
        // Step 2: Verify OTP
        if (isset($_SESSION['reset_phone'])) {
            $phone = $_SESSION['reset_phone'];
            $otp = $_POST['otp'];
            
            if (verifyOTP($phone, $otp)) {
                markOTPUsed($otp);
                $_SESSION['otp_verified'] = true;
                header("Location: reset_password.php?phone=" . urlencode($phone));
                exit();
            } else {
                $error = "OTP tidak valid atau sudah kadaluarsa!";
                $show_otp_form = true;
            }
        } else {
            $error = "Sesi tidak valid. Silakan mulai dari awal.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SMA Negeri 1 Maju Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .forgot-password-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .forgot-card {
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: none;
            border-radius: 15px;
        }
        .forgot-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card forgot-card">
                        <div class="forgot-header">
                            <h3><i class="fas fa-school me-2"></i>SMA Negeri 1 Maju Jaya</h3>
                            <p class="mb-0">Reset Password dengan OTP</p>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($message): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!$show_otp_form): ?>
                                <!-- Form Input Nomor HP -->
                                <p class="text-muted mb-4">Masukkan nomor HP admin Anda yang terdaftar.</p>
                                
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Nomor HP Admin</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                                            <input type="tel" class="form-control" id="phone" name="phone" required 
                                                   placeholder="Contoh: +628123456789" pattern="\+62[0-9]{9,13}"
                                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                        </div>
                                        <small class="text-muted">Format: +62xxxxxxxxxx (contoh: +628123456789)</small>
                                    </div>
                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-paper-plane me-2"></i>Request OTP
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <!-- Form Input OTP -->
                                <p class="text-muted mb-4">Masukkan 6-digit OTP yang dikirim ke <?php echo htmlspecialchars($phone); ?></p>
                                
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="otp" class="form-label">Kode OTP</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                                            <input type="text" class="form-control" id="otp" name="otp" required 
                                                   placeholder="123456" pattern="[0-9]{6}" maxlength="6"
                                                   autocomplete="off">
                                        </div>
                                        <small class="text-muted">OTP berlaku 10 menit</small>
                                    </div>
                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-check-circle me-2"></i>Verifikasi OTP
                                        </button>
                                    </div>
                                </form>
                                
                                <div class="text-center">
                                    <form method="POST">
                                        <input type="hidden" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-redo me-2"></i>Kirim Ulang OTP
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                            <div class="text-center mt-3">
                                <a href="admin_login.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Login
                                </a>
                            </div>
                            
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6><i class="fas fa-info-circle me-2"></i>Informasi:</h6>
                                <p class="mb-1">• OTP akan disimpan di: <code>sms_logs/otp_logs.txt</code></p>
                                <p class="mb-1">• Data OTP disimpan di: <code>sms_logs/otp_tokens.json</code></p>
                                <p class="mb-0">• OTP berlaku 10 menit</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus pada input OTP
        <?php if ($show_otp_form): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('otp').focus();
            });
        <?php endif; ?>
        
        // Validasi client-side untuk nomor HP
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    if (!this.value.startsWith('+62')) {
                        this.setCustomValidity('Nomor HP harus diawali dengan +62');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        });
    </script>
</body>
</html>