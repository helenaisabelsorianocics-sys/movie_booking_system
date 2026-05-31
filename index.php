<?php
session_start();
require_once 'config/db_connection.php';

try {
    $sql = "SELECT s.showtime_id, m.title, m.genre, m.duration, m.description, m.poster, t.theater_name, s.show_date, s.show_time, s.available_seats 
            FROM hius_showtime_table s
            JOIN hius_movie_table m ON s.movie_id = m.movie_id
            JOIN hius_theater_table t ON s.theater_id = t.theater_id
            WHERE s.show_date >= CURDATE()
            ORDER BY s.show_date ASC, s.show_time ASC";
    $stmt = $pdo->query($sql);
    $screenings = $stmt->fetchAll();
} catch (PDOException $e) {
    $screenings = [];
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frasshawty Cinema Hub - Now Showing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0b0b0b; color: #ffffff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .bg-custom-card { background-color: #121212; border: 1px solid #222222 !important; }
        .text-gold { color: #ffb800 !important; }
        .btn-gold { background-color: #ffb800; color: #0b0b0b; font-weight: 700; border: none; }
        .btn-gold:hover { background-color: #e0a200; color: #0b0b0b; }
        .btn-outline-gold { border: 1px solid #ffb800; color: #ffb800; font-weight: 600; background: transparent; }
        .btn-outline-gold:hover { background-color: #ffb800; color: #0b0b0b; }
        .transition-all { transition: all 0.25s ease-in-out; }
        .transition-all:hover { transform: translateY(-4px); box-shadow: 0 .5rem 1.5rem rgba(255, 184, 0, 0.15) !important; border-color: #ffb800 !important; }
        hr { border-color: #333333; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-black shadow-sm py-3 border-b border-secondary" style="border-bottom: 1px solid #1a1a1a;">
    <div class="container">
        <a class="navbar-brand fw-bold text-gold" href="index.php">
            <i class="fa-solid fa-clapperboard me-2"></i>FRASSHAWTY CINEMA HUB
        </a>
        <div class="d-flex align-items-center gap-2">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="text-light small me-2 d-none d-sm-inline">Welcome, <strong class="text-gold"><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                <?php if (strtolower($_SESSION['role']) === 'superadmin'): ?>
                    <a href="admin/dashboard.php" class="btn btn-sm btn-danger fw-bold">Master Admin</a>
                <?php elseif ($_SESSION['role'] === 'Employee'): ?>
                    <a href="employee/index.php" class="btn btn-sm btn-success fw-bold">Staff Panel</a>
                <?php else: ?>
                    <a href="customer/index.php" class="btn btn-sm btn-gold">My Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-sm btn-outline-danger ms-1" title="Sign Out"><i class="fa-solid fa-power-off"></i></a>
            <?php else: ?>
                <a href="login.php" class="btn btn-sm btn-outline-light px-3 me-1 fw-bold">Sign In</a>
                <a href="register.php" class="btn btn-sm btn-gold px-3">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="p-5 text-center bg-custom-card rounded-3 shadow-sm mb-5">
        <h1 class="text-white fw-bold display-5">Welcome to <span class="text-gold">Frasshawty Cinema Hub</span></h1>
        <p class="col-lg-8 mx-auto fs-5 text-muted mt-3 mb-0">
            Browse currently screening box office collections. Register an account today to customize seat reservation schedules!
        </p>
    </div>

    <h3 class="fw-bold text-white mb-4"><i class="fa-solid fa-fire text-gold me-2"></i>Now Screening in Theaters</h3>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger bg-danger text-white border-0 small mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>Catalog Sync Error: <?= htmlspecialchars($db_error) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (count($screenings) > 0): ?>
            <?php foreach ($screenings as $s): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm bg-custom-card rounded-3 overflow-hidden transition-all">
                        
                        <div style="height: 280px; background-color: #1a1a1a; overflow: hidden; position: relative;">
                            <img src="uploads/<?= htmlspecialchars(!empty($s['poster']) ? $s['poster'] : 'default_poster.png') ?>" 
                                 alt="<?= htmlspecialchars($s['title']) ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover;"
                                 onerror="this.src='https://placehold.co/400x260/1a1a1a/ffffff?text=Poster+Unavailable';">
                        </div>

                        <div class="card-body d-flex flex-column justify-content-between p-4">
                            <div>
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <span class="badge bg-dark text-gold text-uppercase border border-secondary" style="font-size: 10px; padding: 5px 10px;"><?= htmlspecialchars($s['genre']) ?></span>
                                    
                                    <?php 
                                        $seats = intval($s['available_seats']);
                                        if ($seats === 0) {
                                            echo '<span class="badge bg-danger text-uppercase" style="font-size: 10px;">Sold Out</span>';
                                        } elseif ($seats <= 10) {
                                            echo '<span class="badge bg-warning text-dark text-uppercase" style="font-size: 10px;">Filling Fast</span>';
                                        }
                                    ?>
                                </div>
                                <h4 class="fw-bold text-white mb-2 text-truncate" title="<?= htmlspecialchars($s['title']) ?>"><?= htmlspecialchars($s['title']) ?></h4>
                                <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; height: 60px; line-height: 20px;">
                                    <?= htmlspecialchars($s['description']) ?>
                                </p>
                                <hr class="my-3">
                                <p class="mb-2 text-light small fw-medium"><i class="fa-solid fa-display me-2 text-gold"></i><?= htmlspecialchars($s['theater_name']) ?></p>
                                <p class="mb-2 text-light small fw-medium"><i class="fa-solid fa-calendar me-2 text-gold"></i><?= date('M d, Y', strtotime($s['show_date'])) ?></p>
                                <p class="mb-0 text-light small fw-medium"><i class="fa-solid fa-clock me-2 text-gold"></i><?= date('g:i A', strtotime($s['show_time'])) ?> <span class="text-muted fw-normal">(<?= intval($s['duration']) ?> mins)</span></p>
                            </div>
                            
                            <div class="mt-4">
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Customer'): ?>
                                    <?php if ($seats > 0): ?>
                                        <a href="customer/select_seats.php?showtime_id=<?= $s['showtime_id'] ?>" class="btn btn-gold w-100 py-2 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Reserve Seats</a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100 py-2 text-uppercase" style="font-size: 12px;" disabled>Sold Out</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline-gold w-100 py-2 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Login to Reserve</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-solid fa-film display-4 mb-3 d-block text-secondary"></i> No scheduled showtimes active right now.
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="bg-black text-white-50 text-center py-4 mt-5 style="border-top: 1px solid #1a1a1a;">
    <div class="container small">
        <p class="mb-0">&copy; <?= date('Y') ?> FRASSHAWTY CINEMA HUB. All Rights Reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>