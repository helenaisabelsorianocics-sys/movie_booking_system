<?php
session_start();
require_once '../config/db_connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer' || !isset($_GET['ref'])) {
    header("Location: index.php");
    exit();
}

$booking_id = intval($_GET['ref']);

try {

    $sql = "SELECT b.booking_id, b.booking_date, b.seat_number, b.total_price, b.status,
                   s.show_date, s.show_time, 
                   m.title AS movie_title, m.duration,
                   t.theater_name
            FROM hius_booking_table b
            JOIN hius_showtime_table s ON b.showtime_id = s.showtime_id
            JOIN hius_movie_table m ON s.movie_id = m.movie_id
            JOIN hius_theater_table t ON s.theater_id = t.theater_id
            WHERE b.booking_id = ? AND b.user_id = ? LIMIT 1";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error retrieving your booking receipt: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - Cinema Reserve</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 0; }
        .ticket-card { width: 100%; max-width: 500px; border-radius: 16px; background: #ffffff; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: none; overflow: hidden; }
        .ticket-header { background: linear-gradient(135deg, #0284c7, #0369a1); color: #ffffff; padding: 2rem; text-align: center; position: relative; }
        .ticket-body { padding: 2rem; position: relative; }
        /* Traditional movie ticket dashed separator line */
        .ticket-divider { border-top: 2px dashed #e2e8f0; position: relative; margin: 1.5rem 0; }
        .ticket-divider::before, .ticket-divider::after { content: ''; position: absolute; width: 20px; height: 20px; background: #f4f6f9; border-radius: 50%; top: -11px; }
        .ticket-divider::before { left: -31px; }
        .ticket-divider::after { right: -31px; }
    </style>
</head>
<body>

<div class="ticket-card">
    <div class="ticket-header">
        <div class="display-4 mb-2"><i class="fa-solid fa-circle-check text-white"></i></div>
        <h4 class="fw-bold mb-1">Booking Confirmed!</h4>
        <p class="small text-white-50 mb-0">Transaction Reference: #CR-<?= htmlspecialchars($booking['booking_id']) ?></p>
    </div>

    <div class="ticket-body">
        <div class="mb-3">
            <label class="text-muted small uppercase fw-semibold tracking-wider d-block mb-1">Movie Title</label>
            <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($booking['movie_title']) ?></h5>
            <small class="text-muted"><i class="fa-regular fa-clock me-1"></i> <?= htmlspecialchars($booking['duration']) ?> mins</small>
        </div>

        <div class="row g-3 mb-1">
            <div class="col-6">
                <label class="text-muted small fw-semibold d-block mb-1">Date</label>
                <span class="fw-bold text-secondary"><i class="fa-regular fa-calendar me-1"></i> <?= date('F d, Y', strtotime($booking['show_date'])) ?></span>
            </div>
            <div class="col-6">
                <label class="text-muted small fw-semibold d-block mb-1">Time Slot</label>
                <span class="fw-bold text-secondary"><i class="fa-regular fa-clock me-1"></i> <?= date('h:i A', strtotime($booking['show_time'])) ?></span>
            </div>
        </div>

        <div class="ticket-divider"></div>

        <div class="row g-3">
            <div class="col-4">
                <label class="text-muted small d-block mb-1">Cinema Hall</label>
                <span class="fw-bold text-dark"><i class="fa-solid fa-film me-1"></i> <?= htmlspecialchars($booking['theater_name']) ?></span>
            </div>
            <div class="col-4 text-center">
                <label class="text-muted small d-block mb-1">Seat Assignment</label>
                <span class="badge bg-success fs-6 px-3 py-1.5 fw-bold text-uppercase"><?= htmlspecialchars($booking['seat_number']) ?></span>
            </div>
            <div class="col-4 text-end">
                <label class="text-muted small d-block mb-1">Total Paid</label>
                <span class="fw-bold text-dark fs-5">₱<?= number_format($booking['total_price'], 2) ?></span>
            </div>
        </div>

        <div class="mt-4 pt-2 text-center">
            <p class="text-muted small mb-3">Show this screen or reference code to our theater usher upon arrival.</p>
            <a href="index.php" class="btn btn-outline-primary fw-bold px-4 rounded-pill py-2">
                <i class="fa-solid fa-house me-2"></i>Return to Homepage
            </a>
        </div>
    </div>
</div>

</body>
</html>