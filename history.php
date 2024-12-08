<?php
session_start();

// Cek apakah user sudah login
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit();
// }

// Koneksi ke database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'fpp';
$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    die("Koneksi gagal: " . $connection->connect_error);
}

// Ambil user_id dari session
$user_id = $_SESSION['is_login'];

// Query untuk mendapatkan daftar bukti pembayaran
$query = "
    SELECT 
        registrations.order_id,
        event.event_name,
        registrations.status_pembayaran,
        event.event_date,
        event.event_time
    FROM 
        registrations
    JOIN 
        event ON registrations.event_id = event.event_id
    WHERE 
        registrations.user_id = ?
    ORDER BY 
        registrations.created_at DESC
";
$stmt = $connection->prepare($query);
if (!$stmt) {
    die("Query gagal: " . $connection->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSC - HISTORY PEMBAYARAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8));
            color: #fff;
            min-height: 100vh;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            padding: 15px;
        }
        .table th, .table td {
            vertical-align: middle;
            padding: 12px;
        }
        .table th {
            background-color: #0056b3;
            color: white;
        }
        .btn-back {
            margin-top: 20px;
            background-color: #007bff;
            color: white;
        }
        .btn-back:hover {
            background-color: #0056b3;
            color: white;
        }
        .btn-view {
            background-color: white;
            color: #007bff;
            border: 1px solid #007bff;
        }
        .btn-view:hover {
            background-color: #e2e6ea;
            color: #0056b3;
        }
        @media print {
            body {
                background: none;
                color: black;
            }
            .card {
                box-shadow: none;
                border: none;
            }
            .btn {
                display: none; /* Hide buttons when printing */
            }
            .table th, .table td {
                border: 1px solid black;
            }
            .table {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    
    <div class="container my-5">
        <div class="card">
            <div class="card-header">
                History Bukti Pembayaran
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Event</th>
                                <th>Status Pembayaran</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['order_id']) ?></td>
                                        <td><?= htmlspecialchars($row['event_name']) ?></td>
                                        <td><?= htmlspecialchars($row['status_pembayaran']) ?></td>
                                        <td><?= htmlspecialchars(date("d-m-Y", strtotime($row['event_date']))) ?></td>
                                        <td><?= htmlspecialchars(date("H:i", strtotime($row['event_time']))) ?></td>
                                        <td>
                                            <a href="bukti-pembayaran.php?order_id=<?= urlencode($row['order_id']) ?>" class="btn btn-view btn-sm">Lihat Bukti</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada bukti pembayaran.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                            </div>
                <div class="text-end">
                    <a href="../dashboard.php" class="btn btn-secondary btn-back">Kembali</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html> 


<?php
// Tutup koneksi
$stmt->close();
$connection->close();
?>