<?php
// Menghubungkan ke database
require 'service/database.php';

// Ambil data event dari database
$event = [];
$query = "SELECT * FROM event"; // Ganti 'events' dengan nama tabel Anda
$result = mysqli_query($db, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $event[] = $row;
    }
} else {
    echo "Error: " . mysqli_error($db);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="layout/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <title>RSC - LANDING PAGE</title>
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8));
            color: #fff;
        }
        .card {
            border: none;
            background-color: #fff;
            transition: transform 0.3s ease-in-out;
        }
        .card:hover {
            transform: scale(1.05);
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .hero-img {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .hero-img img {
            max-width: 70%;
            height: auto;
            margin-left: 20px;
        }
        .card .badge {
            position: absolute;
            top: 10px;
            left: 10px;
        }
        @media (max-width: 768px) {
        .carousel-caption h5 {
          font-size: 2rem;
        }
      
        .carousel-caption p {
          font-size: 1rem;
        }
      
        .card-body h5 {
          font-size: 1.2rem;
        }
      
        .card-body p {
          font-size: 0.9rem;
        }
      }
    </style>
</head>
<body class="bg-primary">
    <!-- Nav section start-->
    <nav class="navbar navbar-expand-lg navbar-light bg-transparent">
        <div class="container-fluid">
            <a class="navbar-brand text-light fs-3 fw-bold" href="">RSC</a>
        </div>
    </nav>
      
    <section id="hero" class="hero section">
    <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center" data-aos="zoom-out">
                    <p class="text-light" style="font-size: 20px;">Halo, selamat datang</p>
                    <h1 class="display-5 fw-bold text-light" style="white-space: nowrap; font-size: 40px;">REKTOR SPORT CHAMPIONSHIP</h1>
                    <h1 class="display-5 fw-bold text-light" style="white-space: nowrap; font-size: 40px;">UBP KARAWANG</h1>
                    <p class="lead mb-6 text-light" style="font-size: 20px;">
                        <b>Saatnya untuk menunjukkan semangat juang dan kemampuan terbaikmu! Rektor Sport Championship.</b>
                    </p>
                    <div class="d-flex">
                        <a href="login.php" class="btn btn-primary btn-lg shadow btn-signin me-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </a>
                        <a href="#competitions-section" class="btn btn-warning btn-lg shadow btn-explore">
                            Jelajahi Sekarang <i class="bi bi-arrow-down-circle ms-2"></i>
                        </a>
                    </div>
                </div>

                <!-- Gambar besar di sebelah kanan -->
                <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-out" data-aos-delay="200">
                        <img src="layout/lomba.png" class="img-fluid animated" alt="Rektor Sport Image">
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <div id="about-section" class="container mt-5">
        <div class="row align-items-center">
            <div class="col-md-6 col-12">
                <img src="layout/Badminton.JPG" alt="Rektor Sport Championship" class="img-fluid rounded shadow" data-aos="fade-in">
            </div>
            <div class="col-md-6 col-12" data-aos="fade-left">
                <h2 class="fw-bold text-light mb-4">Apa Itu Rektor Sport Championship?</h2>
                <p class="lead" style="text-align: justify; font-size: 1.2rem;">
                    <strong>Rektor Sport Championship</strong> adalah sebuah ajang olahraga tahunan yang menjadi kebanggaan 
                    Universitas Buana Perjuangan Karawang. Event ini mempertemukan mahasiswa dari berbagai program pendidikan 
                    untuk bersaing secara sehat di berbagai cabang olahraga. 
                </p>
                <p class="lead" style="text-align: justify; font-size: 1.2rem;">
                    Selain sebagai arena kompetisi, acara ini juga menjadi wadah untuk mempererat kebersamaan, menciptakan 
                    semangat kolaborasi, dan mendorong fair play di lingkungan kampus.  
                </p>
            </div>
        </div>
    </div>

    <!-- Sports Competitions Section -->
    <div id="competitions-section" class="container mt-5">
        <h2 class="fw-bold text-light mb-4" data-aos="fade-up">Lomba yang tersedia</h2>
        <div class="mb-4 text-center">
        </div>
        <div class="row">
            <?php if (!empty($event)): ?>
                <?php foreach ($event as $event): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6 col-12 mb-4">
                        <div class="card shadow border-0 rounded-3 custom-card position-relative" data-aos="fade-up">
                            <?php if (!empty($event['image_url'])): ?>
                                <img src="<?= htmlspecialchars('uploads/' . $event['image_url']) ?>" class="card-img-top img-fluid" alt="<?= htmlspecialchars($event['event_name']) ?>" style="object-fit: cover; height: 200px;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($event['event_name']) ?></h5>
                                <i class="bi bi-calendar"></i> <?= htmlspecialchars($event['event_date']) ?> <br>
                                <i class="bi bi-clock"></i> <?= htmlspecialchars($event['event_time']) ?> <br>
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['location']) ?>
                                <hr>
                                <strong class="float-start">Rp <?= number_format($event['registration_fee'], 2, ',', '.') ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-light">Belum ada lomba yang tersedia.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include "layout/footer.html"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration:2000,
        });

        function filterEvents() {
            const input = document.getElementById('searchEvent').value.toLowerCase();
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                card.style.display = title.includes(input) ? 'block' : 'none';
            });
        }
    </script>
</body>
</html>