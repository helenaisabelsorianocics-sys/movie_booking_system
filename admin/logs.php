<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: ../index.php"); 
    exit(); 
}
require_once '../config/db_connection.php';

try {

    $sql = "SELECT l.log_id, l.action, l.timestamp, u.username 
            FROM hius_log_table l
            LEFT JOIN hius_user_table u ON l.user_id = u.user_id
            ORDER BY l.timestamp DESC";
    $stmt = $pdo->query($sql);
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    $logs = [];
    $db_error = $e->getMessage();
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-admin.php'; ?>
<div class="container-fluid text-start text-dark">
    <div class="mb-4">
        <h2 class="fw-bold text-dark text-uppercase tracking-wider m-0" style="letter-spacing: 1px;">System Audit Logs</h2>
        <p class="text-muted mb-0">Track administrative actions, user updates, and transaction triggers.</p>
    </div>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger bg-transparent border-danger text-danger small" style="border-radius: 6px;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>Log Synchronization Error: <?= htmlspecialchars($db_error) ?>
        </div>
    <?php endif; ?>

    <div class="shadow rounded-3 overflow-hidden" style="background-color: #121212; border: 1px solid #2d2d2d;">
        
        <?php if (count($logs) > 0): ?>
            
            <div class="row mx-0 py-3 text-uppercase fw-bold text-white-50 d-none d-md-flex align-items-center" style="background-color: #1a1a1a; border-bottom: 2px solid #2d2d2d; font-size: 11px; letter-spacing: 0.5px;">
                <div class="col-md-2 ps-4">Log ID</div>
                <div class="col-md-3">Timestamp</div>
                <div class="col-md-2">User Operator</div>
                <div class="col-md-5 pe-4">Executed Action Entry Description</div>
            </div>

            <?php foreach($logs as $l): ?>
                <div class="row mx-0 py-3 align-items-center text-white" style="border-bottom: 1px solid #2d2d2d;">
                    
                    <div class="col-6 col-md-2 ps-md-4 mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase block-label">Log Reference:</span>
                        <div class="fw-bold" style="color: var(--cinema-gold, #ffb800);">#LOG-<?= str_pad($l['log_id'], 5, '0', STR_PAD_LEFT) ?></div>
                    </div>
                    
                    <div class="col-6 col-md-3 mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase block-label">Date & Time:</span>
                        <div class="fw-semibold text-light"><?= date('M d, Y • g:i A', strtotime($l['timestamp'])) ?></div>
                    </div>
                    
                    <div class="col-12 col-md-2 mb-2 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase block-label">Operator:</span>
                        <div>
                            <span class="badge border border-secondary px-2 py-1 fw-bold text-white" style="background-color: #1a1a1a;">
                                <i class="fa-regular fa-user me-1 text-white-50"></i> <?= htmlspecialchars($l['username'] ?? 'System / Guest') ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-5 pe-md-4 mb-1 mb-md-0">
                        <span class="d-inline-block d-md-none text-white-50 small text-uppercase block-label">Action Logged:</span>
                        <div class="font-monospace text-light" style="font-size: 12px; word-break: break-word;"><?= htmlspecialchars($l['action']) ?></div>
                    </div>

                </div>
            <?php endforeach; ?>

        <?php else: ?>
            
            <div class="p-5 text-center text-white-50">
                <i class="fa-solid fa-list-check display-4 d-block mb-2 text-secondary"></i>
                The log history ledger is completely empty right now.
            </div>
            
        <?php endif; ?>
        
    </div>
</div>
<?php include '../includes/footer.php'; ?>