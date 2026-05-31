<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employee') { 
    header("Location: ../login.php"); 
    exit(); 
}
require_once '../config/db_connection.php';

$selected_showtime = isset($_GET['showtime_id']) ? intval($_GET['showtime_id']) : 0;
$taken_seats = [];

try {
    $showtimes_sql = "SELECT s.showtime_id, s.show_date, s.show_time, m.title, t.theater_name 
                      FROM hius_showtime_table s 
                      JOIN hius_movie_table m ON s.movie_id = m.movie_id 
                      JOIN hius_theater_table t ON s.theater_id = t.theater_id 
                      WHERE s.show_date >= CURDATE() 
                      ORDER BY s.show_date ASC";
    $showtimes = $pdo->query($showtimes_sql)->fetchAll();

    if ($selected_showtime > 0) {

        $stmt = $pdo->prepare("SELECT seat_number FROM hius_booking_table WHERE showtime_id = ? AND status != 'Cancelled'");
        $stmt->execute([$selected_showtime]);
        $taken_seats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (PDOException $e) {
    $showtimes = [];
    $db_error = $e->getMessage();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-employee.php'; ?>

<div class="container-fluid text-start">
    <div class="mb-4">
        <h2 class="fw-bold text-dark">Seating Allocation Map</h2>
        <p class="text-muted">Select an active screening schedule below to view filled and open seat coordinate layouts.</p>
    </div>

    <div class="card p-4 shadow-sm border bg-white rounded-3 mb-4">
        <form method="GET" action="seats.php">
            <label class="form-label small fw-bold text-secondary mb-2">Select Screening Showtime</label>
            <div class="d-flex gap-2">
                <select name="showtime_id" class="form-select small" required>
                    <option value="" disabled <?= $selected_showtime === 0 ? 'selected' : '' ?>>-- Select an upcoming showtime --</option>
                    <?php foreach ($showtimes as $s): ?>
                        <option value="<?= $s['showtime_id'] ?>" <?= $selected_showtime === $s['showtime_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['title']) ?> [<?= htmlspecialchars($s['theater_name']) ?>] - <?= date('M d', strtotime($s['show_date'])) ?> @ <?= date('g:i A', strtotime($s['show_time'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-dark px-4 fw-bold text-nowrap">Load Map</button>
            </div>
        </form>
    </div>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger small mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i>Map Sync Error: <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <?php if ($selected_showtime > 0): ?>
        <div class="card p-4 shadow-sm border bg-white rounded-3 text-center">
            <div class="w-100 bg-secondary text-white py-2 rounded-2 small mb-5 fw-bold shadow-sm" style="letter-spacing: 2px;">STAGE SCREEN DIRECTION</div>
            
            <div class="d-flex flex-column gap-2 align-items-center overflow-auto py-2">
                <?php foreach (range('A', 'E') as $row): ?>
                    <div class="d-flex gap-2 align-items-center text-nowrap">
                        <strong class="me-3 text-secondary text-center" style="width: 20px;"><?= $row ?></strong>
                        <?php foreach (range(1, 8) as $num): 
                            $seat_id = $row . $num;
                            $is_taken = in_array($seat_id, $taken_seats);
                            $btn_class = $is_taken ? 'btn-danger' : 'btn-outline-success bg-success bg-opacity-10';
                        ?>
                            <button class="btn btn-sm <?= $btn_class ?> fw-bold" style="width: 44px; height: 42px; font-size: 11px;" disabled>
                                <?= $seat_id ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex justify-content-center gap-4 mt-5 small border-top pt-3 flex-wrap">
                <div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 me-1" style="width: 30px; height: 18px; display: inline-block; vertical-align: middle;">&nbsp;</span> 
                    <span class="align-middle fw-medium text-secondary">Available Seat</span>
                </div>
                <div>
                    <span class="badge bg-danger px-3 py-2 me-1" style="width: 30px; height: 18px; display: inline-block; vertical-align: middle;">&nbsp;</span> 
                    <span class="align-middle fw-medium text-secondary">Reserved / Sold Out</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>