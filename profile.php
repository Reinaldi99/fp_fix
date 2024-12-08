<?php
session_start();
require '../service/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['is_login'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['is_login']; // ID user yang sedang login

// Query untuk mendapatkan data user
$query = "
    SELECT u.username, u.email, p.profile_picture, p.phone_number 
    FROM users u 
    LEFT JOIN user_profiles p ON u.id = p.user_id 
    WHERE u.id = ?
";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Path foto profil
$defaultProfilePicture = "../layout/profil-default.jpg";
$profilePicture = !empty($user['profile_picture']) ? $user['profile_picture'] : $defaultProfilePicture;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8));
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }
        .profile-card {
            background-color: #f8f9fa;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            padding: 20px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .profile-img {
    width: 120px; /* Ukuran lebar tetap */
    height: 120px; /* Ukuran tinggi tetap */
    border-radius: 50%; /* Membuat gambar menjadi lingkaran */
    margin-bottom: 20px;
    border: 4px solid #1a73e8;
    object-fit: cover; /* Mengatur agar gambar terpotong dan proporsi tetap */
}
        .card-title {
            color: #1a73e8;
            font-weight: bold;
            font-size: 24px;
        }
        .form-group {
            text-align: left; /* Mengatur posisi teks ke kiri */
        }
        .form-label {
            color: #1a73e8;
            font-size: 14px;
        }
        .form-control {
            border-radius: 10px;
            padding: 10px;
            border: 1px solid silver;
        }
        .form-control:focus {
            border-color: #155bb5;
            box-shadow: 0 0 5px rgba(26, 115, 232, 0.5);
        }
        .btn-primary {
            background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8));
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            width: 100%;
        }
        .btn-primary:hover {
            background: linear-gradient(to right, #0056b3, #0096dc);
        }
    </style>
</head>
<body>
    <div class="profile-card">
    <img src="<?= $profilePicture . '?' . time(); ?>" alt="Foto Profil" class="profile-img">
        <form action="update-profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']); ?>" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']); ?>" class="form-control" required>
            </div>
            <div class="form-group mb-3">
                <label for="phone_number" class="form-label">Nomor WhatsApp:</label>
                <input type="text" name="phone_number" id="phone_number" value="<?= htmlspecialchars($user['phone_number'] ?? ''); ?>" class="form-control">
            </div>
            <div class="form-group mb-3">
                <label for="profile_picture" class="form-label">Ganti Foto Profil:</label>
                <input type="file" name="profile_picture" id="profile_picture" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary mt-3">Simpan Perubahan</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


