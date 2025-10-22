<?php
session_start();
require_once 'config/database.php';
require_once 'config/helpers.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$admin = $_SESSION['admin'];

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';

switch ($action) {
    case 'save_settings':
        if ($_POST) {
            $query = "UPDATE school_settings SET 
                     name = :name, address = :address, phone = :phone, email = :email,
                     vision = :vision, mission = :mission, total_students = :total_students
                     WHERE id = 1";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':name' => $_POST['name'],
                ':address' => $_POST['address'],
                ':phone' => $_POST['phone'],
                ':email' => $_POST['email'],
                ':vision' => $_POST['vision'],
                ':mission' => $_POST['mission'],
                ':total_students' => $_POST['total_students']
            ]);
            $message = "Pengaturan berhasil disimpan!";
        }
        break;
        
    case 'save_background':
        if ($_POST) {
            $field = $_POST['type'] . '_background';
            $query = "UPDATE school_settings SET $field = :background WHERE id = 1";
            $stmt = $db->prepare($query);
            $stmt->execute([':background' => $_POST['background']]);
            $message = "Background berhasil disimpan!";
        }
        break;
        
    case 'add_news':
        if ($_POST) {
            $image_path = '';
            if (!empty($_FILES['image']['name'])) {
                $upload = uploadImage($_FILES['image']);
                if ($upload['success']) {
                    $image_path = $upload['file_path'];
                }
            }
            
            $query = "INSERT INTO news (title, content, image, date, status) 
                     VALUES (:title, :content, :image, :date, :status)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':content' => $_POST['content'],
                ':image' => $image_path,
                ':date' => $_POST['date'],
                ':status' => $_POST['status']
            ]);
            $message = "Berita berhasil ditambahkan!";
        }
        break;
        
    case 'edit_news':
        if ($_POST) {
            $id = $_POST['id'];
            $query = "UPDATE news SET title = :title, content = :content, date = :date, status = :status WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':content' => $_POST['content'],
                ':date' => $_POST['date'],
                ':status' => $_POST['status'],
                ':id' => $id
            ]);
            $message = "Berita berhasil diupdate!";
        }
        break;
        
    case 'delete_news':
        $id = $_GET['id'];
        $query = "DELETE FROM news WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $id]);
        $message = "Berita berhasil dihapus!";
        break;
        
    case 'add_teacher':
        if ($_POST) {
            $query = "INSERT INTO teachers (name, subject) VALUES (:name, :subject)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':name' => $_POST['name'],
                ':subject' => $_POST['subject']
            ]);
            $message = "Data guru berhasil ditambahkan!";
        }
        break;
        
    case 'edit_teacher':
        if ($_POST) {
            $id = $_POST['id'];
            $query = "UPDATE teachers SET name = :name, subject = :subject WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':name' => $_POST['name'],
                ':subject' => $_POST['subject'],
                ':id' => $id
            ]);
            $message = "Data guru berhasil diupdate!";
        }
        break;
        
    case 'delete_teacher':
        $id = $_GET['id'];
        $query = "DELETE FROM teachers WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $id]);
        $message = "Data guru berhasil dihapus!";
        break;
        
    case 'add_gallery':
        if ($_POST) {
            $image_path = '';
            if (!empty($_FILES['image']['name'])) {
                $upload = uploadImage($_FILES['image']);
                if ($upload['success']) {
                    $image_path = $upload['file_path'];
                }
            }
            
            $query = "INSERT INTO gallery (title, image, date) 
                     VALUES (:title, :image, :date)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':image' => $image_path,
                ':date' => $_POST['date']
            ]);
            $message = "Foto galeri berhasil ditambahkan!";
        }
        break;
        
    case 'edit_gallery':
        if ($_POST) {
            $id = $_POST['id'];
            
            if (!empty($_FILES['image']['name'])) {
                $upload = uploadImage($_FILES['image']);
                if ($upload['success']) {
                    $image_path = $upload['file_path'];
                    $query = "UPDATE gallery SET title = :title, image = :image, date = :date WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->execute([
                        ':title' => $_POST['title'],
                        ':image' => $image_path,
                        ':date' => $_POST['date'],
                        ':id' => $id
                    ]);
                }
            } else {
                $query = "UPDATE gallery SET title = :title, date = :date WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':title' => $_POST['title'],
                    ':date' => $_POST['date'],
                    ':id' => $id
                ]);
            }
            $message = "Foto galeri berhasil diupdate!";
        }
        break;
        
    case 'delete_gallery':
        $id = $_GET['id'];
        $query = "DELETE FROM gallery WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $id]);
        $message = "Foto galeri berhasil dihapus!";
        break;
case 'add_admin':
        if ($_POST && $admin['role'] === 'superadmin') {
            $username = trim($_POST['username']);
            $fullname = trim($_POST['fullname']);
            $phone = trim($_POST['phone']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            
            // Validasi input
            if (empty($username) || empty($fullname) || empty($phone) || empty($password)) {
                $error = "Semua field harus diisi!";
            } elseif (strlen($password) < 6) {
                $error = "Password minimal 6 karakter!";
            } elseif (!preg_match('/^\+62[0-9]{9,13}$/', $phone)) {
                $error = "Format nomor HP tidak valid! Gunakan format: +628123456789";
            } else {
                // Cek apakah username sudah ada
                $check_query = "SELECT COUNT(*) FROM admins WHERE username = :username";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->execute([':username' => $username]);
                $username_exists = $check_stmt->fetchColumn();
                
                if ($username_exists > 0) {
                    $error = "Username '$username' sudah digunakan. Silakan pilih username lain.";
                } else {
                    try {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $query = "INSERT INTO admins (username, password, fullname, phone, role) 
                                 VALUES (:username, :password, :fullname, :phone, :role)";
                        $stmt = $db->prepare($query);
                        $stmt->execute([
                            ':username' => $username,
                            ':password' => $hashed_password,
                            ':fullname' => $fullname,
                            ':phone' => $phone,
                            ':role' => $role
                        ]);
                        $message = "Admin berhasil ditambahkan!";
                    } catch (PDOException $e) {
                        if ($e->getCode() == 23000) {
                            $error = "Username '$username' sudah digunakan. Silakan pilih username lain.";
                        } else {
                            $error = "Terjadi kesalahan database: " . $e->getMessage();
                        }
                    }
                }
            }
        }
        break;

    case 'edit_admin':
        if ($_POST && $admin['role'] === 'superadmin') {
            $id = $_POST['id'];
            $username = trim($_POST['username']);
            $fullname = trim($_POST['fullname']);
            $phone = trim($_POST['phone']);
            $role = $_POST['role'];
            
            // Cek apakah username sudah digunakan oleh admin lain
            $check_query = "SELECT COUNT(*) FROM admins WHERE username = :username AND id != :id";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute([':username' => $username, ':id' => $id]);
            $username_exists = $check_stmt->fetchColumn();
            
            if ($username_exists > 0) {
                $error = "Username '$username' sudah digunakan oleh admin lain. Silakan pilih username lain.";
            } else {
                try {
                    if (!empty($_POST['password'])) {
                        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $query = "UPDATE admins SET username = :username, fullname = :fullname, 
                                 phone = :phone, password = :password, role = :role WHERE id = :id";
                        $stmt = $db->prepare($query);
                        $stmt->execute([
                            ':username' => $username,
                            ':fullname' => $fullname,
                            ':phone' => $phone,
                            ':password' => $hashed_password,
                            ':role' => $role,
                            ':id' => $id
                        ]);
                    } else {
                        $query = "UPDATE admins SET username = :username, fullname = :fullname, 
                                 phone = :phone, role = :role WHERE id = :id";
                        $stmt = $db->prepare($query);
                        $stmt->execute([
                            ':username' => $username,
                            ':fullname' => $fullname,
                            ':phone' => $phone,
                            ':role' => $role,
                            ':id' => $id
                        ]);
                    }
                    $message = "Data admin berhasil diupdate!";
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        $error = "Username '$username' sudah digunakan. Silakan pilih username lain.";
                    } else {
                        $error = "Terjadi kesalahan: " . $e->getMessage();
                    }
                }
            }
        }
        break;

    case 'delete_admin':
        if ($admin['role'] === 'superadmin') {
            $id = $_GET['id'];
            $query = "DELETE FROM admins WHERE id = :id AND username != 'superadmin'";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);
            $message = "Admin berhasil dihapus!";
        }
        break;

    case 'search_news':
        $search_term = '%' . $_POST['search'] . '%';
        $query = "SELECT * FROM news WHERE title LIKE :search OR content LIKE :search ORDER BY date DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([':search' => $search_term]);
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
        
    case 'search_teachers':
        $search_term = '%' . $_POST['search'] . '%';
        $query = "SELECT * FROM teachers WHERE name LIKE :search OR subject LIKE :search ORDER BY name ASC";
        $stmt = $db->prepare($query);
        $stmt->execute([':search' => $search_term]);
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
        
    case 'search_gallery':
        $search_term = '%' . $_POST['search'] . '%';
        $query = "SELECT * FROM gallery WHERE title LIKE :search ORDER BY date DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([':search' => $search_term]);
        $gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
        
    case 'search_admins':
        if ($admin['role'] === 'superadmin') {
            $search_term = '%' . $_POST['search'] . '%';
            $query = "SELECT * FROM admins WHERE username LIKE :search OR fullname LIKE :search ORDER BY id ASC";
            $stmt = $db->prepare($query);
            $stmt->execute([':search' => $search_term]);
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        break;
}

// Get data
$settings = $db->query("SELECT * FROM school_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$news = $db->query("SELECT * FROM news ORDER BY date DESC")->fetchAll(PDO::FETCH_ASSOC);
$gallery = $db->query("SELECT * FROM gallery ORDER BY date DESC")->fetchAll(PDO::FETCH_ASSOC);
$teachers = $db->query("SELECT * FROM teachers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$admins = $db->query("SELECT * FROM admins ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo $settings['name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
        }
        
        .admin-sidebar {
            background-color: var(--primary);
            color: white;
            min-height: 100vh;
            padding: 0;
        }
        
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .admin-sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .admin-header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .stats-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }

        .tab-content {
            min-height: 500px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-3 text-white min-vh-100">
                    <a href="index.php" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <span class="fs-5 d-none d-sm-inline">Admin Panel</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100" id="admin-menu">
                        <li class="nav-item w-100">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#dashboard">
                                <i class="fas fa-tachometer-alt"></i> <span class="ms-1 d-none d-sm-inline">Dashboard</span>
                            </button>
                        </li>
                        <li class="nav-item w-100">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#settings">
                                <i class="fas fa-cogs"></i> <span class="ms-1 d-none d-sm-inline">Pengaturan</span>
                            </button>
                        </li>
                        <li class="nav-item w-100">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#news">
                                <i class="fas fa-newspaper"></i> <span class="ms-1 d-none d-sm-inline">Kelola Berita</span>
                            </button>
                        </li>
                        <li class="nav-item w-100">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#teachers">
                                <i class="fas fa-chalkboard-teacher"></i> <span class="ms-1 d-none d-sm-inline">Kelola Guru</span>
                            </button>
                        </li>
                        <li class="nav-item w-100">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#gallery">
                                <i class="fas fa-images"></i> <span class="ms-1 d-none d-sm-inline">Kelola Galeri</span>
                            </button>
                        </li>
                        <?php if ($admin['role'] === 'superadmin'): ?>
                        <li class="nav-item w-100">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#admins">
                                <i class="fas fa-users-cog"></i> <span class="ms-1 d-none d-sm-inline">Kelola Admin</span>
                            </button>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item w-100 mt-4">
                            <a href="?logout" class="nav-link text-danger">
                                <i class="fas fa-sign-out-alt"></i> <span class="ms-1 d-none d-sm-inline">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 admin-content">
                <div class="admin-header d-flex justify-content-between align-items-center">
                    <h4>Selamat Datang, <strong><?php echo $admin['fullname']; ?></strong>!</h4>
                    <div>
                        <span class="badge bg-<?php echo $admin['role'] === 'superadmin' ? 'warning' : 'info'; ?>">
                            <?php echo ucfirst($admin['role']); ?>
                        </span>
                        <a href="index.php" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="fas fa-external-link-alt me-1"></i>Lihat Website
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <h3 class="mb-4">Dashboard</h3>
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <div class="card stats-card text-white bg-primary">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title">Total Berita</h5>
                                                <p class="card-text fs-3"><?php echo count($news); ?></p>
                                            </div>
                                            <i class="fas fa-newspaper fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="card stats-card text-white bg-success">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title">Total Galeri</h5>
                                                <p class="card-text fs-3"><?php echo count($gallery); ?></p>
                                            </div>
                                            <i class="fas fa-images fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="card stats-card text-white bg-info">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title">Total Guru</h5>
                                                <p class="card-text fs-3"><?php echo count($teachers); ?></p>
                                            </div>
                                            <i class="fas fa-chalkboard-teacher fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-4">
                                <div class="card stats-card text-white bg-warning">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="card-title">Total Siswa</h5>
                                                <p class="card-text fs-3"><?php echo $settings['total_students']; ?></p>
                                            </div>
                                            <i class="fas fa-user-graduate fa-3x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Berita Terbaru</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($news): ?>
                                            <?php foreach (array_slice($news, 0, 5) as $item): ?>
                                                <div class="border-bottom pb-2 mb-2">
                                                    <h6><?php echo $item['title']; ?></h6>
                                                    <small class="text-muted"><?php echo formatDate($item['date']); ?></small>
                                                    <span class="badge bg-<?php echo $item['status'] === 'published' ? 'success' : 'secondary'; ?> float-end">
                                                        <?php echo $item['status']; ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-muted">Belum ada berita.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">Galeri Terbaru</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if ($gallery): ?>
                                            <?php foreach (array_slice($gallery, 0, 5) as $item): ?>
                                                <div class="border-bottom pb-2 mb-2">
                                                    <h6><?php echo $item['title']; ?></h6>
                                                    <small class="text-muted"><?php echo formatDate($item['date']); ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-muted">Belum ada foto galeri.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Tab -->
                    <div class="tab-pane fade" id="settings">
                        <h3 class="mb-4">Pengaturan Website</h3>

                         <!-- Background Settings Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Pengaturan Background</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Hero Background -->
                                    <div class="col-md-6 mb-4">
                                        <h6>Background Hero Section</h6>
                                        <div class="mb-3">
                                            <label class="form-label">Background Saat Ini:</label><br>
                                            <div id="currentHeroBackground" style="width: 100%; height: 150px; background-size: cover; background-position: center; border: 2px solid #ddd; border-radius: 5px;"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="heroBackgroundFile" class="form-label">Ganti Background Hero</label>
                                            <input type="file" class="form-control" id="heroBackgroundFile" accept="image/*" onchange="previewHeroBackground(this)">
                                            <small class="text-muted">Rekomendasi: 1920x1080px, format JPG/PNG</small>
                                        </div>
                                        <button type="button" class="btn btn-primary" onclick="saveHeroBackground()">Simpan Background Hero</button>
                                    </div>
                                    
                                    <!-- Section Background -->
                                    <div class="col-md-6 mb-4">
                                        <h6>Background Section Abu-abu</h6>
                                        <div class="mb-3">
                                            <label class="form-label">Background Saat Ini:</label><br>
                                            <div id="currentSectionBackground" style="width: 100%; height: 150px; background-size: cover; background-position: center; border: 2px solid #ddd; border-radius: 5px;"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="sectionBackgroundFile" class="form-label">Ganti Background Section</label>
                                            <input type="file" class="form-control" id="sectionBackgroundFile" accept="image/*" onchange="previewSectionBackground(this)">
                                            <small class="text-muted">Untuk section Berita & Kontak, format JPG/PNG</small>
                                        </div>
                                        <button type="button" class="btn btn-primary" onclick="saveSectionBackground()">Simpan Background Section</button>
                                        <button type="button" class="btn btn-secondary ms-2" onclick="resetSectionBackground()">Reset ke Abu-abu</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Informasi Sekolah</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="?action=save_settings">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-label">Nama Sekolah</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($settings['name']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="total_students" class="form-label">Total Siswa</label>
                                            <input type="number" class="form-control" id="total_students" name="total_students" 
                                                   value="<?php echo $settings['total_students']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Alamat</label>
                                        <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($settings['address']); ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">Telepon</label>
                                            <input type="text" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($settings['phone']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($settings['email']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="vision" class="form-label">Visi Sekolah</label>
                                        <textarea class="form-control" id="vision" name="vision" rows="3" required><?php echo htmlspecialchars($settings['vision']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mission" class="form-label">Misi Sekolah</label>
                                        <textarea class="form-control" id="mission" name="mission" rows="5" required><?php echo htmlspecialchars($settings['mission']); ?></textarea>
                                        <small class="text-muted">Pisahkan setiap misi dengan baris baru</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- News Tab -->
                    <div class="tab-pane fade" id="news">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3>Kelola Berita</h3>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                                <i class="fas fa-plus-circle me-2"></i>Tambah Berita
                            </button>
                        </div>

                        <!-- Search Form -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="POST" action="?action=search_news" class="row g-3">
                                    <div class="col-md-8">
                                        <input type="text" name="search" class="form-control" placeholder="Cari berita..." 
                                               value="<?php echo $_POST['search'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search me-2"></i>Cari
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="admin.php" class="btn btn-secondary w-100">
                                            <i class="fas fa-refresh me-2"></i>Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Judul</th>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($news as $index => $item): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                                <td><?php echo formatDate($item['date']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $item['status'] === 'published' ? 'success' : 'secondary'; ?>">
                                                        <?php echo $item['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?action=delete_news&id=<?php echo $item['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Yakin ingin menghapus berita ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Teachers Tab -->
                    <div class="tab-pane fade" id="teachers">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3>Kelola Data Guru</h3>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                                <i class="fas fa-plus-circle me-2"></i>Tambah Guru
                            </button>
                        </div>

                        <!-- Search Form -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="POST" action="?action=search_teachers" class="row g-3">
                                    <div class="col-md-8">
                                        <input type="text" name="search" class="form-control" placeholder="Cari guru..." 
                                               value="<?php echo $_POST['search'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search me-2"></i>Cari
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="admin.php" class="btn btn-secondary w-100">
                                            <i class="fas fa-refresh me-2"></i>Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nama Guru</th>
                                                <th>Mata Pelajaran/Jabatan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($teachers as $index => $teacher): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                                <td><?php echo htmlspecialchars($teacher['subject']); ?></td>
                                                <td>
                                                    <a href="?action=delete_teacher&id=<?php echo $teacher['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Yakin ingin menghapus data guru ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gallery Tab -->
                    <div class="tab-pane fade" id="gallery">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3>Kelola Galeri Foto</h3>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addGalleryModal">
                                <i class="fas fa-plus-circle me-2"></i>Tambah Foto
                            </button>
                        </div>

                        <!-- Search Form -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="POST" action="?action=search_gallery" class="row g-3">
                                    <div class="col-md-8">
                                        <input type="text" name="search" class="form-control" placeholder="Cari foto galeri..." 
                                               value="<?php echo $_POST['search'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search me-2"></i>Cari
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="admin.php" class="btn btn-secondary w-100">
                                            <i class="fas fa-refresh me-2"></i>Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Judul Foto</th>
                                                <th>Tanggal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($gallery as $index => $item): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                                <td><?php echo formatDate($item['date']); ?></td>
                                                <td>
                                                    <a href="?action=delete_gallery&id=<?php echo $item['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Yakin ingin menghapus foto ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                   
                    <!-- Admins Tab (Super Admin Only) -->
                    <?php if ($admin['role'] === 'superadmin'): ?>
                    <div class="tab-pane fade" id="admins">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3>Kelola Administrator</h3>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                                <i class="fas fa-user-plus me-2"></i>Tambah Admin
                            </button>
                        </div>

                        <!-- Search Form -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="POST" action="?action=search_admins" class="row g-3">
                                    <div class="col-md-8">
                                        <input type="text" name="search" class="form-control" placeholder="Cari admin..." 
                                               value="<?php echo $_POST['search'] ?? ''; ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search me-2"></i>Cari
                                        </button>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="admin.php" class="btn btn-secondary w-100">
                                            <i class="fas fa-refresh me-2"></i>Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Username</th>
                                                <th>Nama Lengkap</th>
                                                <th>Nomor HP</th>
                                                <th>Role</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($admins as $index => $admin_item): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($admin_item['username']); ?></td>
                                                <td><?php echo htmlspecialchars($admin_item['fullname']); ?></td>
                                                <td><?php echo htmlspecialchars($admin_item['phone'] ?? '-'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $admin_item['role'] === 'superadmin' ? 'warning' : 'info'; ?>">
                                                        <?php echo ucfirst($admin_item['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($admin_item['username'] !== 'superadmin'): ?>
                                                    <a href="?action=delete_admin&id=<?php echo $admin_item['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Yakin ingin menghapus admin ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <?php else: ?>
                                                    <span class="text-muted">Protected</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>

        <?php endforeach; ?>
    </tbody>
</table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Add News Modal -->
    <div class="modal fade" id="addNewsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Berita Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=add_news" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="newsTitle" class="form-label">Judul Berita</label>
                            <input type="text" class="form-control" id="newsTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="newsContent" class="form-label">Isi Berita</label>
                            <textarea class="form-control" id="newsContent" name="content" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="newsDate" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="newsDate" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="newsImage" class="form-label">Gambar</label>
                            <input type="file" class="form-control" id="newsImage" name="image" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label for="newsStatus" class="form-label">Status</label>
                            <select class="form-select" id="newsStatus" name="status" required>
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Teacher Modal -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=add_teacher">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="teacherName" class="form-label">Nama Guru</label>
                            <input type="text" class="form-control" id="teacherName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="teacherSubject" class="form-label">Mata Pelajaran/Jabatan</label>
                            <input type="text" class="form-control" id="teacherSubject" name="subject" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Gallery Modal -->
    <div class="modal fade" id="addGalleryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Foto Galeri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=add_gallery" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="galleryTitle" class="form-label">Judul Foto</label>
                            <input type="text" class="form-control" id="galleryTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="galleryDate" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="galleryDate" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label for="galleryImage" class="form-label">Gambar</label>
                            <input type="file" class="form-control" id="galleryImage" name="image" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 <!-- Modals -->
    <!-- Add Admin Modal -->
    <?php if ($admin['role'] === 'superadmin'): ?>
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Administrator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=add_admin" id="addAdminForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="adminUsername" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="adminUsername" name="username" required
                                   placeholder="Masukkan username unik">
                            <div class="form-text">Username harus unik dan belum digunakan</div>
                        </div>
                        <div class="mb-3">
                            <label for="adminFullname" class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" id="adminFullname" name="fullname" required
                                   placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="mb-3">
                            <label for="adminPhone" class="form-label">Nomor HP *</label>
                            <input type="tel" class="form-control" id="adminPhone" name="phone" required 
                                   pattern="\+62[0-9]{9,13}" placeholder="+628123456789">
                            <small class="text-muted">Format: +62xxxxxxxxxx</small>
                        </div>
                        <div class="mb-3">
                            <label for="adminPassword" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="adminPassword" name="password" required minlength="6"
                                   placeholder="Minimal 6 karakter">
                        </div>
                        <div class="mb-3">
                            <label for="adminRole" class="form-label">Role *</label>
                            <select class="form-select" id="adminRole" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="admin">Admin</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set today's date as default for date inputs
            const newsDate = document.getElementById('newsDate');
            const galleryDate = document.getElementById('galleryDate');
            
            if (newsDate) newsDate.valueAsDate = new Date();
            if (galleryDate) galleryDate.valueAsDate = new Date();
            
            // Handle tab navigation
            const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                });
            });

            // Reset form modal ketika ditutup
            const addAdminModal = document.getElementById('addAdminModal');
            if (addAdminModal) {
                addAdminModal.addEventListener('hidden.bs.modal', function () {
                    document.getElementById('addAdminForm').reset();
                });
            }
        });

        // Validasi username unik sebelum submit
        document.getElementById('addAdminForm')?.addEventListener('submit', function(e) {
            const username = document.getElementById('adminUsername').value;
            if (username === 'admin' || username === 'superadmin') {
                e.preventDefault();
                alert('Username "' + username + '" sudah digunakan. Silakan pilih username lain.');
                return false;
            }
        });
    </script>
    
  

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set today's date as default for date inputs
            document.getElementById('newsDate').valueAsDate = new Date();
            document.getElementById('galleryDate').valueAsDate = new Date();
            
            // Handle tab navigation
            const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                });
            });
        });
    </script>
        <script>
        // Background Management Functions
        let currentHeroBackgroundFile = null;
        let currentSectionBackgroundFile = null;

        // Load saved backgrounds on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadBackgrounds();
        });

        function loadBackgrounds() {
            // Load hero background
            const heroBackground = localStorage.getItem('schoolHeroBackground');
            if (heroBackground) {
                document.getElementById('currentHeroBackground').style.backgroundImage = `url(${heroBackground})`;
            } else {
                // Default hero background from database
                document.getElementById('currentHeroBackground').style.backgroundImage = `url('<?php echo $settings['hero_background']; ?>')`;
            }

            // Load section background
            const sectionBackground = localStorage.getItem('schoolSectionBackground');
            if (sectionBackground) {
                document.getElementById('currentSectionBackground').style.backgroundImage = `url(${sectionBackground})`;
            } else {
                document.getElementById('currentSectionBackground').style.backgroundColor = '#f8f9fa';
            }
        }

        function previewHeroBackground(input) {
            currentHeroBackgroundFile = input.files[0];
            const preview = document.getElementById('currentHeroBackground');
            if (currentHeroBackgroundFile) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.style.backgroundImage = `url(${e.target.result})`;
                    preview.style.backgroundColor = 'transparent';
                }
                reader.readAsDataURL(currentHeroBackgroundFile);
            }
        }

        function previewSectionBackground(input) {
            currentSectionBackgroundFile = input.files[0];
            const preview = document.getElementById('currentSectionBackground');
            if (currentSectionBackgroundFile) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.style.backgroundImage = `url(${e.target.result})`;
                    preview.style.backgroundColor = 'transparent';
                }
                reader.readAsDataURL(currentSectionBackgroundFile);
            }
        }

        function saveHeroBackground() {
            if (currentHeroBackgroundFile) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    localStorage.setItem('schoolHeroBackground', e.target.result);
                    alert('Background Hero berhasil disimpan!');
                    // Clear file input
                    document.getElementById('heroBackgroundFile').value = '';
                    currentHeroBackgroundFile = null;
                };
                reader.readAsDataURL(currentHeroBackgroundFile);
            } else {
                alert('Silakan pilih gambar terlebih dahulu!');
            }
        }

        function saveSectionBackground() {
            if (currentSectionBackgroundFile) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    localStorage.setItem('schoolSectionBackground', e.target.result);
                    alert('Background Section berhasil disimpan!');
                    // Clear file input
                    document.getElementById('sectionBackgroundFile').value = '';
                    currentSectionBackgroundFile = null;
                };
                reader.readAsDataURL(currentSectionBackgroundFile);
            } else {
                alert('Silakan pilih gambar terlebih dahulu!');
            }
        }

        function resetSectionBackground() {
            if (confirm('Yakin ingin mengembalikan background section ke abu-abu default?')) {
                localStorage.removeItem('schoolSectionBackground');
                const preview = document.getElementById('currentSectionBackground');
                preview.style.backgroundImage = 'none';
                preview.style.backgroundColor = '#f8f9fa';
                document.getElementById('sectionBackgroundFile').value = '';
                currentSectionBackgroundFile = null;
                alert('Background Section dikembalikan ke abu-abu!');
            }
        }
    </script>
</body>
</html>
