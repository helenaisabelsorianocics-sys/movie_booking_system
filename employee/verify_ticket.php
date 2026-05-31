<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employee') { 
    header("Location: ../login.php"); 
    exit(); 
}

require_once '../config/db_connection.php';

$search_query = '';
$ticket = null;
$message = '';
$message_type = '';

if (isset($_GET['search'])) {
    $search_query = preg_replace('/[^0-9]/', '', $_GET['search']);
    
    if (!empty($search_query)) {
        try {

            $sql = "SELECT b.booking_id, b.seat_number, b.total_price, b.booking_date, b.status,
                           u.username as customer_name, u.email as customer_email,
                           m.title, t.theater_name, s.show_date, s.show_time
                    FROM hius_booking_table b
                    JOIN hius_user_table u ON b.user_id = u.user_id
                    JOIN hius_showtime_table s ON b.showtime_id = s.showtime_id
                    JOIN hius_movie_table m ON s.movie_id = m.movie_id
                    JOIN hius_theater_table t ON s.theater_id = t.theater_id
                    WHERE b.booking_id = ? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$search_query]);
            $ticket = $stmt->fetch();
            
            if (!$ticket) {
                $message = "No record found for ticket ID #CR-" . str_pad($search_query, 5, '0', STR_PAD_LEFT);
                $message_type = "danger";
            }
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_booking_id'], $_POST['update_status'])) {
    $target_id = intval($_POST['action_booking_id']);
    $new_status = trim($_POST['update_status']); 
    try {
        $update_sql = "UPDATE hius_booking_table SET status = ? WHERE booking_id = ?";
        $update_stmt = $pdo->prepare($update_sql);
        $update_stmt->execute([$new_status, $target_id]);

        $message = "Ticket #CR-" . str_pad($target_id, 5, '0', STR_PAD_LEFT) . " status successfully updated to '{$new_status}'!";
        $message_type = "success";

        $search_query = $target_id;

        $reload_sql = "SELECT b.*, u.username as customer_name, u.email as customer_email, m.title, t.theater_name, s.show_date, s.show_time 
                       FROM hius_booking_table b 
                       JOIN hius_user_table u ON b.user_id = u.user_id 
                       JOIN hius_showtime_table s ON b.showtime_id = s.showtime_id 
                       JOIN hius_movie_table m ON s.movie_id = m.movie_id 
                       JOIN hius_theater_table t ON s.theater_id = t.theater_id 
                       WHERE b.booking_id = ?";
        $stmt = $pdo->prepare($reload_sql);
        $stmt->execute([$target_id]);
        $ticket = $stmt->fetch();

    } catch (PDOException $e) {
        $message = "Failed to update record state: " . $e->getMessage();
        $message_type = "danger";
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-employee.php'; ?>

<div class="container-fluid text-start">
    <div class="mb-4">
        <h2 class="fw-bold text-dark">Counter Ticket Verification</h2>
        <p class="text-muted">Search, validate, and manage live customer ticket entries on the cinema floor.</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?> shadow-sm alert-dismissible fade show" role="alert">
            <i class="fa-solid <?= $message_type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-4 shadow-sm border bg-white rounded-3">
                <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-magnifying-glass me-2 text-secondary"></i>Scan / Input Ticket</h5>
                <form action="verify_ticket.php" method="GET">
                    <div class="mb-3">
                        <label class="form-label small text-secondary fw-semibold">Enter Booking Reference ID</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">#CR-</span>
                            <input type="text" name="search" class="form-control" placeholder="00001" value="<?= !empty($search_query) ? str_pad($search_query, 5, '0', STR_PAD_LEFT) : '' ?>" required autocomplete="off">
                        </div>
                        <div class="form-text text-muted small mt-1" style="font-size: 11px;">Type the numerical digits found on the customer's payment email receipt.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fa-solid fa-search me-1"></i> Verify Record</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <?php if ($ticket): ?>
                <div class="card shadow-sm border bg-white rounded-3 overflow-hidden">
                    <div class="card-header bg-dark py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="m-0 text-white fw-bold"><i class="fa-solid fa-ticket text-primary me-2"></i>Manifest Record Found</h5>
                        <span class="badge text-uppercase fw-bold px-3 py-2 <?= $ticket['status'] === 'Approved' ? 'bg-success text-white' : ($ticket['status'] === 'Cancelled' ? 'bg-danger text-white' : 'bg-warning text-dark') ?>" style="font-size: 10px;">
                            <?= htmlspecialchars($ticket['status']) ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <span class="text-muted d-block small text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Customer Identity</span>
                                <strong class="text-dark fs-5"><?= htmlspecialchars($ticket['customer_name']) ?></strong>
                                <span class="text-muted d-block small"><?= htmlspecialchars($ticket['customer_email']) ?></span>
                            </div>
                            <div class="col-6 text-end">
                                <span class="text-muted d-block small text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Booking Timestamp</span>
                                <span class="text-dark d-block fw-semibold mt-1"><?= date('M d, Y • g:i A', strtotime($ticket['booking_date'])) ?></span>
                            </div>
                        </div>

                        <hr class="my-3 text-black-50">

                        <div class="row g-3 py-2">
                            <div class="col-sm-6">
                                <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 10px;">Movie Title</div>
                                <div class="fw-bold text-dark fs-6"><i class="fa-solid fa-film text-muted me-2"></i><?= htmlspecialchars($ticket['title']) ?></div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 10px;">Location Theater</div>
                                <div class="fw-semibold text-dark"><i class="fa-solid fa-display text-muted me-2"></i><?= htmlspecialchars($ticket['theater_name']) ?></div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 10px;">Screening Schedule Slot</div>
                                <div class="text-dark fw-semibold"><i class="fa-solid fa-clock text-muted me-2"></i><?= date('M d, Y', strtotime($ticket['show_date'])) ?> at <?= date('g:i A', strtotime($ticket['show_time'])) ?></div>
                            </div>
                            <div class="col-sm-6 mt-3">
                                <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 10px;">Assigned Seat Selection</div>
                                <div><span class="badge bg-light text-primary border border-primary px-3 py-1 fw-bold fs-6 mt-1"><?= htmlspecialchars($ticket['seat_number']) ?></span></div>
                            </div>
                        </div>

                        <hr class="my-4 text-black-50">
                        
                        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border flex-wrap gap-3">
                            <div>
                                <span class="text-muted d-block small fw-bold" style="font-size: 11px;">Total Price Paid:</span>
                                <strong class="text-success fs-4">PHP <?= number_format($ticket['total_price'], 2) ?></strong>
                            </div>
                            
                            <form action="verify_ticket.php?search=<?= intval($ticket['booking_id']) ?>" method="POST" class="d-flex gap-2">
                                <input type="hidden" name="action_booking_id" value="<?= intval($ticket['booking_id']) ?>">
                                
                                <?php if ($ticket['status'] !== 'Approved'): ?>
                                    <button type="submit" name="update_status" value="Approved" class="btn btn-success fw-bold btn-sm px-3 text-uppercase" style="font-size: 11px;"><i class="fa-solid fa-check me-1"></i> Approve / Claim</button>
                                <?php endif; ?>
                                
                                <?php if ($ticket['status'] !== 'Cancelled'): ?>
                                    <button type="submit" name="update_status" value="Cancelled" class="btn btn-outline-danger fw-bold btn-sm px-3 text-uppercase" style="font-size: 11px;"><i class="fa-solid fa-ban me-1"></i> Void Ticket</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card p-5 text-center text-muted border border-dashed rounded-3 bg-white" style="border-style: dashed !important; border-width: 2px;">
                    <i class="fa-solid fa-id-card-clip display-4 mb-3 text-black-50"></i>
                    <h5 class="fw-bold text-dark">No Ticket Selected</h5>
                    <p class="small mb-0 max-width-400 mx-auto text-secondary">Input a booking reference ID code on the left controller pane to fetch real-time gate validation states.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>