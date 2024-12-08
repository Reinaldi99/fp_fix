<?php
session_start();
require '../service/database.php';

// Ambil event_id dari URL
$event_id = $_GET['event_id'];

// Ambil data lomba berdasarkan event_id
$event_query = "SELECT * FROM event WHERE event_id = ?";
$stmt = $db->prepare($event_query);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$event_result = $stmt->get_result();
$event = $event_result->fetch_assoc();

// Ambil daftar peserta yang terdaftar untuk event ini
$participants_query = "SELECT r.id AS registration_id, u.username, u.email, r.status_pembayaran
                       FROM registrations r
                       JOIN users u ON r.user_id = u.id
                       WHERE r.event_id = ?";
$stmt = $db->prepare($participants_query);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$participants_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Detail Peserta Lomba: <?= htmlspecialchars($event['event_name']) ?></title>
</head>
<style>
 body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8)); /* Gradient biru segar ke aqua cerah */
    color: #fff; /* Warna teks untuk kontras yang lebih baik */
}
    body {
            position: relative;
            overflow: auto; /* Memungkinkan scroll di halaman */
            height: 100vh; /* Pastikan halaman mengisi tinggi layar */
            margin: 0;
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

        /* Partikel yang berada di belakang konten */
        .particle {
            position: fixed;
            width: 10px;
            height: 10px;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            animation: particleMovement 4s ease-in-out infinite;
            z-index: -1; /* Pastikan partikel di belakang konten */
        }

        .particle:nth-child(odd) {
            background-color: rgba(0, 255, 255, 0.7);
            animation-duration: 5s;
        }

        .particle:nth-child(even) {
            background-color: rgba(0, 128, 255, 0.7);
            animation-duration: 6s;
          }
          .navbar-custom {
        background-color: #0056b3; /* Warna biru lebih gelap */
    }
</style>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="#">Event Manager</a>
    </div>
</nav>


    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3>Peserta Lomba: <?= htmlspecialchars($event['event_name']) ?></h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status Pembayaran</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($participant = $participants_result->fetch_assoc()) : ?>
                        <tr>
                            <td><?= htmlspecialchars($participant['username']) ?></td>
                            <td><?= htmlspecialchars($participant['email']) ?></td>
                            <td>
                                <!-- Elemen span ini -->
                                <span class="badge <?= $participant['status_pembayaran'] === 'Berhasil' ? 'bg-success' : 'bg-warning' ?>">
                                    <?= htmlspecialchars($participant['status_pembayaran']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="participant-details.php?registration_id=<?= $participant['registration_id'] ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-info-circle"></i> Detail
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

        
        <script>
    // Fungsi untuk membuat partikel secara dinamis
    function createParticles() {
        const numberOfParticles = 30; // Jumlah partikel yang ingin ditambahkan
        const windowHeight = window.innerHeight; // Menyimpan tinggi jendela
        const windowWidth = window.innerWidth; // Menyimpan lebar jendela

        for (let i = 0; i < numberOfParticles; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');

            // Menentukan posisi acak untuk partikel di seluruh halaman
            const particleX = Math.random() * windowWidth;
            const particleY = Math.random() * windowHeight;

            // Menempatkan partikel pada posisi acak di jendela
            particle.style.top = `${particleY}px`;
            particle.style.left = `${particleX}px`;

            // Menambahkan partikel ke body
            document.body.appendChild(particle);
        }
    } 

    // Memanggil fungsi untuk membuat partikel
    createParticles();
    </script>



    <footer class="py-3 mt-5">
        <div class="text-center mt-4">
            <p class="small">© 2024 Rektor Sport Championship.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
