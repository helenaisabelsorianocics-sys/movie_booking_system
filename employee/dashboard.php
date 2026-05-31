<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employee') { 
    header("Location: ../login.php"); 
    exit(); 
}

require_once '../config/db_connection.php';

$username = $_SESSION['username'];

try {
    $sql = "SELECT b.booking_id, b.seat_number, b.total_price, b.booking_date, b.status,
                   u.username as customer_name, m.title, t.theater_name, s.show_date, s.show_time
            FROM hius_booking_table b
            JOIN hius_user_table u ON b.user_id = u.user_id
            JOIN hius_showtime_table s ON b.showtime_id = s.showtime_id
            JOIN hius_movie_table m ON s.movie_id = m.movie_id
            JOIN hius_theater_table t ON s.theater_id = t.theater_id
            ORDER BY b.booking_id DESC LIMIT 15";
    $stmt = $pdo->query($sql);
    $active_bookings = $stmt->fetchAll();
    

    $todays_tickets = $pdo->query("SELECT COUNT(*) FROM hius_booking_table WHERE DATE(booking_date) = CURDATE()")->fetchColumn();
} catch (PDOException $e) {
    $active_bookings = [];
    $todays_tickets = 0;
    $db_error = $e->getMessage();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-employee.php'; ?>

<div class="container-fluid">
    <div class="mb-4 text-start">
        <h2 class="fw-bold text-dark">Employee Operations Panel</h2>
        <p class="text-muted">Logged in as staff operator: <strong><?= htmlspecialchars($username) ?></strong></p>
    </div>
    
    <div class="row g-4 mb-4 text-start">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm border-start border-info border-4 bg-white rounded-3">
                <h6 class="text-muted text-uppercase small fw-bold" style="letter-spacing: 0.5px;">Processed Today (System-wide)</h6>
                <h3 class="fw-bold mb-0 text-dark"><?= intval($todays_tickets) ?> Tickets</h3>
            </div>
        </div>
    </div>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger small text-start"><i class="fa-solid fa-triangle-exclamation me-2"></i>Log Synchronization Sync Error: <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>
    
    <div class="p-4 bg-white rounded-3 shadow-sm border text-start">
        <div class="mb-3">
            <h5 class="fw-bold text-dark m-0"><i class="fa-solid fa-clipboard-list me-2 text-secondary"></i>Customer Ticket Manifest</h5>
            <p class="text-muted small mb-0">Use this live ledger to verify customer reservation emails at the ticketing counter.</p>
        </div>

        <?php if (empty($active_bookings)): ?>
            <div class="p-5 text-center text-muted border rounded bg-light">
                <i class="fa-solid fa-ticket-simple display-5 d-block mb-2 text-black-50"></i>
                No current customer ticket allocations logged inside the booking matrix yet.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle m-0 small bg-white">
                    <thead class="table-dark text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3 py-3">Receipt ID</th>
                            <th class="py-3">Customer Account</th>
                            <th class="py-3">Movie Choice</th>
                            <th class="py-3">Theater Hall</th>
                            <th class="py-3 text-center">Seat Row/Num</th>
                            <th class="py-3 text-end">Amount</th>
                            <th class="py-3 text-center pe-3">Ticket Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-secondary">
                        <?php foreach ($active_bookings as $b): ?>
                            <tr>
                                <td class="fw-bold text-dark ps-3">#CR-<?= str_pad($b['booking_id'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($b['customer_name']) ?></td>
                                <td>
                                    <div class="text-dark fw-bold"><?= htmlspecialchars($b['title']) ?></div>
                                    <div style="font-size: 11px;" class="text-muted"><?= date('M d, Y', strtotime($b['show_date'])) ?> • <?= date('g:i A', strtotime($b['show_time'])) ?></div>
                                </td>
                                <td class="text-dark"><i class="fa-solid fa-display me-1 text-muted"></i> <?= htmlspecialchars($b['theater_name']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border border-secondary px-2 py-1 fw-bold"><?= htmlspecialchars($b['seat_number']) ?></span>
                                </td>
                                <td class="text-end fw-bold text-dark text-nowrap">PHP <?= number_format($b['total_price'], 2) ?></td>
                                <td class="text-center pe-3">
                                    <?php 
                                        $badge_class = 'bg-warning text-dark';
                                        if ($b['status'] === 'Approved') $badge_class = 'bg-success text-white';
                                        if ($b['status'] === 'Cancelled') $badge_class = 'bg-danger text-white';
                                    ?>
                                    <span class="badge <?= $badge_class ?> rounded-pill fw-bold text-uppercase" style="font-size: 10px; padding: 5px 10px;">
                                        <?= htmlspecialchars($b['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>