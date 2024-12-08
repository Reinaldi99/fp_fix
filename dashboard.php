<?php
session_start();

// Menghubungkan ke database
require 'service/database.php';

// Logout logic
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['is_login'])) {
    header('Location: index.php');
    exit;
} elseif ($_SESSION['is_login'] == false) {
    header('Location: index.php');
    exit;
}

// Ambil user_id dari session
$user_id = $_SESSION['is_login'];



// Query untuk mendapatkan daftar lomba
$query = "SELECT event_id, event_name, event_description, registration_fee, image_url FROM event";
$result = mysqli_query($db, $query);

$event = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $event[] = $row;
    }
} else {
    echo "Error: " . mysqli_error($db);
}

// Query untuk mengambil profil pengguna
$query = "SELECT profile_picture FROM user_profiles WHERE user_id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $profile_picture);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Menyimpan foto profil di session
if ($profile_picture) {
    $_SESSION['profile_picture'] = 'uploads/' . $profile_picture;
} else {
    $_SESSION['profile_picture'] = 'layout/profil-default.jpg'; // Gambar default jika tidak ada foto profil
}

mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="layout/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
    <title>RSC - HOMEPAGE</title>
</head>

<body class="bg-primary">
    <nav class="navbar navbar-expand-lg navbar-light bg-transparent">
        <div class="container-fluid">
            <a class="navbar-brand text-light fs-3 fw-bold" href="">RSC</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-light px-3 py-2" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light px-3 py-2" href="#card-section">Event</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light px-3 py-2" href="#contact-section">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light px-3 py-2" href="service/history.php">History</a>
                    </li>
                </ul>
                <!-- dropdown nya -->
                <div class="dropdown">
                    <button class="btn p-0 border-0" type="button" id="dropdownMenuButton" aria-expanded="false">
                        <img src="<?= $_SESSION['profile_picture'] ?>" alt="Profile Picture" class="img-thumbnail" style="width: 50px; height: 50px; border-radius: 70%;">
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="left: auto; right: 0; transform: translateX(-10%);">
                        <li><a class="dropdown-item" href="profile/profile.php">Profile</a></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                        </li>
                    </ul>
                </div>

                <!-- Form Logout Tersembunyi -->
                <form id="logout-form" action="" method="POST" style="display: none;">
                    <input type="hidden" name="logout" value="1">
                </form>

                <script>
                    document.getElementById('dropdownMenuButton').addEventListener('click', function () {
                        const dropdownMenu = this.nextElementSibling;
                        const isExpanded = this.getAttribute('aria-expanded') === 'true';

                        if (isExpanded) {
                            dropdownMenu.classList.remove('show');
                            this.setAttribute('aria-expanded', 'false');
                        } else {
                            dropdownMenu.classList.add('show');
                            this.setAttribute('aria-expanded', 'true');
                        }
                    });

                    // Menutup dropdown jika klik di luar
                    document.addEventListener('click', function (event) {
                        const button = document.getElementById('dropdownMenuButton');
                        const dropdownMenu = button.nextElementSibling;
                        if (!button.contains(event.target) && !dropdownMenu.contains(event.target)) {
                            dropdownMenu.classList.remove('show');
                            button.setAttribute('aria-expanded', 'false');
                        }
                    });
                </script>
            </nav>

            <div class="d-flex flex-column align-items-center justify-content-center text-center mt-5" style="min-height: 60vh;">
    <h1 class="display-3 fw-bold text-light mb-3" style="text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.7);">
        <span class="text-warning">REKTOR SPORT CHAMPIONSHIP</span>
    </h1>
    <p class="lead text-light fs-4" style="max-width: 800px; text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.6);">
        Temukan semangat kompetisi dalam ajang olahraga tahunan terbesar! Mari raih prestasi, jalin persahabatan, dan jadilah inspirasi di <span class="text-warning fw-bold">RSC 2024</span>.
    </p>
    <blockquote class="blockquote text-light fst-italic mt-3" style="font-size: 1.2rem;">
        "Bersama, kita mencetak sejarah dalam setiap langkah dan kemenangan."
    </blockquote>
    <div class="mt-4">
        <a href="#card-section" class="btn btn-warning btn-lg text-dark shadow-lg rounded-pill px-5 py-2">
            Lihat Lomba Tersedia <i class="bi bi-arrow-down-circle ms-2"></i>
        </a>
    </div>
</div>


                <!-- Carousel Section -->
<div id="eventCarousel" class="carousel slide mt-5" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php if (!empty($event)): ?>
            <?php foreach ($event as $index => $eventItem): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <?php if (!empty($eventItem['image_url'])): ?>
                        <img src="<?= htmlspecialchars('uploadss/' . $eventItem['image_url']) ?>" class="d-block w-100 rounded" alt="Event Image" style="height: 600px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="carousel-caption d-none d-md-block">
                        <h5 class="text-light"><?= htmlspecialchars($eventItem['event_name']) ?></h5>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="carousel-item active">
                <img src="layout/no-event.jpg" class="d-block w-100 rounded" alt="No Events" style="height: 400px; object-fit: cover;">
                <div class="carousel-caption d-none d-md-block">
                    <h5 class="text-light">Belum ada lomba yang tersedia.</h5>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#eventCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#eventCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>


<section id="card-section" class="container mt-5">
    <div class="row justify-content-center"> <!-- Menambahkan justify-content-center untuk memusatkan card -->
        <?php if (!empty($event)): ?>
            <?php foreach ($event as $event): ?>
                <div class="col-sm-6 col-md-6 mb-4">
                    <div class="card shadow border-0 rounded-3 custom-card">
                        <?php if (!empty($event['image_url'])): ?>
                            <img src="<?= htmlspecialchars('uploads/' . $event['image_url']) ?>" alt="Event Image" class="card-img-top rounded-top" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>                 
                        <div class="card-body">
                            <h5 class="card-title text-dark"><?= htmlspecialchars($event['event_name']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($event['event_description']) ?></p>
                            <p class="card-text text-dark"><strong>Rp<?= number_format($event['registration_fee'], 0, ',', '.') ?></strong></p>
                            <a href="pendaftaran.php?event_id=<?= $event['event_id'] ?>" class="btn btn-primary w-100">Daftar Sekarang</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>  
            <div class="col-12">
                <p class="text-center text-light">Belum ada lomba yang tersedia.</p>
            </div>
        <?php endif; ?>
    </div>
</section>
            </div>

            <?php include "layout/footer.html"; ?>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
    </html>