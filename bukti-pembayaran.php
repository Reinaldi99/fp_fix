<?php
// Koneksi ke database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'fpp'; // Sesuaikan dengan nama database Anda
$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    die("Koneksi gagal: " . $connection->connect_error);
}

// Ambil data dari URL
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

// Validasi order_id
if (!$order_id) {
    die("Order ID tidak ditemukan.");
}

// Query untuk mendapatkan data bukti pembayaran dari tabel registrations
$query = "
    SELECT 
        users.email,
        registrations.status_pembayaran,
        event.event_name,
        registrations.order_id,
        event.event_date,
        event.event_time
    FROM 
        registrations
    JOIN 
        users ON registrations.user_id = users.id
    JOIN 
        event ON registrations.event_id = event.event_id
    WHERE 
        registrations.order_id = ? 
        AND registrations.status_pembayaran = 'Berhasil'  -- Sesuaikan dengan status pembayaran yang valid
";

// Persiapkan dan jalankan query dengan menggunakan prepared statement untuk keamanan
$stmt = $connection->prepare($query);
if (!$stmt) {
    die("Query gagal: " . $connection->error);
}
$stmt->bind_param('s', $order_id); // 's' untuk parameter string (order_id)
$stmt->execute();
$result = $stmt->get_result();

// Debugging: Menampilkan hasil query untuk memastikan ada data yang ditemukan
if ($result->num_rows == 0) {
    // Cek apakah order_id ada di tabel registrations dengan status pembayaran selain 'Berhasil'
    $check_query = "SELECT * FROM registrations WHERE order_id = ?";
    $check_stmt = $connection->prepare($check_query);
    $check_stmt->bind_param('s', $order_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        echo "Order ID ditemukan tetapi status pembayaran belum berhasil.<br>";
    } else {
        echo "Order ID tidak ditemukan di tabel registrations.<br>";
    }
    die("Bukti pembayaran tidak ditemukan atau pembayaran belum lunas.");
}

// Ambil data dari hasil query
$data = $result->fetch_assoc();

// Panggil library barcode
require '../vendor/autoload.php';
use Picqer\Barcode\BarcodeGeneratorPNG;

$generator = new BarcodeGeneratorPNG();
$barcode = $generator->getBarcode($data['order_id'], $generator::TYPE_CODE_128);

// Simpan barcode sebagai gambar
$barcodeImage = 'data:image/png;base64,' . base64_encode($barcode);

// Menampilkan bukti pembayaran
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8));
            color: black;
        }
        
        .receipt {
            border: 1px solid #007bff;
            border-radius: 10px;
            padding: 30px;
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .receipt h1 {
            font-size: 26px;
            margin-bottom: 20px;
            color: #007bff;
            text-align: center;
        }
        .receipt hr {
            border: 1px solid #007bff;
            margin: 20px 0;
        }
        .receipt p {
            margin: 10px 0;
            font-size: 16px;
            line-height: 1.5;
        }
        .receipt strong {
            color: #333;
        }
        .barcode {
            margin: 20px 0;
            text-align: center;
        }
        .btn-container {
            display: flex;
            justify-content: center; /* Menyusun tombol secara horizontal dan di tengah */
            margin-top: 20px;
        }
        .btn-back, .btn-print {
            padding: 12px 25px;
            border-radius: 25px; /* Rounded button */
            font-size: 16px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s, transform 0.3s;
            border: none; /* Menghilangkan outline */
            outline: none; /* Menghilangkan outline saat fokus */
            cursor: pointer; /* Menambahkan pointer saat hover */
        }
        .btn-back {
            background-color: green; /* Mengubah warna tombol kembali menjadi hijau */
            color: white;
            max-width: 200px; /* Set a max width */
        }
        .btn-back:hover {
            background-color: darkgreen; /* Warna saat hover */
            transform: scale(1.05);
        }
        .btn-print {
            background-color: #007bff; /* Mengubah warna tombol cetak menjadi biru tua */
            color: white; /* Mengubah warna tulisan menjadi putih */
        }
        .btn-print:hover {
            background-color: #0056b3; /* Warna saat hover */
            transform: scale(1.05);
        }
    </style>
</head>
<body>
<div class="receipt" id="receipt">
    <h1>Bukti Pembayaran Lomba</h1>
    <hr>
    <p><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>
    <p><strong>Status Pembayaran:</strong> <?= ucfirst(htmlspecialchars($data['status_pembayaran'])) ?></p>
    <p><strong>Lomba:</strong> <?= htmlspecialchars($data['event_name']) ?></p>
    <p><strong>Order ID:</strong> <?= htmlspecialchars($data['order_id']) ?></p>
    <p><strong>Tanggal Lomba:</strong> <?= htmlspecialchars($data['event_date']) ?></p>
    <p><strong>Waktu:</strong> <?= htmlspecialchars($data['event_time']) ?></p>
    <div class="barcode">
        <img src="<?= $barcodeImage ?>" alt="Barcode" />
    </div>
    <hr>
    <p>Terima kasih telah mendaftar!</p>
</div>

<!-- Button container for printing and going back -->
<div class="btn-container">
    <button id="printBtn" class="btn-print">Cetak Bukti Pembayaran</button>
    <a href="../dashboard.php" class="btn-back">Kembali ke Dashboard</a>
</div>

<script>
    document.getElementById('printBtn').addEventListener('click', function () {
        const receiptContent = document.getElementById('receipt').outerHTML;
        const printWindow = window.open('', '_blank');
        printWindow.document.open();
        printWindow.document.write(`
            <html>
            <head>
                <title>Cetak Bukti Pembayaran</title>
                <style>
                    body { font-family: 'Arial', sans-serif; margin: 0; padding: 20px; }
                    .receipt { border: 1px solid #007bff; padding: 30px; }
                    h1 { text-align: center; color: #007bff; }
                    hr { border: 1px solid #007bff; }
                    .barcode { text-align: center; margin-top: 20px; }
                </style>
            </head>
            <body>
                ${receiptContent}
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    });
</script>
</body>
</html>

<?php
// Menutup koneksi
$stmt->close();
$connection->close();
?>