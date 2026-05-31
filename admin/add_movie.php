<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/db_connection.php';

$alert_script = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $genre = trim($_POST['genre']);
    $duration = intval($_POST['duration']);
    $release_date = $_POST['release_date'] ?? date('Y-m-d');
    $description = trim($_POST['description']);

    $poster_name = 'default_poster.png'; 
    $upload_ok = true;

    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['poster']['tmp_name'];
        $original_name = $_FILES['poster']['name'];
        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_extensions)) {
            $poster_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $original_name);
            $target_directory = '../uploads/' . $poster_name;
            
            if (!move_uploaded_file($file_tmp, $target_directory)) {
                $upload_ok = false;
                $alert_script = "<script>Swal.fire('Upload Error', 'Failed to save the image to server directories.', 'error');</script>";
            }
        } else {
            $upload_ok = false;
            $alert_script = "<script>Swal.fire('Invalid File Type', 'Please upload a valid image file (JPG, PNG, WEBP).', 'warning');</script>";
        }
    }

    if ($upload_ok && !empty($title)) {
        try {
            $sql = "INSERT INTO hius_movie_table (title, genre, duration, release_date, description, poster) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $genre, $duration, $release_date, $description, $poster_name]);
            
            $alert_script = "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Movie Added Successfully!',
                    text: '\"" . addslashes($title) . "\" is now active in the system catalog.',
                    confirmButtonColor: '#ffb800'
                }).then(() => {
                    window.location.href = 'dashboard.php';
                });
            </script>";
        } catch (PDOException $e) {
            $alert_script = "<script>Swal.fire('Database Error', '" . addslashes($e->getMessage()) . "', 'error');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Movie - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #0b0b0b; color: #ffffff; font-family: 'Segoe UI', sans-serif; }
        .text-gold { color: #ffb800 !important; }
        .btn-gold { background-color: #ffb800; color: #0b0b0b; font-weight: bold; border: none; }
        .btn-gold:hover { background-color: #e0a200; color: #0b0b0b; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm py-3" style="background-color: #121212; border-bottom: 1px solid #1a1a1a;">
    <div class="container">
        <a class="navbar-brand fw-bold text-uppercase text-gold" href="dashboard.php" style="letter-spacing: 1px;">
            <i class="fa-solid fa-gauge me-2"></i>Admin Control Panel
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white-50 small">Logged in as: <strong class="text-gold"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></strong></span>
            <a href="../logout.php" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-power-off"></i></a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col-sm-8 text-start">
            <h2 class="fw-bold text-white text-uppercase tracking-wider m-0" style="letter-spacing: 1px;">Add New Movie</h2>
            <p class="text-muted small mb-0">Populate the general catalog to activate guest screenings.</p>
        </div>
        <div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm text-light"><i class="fa-solid fa-arrow-left me-1"></i> Back Dashboard</a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4 shadow mb-5 text-start" style="background-color: #121212; border: 1px solid #222222; border-radius: 12px;">
                <form action="add_movie.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-white-50">Movie Title</label>
                            <input type="text" name="title" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" placeholder="e.g. Inception" required autocomplete="off">
                        </div>
                    </div>

                    <div class="row mb-3 g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-white-50">Genre</label>
                            <input type="text" name="genre" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" placeholder="e.g. Sci-Fi, Action" required autocomplete="off">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-white-50">Duration (Minutes)</label>
                            <input type="number" name="duration" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" placeholder="e.g. 148" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-white-50">Release Date</label>
                            <input type="date" name="release_date" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-white-50">Movie Description / Plot Summary</label>
                            <textarea name="description" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" rows="4" placeholder="Enter a brief breakdown of the movie plot layout..." required></textarea>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-white-50">Poster Image Artwork</label>
                            <input type="file" name="poster" class="form-control text-light border-secondary" style="background-color: #1a1a1a;" accept="image/*" required>
                            <div class="form-text small mt-1" style="color: #b3b3b3; font-size: 11px;">Supported formats: JPG, PNG, WEBP. Maximum file size 2MB.</div>
                        </div>
                    </div>

                    <div class="row pt-2">
                        <div class="col-12">
                            <hr class="border-secondary mb-4 opacity-25">
                            <button type="submit" class="btn btn-gold w-100 py-2.5 text-uppercase shadow-sm" style="font-size: 13px; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-plus me-1"></i> Commit Movie to Catalog
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?= $alert_script ?>
</body>
</html>