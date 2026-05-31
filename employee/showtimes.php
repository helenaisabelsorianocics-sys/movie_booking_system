<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employee') { 
    header("Location: ../login.php"); 
    exit(); 
}
require_once '../config/db_connection.php';

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
<?php include '../includes/sidebar-employee.php'; ?>

<div class="container-fluid text-start">
    <div class="mb-4">
        <h2 class="fw-bold text-dark">Active Screening Timetables</h2>
        <p class="text-muted">Operational breakdown of movie showtimes across cinema screening rooms.</p>
    </div>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger small mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i>Schedule Sync Error: <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border bg-white rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle m-0 small bg-white">
                <thead class="table-dark text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">Movie Title</th>
                        <th class="py-3">Theater Location</th>
                        <th class="py-3">Screen Date</th>
                        <th class="py-3">Start Time</th>
                        <th class="py-3 text-center">Remaining Availability</th>
                    </tr>
                </thead>
                <tbody class="text-secondary">
                    <?php if (empty($showtimes)): ?>
                        <tr>
                            <td colspan="5" class="text-center p-5 text-muted">
                                <i class="fa-regular fa-calendar-times display-6 d-block mb-2 text-black-50"></i>
                                No active screening dates scheduled in the database.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($showtimes as $s): ?>
                            <tr>
                                <td class="fw-bold text-dark ps-4">
                                    <?= htmlspecialchars($s['title']) ?>
                                    <span class="text-muted d-block small normal fw-normal mt-0.5" style="font-size: 11px;"><?= intval($s['duration']) ?> mins runtime</span>
                                </td>
                                <td class="text-dark fw-semibold"><i class="fa-solid fa-display me-1 text-muted"></i> <?= htmlspecialchars($s['theater_name']) ?></td>
                                <td class="fw-semibold text-dark"><?= date('M d, Y', strtotime($s['show_date'])) ?></td>
                                <td class="text-dark"><i class="fa-regular fa-clock me-1 text-muted"></i> <?= date('g:i A', strtotime($s['show_time'])) ?></td>
                                <td class="text-center">
                                    <?php 

                                        $seats = intval($s['available_seats']);
                                        if ($seats === 0) {
                                            $badge_class = 'bg-danger text-white';
                                            $label_text = 'Sold Out';
                                        } elseif ($seats <= 10) {
                                            $badge_class = 'bg-warning text-dark';
                                            $label_text = $seats . ' Seats Left';
                                        } else {
                                            $badge_class = 'bg-success text-white';
                                            $label_text = $seats . ' Free Seats';
                                        }
                                    ?>
                                    <span class="badge <?= $badge_class ?> px-3 py-2 rounded-pill fw-bold text-uppercase" style="font-size: 10px;">
                                        <?= $label_text ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>