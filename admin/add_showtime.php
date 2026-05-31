<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db_connection.php';

$alert_script = '';

try {
    $movies = $pdo->query("SELECT movie_id, title FROM hius_movie_table ORDER BY title ASC")->fetchAll();

    $theaters = $pdo->query("SELECT theater_id, theater_name FROM hius_theater_table ORDER BY theater_name ASC")->fetchAll();
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = intval($_POST['movie_id']);
    $theater_id = intval($_POST['theater_id']);
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];

    if (!empty($movie_id) && !empty($theater_id) && !empty($show_date) && !empty($show_time)) {
        try {

            $t_stmt = $pdo->prepare("SELECT capacity FROM hius_theater_table WHERE theater_id = ?");
            $t_stmt->execute([$theater_id]);
            $fetched_capacity = $t_stmt->fetchColumn();
            

            $default_capacity = ($fetched_capacity && $fetched_capacity > 0) ? intval($fetched_capacity) : 40;


            $sql = "INSERT INTO hius_showtime_table (movie_id, theater_id, show_date, show_time, available_seats) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$movie_id, $theater_id, $show_date, $show_time, $default_capacity]);

            $alert_script = "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Showtime Scheduled!',
                    text: 'The screening slot has been successfully added to the timetable.',
                    confirmButtonColor: '#0ea5e9'
                }).then(() => {
                    window.location.href = 'dashboard.php';
                });
            </script>";
        } catch (PDOException $e) {
            $alert_script = "<script>Swal.fire('Scheduling Error', '" . addslashes($e->getMessage()) . "', 'error');</script>";
        }
    } else {
        $alert_script = "<script>Swal.fire('Missing Information', 'Please populate all routing timetable fields.', 'warning');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Showtime - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f4f6f9; }
        .form-card { border-radius: 12px; border: none; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-info" href="dashboard.php"><i class="fa-solid fa-gauge me-2"></i>Admin Control Panel</a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-light small">Logged in as: <strong class="text-info"><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            <a href="../logout.php" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-power-off"></i></a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-dark m-0">Schedule Showtime</h2>
                    <p class="text-muted mb-0">Map standard movie releases into targeted cinema rooms.</p>
                </div>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
            </div>

            <div class="card p-4 shadow-sm form-card bg-white">
                <form action="add_showtime.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Select Movie Catalog Title</label>
                        <select name="movie_id" class="form-select" required>
                            <option value="" disabled selected>-- Choose a registered movie title --</option>
                            <?php foreach ($movies as $movie): ?>
                                <option value="<?= $movie['movie_id'] ?>"><?= htmlspecialchars($movie['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Cinema Screening Room Location</label>
                        <select name="theater_id" class="form-select" required>
                            <option value="" disabled selected>-- Choose a target theater hall --</option>
                            <?php foreach ($theaters as $theater): ?>
                                <option value="<?= $theater['theater_id'] ?>"><?= htmlspecialchars($theater['theater_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label small fw-bold text-secondary">Screening Date</label>
                            <input type="date" name="show_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Screening Start Time</label>
                            <input type="time" name="show_time" class="form-control" required>
                        </div>
                    </div>

                    <hr class="text-muted mb-4">

                    <button type="submit" class="btn btn-info w-100 fw-bold text-white py-2 shadow-sm">
                        <i class="fa-solid fa-calendar-plus me-1"></i> Activate Showtime Slot
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?= $alert_script ?>

</body>
</html>