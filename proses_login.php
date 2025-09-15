<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    die('Username dan password harus diisi. <a href="index.php">Kembali</a>');
}

$stmt = $koneksi->prepare('SELECT * FROM users WHERE username = :u LIMIT 1');
$stmt->execute([':u'=>$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

    if ($user['role'] === 'admin') header('Location: admin.php');
    if ($user['role'] === 'guru') header('Location: guru.php');
    if ($user['role'] === 'siswa') header('Location: siswa.php');
    exit;
} else {
    echo 'Username atau password salah. <a href="index.php">Kembali</a>';
}
