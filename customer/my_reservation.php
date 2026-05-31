<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db_connection.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

try {
    $sql = "SELECT b.booking_id, b.seat_number, b.total_price, b.booking_date, b.status,
                   m.title, m.genre, m.duration, t.theater_name, s.show_date, s.show_time
            FROM hius_booking_table b
            JOIN hius_showtime_table s ON b.showtime_id = s.showtime_id
            JOIN hius_movie_table m ON s.movie_id = m.movie_id
            JOIN hius_theater_table t ON s.theater_id = t.theater_id
            WHERE b.user_id = ?
            ORDER BY b.booking_id DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $my_bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $my_bookings = [];
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - Frasshawty Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link href="/Soriano/movie_booking/css/customer-style.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-premium">
        <div class="container-fluid">
            <a class="navbar-brand navbar-brand-premium" href="index.php">
                <i class="fa-solid fa-clapperboard text-danger me-2"></i>FRASSHAWTY <span>CINEMA</span>
            </a>
            <div class="collapse navbar-collapse d-flex justify-content-center">
                <div class="navbar-nav">
                    <a class="nav-link nav-link-premium" href="index.php">MOVIES</a>
                    <a class="nav-link nav-link-premium active" href="my_reservation.php">MY TICKETS</a>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <span class="text-white me-3 small"><i class="fa-regular fa-user me-2 text-warning"></i><?= htmlspecialchars($username) ?></span>
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
        <div class="mb-4 text-start">
            <h4 class="text-uppercase fw-bold m-0" style="color: var(--cinema-gold); letter-spacing: 1px;">My Ticket Orders</h4>
            <p class="text-white-50 small mb-0">Review your active seating reservations and entry vouchers below.</p>
        </div>

        <?php if (isset($db_error)): ?>
            <div class="alert alert-danger bg-dark border-danger text-danger small"><i class="fa-solid fa-triangle-exclamation me-2"></i>Error fetching records: <?= htmlspecialchars($db_error) ?></div>
        <?php endif; ?>

        <?php if (empty($my_bookings)): ?>
            <div class="p-5 text-center bg-dark rounded border border-secondary my-5">
                <i class="fa-solid fa-ticket-simple fs-1 text-muted mb-3"></i>
                <h5 class="text-white mb-2">No Active Tickets Found</h5>
                <p class="text-muted small mb-4">You have not reserved or booked any screening sessions currently.</p>
                <a href="index.php" class="btn btn-sm btn-danger px-4 py-2 text-uppercase fw-bold" style="letter-spacing: 0.5px;">Browse Showtimes</a>
            </div>
        <?php else: ?>
            
            <div class="card bg-dark border border-secondary overflow-hidden shadow mb-5" style="border-radius: 8px;">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle m-0 text-start">
                        <thead class="text-uppercase small text-warning" style="font-size: 11px; letter-spacing: 1px; border-bottom: 2px solid #333;">
                            <tr>
                                <th class="ps-4 py-3">Booking ID</th>
                                <th class="py-3">Movie Details</th>
                                <th class="py-3">Auditorium / Room</th>
                                <th class="py-3">Date & Time</th>
                                <th class="py-3 text-center">Seat Allocation</th>
                                <th class="py-3 text-end">Total Price</th>
                                <th class="py-3 text-center pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="small text-light" style="border-top: none;">
                            <?php foreach ($my_bookings as $booking): ?>
                                <tr style="border-bottom: 1px solid #2d2d2d;">
                                    <td class="fw-bold text-warning ps-4">#CR-<?= str_pad($booking['booking_id'], 5, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <div class="fw-bold text-white fs-6"><?= htmlspecialchars($booking['title']) ?></div>
                                        <span class="text-white-50" style="font-size: 11px;"><?= htmlspecialchars($booking['genre']) ?> • <?= intval($booking['duration']) ?> mins</span>
                                    </td>
                                    <td class="text-white">
                                        <i class="fa-solid fa-display me-2 text-secondary"></i><?= htmlspecialchars($booking['theater_name']) ?>
                                    </td>
                                    <td>
                                        <div class="text-white fw-bold"><?= date('M d, Y', strtotime($booking['show_date'])) ?></div>
                                        <div style="font-size: 11px;" class="text-white-50"><i class="fa-regular fa-clock me-1 text-warning"></i><?= date('g:i A', strtotime($booking['show_time'])) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-transparent border text-warning border-warning px-3 py-1.5 fw-bold" style="font-size: 11px; letter-spacing: 0.5px;"><?= htmlspecialchars($booking['seat_number']) ?></span>
                                    </td>
                                    <td class="text-end fw-bold text-white">PHP <?= number_format($booking['total_price'], 2) ?></td>
                                    <td class="text-center pe-4">
                                        <?php 
                                            $badge_styles = 'border: 1px solid var(--cinema-gold); color: var(--cinema-gold); background: rgba(255, 184, 0, 0.05);';
                                            if ($booking['status'] === 'Approved') {
                                                $badge_styles = 'border: 1px solid #198754; color: #198754; background: rgba(25, 135, 84, 0.05);';
                                            } elseif ($booking['status'] === 'Cancelled') {
                                                $badge_styles = 'border: 1px solid #dc3545; color: #dc3545; background: rgba(220, 53, 69, 0.05);';
                                            }
                                        ?>
                                        <span class="badge rounded-pill fw-bold text-uppercase" style="font-size: 10px; padding: 6px 12px; letter-spacing: 0.5px; <?= $badge_styles ?>">
                                            <?= htmlspecialchars($booking['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        <?php endif; ?>
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
                    <a href="index.php" class="footer-link small">Now Showing</a>
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
            <p class="small mb-0">&copy; 2026 Frasshawty Cinema Reserve. All rights reserved runtime engine.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>