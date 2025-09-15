<?php
// koneksi.php
$host = '127.0.0.1';
$db   = 'absensi_smk';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $koneksi = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Jika database belum ada, seed.php akan membuatnya.
    die('Database connection failed: ' . $e->getMessage());
}

if (session_status() == PHP_SESSION_NONE) session_start();
?>
