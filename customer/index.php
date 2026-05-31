<?php
session_start();
require_once '../config/db_connection.php';

try {

    $movies = $pdo->query("SELECT * FROM hius_movie_table ORDER BY movie_id DESC")->fetchAll();
    
    $showtimes_query = $pdo->query("
        SELECT s.*, t.theater_name 
        FROM hius_showtime_table s
        INNER JOIN hius_theater_table t ON s.theater_id = t.theater_id
        ORDER BY s.show_time ASC
    ")->fetchAll();
    
    $movie_showtimes = [];
    foreach ($showtimes_query as $time_row) {
        $movie_showtimes[$time_row['movie_id']][] = $time_row;
    }
} catch (PDOException $e) {
    $movies = [];
    $movie_showtimes = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frasshawty Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/Soriano/movie_booking/css/customer-style.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-premium">
        <div class="container-fluid">
            <a class="navbar-brand navbar-brand-premium" href="#">
                <i class="fa-solid fa-clapperboard text-danger me-2"></i>FRASSHAWTY <span>CINEMA</span>
            </a>
            <div class="collapse navbar-collapse d-flex justify-content-center">
                <div class="navbar-nav">
                    <a class="nav-link nav-link-premium active" href="#">MOVIES</a>
                    <a class="nav-link nav-link-premium" href="my_reservation.php">MY TICKETS</a>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <span class="text-white me-3 small"><i class="fa-regular fa-user me-2 text-warning"></i><?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?></span>
                <a href="../logout.php" class="btn btn-sm btn-outline-danger">Sign Out</a>
            </div>
        </div>
    </nav>

    <div class="location-banner text-start">
        <div class="container-fluid">
            <span class="text-warning fw-semibold"><i class="fa-solid fa-location-dot me-2"></i>Metro Manila, Philippines | Elite Screening Engine</span>
        </div>
    </div>

    <div class="container my-5">
        <h4 class="text-uppercase fw-bold mb-4 text-start" style="color: var(--cinema-gold); letter-spacing: 1px;">Showtimes</h4>

        <div class="date-selector-container shadow mb-5">
            <button class="date-tab active"><span class="day-label">Today</span><span class="date-number">30</span><span class="day-label">May</span></button>
            <button class="date-tab"><span class="day-label">Sun</span><span class="date-number">31</span><span class="day-label">May</span></button>
            <button class="date-tab"><span class="day-label">Mon</span><span class="date-number">01</span><span class="day-label">Jun</span></button>
            <button class="date-tab"><span class="day-label">Tue</span><span class="date-number">02</span><span class="day-label">Jun</span></button>
            <button class="date-tab"><span class="day-label">Wed</span><span class="date-number">03</span><span class="day-label">Jun</span></button>
            <button class="date-tab"><span class="day-label">Thu</span><span class="date-number">04</span><span class="day-label">Jun</span></button>
        </div>

        <div class="row">
            <div class="col-12">
                <?php if (empty($movies)): ?>
                    <div class="p-5 text-center bg-dark rounded border border-secondary">
                        <i class="fa-solid fa-box-open fs-1 text-muted mb-3"></i>
                        <p class="text-muted mb-0">No blockbuster titles found in your database records currently.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($movies as $movie): ?>
                        <div class="movie-list-row">
                            <div class="row g-4">
                                
                                <div class="col-md-3 col-lg-2 d-flex justify-content-start">
                                    <div class="movie-poster-frame">
                                        <?php 
                                            $poster_file = $movie['poster'] ?? '';
                                            $poster_path = "/Soriano/movie_booking/uploads/" . $poster_file;
                                            
                                            if (empty($poster_file)) {
                                                $poster_path = "https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=500"; 
                                            }
                                        ?>
                                        <img src="<?= htmlspecialchars($poster_path) ?>" alt="Movie Poster">
                                    </div>
                                </div>
                                
                                <div class="col-md-9 col-lg-10 text-start d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center mb-2">
                                            <h3 class="movie-title mb-0"><?= htmlspecialchars($movie['title']) ?></h3>
                                        </div>
                                        <div class="mb-3">
                                            <span class="text-secondary small me-4"><strong>Genre:</strong> <?= htmlspecialchars($movie['genre']) ?></span>
                                            <span class="text-secondary small"><strong>Duration:</strong> <?= htmlspecialchars($movie['duration']) ?> mins</span>
                                        </div>
                                        <p class="small mb-0" style="max-width: 800px; color: #e0e0e0; line-height: 1.5;">
                                            <?= htmlspecialchars(!empty($movie['description']) ? $movie['description'] : 'Experience this major blockbuster screening now inside our premier stadium auditoriums featuring spatial surround audio arrays. Select an available session timing block below to begin reservation mapping.') ?>
                                        </p>
                                    </div>

                                    <div class="mt-4">
                                        <h6 class="text-uppercase text-warning fw-bold small mb-3">Available Showtimes</h6>
                                        <div class="d-flex flex-wrap gap-3">
                                            <?php 
                                            $current_movie_id = $movie['movie_id'];
                                            
                                            if (!empty($movie_showtimes[$current_movie_id])): 
                                                foreach ($movie_showtimes[$current_movie_id] as $time_slot): 
                                                    // Formats using the exact database column 'show_time'
                                                    $formatted_time = date('g:i A', strtotime($time_slot['show_time']));
                                            ?>
                                                    <a href="select_seats.php?showtime_id=<?= $time_slot['showtime_id'] ?>" class="showtime-button text-center">
                                                        <?= htmlspecialchars($formatted_time) ?>
                                                        <span class="showtime-type"><?= htmlspecialchars($time_slot['theater_name']) ?></span>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-danger small"><i class="fa-regular fa-clock me-1"></i> No active screening sessions found in the system for this title today.</span>
                                            <?php endif; ?>
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

    <footer class="footer-cinema text-start">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="text-uppercase fw-bold"><i class="fa-solid fa-clapperboard text-danger me-2"></i>FRASSHAWTY CINEMA</h5>
                    <p class="small text-warning">Providing next-generation cinema presentation engines, absolute viewing configurations, and premium ticketing delivery platforms.</p>
                </div>
                <div class="col-md-2 offset-md-1">
                    <h5>EXPLORE</h5>
                    <a href="#" class="footer-link small">Now Showing</a>
                    <a href="#" class="footer-link small">Coming Soon</a>
                </div>
                <div class="col-md-2">
                    <h5>SUPPORT</h5>
                    <a href="#" class="footer-link small">Terms of Use</a>
                    <a href="#" class="footer-link small">Privacy Policy</a>
                </div>
                <div class="col-md-3">
                    <h5>CINEMA LOCATIONS</h5>
                    <p class="small mb-1"><i class="fa-solid fa-map-marker-alt me-2 text-warning"></i> Metro Manila, Philippines</p>
                    <p class="small mb-1"><i class="fa-solid fa-envelope me-2 text-warning"></i> support@frasshawtycinema.com</p>
                </div>
            </div>
            <hr class="border-secondary my-4 opacity-25">
            <p class="small mb-0">&copy; 2026 Frasshawty Cinema Reserve. All rights secured runtime engine.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>