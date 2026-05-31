<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: ../index.php"); 
    exit(); 
}
require_once '../config/db_connection.php';

if (isset($_GET['delete_id'])) {
    $target_showtime_id = intval($_GET['delete_id']);
    try {
        $pdo->beginTransaction();


        $delete_stmt = $pdo->prepare("DELETE FROM hius_showtime_table WHERE showtime_id = ?");
        $delete_stmt->execute([$target_showtime_id]);

        $log_stmt = $pdo->prepare("INSERT INTO hius_log_table (user_id, action, timestamp) VALUES (?, ?, NOW())");
        $log_action = "Admin dropped Showtime schedule entry slot ID: #{$target_showtime_id}";
        $log_stmt->execute([$_SESSION['user_id'], $log_action]);

        $pdo->commit();
        header("Location: showtimes.php?deleted=1");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $db_error = "Deletion Blocked: Check if booking tickets exist for this slot. " . $e->getMessage();
    }
}

try {

    $sql = "SELECT s.*, m.title, m.duration, t.theater_name 
            FROM hius_showtime_table s
            JOIN hius_movie_table m ON s.movie_id = m.movie_id
            JOIN hius_theater_table t ON s.theater_id = t.theater_id
            ORDER BY s.show_date ASC, s.show_time ASC";
            
    $showtimes = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    $showtimes = [];
    $db_error = $e->getMessage();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-admin.php'; ?>

<div class="container-fluid text-start">
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>Screening Showtimes</h2>
            <p class="text-muted mb-0">Monitor, track, and manage movie timetables configured across standard screening halls.</p>
        </div>
        <a href="add_showtime.php" class="btn btn-primary fw-bold btn-sm px-3 shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Add New Showtime
        </a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success small py-2"><i class="fa-solid fa-circle-check me-2"></i>Showtime entry removed from active schedule.</div>
    <?php endif; ?>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger small py-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>System Error Trace: <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border bg-white rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle m-0 small">
                <thead class="table-dark text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Movie Title</th>
                        <th class="py-3">Theater Room</th>
                        <th class="py-3">Screening Date</th>
                        <th class="py-3">Start Time</th>
                        <th class="py-3 text-center">Available Seats</th>
                        <th class="py-3 text-center pe-4" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-secondary">
                    <?php if (empty($showtimes)): ?>
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted">
                                <i class="fa-regular fa-calendar-times display-6 d-block mb-2 text-black-50"></i>
                                No upcoming screening showtimes found in the database.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($showtimes as $s): ?>
                            <tr>
                                <td class="fw-bold text-dark ps-4">
                                    <?= htmlspecialchars($s['title']) ?>
                                    <span class="text-muted d-block small fw-normal" style="font-size: 11px;">
                                        <i class="fa-regular fa-clock me-1"></i> <?= $s['duration'] ?> mins
                                    </span>
                                </td>
                                <td class="text-dark fw-semibold">
                                    <i class="fa-solid fa-display me-1 text-muted"></i> <?= htmlspecialchars($s['theater_name']) ?>
                                </td>
                                <td class="fw-semibold text-dark"><?= date('M d, Y', strtotime($s['show_date'])) ?></td>
                                <td><span class="badge bg-light text-dark border border-secondary px-2 py-1 fw-bold"><?= date('g:i A', strtotime($s['show_time'])) ?></span></td>
                                <td class="text-center">
                                    <span class="badge <?= $s['available_seats'] > 0 ? 'bg-success text-white' : 'bg-danger text-white' ?> px-3 py-1.5 rounded-pill fw-bold" style="font-size: 11px;">
                                        <?= $s['available_seats'] ?> Vacant Seats
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <a href="showtimes.php?delete_id=<?= $s['showtime_id'] ?>" 
                                       class="btn btn-outline-danger btn-sm py-0.5 px-2 small" 
                                       onclick="return confirm('Are you sure you want to completely cancel and delete this showtime tracking row entry?');"
                                       title="Remove Showtime">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>