<?php
session_start();
require_once 'config/db_connection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = 'Customer';

    if (!empty($username) && !empty($email) && !empty($password)) {
        if ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM hius_user_table WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                
                if ($stmt->fetchColumn() > 0) {
                    $error_message = "Username or Email is already registered.";
                } else {
                    $otp = rand(100000, 999999);
                    $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    
                    $mail = new PHPMailer(true);
                    
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'helenaisabel.soriano.cics@ust.edu.ph'; 
                    $mail->Password   = 'qlqz fvjo vlll mimw';                 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        
                    $mail->Port       = 587;

                    $mail->setFrom('FrasshawtyCinema@gmail.com', 'Frasshawty Cinema Reserve');
                    $mail->addAddress($email, $username);

                    $mail->isHTML(true);
                    $mail->Subject = 'Verify Your Frasshawty Cinema Reserve Account';
                    $mail->Body    = "<h3>Hello " . htmlspecialchars($username) . ",</h3>
                                     <p>Thank you for registering. Your verification code to activate your account is:</p>
                                     <h2 style='color:#ffb800; letter-spacing:4px; font-family: monospace;'>{$otp}</h2>
                                     <p>This code will expire in 15 minutes.</p>";

                    $mail->send();

                    $md5_password = md5($password);

                    $sql = "INSERT INTO hius_user_table 
                            (username, email, password, role, otp_code, otp_expires_at, is_verified) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                            
                    $ins = $pdo->prepare($sql);
                    $ins->execute([
                        $username, 
                        $email, 
                        $md5_password,
                        $role, 
                        $otp, 
                        $expires_at, 
                        0
                    ]);

                    $_SESSION['verify_username'] = $username;
                    header("Location: verify_otp.php");
                    exit();
                }
            } catch (Exception $e) {
                $error_message = "Mailer Error: " . $mail->ErrorInfo;
            } catch (PDOException $e) {
                $error_message = "Database Error: " . $e->getMessage();
            }
        }
    } else {
        $error_message = "Please complete all field elements.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Frasshawty Cinema Reserve</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/Soriano/movie_booking/css/customer-style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="background-color: #0b0b0b; min-height: 100vh; padding: 2rem 0;">

<div class="card p-4 text-center text-white" style="width: 100%; max-width: 440px; background-color: #121212; border: 1px solid #2d2d2d; border-radius: 12px; box-shadow: 0 12px 32px rgba(0, 0, 0, 0.5);">
    <div class="mb-4">
        <div class="display-4 mb-2"><i class="fa-solid fa-user-plus text-danger"></i></div>
        <h4 class="fw-bold text-white text-uppercase tracking-wider mb-1" style="letter-spacing: 1px;">
            Create <span style="color: var(--cinema-gold, #ffb800);">Account</span>
        </h4>
        <p class="text-white-50 small mb-0">Register to check schedules and book seats.</p>
    </div>
    
    <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger bg-transparent border-danger text-danger small py-2.5 text-start shadow-sm" style="border-radius: 6px;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $error_message ?>
        </div>
    <?php endif; ?>
    
    <form action="register.php" method="POST" class="text-start">
        <div class="mb-3">
            <label class="form-label fw-semibold small text-white-50">Username</label>
            <div class="input-group">
                <span class="input-group-text border-secondary text-secondary" style="background-color: #1a1a1a;"><i class="fa-solid fa-user"></i></span>
                <input type="text" name="username" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" placeholder="Choose a username" required autocomplete="off">
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-semibold small text-white-50">Email Address</label>
            <div class="input-group">
                <span class="input-group-text border-secondary text-secondary" style="background-color: #1a1a1a;"><i class="fa-solid fa-envelope"></i></span>
                <input type="email" name="email" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" placeholder="name@example.com" required autocomplete="off">
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label fw-semibold small text-white-50">Password</label>
            <div class="input-group">
                <span class="input-group-text border-secondary text-secondary" style="background-color: #1a1a1a;"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" placeholder="Create strong password" required>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label fw-semibold small text-white-50">Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text border-secondary text-secondary" style="background-color: #1a1a1a;"><i class="fa-solid fa-shield-halved"></i></span>
                <input type="password" name="confirm_password" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" placeholder="Repeat your password" required>
            </div>
        </div>
        
        <button type="submit" class="btn btn-danger w-100 py-2 fw-bold text-uppercase shadow-sm mb-3" style="font-size: 12px; letter-spacing: 0.5px;">
            Send Verification OTP
        </button>
        
        <p class="small text-muted mb-0 text-center">
            Already have an account? <br>
            <a href="login.php" class="text-decoration-none fw-bold" style="color: var(--cinema-gold, #ffb800);">Sign In</a>
        </p>
    </form>
</div>

</body>
</html>