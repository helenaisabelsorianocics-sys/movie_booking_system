<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: ../index.php"); 
    exit(); 
}
require_once '../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = trim($_POST['name']); 
    $l = trim($_POST['location']); 
    $c = intval($_POST['capacity']);
    
    if(!empty($n) && !empty($l) && $c > 0) {

        $stmt = $pdo->prepare("INSERT INTO hius_theater_table (theater_name, location, capacity) VALUES (?,?,?)");
        $stmt->execute([$n, $l, $c]);
        header("Location: theaters.php?success=1"); 
        exit();
    }
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-admin.php'; ?>

<div class="container-fluid text-start">
    <div class="mb-4">
        <h3>Initialize New Theater Structure</h3>
        <p class="text-muted">Register a brand new physical screening hall into the theater facility inventory database.</p>
    </div>

    <form action="add_theater.php" method="POST" class="card p-4 row g-3 bg-white shadow-sm border rounded-3 mx-0">
        <div class="col-md-6">
            <label class="form-label small fw-bold text-secondary">Screen Hall Name</label>
            <input type="text" name="name" class="form-control" required autocomplete="off">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-bold text-secondary">Seating Max Capacity Limit</label>
            <input type="number" name="capacity" class="form-control" required min="1">
        </div>
        <div class="col-12">
            <label class="form-label small fw-bold text-secondary">Location Context Breakdown</label>
            <input type="text" name="location" class="form-control" required autocomplete="off" placeholder="e.g., 3rd Floor, South Wing">
        </div>
        <div class="col-12 pt-2">
            <button class="btn btn-primary fw-bold px-4">Build Theater</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>