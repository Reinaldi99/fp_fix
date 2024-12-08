<?php
include "service/database.php";
session_start();

$login_message = "";

if (isset($_POST['login'])) {
    $username_or_email = $_POST['username_or_email'];
    $password = $_POST['password'];
    $hash_password = hash('sha256', $password);

    // Query untuk login user
    $sql_user = "SELECT * FROM users WHERE (username='$username_or_email' OR email='$username_or_email') AND password='$hash_password' AND role='user'";
    $result_user = $db->query($sql_user);

    // Query untuk login admin
    $admin_email = "admin"; // Ganti dengan email admin yang diinginkan
    $admin_password = hash('sha256', 'admin'); // Password admin yang di-hash

    if ($result_user->num_rows > 0) {
        $data = $result_user->fetch_assoc();
        $_SESSION["username"] = $data["username"];
        $_SESSION["role"] = $data["role"];
        $_SESSION["is_login"] = $data["id"]; // Simpan ID pengguna yang login

        // Redirect ke halaman user
        header("location: dashboard.php");
        exit();
    } elseif ($username_or_email === $admin_email && $hash_password === $admin_password) {
        // Jika login sebagai admin
        $_SESSION["username"] = "Admin"; // Nama admin
        $_SESSION["role"] = "admin"; // Role admin
        $_SESSION["is_login"] = true;

        // Redirect ke halaman admin
        header("location: admin/dashboard_admin.php");
        exit();
    } else {
        $login_message = "Username, Email, atau Password salah!";
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
    <title>RSC - LOGIN</title>
    <style>
        body {
            background: linear-gradient(to right, rgba(0, 128, 255, 1), rgba(0, 255, 255, 0.8)); /* Gradient biru segar ke aqua cerah */
  color: #fff; /* Warna teks untuk kontras yang lebih baik */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
        .link-daftar {
            display: block;
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #6c757d;
        }
        .link-daftar a {
            color: #1a73e8;
            text-decoration: none;
        }
        .link-daftar a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!--card login start-->
    <div class="card">
        <div class="text-center mb-3">
            <img src="layout/logo.jpg" alt="logo" style="width: 120px; height: auto; border-radius: 50%;">
        </div>
        <h5 class="card-title">Login</h5>

        <!-- Tampilkan pesan jika login gagal -->
        <b style="color: red;"><?php echo $login_message; ?></b>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="username_or_email" class="form-label">Username atau Email</label>
                <input type="text" class="form-control" id="username_or_email" name="username_or_email" placeholder="Masukkan Username atau Email Anda" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password Anda" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="showPassword" onclick="togglePasswordVisibility()">
                <label class="form-check-label" for="showPassword">Show Password</label>
            </div>
            <button type="submit" name="login" class="btn btn-primary mt-3">Login</button>
            <div class="link-daftar">
                <p>Tidak punya akun? <a href="register.php">Daftar Sekarang</a></p>
            </div>
        <!-- </form>
        <div class="link-adminr">
                <p>Login Admin<a href="admin/login_admin.php"> ADMIN</a></p>
            </div>
        </form> -->
    </div>
    <!--card login end-->

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById("password");
            passwordField.type = passwordField.type === "password" ? "text" : "password";
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>