<?php
session_start();
require '../service/database.php';

// Validasi parameter registration_id
if (!isset($_GET['registration_id']) || !is_numeric($_GET['registration_id'])) {
    die("Parameter registration_id tidak valid.");
}

$registration_id = intval($_GET['registration_id']);

// Ambil data formulir pendaftaran berdasarkan registration_id
$form_data_query = "SELECT field_name, field_value FROM registration_data WHERE registration_id = ?";
$stmt = $db->prepare($form_data_query);
if (!$stmt) {
    die("Query gagal: " . $db->error);
}
$stmt->bind_param('i', $registration_id);
$stmt->execute();
$form_data_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Detail Pendaftaran Peserta</title>
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

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="#">Event Manager</a>
    </div>
</nav>

    </nav>

    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3>Detail Pendaftaran Peserta</h3>
            </div>
            <div class="card-body">
                <?php if ($form_data_result->num_rows > 0) : ?>
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Field Name</th>
                                <th>Field Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($form_data = $form_data_result->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($form_data['field_name']) ?></td>
                                    <td>
                                        <?php
                                        // Cek apakah field_value mengarah ke file
                                        if (strpos($form_data['field_value'], 'admin/uploads/') !== false) {
                                            $file_path = htmlspecialchars($form_data['field_value']);
                                            $file_server_path = __DIR__ . '/../' . $file_path;
                                            if (file_exists($file_server_path)) {
                                                $file_name = basename($file_path);
                                                echo '<a href="../' . $file_path . '" download>' . $file_name . '</a>';
                                            } else {
                                                echo '<span class="text-danger">File tidak ditemukan</span>';
                                            }
                                        } else {
                                            echo nl2br(htmlspecialchars($form_data['field_value']));
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="alert alert-warning">Data pendaftaran tidak ditemukan untuk ID: <?= htmlspecialchars($registration_id) ?></div>
                <?php endif; ?>
                <a href="dashboard_admin.php" class="btn btn-secondary mt-3">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
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

<?php
$stmt->close();
$db->close();
?>
