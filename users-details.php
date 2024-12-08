<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fpp";

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data dari tabel users
$sql = "SELECT id, username, email, role, created_at FROM users";
$result = $conn->query($sql);

$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User</title>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

    <style>
      body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8)); /* Gradient biru segar ke aqua cerah */
    color: #333; /* Warna teks untuk latar belakang yang lebih gelap */
}

           /* A/* Pastikan partikel berada di latar belakang halaman */
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

        /* Kontainer yang memungkinkan scroll */
        .container {
            margin-top: 50px;
            overflow-y: auto; /* Memungkinkan scroll pada kontainer */
            height: calc(100vh - 60px); /* Membuat area kontainer bisa digulir */
        }

        /* Desain kontainer lainnya */
        .event-container, .add-event-container {
            background-color: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .event-container:hover, .add-event-container:hover {
            transform: scale(1.02);
            box-shadow: 0 0 25px rgba(0, 123, 255, 0.3);
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            background: #f8f9fa; /* Warna latar belakang yang lebih lembut */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            color: #333; /* Warna teks yang lebih gelap untuk kontras */
            font-weight: 700;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        table thead th {
            background: rgba(0, 123, 255, 0.8); /* Warna biru dengan transparansi */
            color: #fff;
            padding: 12px 15px;
            text-align: left;
        }

        table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            color: #333; /* Warna teks yang lebih gelap untuk kontras */
        }

        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        table tbody tr:hover {
            background: #e2e6ea; /* Warna hover yang lebih halus */
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.85rem;
            color: #666;
        }

        /* Styling untuk elemen DataTables */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5em background: #007BFF; /* Warna latar belakang untuk pagination */
            color: #fff; /* Warna teks untuk pagination */
            border-radius: 5px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #0056b3; /* Warna hover untuk pagination */
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background: #e9ecef; /* Warna latar belakang untuk input */
            color: #333; /* Warna teks untuk input */
            border: 1px solid #ced4da; /* Border untuk input */
            border-radius: 5px;
            padding: 0.5em;
        }

        .dataTables_wrapper .dataTables_length select {
            margin-right: 10px; /* Jarak antara select dan label */
        }

        .dataTables_wrapper .dataTables_filter input {
            margin-left: 10px; /* Jarak antara input dan label */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📊 Data User</h2>
        <table id="users-table" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data untuk ditampilkan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="footer">
            &copy; <?php echo date('Y'); ?> Data User. All rights reserved.
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


    <script>
        $(document).ready(function () {
            $('#users-table').DataTable({
                paging: true,       // Aktifkan paginasi
                searching: true,    // Aktifkan pencarian
                ordering: true,     // Aktifkan pengurutan
                responsive: true,   // Tabel responsif
                language: {
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Tidak ada data yang ditemukan",
                    info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                    infoEmpty: "Tidak ada data tersedia",
                    infoFiltered: "(difilter dari total _MAX_ data)",
                    search: "Cari:",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });
        });
    </script>
</body>
</html>