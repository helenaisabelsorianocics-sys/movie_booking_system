<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: ../index.php"); 
    exit(); 
}
require_once '../config/db_connection.php';


if (isset($_GET['delete_id'])) {
    $target_theater_id = intval($_GET['delete_id']);
    try {
        $pdo->beginTransaction();


        $delete_stmt = $pdo->prepare("DELETE FROM hius_theater_table WHERE theater_id = ?");
        $delete_stmt->execute([$target_theater_id]);


        $log_stmt = $pdo->prepare("INSERT INTO hius_log_table (user_id, action, timestamp) VALUES (?, ?, NOW())");
        $log_action = "Admin dropped Theater Infrastructure Profile room configuration ID: #{$target_theater_id}";
        $log_stmt->execute([$_SESSION['user_id'], $log_action]);

        $pdo->commit();
        header("Location: theaters.php?deleted=1");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        // Friendly block error message if foreign key constraints exist on showtime_table links
        $db_error = "Deletion Blocked: Clear out active scheduled movie showtimes assigned to this hall room space first. " . $e->getMessage();
    }
}

try {
  
    $theaters = $pdo->query("SELECT * FROM hius_theater_table ORDER BY theater_name ASC")->fetchAll();
} catch (PDOException $e) {
    $theaters = [];
    $db_error = $e->getMessage();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-admin.php'; ?>

<div class="container-fluid text-start">
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2>Physical Theater Infrastructure</h2>
            <p class="text-muted mb-0">Review and manage current active commercial theater halls inside the facility ecosystem.</p>
        </div>
        <a href="add_theater.php" class="btn btn-primary fw-bold btn-sm px-3 shadow-sm">
            <i class="fa-solid fa-plus me-1"></i> Build New Hall
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success small py-2"><i class="fa-solid fa-circle-check me-2"></i>New theater hall architectural parameters initialized successfully!</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success small py-2"><i class="fa-solid fa-circle-check me-2"></i>Theater infrastructure profile removed from system catalog mappings.</div>
    <?php endif; ?>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger small py-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Database Sync Error: <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (empty($theaters)): ?>
            <div class="col-12 text-center text-muted p-5 bg-white rounded border shadow-sm">
                <i class="fa-solid fa-building-columns display-4 d-block mb-2 text-black-50"></i> No physical screens registered in the schema infrastructure yet.
            </div>
        <?php else: ?>
            <?php foreach ($theaters as $t): ?>
                <div class="col-xl-4 col-md-6">
                    <div class="card p-4 shadow-sm border bg-white rounded-3 position-relative h-100 d-flex flex-column justify-content-between">
                        
                        <div class="d-flex align-items-start gap-3 mb-2">
                            <div class="p-3 bg-dark text-info rounded-3 fs-3 px-4"><i class="fa-solid fa-desktop"></i></div>
                            <div class="overflow-hidden w-100">
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($t['theater_name']) ?>"><?= htmlspecialchars($t['theater_name']) ?></h5>
                                <div class="text-muted small mb-0.5"><i class="fa-solid fa-users me-1"></i> Max Capacity: <strong class="text-dark"><?= intval($t['capacity']) ?></strong> seats</div>
                                <div class="text-muted small text-truncate mb-2" title="<?= htmlspecialchars($t['location']) ?>">
                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i> <?= htmlspecialchars($t['location']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between border-top pt-2 mt-auto">
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small" style="font-size: 10px; padding: 4px 10px;">Operational</span>
                            
                            <a href="theaters.php?delete_id=<?= $t['theater_id'] ?>" 
                               class="btn btn-sm btn-outline-danger py-0 px-2 fs-7" 
                               style="font-size: 12px;"
                               onclick="return confirm('Are you completely sure you want to decommission and drop this physical theater mapping? This cannot be undone.');" 
                               title="Decommission Room Profile">
                                <i class="fa-regular fa-trash-can me-1"></i> Remove
                            </a>
                        </div>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>