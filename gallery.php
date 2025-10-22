<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

$database = new Database();
$db = $database->getConnection();

$settings = $db->query("SELECT * FROM school_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$gallery = $db->query("SELECT * FROM gallery ORDER BY date DESC")->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Galeri Foto - <?php echo $settings['name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gallery-item {
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
            cursor: pointer;
            border-radius: 10px;
        }
        .gallery-item img {
            transition: transform 0.5s;
            height: 250px;
            object-fit: cover;
            width: 100%;
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
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
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
            <h1>Galeri Foto</h1>
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
            </a>
        </div>

        <div class="row">
            <?php if ($gallery): ?>
                <?php foreach ($gallery as $item): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="gallery-item" data-bs-toggle="modal" data-bs-target="#galleryModal<?php echo $item['id']; ?>">
                        <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <div class="gallery-overlay">
                            <div>
                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="mb-0"><?php echo formatDate($item['date']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gallery Modal -->
                <div class="modal fade" id="galleryModal<?php echo $item['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="<?php echo $item['image']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <p class="mt-3 text-muted">
                                    <i class="fas fa-calendar me-2"></i><?php echo formatDate($item['date']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>Belum ada foto galeri</h4>
                        <p class="mb-0">Silakan kembali lagi nanti.</p>
                    </div>
                </div>
            <?php endif; ?>
            <!-- Advanced Pagination dengan Ellipsis -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <!-- Previous Page -->
        <li class="page-item <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <!-- First Page -->
        <li class="page-item <?php echo $current_page == 1 ? 'active' : ''; ?>">
            <a class="page-link" href="?page=1">1</a>
        </li>
        
        <!-- Ellipsis jika perlu -->
        <?php if ($current_page > 3): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
        <?php endif; ?>
        
        <!-- Middle Pages -->
        <?php for ($i = max(2, $current_page - 1); $i <= min($total_pages - 1, $current_page + 1); $i++): ?>
            <?php if ($i != 1 && $i != $total_pages): ?>
                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endif; ?>
        <?php endfor; ?>
        
        <!-- Ellipsis jika perlu -->
        <?php if ($current_page < $total_pages - 2): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
        <?php endif; ?>
        
        <!-- Last Page -->
        <?php if ($total_pages > 1): ?>
            <li class="page-item <?php echo $current_page == $total_pages ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
            </li>
        <?php endif; ?>
        
        <!-- Next Page -->
        <li class="page-item <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $current_page + 1; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>