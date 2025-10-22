<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

$database = new Database();
$db = $database->getConnection();

$settings = $db->query("SELECT * FROM school_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$news = $db->query("SELECT * FROM news WHERE status = 'published' ORDER BY date DESC")->fetchAll(PDO::FETCH_ASSOC);
// Pagination logic
$items_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$total_items = $db->query("SELECT COUNT(*) FROM news WHERE status = 'published'")->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

$news = $db->query("SELECT * FROM news WHERE status = 'published' ORDER BY date DESC LIMIT $items_per_page OFFSET $offset")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Berita - <?php echo $settings['name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .news-card {
            transition: transform 0.3s;
        }
        .news-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-school me-2"></i><?php echo $settings['name']; ?>
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Semua Berita</h1>
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
            </a>
        </div>

        <div class="row">
            <?php if ($news): ?>
                <?php foreach ($news as $item): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card news-card h-100">
                        <img src="<?php echo $item['image']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo substr(strip_tags($item['content']), 0, 100); ?>...</p>
                            <div class="mt-auto">
                                <small class="text-muted"><?php echo formatDate($item['date']); ?></small>
                                <button class="btn btn-sm btn-primary float-end" data-bs-toggle="modal" data-bs-target="#newsModal<?php echo $item['id']; ?>">
                                    Baca Selengkapnya
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- News Modal -->
                <div class="modal fade" id="newsModal<?php echo $item['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <img src="<?php echo $item['image']; ?>" class="img-fluid mb-3 rounded" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <p class="text-muted mb-3">
                                    <i class="fas fa-calendar me-2"></i><?php echo formatDate($item['date']); ?>
                                </p>
                                <div style="text-align: justify; line-height: 1.6;">
                                    <?php echo nl2br(htmlspecialchars($item['content'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>Belum ada berita</h4>
                        <p class="mb-0">Silakan kembali lagi nanti.</p>
                    </div>
                </div>
            <?php endif; ?>
        <!-- Pagination Navigation -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <!-- Previous Page -->
        <li class="page-item <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <!-- Page Numbers -->
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <!-- Next Page -->
        <li class="page-item <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>

<!-- Info Pagination -->
<div class="text-center mt-3">
    <small class="text-muted">
        Menampilkan <?php echo count($gallery); ?> dari <?php echo $total_items; ?> foto 
        (Halaman <?php echo $current_page; ?> dari <?php echo $total_pages; ?>)
    </small>
</div>
<?php endif; ?>
        </div>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>