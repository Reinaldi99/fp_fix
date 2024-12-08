<?php
session_start();
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Koneksi ke database
$conn = new mysqli('localhost', 'root', '', 'fpp');
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Menghitung total data pengguna
$total_users_query = "SELECT COUNT(*) AS total FROM users";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total'];

// Pagination logic
$limit = 10; // Max rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Data pengguna untuk tabel
$users_query = "SELECT * FROM users LIMIT $start, $limit";
$users_result = $conn->query($users_query);

// Cek jika query berhasil
if (!$users_result) {
    die("Query gagal: " . $conn->error);
}

// Total pages calculation
$total_pages_users = ceil($total_users / $limit);

// Ambil daftar event/lomba
$events_query = "SELECT * FROM event";
$events_result = $conn->query($events_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Admin Dashboard</title>
    <style>

        /* Animasi Gradien Bergerak */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8));
            background-size: 400% 400%;
            animation: gradientBG 8s ease infinite;
            color: #fff;
            overflow-x: hidden;
            overflow-y: auto;
            min-height: 100vh;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        /* Animasi Partikel */
        @keyframes particleMovement {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 1;
            }
            50% {
                transform: translateY(-50px) translateX(50px);
                opacity: 0.5;
            }
            100% {
                transform: translateY(0) translateX(0);
                opacity: 1;
            }
        }

        /* Partikel */
        .particle {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            animation: particleMovement 4s ease-in-out infinite;
        }

        .particle:nth-child(odd) {
            background-color: rgba(0, 255, 255, 0.7);
            animation-duration: 5s;
        }

        .particle:nth-child(even) {
            background-color: rgba(0, 128, 255, 0.7);
            animation-duration: 6s;
        }

        /* Header */
        header {
            background-color: #0056b3;
            padding: 10px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
        }

        header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        header .logo {
            max-width: 40px;
            height: auto;
            margin-right: 15px;
        }

        header h1, header p {
            animation: fadeIn 1.5s ease-out;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
            padding: 0;
            margin: 0;
        }

        nav ul li {
            position: relative;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        nav ul li a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Responsive Navbar */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                align-items: flex-start;
            }

            nav ul {
                width: 100%;
                justify-content: space-around;
                padding: 10px 0;
            }

            nav ul li {
                text-align: center;
            }
        }

        .card-total-user {
            background-color: #4CAF50;
            color: #fff;
            animation: fadeIn 2s ease-in-out;
        }

        .table-responsive {
            animation: fadeIn 1s ease-out;
        }

        /* Animasi untuk munculnya elemen saat scroll */
        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(50px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

<header>
    <div class="container">
        <div class="d-flex align-items-center">
            <img src="../layout/logo.jpg" alt="Logo RSC" class="logo">
            <div class="ms-3">
                <h1 class="mb-0" style="font-size: 1.5rem;">Admin Dashboard</h1>
                <p class="mb-0">Selamat datang, <strong><?= htmlspecialchars($_SESSION["username"]); ?></strong></p>
            </div>
        </div>
        <nav>
            <ul class="d-flex mb-0">
                <li><a class="nav-link" href="edit-dashboard.php">Kelola Perlombaan</a></li>
                <li><a class="nav-link" href="pivot.php">Kelola Pivot</a></li>
                <li><a class="logout-btn" href="#" onclick="document.getElementById('logout-form').submit();">Logout</a></li>
            </ul>
        </nav>
        <form id="logout-form" action="" method="POST" style="display: none;">
            <input type="hidden" name="logout" value="1">
        </form>
    </div>
</header>

<!-- Partikel Animasi -->
<script>
// Fungsi untuk membuat partikel secara dinamis
function createParticles() {
    for (let i = 0; i < 30; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        // Menentukan posisi acak untuk partikel
        particle.style.top = `${Math.random() * 100}%`;
        particle.style.left = `${Math.random() * 100}%`;
        document.body.appendChild(particle);
    }
}

// Memanggil fungsi untuk membuat partikel
createParticles();
</script>

<!-- Konten Dashboard -->
<div class="container mt-4">
    <!-- Tabel Total Data Peserta -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <div class="card card-total-user text-center">
                <div class="card-header">Total User</div>
                <div class="card-body">
                    <h5 class="card-title"><strong><?= $total_users; ?></strong> Users</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Profile -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-3 mb-4">
            <div class="card shadow border-0 rounded-3 hover-card">
                <div class="card-body text-center">
                    <i class="fas fa-users text-primary mb-3" style="font-size: 40px;"></i>
                    <h5 class="card-title text-dark">Data Peserta</h5>
                    <p class="card-text text-muted">Data dari semua Peserta</p>
                    <a href="users-details.php" class="btn btn-primary w-100">Manage Peserta</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <!-- Tabel Daftar Lomba -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h3>Daftar Lomba</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Event</th>
                            <th>Tanggal Event</th>
                            <th>Lokasi</th>
                            <th>Total Peserta</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($event = $events_result->fetch_assoc()) : ?>
                            <?php 
                                // Hitung jumlah peserta terdaftar untuk lomba ini
                                $participants_query = "SELECT COUNT(*) AS total FROM registrations WHERE event_id = " . $event['event_id'];
                                $participants_result = $conn->query($participants_query);
                                $participants = $participants_result->fetch_assoc()['total'];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($event['event_name']) ?></td>
                                <td><?= date ('d-m-Y', strtotime($event['event_date'])) ?></td>
                                <td><?= htmlspecialchars($event['location']) ?></td>
                                <td><?= htmlspecialchars($participants) ?></td>
                                <td>
                                    <a href="event-details.php?event_id=<?= $event['event_id'] ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-users"></i> Lihat Peserta
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer>
    <footer class="py-3 mt-5">
        <div class="text-center mt-4">
            <p class="small">© 2024 Rektor Sport Championship.</p>
        </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
