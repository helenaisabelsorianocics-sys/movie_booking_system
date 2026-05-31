<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: ../index.php"); 
    exit(); 
}

require_once '../config/db_connection.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $target_booking_id = intval($_POST['booking_id']);
    $new_status = trim($_POST['status']);
    
    if (in_array($new_status, ['Pending', 'Approved', 'Cancelled'])) {
        try {
            $pdo->beginTransaction();
            

            $update_stmt = $pdo->prepare("UPDATE hius_booking_table SET status = ? WHERE booking_id = ?");
            $update_stmt->execute([$new_status, $target_booking_id]);
            

            $log_sql = "INSERT INTO hius_log_table (user_id, action, timestamp) VALUES (?, ?, NOW())";
            $log_stmt = $pdo->prepare($log_sql);
            $admin_action = "Admin (ID: {$_SESSION['user_id']}) changed Booking status #CR-{$target_booking_id} to '{$new_status}'.";
            $log_stmt->execute([$_SESSION['user_id'], $admin_action]);
            
            $pdo->commit();
            header("Location: bookings.php?status=" . (isset($_GET['status']) ? $_GET['status'] : 'All'));
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $db_error = "Action failure: " . $e->getMessage();
        }
    }
}


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
<?php include '../includes/sidebar-admin.php'; ?>
<div class="container-fluid text-start text-dark">
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="fw-bold text-dark text-uppercase tracking-wider m-0" style="letter-spacing: 1px;">Master Booking Manifest</h2>
            <p class="text-muted mb-0">Browse, filter, and audit historical cinema reservation records system-wide.</p>
        </div>
        
        <div class="btn-group shadow-sm p-1 rounded" role="group" style="background-color: #1a1a1a; border: 1px solid #2d2d2d;">
            <a href="bookings.php?status=All" class="btn btn-sm px-3 fw-bold <?= $status_filter === 'All' ? 'btn-danger text-white' : 'text-white-50' ?>">All Records</a>
            <a href="bookings.php?status=Approved" class="btn btn-sm px-3 fw-bold <?= $status_filter === 'Approved' ? 'btn-success text-white' : 'text-white-50' ?>">Approved</a>
            <a href="bookings.php?status=Pending" class="btn btn-sm px-3 fw-bold <?= $status_filter === 'Pending' ? 'btn-warning text-dark' : 'text-white-50' ?>">Pending</a>
            <a href="bookings.php?status=Cancelled" class="btn btn-sm px-3 fw-bold <?= $status_filter === 'Cancelled' ? 'btn-danger text-white' : 'text-white-50' ?>">Cancelled</a>
        </div>
    </div>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger bg-transparent border-danger text-danger small" style="border-radius: 6px;"><i class="fa-solid fa-triangle-exclamation me-2"></i>Manifest Sync Error: <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <div class="shadow rounded-3 overflow-hidden" style="background-color: #121212; border: 1px solid #2d2d2d;">
        <?php if (empty($all_bookings)): ?>
            <div class="p-5 text-center text-white-50">
                <i class="fa-solid fa-folder-open display-4 d-block mb-2 text-secondary"></i>
                No reservation transactions match your current status filter state.
            </div>
        <?php else: ?>
            
            <div class="row mx-0 py-3 text-uppercase fw-bold text-white-50 d-none d-md-flex align-items-center" style="background-color: #1a1a1a; border-bottom: 2px solid #2d2d2d; font-size: 11px; letter-spacing: 0.5px;">
                <div class="col-md-2 ps-4">Receipt ID</div>
                <div class="col-md-2">Customer Account</div>
                <div class="col-md-3">Movie & Schedule</div>
                <div class="col-md-2">Theater Hall</div>
                <div class="col-md-1 text-center">Seat</div>
                <div class="col-md-1 text-end">Amount Paid</div>
                <div class="col-md-1 text-center pe-4">Status</div>
            </div>

            <?php foreach ($all_bookings as $b): ?>
                <div class="row mx-0 py-3 align-items-center border-bottom-premium text-white position-relative" style="border-bottom: 1px solid #2d2d2d;">
                    
                    <div class="col-6 col-md-2 ps-md-4 mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase block-label">Receipt:</span>
                        <div class="fw-bold" style="color: var(--cinema-gold, #ffb800);">#CR-<?= str_pad($b['booking_id'], 5, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    
                    <div class="col-6 col-md-2 mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase block-label">User:</span>
                        <div class="fw-semibold text-white"><?= htmlspecialchars($b['customer_name']) ?></div>
                    </div>
                    
                    <div class="col-12 col-md-3 mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase block-label">Feature:</span>
                        <div class="text-white fw-bold"><?= htmlspecialchars($b['title']) ?></div>
                        <div style="font-size: 11px; color: #b3b3b3;"><?= date('M d, Y', strtotime($b['show_date'])) ?> • <?= date('g:i A', strtotime($b['show_time'])) ?></div>
                    </div>
                    
                    <div class="col-6 col-md-2 mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase block-label">Location:</span>
                        <div class="text-light"><i class="fa-solid fa-building me-1 text-white-50"></i> <?= htmlspecialchars($b['theater_name']) ?></div>
                    </div>
                    
                    <div class="col-6 col-md-1 text-md-center mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase block-label me-1">Seat:</span>
                        <span class="badge border border-secondary px-2 py-1 fw-bold text-white" style="background-color: #1a1a1a;"><?= htmlspecialchars($b['seat_number']) ?></span>
                    </div>
                    
                    <div class="col-6 col-md-1 text-md-end mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase block-label">Total:</span>
                        <div class="fw-bold" style="color: var(--cinema-gold, #ffb800);">PHP <?= number_format($b['total_price'], 2) ?></div>
                    </div>
                    
                    <div class="col-6 col-md-1 text-md-center pe-md-4 mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase block-label mb-1">Modify:</span>
                        <form action="bookings.php?status=<?= $status_filter ?>" method="POST" class="d-flex align-items-center justify-content-md-center gap-1">
                            <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                            <input type="hidden" name="update_status" value="1">
                            
                            <select name="status" class="form-select form-select-sm py-0 small fw-semibold text-center rounded border-secondary text-light" style="font-size: 11px; max-width: 110px; background-color: #1a1a1a;" onchange="this.form.submit()">
                                <option value="Pending" <?= $b['status'] === 'Pending' ? 'selected class="text-warning fw-bold"' : '' ?>>⚠️ Pending</option>
                                <option value="Approved" <?= $b['status'] === 'Approved' ? 'selected class="text-success fw-bold"' : '' ?>>✅ Approved</option>
                                <option value="Cancelled" <?= $b['status'] === 'Cancelled' ? 'selected class="text-danger fw-bold"' : '' ?>>❌ Cancelled</option>
                            </select>
                        </form>
                    </div>

                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>