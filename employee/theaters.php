<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employee') { 
    header("Location: ../login.php"); 
    exit(); 
}
require_once '../config/db_connection.php';

try {

    $theaters = $pdo->query("SELECT * FROM hius_theater_table ORDER BY theater_name ASC")->fetchAll();
} catch (PDOException $e) {
    $theaters = [];
    $db_error = $e->getMessage();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-employee.php'; ?>

<div class="container-fluid text-start">
    <div class="mb-4">
        <h2 class="fw-bold text-dark">Physical Theater Infrastructure</h2>
        <p class="text-muted">Review current active commercial theater halls inside the facility ecosystem.</p>
    </div>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger small mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i>Infrastructure Sync Error: <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (empty($theaters)): ?>
            <div class="col-12 text-center text-muted p-5 bg-white rounded-3 border shadow-sm">
                <i class="fa-solid fa-building-columns display-4 d-block mb-2 text-black-50"></i> No physical screens registered in the schema infrastructure yet.
            </div>
        <?php else: ?>
            <?php foreach ($theaters as $t): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card p-4 shadow-sm border bg-white rounded-3 h-100 transition-all">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-3 bg-dark text-info rounded-3 fs-3 px-4 d-flex align-items-center justify-content-center" style="min-width: 70px;">
                                <i class="fa-solid fa-display"></i>
                            </div>
                            <div class="overflow-hidden">
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($t['theater_name']) ?>"><?= htmlspecialchars($t['theater_name']) ?></h5>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-semibold text-uppercase" style="font-size: 10px; padding: 4px 10px;">
                                    <i class="fa-solid fa-circle-check me-1 small"></i>Operational
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>