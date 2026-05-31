<?php

function logActivity($pdo, $action) {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
    
    try {

        $sql = "INSERT INTO hius_log_table (user_id, action_made, log_timestamp) VALUES (?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, trim($action)]);
    } catch (PDOException $e) {

        error_log("System Audit Log Failure: " . $e->getMessage());
    }
}
?>