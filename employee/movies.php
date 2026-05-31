<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employee') { 
    header("Location: ../login.php"); 
    exit(); 
}
require_once '../config/db_connection.php';

try {

    $movies = $pdo->query("SELECT * FROM hius_movie_table ORDER BY title ASC")->fetchAll();
} catch (PDOException $e) {
    $movies = [];
    $db_error = $e->getMessage();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-employee.php'; ?>

<style>
    .clamped-text {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;  
        overflow: hidden;
        white-space: normal;
    }
</style>

<div class="container-fluid text-start">
    <div class="mb-4">
        <h2 class="fw-bold text-dark">Movie Catalog</h2>
        <p class="text-muted">Review current and upcoming films scheduled across platform theaters.</p>
    </div>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger small shadow-sm"><i class="fa-solid fa-triangle-exclamation me-2"></i>Catalog Error: <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (empty($movies)): ?>
            <div class="col-12 text-center text-muted p-5 bg-white rounded-3 border shadow-sm">
                <i class="fa-solid fa-film display-4 d-block mb-2 text-black-50"></i> No movies found in the catalog database.
            </div>
        <?php else: ?>
            <?php foreach ($movies as $movie): ?>
                <div class="col-md-4 col-xl-3">
                    <div class="card h-100 shadow-sm border bg-white rounded-3 overflow-hidden d-flex flex-column justify-content-between">
                        <div>
                            <img src="../uploads/<?= htmlspecialchars($movie['poster']) ?>" 
                                 class="card-img-top object-fit-cover bg-secondary" 
                                 style="height: 340px;" 
                                 alt="Poster"
                                 onerror="this.onerror=null; this.src='https://placehold.co/400x600/343a40/ffffff?text=<?= urlencode($movie['title']) ?>';">
                            
                            <div class="card-body p-3">
                                <h5 class="fw-bold text-dark text-truncate mb-1" title="<?= htmlspecialchars($movie['title']) ?>"><?= htmlspecialchars($movie['title']) ?></h5>
                                <div class="mb-2">
                                    <span class="badge bg-light text-primary border border-primary text-uppercase" style="font-size: 10px;"><?= htmlspecialchars($movie['genre']) ?></span>
                                </div>
                                <div class="text-muted small mb-2"><i class="fa-regular fa-clock me-1 text-dark"></i> <?= intval($movie['duration']) ?> Minutes</div>
                                
                                <p class="card-text text-secondary small clamped-text mb-0">
                                    <?= htmlspecialchars($movie['description']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>