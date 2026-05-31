<?php
session_start();
require_once 'config/db_connection.php'; 

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    if ($_SESSION['role'] === 'Admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['role'] === 'Employee') {
        header("Location: employee/index.php");
    } else {
        header("Location: customer/index.php");
    }
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM hius_user_table WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user) {
                $hashed_input = md5($password);

                if ($hashed_input === $user['password']) {
                    if ($user['role'] === 'Customer' && isset($user['is_verified']) && (int)$user['is_verified'] === 0) {
                        $_SESSION['verify_username'] = $user['username'];
                        $error_message = "Your account is not verified. <a href='verify_otp.php' class='alert-link fw-bold text-decoration-underline text-warning'>Click here to enter your OTP code.</a>";
                    } else {
                        $_SESSION['user_id']  = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role']     = $user['role'];

                        if ($user['role'] === 'Admin') {
                            header("Location: admin/dashboard.php");
                        } elseif ($user['role'] === 'Employee') {
                            header("Location: employee/dashboard.php");
                        } else {
                            header("Location: customer/index.php");
                        }
                        exit();
                    }
                } else {
                    $error_message = "Invalid username or password.";
                }
            } else {
                $error_message = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error_message = "Database connection fault: " . $e->getMessage();
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Frasshawty Cinema Reserve</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/Soriano/movie_booking/css/customer-style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="background-color: #0b0b0b; height: 100vh;">

<div class="card p-4 text-center text-white" style="width: 100%; max-width: 420px; background-color: #121212; border: 1px solid #2d2d2d; border-radius: 12px; box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5);">
    <div class="mb-4">
        <div class="display-4 mb-2"><i class="fa-solid fa-clapperboard text-danger"></i></div>
        <h4 class="fw-bold text-white text-uppercase tracking-wider mb-1" style="letter-spacing: 1px;">
            FRASSHAWTY <span style="color: var(--cinema-gold, #ffb800);">CINEMA</span>
        </h4>
        <p class="text-white-50 small mb-0">Sign in to manage bookings or reservations.</p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger bg-transparent border-danger text-danger small py-2.5 text-start shadow-sm" style="border-radius: 6px;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $error_message ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="text-start">
        <div class="mb-3">
            <label class="form-label fw-semibold small text-white-50">Username</label>
            <div class="input-group">
                <span class="input-group-text border-secondary text-secondary" style="background-color: #1a1a1a;"><i class="fa-solid fa-user"></i></span>
                <input type="text" name="username" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" placeholder="Enter username" required autocomplete="off">
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label fw-semibold small text-white-50">Password</label>
            <div class="input-group">
                <span class="input-group-text border-secondary text-secondary" style="background-color: #1a1a1a;"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" placeholder="Enter password" required>
            </div>
        </div>
        
        <button type="submit" class="btn btn-danger w-100 py-2 fw-bold text-uppercase shadow-sm mb-3" style="font-size: 12px; letter-spacing: 0.5px;">
            Sign In
        </button>
        
        <p class="small text-muted mb-0 text-center">
            New to Frasshawty Cinema Reserve? <br>
            <a href="register.php" class="text-decoration-none fw-bold" style="color: var(--cinema-gold, #ffb800);">Create Account</a>
        </p>
    </form>
</div>

</body>
</html>