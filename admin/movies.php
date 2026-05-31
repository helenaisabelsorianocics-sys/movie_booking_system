<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: ../index.php"); 
    exit(); 
}
require_once '../config/db_connection.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    if (!empty($search)) {

        $stmt = $pdo->prepare("SELECT * FROM hius_movie_table WHERE title LIKE :s OR genre LIKE :s ORDER BY movie_id DESC");
        $stmt->execute(['s' => "%$search%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM hius_movie_table ORDER BY movie_id DESC");
    }
    $movies = $stmt->fetchAll();
} catch (PDOException $e) {
    $movies = [];
    $db_error = $e->getMessage();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-admin.php'; ?>

<div class="container-fluid text-start text-dark">
    
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold text-dark text-uppercase tracking-wider m-0" style="letter-spacing: 1px;">Movie Records Management</h2>
            <p class="text-muted mb-0">Review, search, and catalogue titles inside the theater repository framework.</p>
        </div>
        <a href="add_movie.php" class="btn btn-danger fw-bold btn-sm px-3 shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Add New Movie
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success bg-transparent border-success text-success small py-2" style="border-radius: 6px;">
            <i class="fa-solid fa-circle-check me-2"></i>Registry catalog updated successfully!
        </div>
    <?php endif; ?>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger bg-transparent border-danger text-danger small py-2" style="border-radius: 6px;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>Database Connection Crash: <?= htmlspecialchars($db_error) ?>
        </div>
    <?php endif; ?>

    <div class="card p-3 mb-4 rounded-3 text-start shadow" style="background-color: #121212; border: 1px solid #2d2d2d;">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-10">
                <div class="input-group input-group-sm">
                    <span class="input-group-text border-secondary text-secondary" style="background-color: #1a1a1a;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" name="search" class="form-control text-light border-secondary shadow-none" style="background-color: #1a1a1a;" placeholder="Search film catalog contents by title string or genre classification..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary btn-sm w-100 fw-bold border-secondary text-light" style="background-color: #2a2a2a;">
                    Filter Assets
                </button>
            </div>
        </form>
    </div>

    <div class="shadow rounded-3 overflow-hidden mb-5" style="background-color: #121212; border: 1px solid #2d2d2d;">
        
        <?php if (empty($movies)): ?>
            <div class="p-5 text-center text-white-50">
                <i class="fa-solid fa-clapperboard display-4 d-block mb-2 text-secondary"></i>
                No catalog items matched your structural criteria index properties.
            </div>
        <?php else: ?>
            
            <div class="row mx-0 py-3 text-uppercase fw-bold text-white-50 d-none d-md-flex align-items-center" style="background-color: #1a1a1a; border-bottom: 2px solid #2d2d2d; font-size: 11px; letter-spacing: 0.5px;">
                <div class="col-md-1 ps-4 text-center" style="max-width: 90px;">Poster</div>
                <div class="col-md-5">Movie Details</div>
                <div class="col-md-2">Genre Class</div>
                <div class="col-md-2">Length</div>
                <div class="col-md-2 pe-4">Release Date</div>
            </div>

            <?php foreach($movies as $m): ?>
                <div class="row mx-0 py-3 align-items-center text-white" style="border-bottom: 1px solid #2d2d2d;">
                    
                    <div class="col-4 col-md-1 ps-md-4 mb-2 mb-md-0 text-md-center" style="max-width: 90px;">
                        <?php 
                            $poster_url = "../uploads/" . $m['poster'];
                            if (empty($m['poster']) || !file_exists($poster_url)) {
                                $poster_url = "../uploads/default_poster.png"; 
                            }
                        ?>
                        <img src="<?= htmlspecialchars($poster_url) ?>" class="rounded border border-secondary shadow-sm object-fit-cover" style="width: 54px; height: 74px;" alt="Art Frame">
                    </div>
                    
                    <div class="col-8 col-md-5 mb-2 mb-md-0">
                        <div class="fw-bold text-white fs-6 mb-1"><?= htmlspecialchars($m['title']) ?></div>
                        <p class="text-white-50 text-truncate mb-0 small" style="max-width: 100%;" title="<?= htmlspecialchars($m['description'] ?? '') ?>">
                            <?= htmlspecialchars($m['description'] ?? 'No synopsis details provided for this inventory selection item.') ?>
                        </p>
                    </div>
                    
                    <div class="col-6 col-md-2 mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase me-1" style="font-size: 10px;">Genre:</span>
                        <span class="badge border border-secondary text-capitalize px-2 py-1 text-white" style="font-size: 11px; background-color: #1a1a1a;">
                            <?= htmlspecialchars($m['genre']) ?>
                        </span>
                    </div>
                    
                    <div class="col-6 col-md-2 mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase me-1" style="font-size: 10px;">Length:</span>
                        <span class="text-light small">
                            <i class="fa-regular fa-clock text-white-50 me-1"></i> <?= intval($m['duration']) ?> mins
                        </span>
                    </div>
                    
                    <div class="col-12 col-md-2 pe-md-4 mb-1 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase me-1" style="font-size: 10px;">Released:</span>
                        <span class="fw-semibold small" style="color: var(--cinema-gold, #ffb800);">
                            <?= !empty($m['release_date']) ? date('M d, Y', strtotime($m['release_date'])) : 'N/A' ?>
                        </span>
                    </div>

                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>