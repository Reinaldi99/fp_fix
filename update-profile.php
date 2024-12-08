<?php
session_start();
require '../service/database.php'; // Pastikan koneksi database benar

// Pastikan user sudah login
if (!isset($_SESSION['is_login'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['is_login']; // ID user yang sedang login

// Ambil data dari form
$username = $_POST['username'];
$email = $_POST['email'];
$phone_number = $_POST['phone_number'];
$profile_picture_path = null;

// Tentukan direktori upload
$targetDir = "../uploads/"; // Direktori tempat menyimpan file

// Debugging awal
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true); // Buat direktori jika belum ada
}

// Handle file upload jika ada file baru
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['profile_picture']['name']);
    $targetFilePath = $targetDir . uniqid() . '_' . $fileName; // Nama file unik

    // Validasi file upload (hanya menerima gambar)
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            $profile_picture_path = $targetFilePath; // Path file yang diunggah
        } else {
            die('Gagal mengupload foto profil.');
        }
    } else {
        die('Format file tidak didukung.');
    }
}

// Update tabel users
$query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("ssi", $username, $email, $user_id);
$stmt->execute();
$stmt->close();

// Update atau insert ke tabel user_profiles
if ($profile_picture_path) {
    // Jika ada file baru, perbarui semuanya
    $query = "
        INSERT INTO user_profiles (user_id, phone_number, profile_picture)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE phone_number = ?, profile_picture = ?
    ";
    $stmt = $db->prepare($query);
    $stmt->bind_param("issss", $user_id, $phone_number, $profile_picture_path, $phone_number, $profile_picture_path);
} else {
    // Jika tidak ada file baru, hanya perbarui nomor telepon
    $query = "
        INSERT INTO user_profiles (user_id, phone_number)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE phone_number = ?
    ";
    $stmt = $db->prepare($query);
    $stmt->bind_param("iss", $user_id, $phone_number, $phone_number);
}
$stmt->execute();
$stmt->close();

// Perbarui session dengan data baru
$_SESSION["username"] = $username;
if ($profile_picture_path) {
    $_SESSION["profile_picture"] = $profile_picture_path;
}

// Tutup koneksi
$db->close();

// Redirect ke halaman profil
header('Location: ../dashboard.php'); // Redirect ke halaman profile setelah update
exit;
?>
