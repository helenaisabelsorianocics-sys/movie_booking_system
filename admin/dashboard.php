<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: ../index.php"); 
    exit(); 
}
require_once '../config/db_connection.php';

try {

    $movie_count = $pdo->query("SELECT COUNT(*) FROM hius_movie_table")->fetchColumn();
    $theater_count = $pdo->query("SELECT COUNT(*) FROM hius_theater_table")->fetchColumn();
    $booking_count = $pdo->query("SELECT COUNT(*) FROM hius_booking_table")->fetchColumn();
    $user_count = $pdo->query("SELECT COUNT(*) FROM hius_user_table")->fetchColumn();


    $movie_list = $pdo->query("SELECT title, genre, poster FROM hius_movie_table ORDER BY movie_id DESC LIMIT 4")->fetchAll();


    $system_logs = $pdo->query("SELECT l.*, u.username FROM hius_log_table l 
                                LEFT JOIN hius_user_table u ON l.user_id = u.user_id 
                                ORDER BY l.timestamp DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    die("Dashboard Query Sync Failure: " . $e->getMessage());
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-admin.php'; ?>

<div class="container-fluid">
    <div class="mb-4 text-start">
        <h2>System Master Dashboard</h2>
        <p class="text-muted">Welcome back, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>.</p>
    </div>
    
    <div class="row g-4 mb-4 text-start">
        <div class="col-md-3">
            <div class="card p-3 shadow-sm border-start border-primary border-4 bg-white">
                <h6 class="text-muted text-uppercase small">Total Movies</h6>
                <h3 class="fw-bold mb-0"><?= $movie_count ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow-sm border-start border-success border-4 bg-white">
                <h6 class="text-muted text-uppercase small">Active Theaters</h6>
                <h3 class="fw-bold mb-0"><?= $theater_count ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow-sm border-start border-warning border-4 bg-white">
                <h6 class="text-muted text-uppercase small">Total Bookings</h6>
                <h3 class="fw-bold mb-0"><?= $booking_count ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 shadow-sm border-start border-info border-4 bg-white">
                <h6 class="text-muted text-uppercase small">Registered Users</h6>
                <h3 class="fw-bold mb-0"><?= $user_count ?></h3>
            </div>
        </div>
    </div>
    
    <div class="row g-4 text-start">
        <div class="col-xl-8">
            <div class="p-4 bg-white rounded shadow-sm border h-100">
                <div class="mb-4 border-bottom pb-2">
                    <h4>Operational Infrastructure Panel</h4>
                    <p class="text-muted small mb-0">Currently cataloged movie selections inside the booking system database grid.</p>
                </div>

                <div class="row g-4">
                    <?php if (empty($movie_list)): ?>
                        <div class="col-12 text-center text-muted py-4">
                            <i class="fa-solid fa-film fs-2 d-block mb-2 text-black-50"></i>
                            No movie listings found in your database records yet.
                        </div>
                    <?php else: ?>
                        <?php foreach ($movie_list as $movie): ?>
                            <div class="col-sm-6 animate-fade-in">
                                <div class="card h-100 shadow-sm border rounded-3 overflow-hidden bg-light">
                                    <?php 
                                        $poster_path = "../uploads/" . $movie['poster'];
                                        if (empty($movie['poster']) || !file_exists($poster_path)) {
                                            $poster_path = "../uploads/default_poster.png"; 
                                        }
                                    ?>
                                    <div class="row g-0 h-100">
                                        <div class="col-4">
                                            <img src="<?= htmlspecialchars($poster_path) ?>" class="w-100 h-100 object-fit-cover" style="min-height: 110px; max-height: 130px;" alt="Movie Poster">
                                        </div>
                                        <div class="col-8 d-flex align-items-center">
                                            <div class="card-body p-3">
                                                <h6 class="fw-bold text-dark text-truncate mb-1"><?= htmlspecialchars($movie['title']) ?></h6>
                                                <span class="badge bg-white text-secondary border small text-capitalize" style="font-size: 10px;">
                                                    <?= htmlspecialchars($movie['genre']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="p-4 bg-white rounded shadow-sm border h-100">
                <div class="mb-3 border-bottom pb-2">
                    <h4>Live Audit Trail</h4>
                    <p class="text-muted small mb-0">Real-time system actions streamed straight from your log schema.</p>
                </div>
                
                <div class="d-flex flex-column gap-2">
                    <?php if (empty($system_logs)): ?>
                        <div class="text-center text-muted py-5 small">
                            <i class="fa-solid fa-list-check d-block mb-2 text-black-50 fs-4"></i>
                            No activity entries found in log history.
                        </div>
                    <?php else: ?>
                        <?php foreach ($system_logs as $log): ?>
                            <div class="p-2.5 rounded bg-light border-start border-3 border-secondary small" style="font-size: 12px;">
                                <div class="d-flex justify-content-between mb-1 fw-bold text-dark">
                                    <span><i class="fa-regular fa-user me-1"></i> <?= htmlspecialchars($log['username'] ?? 'System') ?></span>
                                    <span class="text-muted fw-normal" style="font-size: 10px;"><?= date('g:i A', strtotime($log['timestamp'])) ?></span>
                                </div>
                                <div class="text-secondary text-wrap" style="line-height: 1.4;"><?= htmlspecialchars($log['action']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>