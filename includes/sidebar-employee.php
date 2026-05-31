

<?php

$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar d-flex flex-column justify-content-between py-4">
    <div>
        <div class="px-4 mb-4 text-center">
            <h4 class="fw-bold text-white mb-0"><i class="fa-solid fa-clapperboard text-success me-2"></i>Frasshawty Cinema Hub</h4>
            <span class="badge bg-success mt-2 px-3 fw-bold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Staff Desk</span>
        </div>
        <hr class="border-secondary mx-3 mb-3">
        <nav class="nav flex-column">
            <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                <i class="fa-solid fa-chart-pie me-3"></i>Dashboard
            </a>
            
            <a class="nav-link <?= $current_page === 'movies.php' ? 'active' : '' ?>" href="movies.php">
                <i class="fa-solid fa-film me-3"></i>Movies Index
            </a>
            
            <a class="nav-link <?= $current_page === 'theaters.php' ? 'active' : '' ?>" href="theaters.php">
                <i class="fa-solid fa-display me-3"></i>Theater Layouts
            </a>
            
            <a class="nav-link <?= $current_page === 'showtimes.php' ? 'active' : '' ?>" href="showtimes.php">
                <i class="fa-solid fa-calendar-days me-3"></i>Schedules Grid
            </a>

            <a class="nav-link <?= $current_page === 'seats.php' ? 'active' : '' ?>" href="seats.php">
                <i class="fa-solid fa-chair me-3"></i>Seating Map
            </a>
            
            <a class="nav-link <?= $current_page === 'verify_ticket.php' ? 'active' : '' ?>" href="verify_ticket.php">
                <i class="fa-solid fa-ticket me-3"></i>Booking Requests
            </a>
        </nav>
    </div>
    <div class="px-3">
        <hr class="border-secondary mb-3">
        <a href="../logout.php" class="btn btn-outline-danger w-100 py-2 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
            <i class="fa-solid fa-power-off me-2"></i>Sign Out
        </a>
    </div>
</div>
<div class="main-content">