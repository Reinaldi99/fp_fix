<?php
require_once '../payment/midtrans-php-master/Midtrans.php';

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-SdGSNrMDhqUgP4KJM_0hTR3O';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Ambil notifikasi dari Midtrans
$json_str = file_get_contents('php://input');
$notification = json_decode($json_str);

$order_id = $notification->order_id;
$transaction_status = $notification->transaction_status;

include '../service/database.php'; // Koneksi ke database

// Update status pembayaran berdasarkan notifikasi
if ($transaction_status == 'settlement') {
    $status = 'success';
} elseif ($transaction_status == 'pending') {
    $status = 'pending';
} elseif ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
    $status = 'failure';
} else {
    $status = 'unknown';
}

$sql = "UPDATE tlb SET status_pembayaran = '$status' WHERE order_id = '$order_id'";
mysqli_query($db, $sql);

// Tutup koneksi database
mysqli_close($db);

http_response_code(200); // Beri respons sukses ke Midtrans
?>
