<?php
include "service/database.php";

$register_message = "";

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi email
    if (strpos($email, '@mhs.ubpkarawang.ac.id') === false) {
        $register_message = "Email harus menggunakan domain @mhs.ubpkarawang.ac.id";
    } else {
        // Hash password
        $hash_password = hash("sha256", $password);

        try {
            // Siapkan statement
            $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hash_password);

            // Eksekusi statement
            if ($stmt->execute()) {
                $register_message = "Daftar akun berhasil, silahkan login";
            } else {
                $register_message = "Daftar akun gagal, silahkan ulangi!";
            }

            // Tutup statement
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            $register_message = "Username sudah digunakan atau terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="layout/logo.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>RSC - REGISTRASI</title>
    <style>
        body {
  background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8)); /* Gradient biru segar ke aqua cerah */
  color: #fff; /* Warna teks untuk kontras yang lebih baik */

            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
            font-family: Arial, sans-serif;
        }
        .card {
            background-color: #f8f9fa;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            padding: 20px;
            max-width: 400px;
            width: 100%;
        }
        .card-title {
            color: #1a73e8;
            font-weight: bold;
            text-align: center;
            font-size: 24px;
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
            background: linear-gradient(to right, #0066cc, #00bfff);
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            width: 100%;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background: linear-gradient(to right, #0066cc, #00bfff);
        }
        .link-login {
            display: block;
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #6c757d;
        }
        .link-login a {
            color: #1a73e8;
            text-decoration: none;
        }
        .link-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="text-center mb-3">
            <img src="layout/logo.jpg" alt="logo" style="width: 120px; height: auto; border-radius: 50%;">
        </div>
        <h5 class="card-title">Registrasi</h5>
        <b><?= $register_message ?> </b>
        <form action="register.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" placeholder="Masukkan username" required />
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" class="form-control" name="email" placeholder="Masukkan email (Wajib Gunakan Email UBP)" required />
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Buat kata sandi" required />
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="showPassword" onclick="togglePasswordVisibility()">
                <label class="form-check-label" for="showPassword">Tampilkan Kata Sandi</label>
            </div>

            <button type="submit" name="register" class="btn btn-primary mt-3">Daftar Sekarang</button>
            <div class="link-login">
                <p>Sudah punya akun? <a href="login.php">Masuk</a></p>
            </div>
        </form>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById("password");
            const type = passwordField.type === "password" ? "text" : "password";
            passwordField.type = type;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>