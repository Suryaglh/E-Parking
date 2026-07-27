<?php
session_start();

// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = false;
if (isset($_POST['btn_login'])) {
    $user = $_POST['username'];
    $pass = md5($_POST['password']); // Enkripsi password yang diinput

    // Koneksi ke database
    $conn = new mysqli("localhost", "root", "!Muhammadiyah1912", "sistem_anpr");

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Cek kecocokan username dan password
    $result = $conn->query("SELECT * FROM admin WHERE username='$user' AND password='$pass'");

    if ($result->num_rows === 1) {
        // Jika benar, buat sesi dan simpan nama username
        $_SESSION['login'] = true;
        $_SESSION['username'] = $user; // BARIS BARU INI DITAMBAHKAN
        header("Location: index.php");
        exit;
    } else {
        $error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ANPR System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
        .brand { text-align: center; font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 30px; }
        .brand i { color: #0d6efd; font-size: 2rem; display: block; margin-bottom: 10px; }
        .btn-login { width: 100%; font-weight: 600; padding: 10px; margin-top: 15px; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand">
        <i class="bi bi-camera-fill"></i>
        ANPR SYSTEM
    </div>
    
    <?php if($error) : ?>
        <div class="alert alert-danger text-center p-2" role="alert">
            Username atau Password salah!
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label text-muted fw-semibold">Username</label>
            <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label text-muted fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="btn_login" class="btn btn-primary btn-login">Login ke Dashboard</button>
    </form>
</div>

</body>
</html>
