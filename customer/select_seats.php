<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db_connection.php';

if (!isset($_GET['showtime_id'])) {
    header("Location: index.php");
    exit();
}

$showtime_id = intval($_GET['showtime_id']);

try {

    $sql = "SELECT s.*, m.title, m.genre, t.theater_name 
            FROM hius_showtime_table s
            JOIN hius_movie_table m ON s.movie_id = m.movie_id
            JOIN hius_theater_table t ON s.theater_id = t.theater_id
            WHERE s.showtime_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$showtime_id]);
    $screening = $stmt->fetch();

    if (!$screening) {
        header("Location: index.php");
        exit();
    }

    $booked_seats = [];
    try {
        $book_stmt = $pdo->prepare("SELECT seat_number FROM hius_booking_table WHERE showtime_id = ? AND status != 'Cancelled'");
        $book_stmt->execute([$showtime_id]);
        $booked_seats = $book_stmt->fetchAll(PDO::FETCH_COLUMN); 
    } catch (PDOException $e) {
        $booked_seats = [];
    }

} catch (PDOException $e) {
    die("System connection fault: " . $e->getMessage());
}

// Configured layout metrics definitions matching matrix
$rows = ['A', 'B', 'C', 'D', 'E'];
$cols = 8;

$ticket_price = 350.00; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats - Cinema Reserve</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .seat-checkbox { display: none; }
        .seat-label {
            display: inline-block;
            width: 42px;
            height: 38px;
            margin: 5px;
            line-height: 38px;
            text-align: center;
            background-color: #e2e8f0;
            color: #334155;
            font-weight: bold;
            font-size: 12px;
            border-radius: 6px;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease;
        }
        .seat-checkbox:checked + .seat-label { background-color: #0ea5e9 !important; color: white; transform: scale(1.05); }
        .seat-checkbox:disabled + .seat-label { background-color: #cbd5e1 !important; color: #94a3b8; cursor: not-allowed; }
        .screen-indicator {
            height: 8px;
            background: #94a3b8;
            border-radius: 100px;
            box-shadow: 0 6px 15px rgba(148, 163, 184, 0.5);
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="index.php"><i class="fa-solid fa-clapperboard me-2"></i>Cinema Reserve</a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-light small">Welcome, <strong class="text-success"><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            <a href="../logout.php" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-power-off"></i></a>
        </div>
    </div>
</nav>

<div class="container py-5">
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show small mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Booking Conflict:</strong> <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <div class="col-lg-8 mb-4">
            <div class="card p-4 shadow-sm border-0 text-center bg-white rounded-3">
                <h6 class="fw-bold text-muted mb-3" style="letter-spacing: 1.5px;">STAGE SCREEN DIRECTION</h6>
                <div class="screen-indicator w-75 mx-auto mb-5"></div>

                <form action="process_booking.php" method="POST" id="bookingForm">
                    <input type="hidden" name="showtime_id" value="<?= $showtime_id ?>">
                    <input type="hidden" name="seat_number" id="hiddenSeatField" value="">
                    <input type="hidden" name="total_price" id="hiddenPriceField" value="0">
                    
                    <div class="d-flex flex-column align-items-center overflow-auto py-2">
                        <?php foreach ($rows as $row): ?>
                            <div class="d-flex justify-content-center text-nowrap">
                                <?php for ($i = 1; $i <= $cols; $i++): 
                                    $seatName = $row . $i;
                                    $isTaken = in_array($seatName, $booked_seats);
                                ?>
                                    <input type="checkbox" name="selected_seats_pool[]" value="<?= $seatName ?>" 
                                           id="seat-<?= $seatName ?>" class="seat-checkbox" 
                                           <?= $isTaken ? 'disabled' : '' ?> onchange="updateSelection(this)">
                                    <label for="seat-<?= $seatName ?>" class="seat-label" title="Seat <?= $seatName ?>">
                                        <?= $seatName ?>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-center gap-4 mt-5 small text-muted flex-wrap">
                        <div><span class="badge px-2 py-2 me-1" style="background-color: #e2e8f0; border: 1px solid #cbd5e1;">&nbsp;</span> Open Space</div>
                        <div><span class="badge bg-primary px-2 py-2 me-1">&nbsp;</span> Your Choice</div>
                        <div><span class="badge px-2 py-2 me-1" style="background-color: #cbd5e1;">&nbsp;</span> Booked Out</div>
                    </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-4 shadow-sm border-0 h-100 d-flex flex-column justify-content-between bg-white rounded-3">
                <div>
                    <span class="badge bg-primary text-uppercase mb-2" style="font-size: 10px;"><?= htmlspecialchars($screening['genre']) ?></span>
                    <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars($screening['title']) ?></h3>
                    <p class="text-muted small mb-3"><i class="fa-solid fa-building me-1"></i> Space: <?= htmlspecialchars($screening['theater_name']) ?></p>
                    <hr class="my-3">
                    
                    <div class="small mb-2 text-secondary"><i class="fa-regular fa-calendar me-2 text-dark"></i><strong>Date:</strong> <?= date('F d, Y', strtotime($screening['show_date'])) ?></div>
                    <div class="small mb-3 text-secondary"><i class="fa-regular fa-clock me-2 text-dark"></i><strong>Time:</strong> <?= date('g:i A', strtotime($screening['show_time'])) ?></div>
                    <div class="small mb-3 text-secondary"><i class="fa-solid fa-tags me-2 text-dark"></i><strong>Rate:</strong> PHP <?= number_format($ticket_price, 2) ?> / ticket</div>
                    
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="small text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">ALLOCATED SEAT MATRIX ROW</div>
                        <div id="seatDisplay" class="fw-bold text-dark fs-5 text-primary">None selected yet</div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="small text-muted fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">TOTAL COST CHARGABLE</div>
                        <div id="priceDisplay" class="fw-bold text-success fs-4">PHP 0.00</div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-success w-100 fw-bold py-3 shadow-sm" id="submitBtn" disabled>
                        Proceed to Payment <i class="fa-solid fa-arrow-right ms-1"></i>
                    </button>
                    <a href="index.php" class="btn btn-link w-100 text-muted small mt-2 text-decoration-none text-center d-block">Go Back to Catalog</a>
                </div>
                </form> 
            </div>
        </div>

    </div>
</div>

<script>
const baseTicketRate = <?= $ticket_price ?>;

function updateSelection(clickedCheckbox) {
    const checkboxes = document.querySelectorAll('.seat-checkbox:checked');
    const display = document.getElementById('seatDisplay');
    const priceDisplay = document.getElementById('priceDisplay');
    const submitBtn = document.getElementById('submitBtn');
    
    const hiddenSeat = document.getElementById('hiddenSeatField');
    const hiddenPrice = document.getElementById('hiddenPriceField');
    
    if (checkboxes.length > 1) {
        checkboxes.forEach(cb => {
            if (cb !== clickedCheckbox) {
                cb.checked = false;
            }
        });
    }
    const activeSelection = document.querySelector('.seat-checkbox:checked');
    
    if (activeSelection) {
        const seatValue = activeSelection.value;
        
        display.innerText = "Seat " + seatValue;
        priceDisplay.innerText = "PHP " + baseTicketRate.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        

        hiddenSeat.value = seatValue;
        hiddenPrice.value = baseTicketRate;
        
        submitBtn.removeAttribute('disabled');
    } else {
        display.innerText = "None selected yet";
        priceDisplay.innerText = "PHP 0.00";
        
        hiddenSeat.value = "";
        hiddenPrice.value = "0";
        
        submitBtn.setAttribute('disabled', 'true');
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>