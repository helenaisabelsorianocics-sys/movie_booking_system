<?php
session_start();
require_once '../config/db_connection.php';
require_once '../includes/functions.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id     = intval($_SESSION['user_id']);
    $showtime_id = intval($_POST['showtime_id']);
    $seat_number = trim($_POST['seat_number']); 
    $total_price = floatval($_POST['total_price']);
    $num_tickets = 1; 
    $status      = 'Approved'; 

    try {
        $pdo->beginTransaction();


        $check_sql = "SELECT COUNT(*) FROM hius_booking_table WHERE showtime_id = ? AND seat_number = ? AND status != 'Cancelled'";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([$showtime_id, $seat_number]);
        
        if ($check_stmt->fetchColumn() > 0) {
            throw new Exception("Race Condition Detected: The requested seat location assignment has already been reserved.");
        }


        $booking_sql = "INSERT INTO hius_booking_table 
                        (showtime_id, user_id, booking_date, num_tickets, status, seat_number, total_price) 
                        VALUES (:showtime_id, :user_id, NOW(), :num_tickets, :status, :seat_number, :total_price)";
                        
        $booking_stmt = $pdo->prepare($booking_sql);
        
        try {
            $booking_stmt->execute([
                ':showtime_id' => $showtime_id,
                ':user_id'     => $user_id,
                ':num_tickets' => $num_tickets,
                ':status'      => $status,
                ':seat_number' => $seat_number,
                ':total_price' => $total_price
            ]);
        } catch (PDOException $e) {
            throw new Exception(" FAILED AT STEP 1 (Booking Table): " . $e->getMessage());
        }
        
        $new_booking_id = $pdo->lastInsertId();

        try {
            $st_stmt = $pdo->prepare("SELECT theater_id FROM hius_showtime_table WHERE showtime_id = ?");
            $st_stmt->execute([$showtime_id]);
            $theater_id = $st_stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception(" FAILED AT STEP 2 (Fetching Theater ID): " . $e->getMessage());
        }

        if (!$theater_id) {
            throw new Exception("Invalid parameters: Targeted timetable projection target index references empty properties.");
        }


        try {
            $seat_sql = "INSERT INTO hius_seats (theater_id, seat_number, showtime_id, booking_id) VALUES (?, ?, ?, ?)";
            $seat_stmt = $pdo->prepare($seat_sql);
            $seat_stmt->execute([$theater_id, $seat_number, $showtime_id, $new_booking_id]);
        } catch (PDOException $e) {
            throw new Exception(" FAILED AT STEP 3 (Seats Table): " . $e->getMessage() . " -> Check if hius_seats table contains 'showtime_id' column!");
        }

        try {
            $update_st_sql = "UPDATE hius_showtime_table 
                              SET available_seats = available_seats - 1 
                              WHERE showtime_id = ? AND available_seats > 0";
            $update_st_stmt = $pdo->prepare($update_st_sql);
            $update_st_stmt->execute([$showtime_id]);
        } catch (PDOException $e) {
            throw new Exception(" FAILED AT STEP 4 (Updating Available Seats): " . $e->getMessage());
        }

        if ($update_st_stmt->rowCount() === 0) {
            throw new Exception("Inventory Exhausted: No available open seating options left.");
        }
        try {
            logActivity($pdo, "Customer account ID {$user_id} securely booked seat {$seat_number} (Booking Reference: #CR-{$new_booking_id}) for showtime ID {$showtime_id}.");
        } catch (Exception $e) {
            throw new Exception(" FAILED AT STEP 5 (logActivity Function): " . $e->getMessage() . " -> Check your log table layout!");
        }

        $pdo->commit();
        header("Location: booking_success.php?ref=" . urlencode($new_booking_id));
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        // Displays the direct, specific step where the failure occurred
        die("<div style='font-family:sans-serif; padding:20px; background:#fff5f5; border-left:5px solid #e53e3e; color:#c53030; margin:20px; border-radius:4px;'>
                <h3 style='margin-top:0;'>⚠️ Database Execution Fault</h3>
                <p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
                <p>Please review your database structures for the step mentioned above.</p>
             </div>");
    }
} else {
    header("Location: index.php");
    exit();
}
?>