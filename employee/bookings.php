<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employee') { 
    header("Location: ../login.php"); 
    exit(); 
}

require_once '../config/db_connection.php';

$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'All';

try {

    $query_str = "SELECT b.booking_id, b.seat_number, b.total_price, b.booking_date, b.status,
                         u.username as customer_name, m.title, t.theater_name, s.show_date, s.show_time
                  FROM hius_booking_table b
                  JOIN hius_user_table u ON b.user_id = u.user_id
                  JOIN hius_showtime_table s ON b.showtime_id = s.showtime_id
                  JOIN hius_movie_table m ON s.movie_id = m.movie_id
                  JOIN hius_theater_table t ON s.theater_id = t.theater_id";
    

    if ($status_filter !== 'All') {

        $query_str .= " WHERE b.status = ? ORDER BY b.booking_id DESC";
        $stmt = $pdo->prepare($query_str);
        $stmt->execute([$status_filter]);
    } else {
        $query_str .= " ORDER BY b.booking_id DESC";
        $stmt = $pdo->query($query_str);
    }
    
    $all_bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_bookings = [];
    $db_error = $e->getMessage();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-employee.php'; ?>

<div class="container-fluid text-start">
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="fw-bold text-dark">Master Booking Manifest</h2>
            <p class="text-muted mb-0">Browse, filter, and track historical cinema reservation files.</p>
        </div>
        
        <div class="btn-group shadow-sm bg-white p-1 rounded border" role="group">
            <a href="bookings.php?status=All" class="btn btn-sm px-3 fw-semibold <?= $status_filter === 'All' ? 'btn-dark' : 'btn-light border-0 text-secondary' ?>">All Records</a>
            <a href="bookings.php?status=Approved" class="btn btn-sm px-3 fw-semibold <?= $status_filter === 'Approved' ? 'btn-success text-white' : 'btn-light border-0 text-secondary' ?>">Approved</a>
            <a href="bookings.php?status=Pending" class="btn btn-sm px-3 fw-semibold <?= $status_filter === 'Pending' ? 'btn-warning text-dark' : 'btn-light border-0 text-secondary' ?>">Pending</a>
            <a href="bookings.php?status=Cancelled" class="btn btn-sm px-3 fw-semibold <?= $status_filter === 'Cancelled' ? 'btn-danger text-white' : 'btn-light border-0 text-secondary' ?>">Cancelled</a>
        </div>
    </div>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger small"><i class="fa-solid fa-circle-exclamation me-2"></i>Manifest Sync Error: <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border bg-white rounded-3 overflow-hidden">
        <?php if (empty($all_bookings)): ?>
            <div class="p-5 text-center text-muted">
                <i class="fa-solid fa-folder-open display-4 d-block mb-2 text-black-50"></i>
                No reservation transactions match your current status filter state.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle m-0 small bg-white">
                    <thead class="table-dark text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4 py-3">Receipt ID</th>
                            <th class="py-3">Customer Account</th>
                            <th class="py-3">Movie & Schedule</th>
                            <th class="py-3">Theater Hall</th>
                            <th class="py-3 text-center">Seat Coordinate</th>
                            <th class="py-3 text-end">Amount Paid</th>
                            <th class="py-3 text-center pe-4">Current Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-secondary">
                        <?php foreach ($all_bookings as $b): ?>
                            <tr>
                                <td class="fw-bold text-dark ps-4">#CR-<?= str_pad($b['booking_id'], 5, '0', STR_PAD_LEFT) ?></td>
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
                                <td class="text-center pe-4">
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