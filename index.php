<?php
session_start();
require_once 'config/database.php';
require_once 'config/helpers.php';

$database = new Database();
$db = $database->getConnection();

// Fungsi helper
function getSettings($db) {
    $query = "SELECT * FROM school_settings LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getNews($db, $limit = null, $status = 'published') {
    $query = "SELECT * FROM news WHERE status = :status ORDER BY date DESC";
    if ($limit) {
        $query .= " LIMIT " . $limit;
    }
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getGallery($db, $limit = null) {
    $query = "SELECT * FROM gallery ORDER BY date DESC";
    if ($limit) {
        $query .= " LIMIT " . $limit;
    }
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTeachers($db) {
    $query = "SELECT * FROM teachers ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatDate($dateString) {
    return date('d F Y', strtotime($dateString));
}

$settings = getSettings($db);
$latestNews = getNews($db, 3);
$latestGallery = getGallery($db, 6);
$teachers = getTeachers($db);

// Handle login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM admins WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = $admin;
            header("Location: admin.php");
            exit();
        }
    }
    $login_error = "Username atau password salah!";
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['name']; ?> - Website Profil Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS yang sama seperti sebelumnya */
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            background-color: #f9f9f9;
        }

        .navbar {
            background-color: var(--primary);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .hero-section {
            background: linear-gradient(rgba(44, 62, 80, 0.8), rgba(44, 62, 80, 0.8)), url('<?php echo $settings['hero_background']; ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .section-title {
            position: relative;
            margin-bottom: 40px;
            font-weight: 600;
        }

        .section-title:after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background-color: var(--secondary);
            margin: 10px auto;
        }

        .card {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .news-card img, .gallery-item img {
            height: 200px;
            object-fit: cover;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
            cursor: pointer;
        }

        .gallery-item img {
            transition: transform 0.5s;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(44, 62, 80, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        footer {
            background-color: var(--primary);
            color: white;
            padding: 40px 0 20px;
        }

        .btn-primary {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        .clickable-card {
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .clickable-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-school me-2"></i><?php echo $settings['name']; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#profile">Profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#news">Berita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gallery">Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">
                            <i class="fas fa-sign-in-alt"></i> Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <h1 class="display-4 fw-bold"><?php echo $settings['name']; ?></h1>
            <p class="lead">Mewujudkan Generasi Unggul, Berkarakter, dan Berprestasi</p>
            <a href="#profile" class="btn btn-primary btn-lg mt-3">Selengkapnya</a>
        </div>
    </section>

    <!-- Profile Section -->
    <section id="profile" class="py-5">
        <div class="container">
            <h2 class="section-title text-center">Profil Sekolah</h2>
            <div class="row">
                <div class="col-md-6">
                    <h3>Visi dan Misi</h3>
                    <h5>Visi:</h5>
                    <p><?php echo $settings['vision']; ?></p>
                    <h5>Misi:</h5>
                    <ul>
                        <?php
                        $missions = explode("\n", $settings['mission']);
                        foreach ($missions as $mission) {
                            if (!empty(trim($mission))) {
                                echo "<li>" . htmlspecialchars($mission) . "</li>";
                            }
                        }
                        ?>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h3>Total Siswa dan Guru</h3>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-graduate fa-2x text-primary me-3"></i>
                                <div>
                                    <h5 class="mb-0">Total Siswa</h5>
                                    <small><?php echo $settings['total_students']; ?> Siswa</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-chalkboard-teacher fa-2x text-primary me-3"></i>
                                <div>
                                    <h5 class="mb-0">Total Guru</h5>
                                    <small><?php echo count($teachers); ?> Guru</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="mt-4">Daftar Guru</h4>
                    <button class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#guruModal">
                        <i class="fas fa-list-alt me-2"></i>Lihat Daftar Guru
                    </button>

                    <!-- Modal Daftar Guru -->
                    <div class="modal fade" id="guruModal" tabindex="-1" aria-labelledby="guruModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="guruModalLabel">Daftar Guru</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Guru</th>
                                                <th>Mata Pelajaran/Jabatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($teachers as $index => $teacher): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                                <td><?php echo htmlspecialchars($teacher['subject']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section id="news" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center">Berita Terbaru</h2>
            <div class="row">
                <?php foreach ($latestNews as $news): ?>
                <div class="col-md-4 mb-4">
                    <div class="card news-card h-100 clickable-card" onclick="viewNewsDetail(<?php echo $news['id']; ?>)">
                        <img src="<?php echo $news['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($news['title']); ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($news['title']); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo substr($news['content'], 0, 100); ?>...</p>
                            <small class="text-muted mt-2"><?php echo formatDate($news['date']); ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="news.php" class="btn btn-outline-primary">Lihat Semua Berita</a>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery" class="py-5">
        <div class="container">
            <h2 class="section-title text-center">Galeri Kegiatan</h2>
            <div class="row">
                <?php foreach ($latestGallery as $item): ?>
                <div class="col-md-4">
                    <div class="gallery-item clickable-card" onclick="viewGalleryDetail('<?php echo $item['image']; ?>', '<?php echo htmlspecialchars($item['title']); ?>')">
                        <img src="<?php echo $item['image']; ?>" class="img-fluid" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <div class="gallery-overlay">
                            <h5 class="text-white"><?php echo htmlspecialchars($item['title']); ?></h5>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="gallery.php" class="btn btn-outline-primary">Lihat Semua Foto</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center">Kontak Kami</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <h4><i class="fas fa-map-marker-alt text-primary me-2"></i> Alamat</h4>
                        <p><?php echo $settings['address']; ?></p>
                    </div>
                    <div class="mb-4">
                        <h4><i class="fas fa-phone text-primary me-2"></i> Telepon</h4>
                        <p><?php echo $settings['phone']; ?></p>
                    </div>
                    <div class="mb-4">
                        <h4><i class="fas fa-envelope text-primary me-2"></i> Email</h4>
                        <p><?php echo $settings['email']; ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="map-container">
                       <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126852.50830678744!2d106.83549051340641!3d-6.503843588510025!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69c0989149fa11%3A0x24ab4e694205e323!2sMIS%20Tarbiyatul%20Falah!5e0!3m2!1sid!2sid!4v1760655654862!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Tentang Sekolah</h5>
                    <p><?php echo $settings['name']; ?> adalah sekolah unggulan yang telah berdiri sejak 1985. Kami berkomitmen untuk memberikan pendidikan terbaik bagi generasi penerus bangsa.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-light">Beranda</a></li>
                        <li><a href="#profile" class="text-light">Profil Sekolah</a></li>
                        <li><a href="#news" class="text-light">Berita</a></li>
                        <li><a href="#gallery" class="text-light">Galeri</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Media Sosial</h5>
                    <div class="social-links">
                       
        <a href="https://www.tiktok.com/@mitarfalhambalang?is_from_webapp=1&sender_device=pc" class="text-light me-3" target="_blank" title="TikTok">
            <i class="fab fa-tiktok fa-2x"></i>
        </a>
           <a href="https://www.instagram.com/tarfalhambalang?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" class="text-light me-3" target="_blank" title="Instagram">
            <i class="fab fa-instagram fa-2x"></i>
        </a>
                        <a href="https://www.youtube.com/@Murqon_Media" class="text-light"><i class="fab fa-youtube fa-2x"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p class="mb-0">© <?php echo date('Y'); ?> <?php echo $settings['name']; ?>. Semua Hak Dilindungi.</p>
            </div>
        </div>
    </footer>

    <!-- Modals -->
    <div class="modal fade" id="newsDetailModal" tabindex="-1" aria-labelledby="newsDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newsDetailModalLabel">Detail Berita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="newsDetailBody">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="galleryViewModal" tabindex="-1" aria-labelledby="galleryViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryViewModalLabel">Lihat Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="galleryViewImage" src="" class="img-fluid" alt="Foto Galeri">
                    <p class="mt-3" id="galleryViewTitle"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewNewsDetail(newsId) {
            fetch('get_news.php?id=' + newsId)
                .then(response => response.json())
                .then(news => {
                    document.getElementById('newsDetailModalLabel').textContent = news.title;
                    document.getElementById('newsDetailBody').innerHTML = `
                        <img src="${news.image}" class="img-fluid mb-3" alt="${news.title}" style="max-height: 400px; width: 100%; object-fit: cover;">
                        <p class="text-muted mb-3"><i class="fas fa-calendar-alt me-2"></i>${new Date(news.date).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                        <p style="white-space: pre-wrap; text-align: justify;">${news.content}</p>
                    `;
                    new bootstrap.Modal(document.getElementById('newsDetailModal')).show();
                });
        }

        function viewGalleryDetail(imageSrc, title) {
            document.getElementById('galleryViewImage').src = imageSrc;
            document.getElementById('galleryViewTitle').textContent = title;
            document.getElementById('galleryViewModalLabel').textContent = title;
            new bootstrap.Modal(document.getElementById('galleryViewModal')).show();
        }
    </script>
        <script>
        // Apply backgrounds from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            applyBackgrounds();
        });

        function applyBackgrounds() {
            // Apply hero background
            const heroBackground = localStorage.getItem('schoolHeroBackground');
            const heroSection = document.querySelector('.hero-section');
            if (heroBackground && heroSection) {
                heroSection.style.background = `linear-gradient(rgba(44, 62, 80, 0.8), rgba(44, 62, 80, 0.8)), url(${heroBackground})`;
                heroSection.style.backgroundSize = 'cover';
                heroSection.style.backgroundPosition = 'center';
            }

            // Apply section background
            const sectionBackground = localStorage.getItem('schoolSectionBackground');
            const bgLightSections = document.querySelectorAll('.bg-light');
            if (sectionBackground && bgLightSections.length > 0) {
                bgLightSections.forEach(section => {
                    section.style.background = `linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url(${sectionBackground})`;
                    section.style.backgroundSize = 'cover';
                    section.style.backgroundPosition = 'center';
                    section.style.backgroundAttachment = 'fixed';
                });
            } else {
                // Reset to default gray
                bgLightSections.forEach(section => {
                    section.style.background = '#f8f9fa';
                    section.style.backgroundSize = 'auto';
                    section.style.backgroundPosition = 'initial';
                    section.style.backgroundAttachment = 'scroll';
                });
            }
        }
    </script>
</body>
</html>