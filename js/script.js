// app.js - validasi ringan & tampilan waktu real-time
document.addEventListener('DOMContentLoaded', function() {
    // waktu real-time untuk siswa.php
    const waktuInput = document.getElementById('waktu');
    if (waktuInput) {
        function updateWaktu() {
            const now = new Date();
            const jam = String(now.getHours()).padStart(2, '0');
            const menit = String(now.getMinutes()).padStart(2, '0');
            const detik = String(now.getSeconds()).padStart(2, '0');
            waktuInput.value = `${jam}:${menit}:${detik}`;
        }
        setInterval(updateWaktu, 1000);
        updateWaktu();
    }

    // validasi form login sederhana
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const u = loginForm.querySelector('input[name="username"]').value.trim();
            const p = loginForm.querySelector('input[name="password"]').value.trim();
            if (!u || !p) {
                e.preventDefault();
                alert('Username dan password harus diisi.');
            }
        });
    }

    // Konfirmasi sebelum hapus (semua link dengan onclick di file sudah ada confirm)
});
