<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { 
    header("Location: ../index.php"); 
    exit(); 
}
require_once '../config/db_connection.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    $target_user_id = intval($_POST['user_id']);
    $new_role = trim($_POST['role']);
    

    if ($target_user_id === intval($_SESSION['user_id'])) {
        $db_error = "Action Blocked: You cannot revoke your own root administrative role account tier state.";
    } elseif (in_array($new_role, ['Admin', 'Employee', 'Customer'])) {
        try {
            $pdo->beginTransaction();


            $get_old = $pdo->prepare("SELECT username, role FROM hius_user_table WHERE user_id = ?");
            $get_old->execute([$target_user_id]);
            $user_data = $get_old->fetch();

            if ($user_data) {

                $update_stmt = $pdo->prepare("UPDATE hius_user_table SET role = ? WHERE user_id = ?");
                $update_stmt->execute([$new_role, $target_user_id]);


                $log_stmt = $pdo->prepare("INSERT INTO hius_log_table (user_id, action, timestamp) VALUES (?, ?, NOW())");
                $log_msg = "Admin altered User Profile authorization role for '{$user_data['username']}' (ID: {$target_user_id}) from '{$user_data['role']}' to '{$new_role}'";
                $log_stmt->execute([$_SESSION['user_id'], $log_msg]);

                $pdo->commit();
                header("Location: users.php?success=1");
                exit();
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $db_error = "Role Shift Failed: " . $e->getMessage();
        }
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT user_id, username, role FROM hius_user_table ";
if (!empty($search)) { 
    $sql .= "WHERE username LIKE :s "; 
}
$sql .= "ORDER BY user_id DESC";

$stmt = $pdo->prepare($sql);
if (!empty($search)) { 
    $stmt->execute(['s' => "%$search%"]); 
} else { 
    $stmt->execute(); 
}
$users = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar-admin.php'; ?>

<div class="container-fluid text-start">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2>User Profiles Access Directories</h2>
            <p class="text-muted mb-0">Audit security permissions clearance levels and configure structural system role assignments.</p>
        </div>
        <form action="users.php" method="GET" class="d-flex gap-2" style="max-width: 320px; width: 100%;">
            <div class="input-group input-group-sm">
                <input type="text" name="search" class="form-control" placeholder="Search usernames..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-dark"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </form>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success small py-2"><i class="fa-solid fa-circle-check me-2"></i>Account accessibility clearance level updated successfully.</div>
    <?php endif; ?>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger small py-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Access Violation: <?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border bg-white rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0 small">
                <thead class="table-dark text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-4 py-3">User ID Ref</th>
                        <th class="py-3">Profile Username</th>
                        <th class="py-3">Clearance Access Role Tier</th>
                        <th class="pe-4 py-3 text-center" style="width: 180px;">Modify Clearance</th>
                    </tr>
                </thead>
                <tbody class="text-secondary">
                    <?php if (count($users) > 0): ?>
                        <?php foreach($users as $u): ?>
                            <tr>
                                <td class="ps-4 text-dark fw-bold">#USR-<?= str_pad($u['user_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td class="fw-bold text-dark"><i class="fa-regular fa-circle-user me-1 text-muted"></i> <?= htmlspecialchars($u['username']) ?></td>
                                <td>
                                    <?php 
                                        $badge_color = 'secondary';
                                        if($u['role'] === 'Admin') $badge_color = 'danger'; 
                                        elseif($u['role'] === 'Employee') $badge_color = 'success'; 
                                    ?>
                                    <span class="badge bg-<?= $badge_color ?> px-2.5 py-1.5 fw-bold rounded">
                                        <?= htmlspecialchars($u['role']) ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <form action="users.php" method="POST" onsubmit="return confirm('Confirm credential permissions profile alteration adjustments?');">
                                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                        <input type="hidden" name="change_role" value="1">
                                        <div class="input-group input-group-sm">
                                            <select name="role" class="form-select form-select-sm small font-weight-bold text-center py-0" style="font-size: 11px;" <?= $u['user_id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                                <option value="Customer" <?= $u['role'] === 'Customer' ? 'selected' : '' ?>>Customer</option>
                                                <option value="Employee" <?= $u['role'] === 'Employee' ? 'selected' : '' ?>>Employee</option>
                                                <option value="Admin" <?= $u['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <button class="btn btn-outline-dark btn-sm font-weight-bold" style="font-size: 11px;" <?= $u['user_id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>><i class="fa-solid fa-user-shield"></i></button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-user-slash d-block mb-2 text-black-50 display-5"></i> No profiles match that search query matrix structure parameters.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>