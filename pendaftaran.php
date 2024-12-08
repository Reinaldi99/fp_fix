<?php
session_start();
require 'service/database.php'; // Koneksi database dengan variabel $db
require_once 'payment/midtrans-php-master/Midtrans.php'; // Pastikan path ini benar


// Logout logic
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}


// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-SdGSNrMDhqUgP4KJM_0hTR3O';
\Midtrans\Config::$isProduction = false; // Sandbox mode untuk testing
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Proses pendaftaran dan pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $user_id = $_SESSION['is_login']; // Pastikan ada sesi user
        $event_id = $_POST['event_id']; // Event yang dipilih

        // Ambil data event
        $query_event = "SELECT * FROM event WHERE event_id = ?";
        $stmt = mysqli_prepare($db, $query_event);
        mysqli_stmt_bind_param($stmt, 'i', $event_id);
        mysqli_stmt_execute($stmt);
        $event_result = mysqli_stmt_get_result($stmt);
        $event = mysqli_fetch_assoc($event_result);
        mysqli_stmt_close($stmt);

        if (!$event) {
            die("Event tidak ditemukan.");
        }

        // Menyimpan data registrasi
        $query = "INSERT INTO registrations (user_id, event_id, order_id, status_pembayaran, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($db, $query);
        $order_id = uniqid('order_'); // ID unik untuk pendaftaran
        $status_pembayaran = 'Belum Dibayar'; // Default status pembayaran
        mysqli_stmt_bind_param($stmt, 'iiss', $user_id, $event_id, $order_id, $status_pembayaran);
        mysqli_stmt_execute($stmt);
        $registration_id = mysqli_insert_id($db); // Dapatkan ID pendaftaran baru
        mysqli_stmt_close($stmt);

        // Menyimpan data formulir yang diisi oleh user ke tabel 'registration_data'
        if (isset($_POST['fields']) && !empty($_POST['fields'])) {
            foreach ($_POST['fields'] as $field_name => $field_value) {
                $query_field = "INSERT INTO registration_data (registration_id, field_name, field_value) 
                                VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($db, $query_field);
                mysqli_stmt_bind_param($stmt, 'iss', $registration_id, $field_name, $field_value);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        // Menangani unggahan file jika ada
        if (isset($_FILES['fields']) && !empty($_FILES['fields']['name'])) {
            foreach ($_FILES['fields']['name'] as $field_name => $file) {
                if ($_FILES['fields']['error'][$field_name] === UPLOAD_ERR_OK) {
                    // Validasi ekstensi file
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'docx']; // Tambahkan ekstensi file yang diizinkan
                    $file_extension = pathinfo($_FILES['fields']['name'][$field_name], PATHINFO_EXTENSION);

                    if (in_array(strtolower($file_extension), $allowed_extensions)) {
                        // Tentukan direktori tujuan untuk file upload
                        $upload_dir = 'admin/uploads/';
                        $file_name = uniqid() . '.' . $file_extension; // Nama file unik
                        $target_file = $upload_dir . $file_name;

                        // Pindahkan file ke direktori tujuan
                        if (move_uploaded_file($_FILES['fields']['tmp_name'][$field_name], $target_file)) {
                            // Simpan path file di database
                            $query = "INSERT INTO registration_data (registration_id, field_name, field_value) 
                                      VALUES (?, ?, ?)";
                            $stmt = mysqli_prepare($db, $query);
                            mysqli_stmt_bind_param($stmt, 'iss', $registration_id, $field_name, $target_file);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);
                        } else {
                            echo "Gagal mengunggah file untuk field $field_name.";
                        }
                    } else {
                        echo "Format file untuk field $field_name tidak didukung.";
                    }
                }
            }
        }

        // Data pembayaran untuk Midtrans
        $biaya_pendaftaran = $event['registration_fee'];
        $transaction_details = [
            'order_id' => $order_id,
            'gross_amount' => $biaya_pendaftaran, // Total biaya
        ];

        $item_details = [
            [
                'id' => 'event_fee',
                'price' => $biaya_pendaftaran,
                'quantity' => 1,
                'name' => "Pendaftaran Event " . htmlspecialchars($event['event_name']),
            ]
        ];

        $customer_details = [
            'first_name' => $_POST['fields']['Nama'] ?? 'Tidak Diketahui', // Nama peserta
            'email' => $_POST['fields']['Email'] ?? 'email@domain.com', // Email peserta
            'phone' => $_POST['fields']['No Whatsapp'] ?? 'N/A', // Nomor HP peserta
        ];

        $transaction = [
            'transaction_details' => $transaction_details,
            'item_details' => $item_details,
            'customer_details' => $customer_details,
            'finish_redirect_url' => 'https://www.yourwebsite.com/success-page.php',
        ];

        try {
            // Buat Snap Token
            $snapToken = \Midtrans\Snap::getSnapToken($transaction);

            // Redirect ke halaman pembayaran Snap
            echo "<html><body>";
            echo "<h3>Mohon tunggu, sedang diarahkan ke halaman pembayaran...</h3>";
            echo "<script src='https://app.sandbox.midtrans.com/snap/snap.js' data-client-key='SB-Mid-client-uw81o6eb7cacAn_V'></script>";
            echo "<script>snap.pay('$snapToken');</script>";
            echo "</body></html>";
            exit;
        } catch (Exception $e) {
            echo "Gagal membuat transaksi. Error: " . $e->getMessage();
        }
    }
}

// Ambil data event dan field formulir
$event_id = $_GET['event_id'];
$query_event = "SELECT * FROM event WHERE event_id = ?";
$stmt = mysqli_prepare($db, $query_event);
mysqli_stmt_bind_param($stmt, 'i', $event_id);
mysqli_stmt_execute($stmt);
$event_result = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($event_result);
mysqli_stmt_close($stmt);

$query_fields = "SELECT * FROM event_form_fields WHERE event_id = ?";
$stmt = mysqli_prepare($db, $query_fields);
mysqli_stmt_bind_param($stmt, 'i', $event_id);
mysqli_stmt_execute($stmt);
$fields_result = mysqli_stmt_get_result($stmt);
$fields = $fields_result ? mysqli_fetch_all($fields_result, MYSQLI_ASSOC) : [];
mysqli_stmt_close($stmt);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSC - REGISTRASI LOMBA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8));
            color: #fff;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            margin-top: 20px;
        }

        .form-container {
            background: #fff;
            color: #333; /* Warna teks lebih gelap untuk kontras */
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  .navbar-nav .nav-link {
    font-weight: 500;
    font-size: 1.1rem;
  }

        .btn {
            font-size: 1rem;
            padding: 10px 0;
            border-radius: 30px;
            font-weight: bold;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .rounded-top {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .img-thumbnail {
            border: 2px solid #007bff;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }

        .form-label {
            font-weight: 500;
        }

        .card-title,
        .card-text {
            color: #333; /* Warna teks di dalam kartu */
        }

        .card-title {
            font-weight: bold;
        }

        ul {
            list-style: disc;
            margin-left: 20px;
            color: #333;
        }

        ul li {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand text-light fs-3 fw-bold" href="">RSC</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-light px-3 py-2" href="dashboard.php">HOME</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light px-3 py-2" href="dashboard.php">EVENT</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light px-3 py-2" href="dashboard.php">CONTACT</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light px-3 py-2" href="service/history.php">HISTORY</a>
                    </li>
                </ul>
                <div class="dropdown">
                    <button class="btn p-0 border-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $_SESSION['profile_picture'] ?>" alt="Profile Picture" class="img-thumbnail" style="width: 50px; height: 50px; border-radius: 50%;">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="profile/profile.php">Profile</a></li>
                        <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                    </ul>
                </div>
                <form id="logout-form" action="" method="POST" style="display: none;">
                    <input type="hidden" name="logout" value="1">
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="form-container">
                    <h1 class="mb-3"><strong>Formulir Pendaftaran <?= htmlspecialchars($event['event_name']) ?></strong></h1>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="event_id" value="<?= htmlspecialchars($event_id) ?>">

                        <?php foreach ($fields as $field): ?>
                        <div class="mb-3">
                            <label for="field_<?= htmlspecialchars($field['field_name']) ?>" class="form-label">
                                <?= htmlspecialchars($field['field_name']) ?>
                            </label>
                            <?php if ($field['field_type'] === 'file'): ?>
                            <input type="file" name="fields[<?= htmlspecialchars($field['field_name']) ?>]" id="field_<?= htmlspecialchars($field['field_name']) ?>" class="form-control">
                            <?php elseif ($field['field_type'] === 'text'): ?>
                            <input type="text" name="fields[<?= htmlspecialchars($field['field_name']) ?>]" id="field_<?= htmlspecialchars($field['field_name']) ?>" class="form-control" required>
                            <?php elseif ($field['field_type'] === 'number'): ?>
                            <input type="number" name="fields[<?= htmlspecialchars($field['field_name']) ?>]" id="field_<?= htmlspecialchars($field['field_name']) ?>" class="form-control" required>
                            <?php elseif ($field['field_type'] === 'email'): ?>
                            <input type="email" name="fields[<?= htmlspecialchars($field['field_name']) ?>]" id="field_<?= htmlspecialchars($field['field_name']) ?>" class="form-control" required>
                            <?php elseif ($field['field_type'] === 'date'): ?>
                            <input type="date" name="fields[<?= htmlspecialchars($field['field_name']) ?>]" id="field_<?= htmlspecialchars($field['field_name']) ?>" class="form-control" required>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-container">
                    <?php if (!empty($event['image_url'])): ?>
                    <img src="<?= htmlspecialchars('uploads/' . $event['image_url']) ?>" alt="Event Image" class="card-img-top rounded-top" style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <h5 class="card-title"><?= htmlspecialchars($event['event_name']) ?></h5>
                    <p class="card-text">
                        <i class="bi bi-calendar"></i> <?= htmlspecialchars($event['event_date']) ?> <br>
                        <i class="bi bi-clock"></i> <?= htmlspecialchars($event['event_time']) ?> <br>
                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['location']) ?>
                    </p>
                    <hr>
                    <div class="event-rules mb-3">
                    <p><strong>Rules:</strong></p>
                    <ul>
                        <li><?= htmlspecialchars($event['rules']) ?> </li>
                    </ul>
                    </div>
                    <hr>
                    <h6><strong>Data Pembayaran</strong> </h6>
                    <p>
                        Jenis Perlombaan: <span class="float-end"><?= htmlspecialchars($event['event_name']) ?></span><br>
                        Biaya Pendaftaran: <span class="float-end">Rp <?= number_format($event['registration_fee'], 2, ',', '.') ?></span>
                        <hr>
                        <strong>Total: <span class="float-end">Rp <?= number_format($event['registration_fee'], 2, ',', '.') ?></span></strong>
                        </p>
                    </p>
                    <button type="submit" name="register" class="btn btn-primary w-100">Proses Pendaftaran</button>
                </div>
            </div>
            </form>
        </div>
    </div>
    <?php include "layout/footer.html"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

