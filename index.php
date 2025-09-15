<?php
require 'koneksi.php';
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') header('Location: admin.php');
    if ($_SESSION['role'] === 'guru') header('Location: guru.php');
    if ($_SESSION['role'] === 'siswa') header('Location: siswa.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - Absensi</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="center-card">
    <form id="loginForm" action="proses_login.php" method="POST" novalidate>
        <h2>Login</h2>
        <label>Username
            <input type="text" name="username" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit">Masuk</button>
    </form>
</div>
<script src="js/app.js"></script>
</body>
</html>
