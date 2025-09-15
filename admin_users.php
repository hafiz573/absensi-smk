<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: index.php'); exit; }

$err = '';

// Ambil data kelas untuk dropdown
$kelas_list = $koneksi->query('SELECT id, nama_kelas FROM kelas ORDER BY nama_kelas')->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tambah user
    if (isset($_POST['tambah'])) {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        $nama = trim($_POST['nama_lengkap'] ?? '');
        $kelas_id = isset($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null;
        if ($username==='' || $password==='' || $role==='' || $nama==='') $err='Semua field harus diisi';
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare('INSERT INTO users (username,password,role,nama_lengkap) VALUES (:u,:p,:r,:n)');
            try {
                $stmt->execute([':u'=>$username, ':p'=>$hash, ':r'=>$role, ':n'=>$nama]);
                $user_id = $koneksi->lastInsertId();
                // Jika siswa, tambahkan ke tabel siswa
                if ($role === 'siswa' && $kelas_id) {
                    // Generate NIS unik, misal: NIS + user_id (atau random 5 digit)
                    $nis = 'NIS' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
                    $stmt2 = $koneksi->prepare('INSERT INTO siswa (user_id, nis, kelas_id) VALUES (:user_id, :nis, :kelas_id)');
                    $stmt2->execute([':user_id'=>$user_id, ':nis'=>$nis, ':kelas_id'=>$kelas_id]);
                }
                header('Location: admin_users.php'); exit;
            } catch (PDOException $e) {
                $err = 'Error: ' . $e->getMessage();
            }
        }
    }

    // Edit user (tanpa ubah password jika kosong)
    if (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';
        $nama = trim($_POST['nama_lengkap'] ?? '');
        $kelas_id = isset($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null;
        if ($username==='' || $role==='' || $nama==='') $err='Field username, role, dan nama harus diisi';
        else {
            if ($password!=='') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $koneksi->prepare('UPDATE users SET username=:u, password=:p, role=:r, nama_lengkap=:n WHERE id=:id');
                $stmt->execute([':u'=>$username, ':p'=>$hash, ':r'=>$role, ':n'=>$nama, ':id'=>$id]);
            } else {
                $stmt = $koneksi->prepare('UPDATE users SET username=:u, role=:r, nama_lengkap=:n WHERE id=:id');
                $stmt->execute([':u'=>$username, ':r'=>$role, ':n'=>$nama, ':id'=>$id]);
            }
            // Jika siswa, update/insert ke tabel siswa
            if ($role === 'siswa' && $kelas_id) {
                $cek = $koneksi->prepare('SELECT COUNT(*) FROM siswa WHERE user_id=:user_id');
                $cek->execute([':user_id'=>$id]);
                if ($cek->fetchColumn() > 0) {
                    $stmt2 = $koneksi->prepare('UPDATE siswa SET kelas_id=:kelas_id WHERE user_id=:user_id');
                    $stmt2->execute([':kelas_id'=>$kelas_id, ':user_id'=>$id]);
                } else {
                    $nis = 'NIS' . str_pad($id, 5, '0', STR_PAD_LEFT);
                    $stmt2 = $koneksi->prepare('INSERT INTO siswa (user_id, nis, kelas_id) VALUES (:user_id, :nis, :kelas_id)');
                    $stmt2->execute([':user_id'=>$id, ':nis'=>$nis, ':kelas_id'=>$kelas_id]);
                }
            }
            header('Location: admin_users.php'); exit;
        }
    }
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $koneksi->prepare('DELETE FROM users WHERE id=:id');
    $stmt->execute([':id'=>$id]);
    header('Location: admin_users.php'); exit;
}

$users = $koneksi->query('SELECT * FROM users ORDER BY id')->fetchAll();

// Untuk edit: ambil data siswa jika role siswa
$siswa_kelas = [];
foreach ($users as $u) {
    if ($u['role'] === 'siswa') {
        $sid = $u['id'];
        $row = $koneksi->query("SELECT kelas_id FROM siswa WHERE user_id=$sid")->fetch(PDO::FETCH_ASSOC);
        if ($row) $siswa_kelas[$sid] = $row['kelas_id'];
    }
}
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Users</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="container">
    <h2>Kelola Users</h2>
    <?php if($err):?><p class="error"><?=htmlspecialchars($err)?></p><?php endif;?>
    <form method="POST">
        <input type="hidden" name="id" id="user_id">
        <label>Username<input type="text" name="username" id="username" required></label>
        <label>Password (kosongkan jika tidak diubah)<input type="password" name="password" id="password"></label>
        <label>Role
            <select name="role" id="role" required>
                <option value="admin">Admin</option>
                <option value="guru">Guru</option>
                <option value="siswa">Siswa</option>
            </select>
        </label>
        <label>Nama Lengkap<input type="text" name="nama_lengkap" id="nama_lengkap" required></label>
        <label id="kelas_label" style="display:none;">Kelas
            <select name="kelas_id" id="kelas_id">
                <option value="">Pilih Kelas</option>
                <?php foreach($kelas_list as $k): ?>
                    <option value="<?=$k['id']?>"><?=htmlspecialchars($k['nama_kelas'])?></option>
                <?php endforeach;?>
            </select>
        </label>
        <button type="submit" name="tambah">Tambah</button>
        <button type="submit" name="edit">Simpan Perubahan</button>
    </form>

    <hr>
    <table class="table">
        <thead><tr><th>No</th><th>Username</th><th>Role</th><th>Nama</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php foreach($users as $i=>$u): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?=htmlspecialchars($u['username'])?></td>
                    <td><?=htmlspecialchars($u['role'])?></td>
                    <td><?=htmlspecialchars($u['nama_lengkap'])?></td>
                    <td>
                        <a href="#" class="edit-user"
                           data-id="<?=$u['id']?>"
                           data-username="<?=htmlspecialchars($u['username'], ENT_QUOTES)?>"
                           data-role="<?=$u['role']?>"
                           data-nama="<?=htmlspecialchars($u['nama_lengkap'], ENT_QUOTES)?>"
                           data-kelas="<?=isset($siswa_kelas[$u['id']]) ? $siswa_kelas[$u['id']] : ''?>"
                        >Edit</a> |
                        <a href="admin_users.php?hapus=<?=$u['id']?>" onclick="return confirm('Hapus user ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
    <br><a href="admin.php">Kembali</a>
</div>
<script>
function showKelasField(show) {
    document.getElementById('kelas_label').style.display = show ? '' : 'none';
    if (!show) document.getElementById('kelas_id').value = '';
}
document.getElementById('role').addEventListener('change', function() {
    showKelasField(this.value === 'siswa');
});
document.querySelectorAll('.edit-user').forEach(b=>{
    b.addEventListener('click', e=>{
        e.preventDefault();
        document.getElementById('user_id').value = b.getAttribute('data-id');
        document.getElementById('username').value = b.getAttribute('data-username');
        document.getElementById('role').value = b.getAttribute('data-role');
        document.getElementById('nama_lengkap').value = b.getAttribute('data-nama');
        document.getElementById('password').value = '';
        showKelasField(b.getAttribute('data-role') === 'siswa');
        if (b.getAttribute('data-role') === 'siswa') {
            document.getElementById('kelas_id').value = b.getAttribute('data-kelas') || '';
        } else {
            document.getElementById('kelas_id').value = '';
        }
    });
});
// Tampilkan field kelas jika role siswa saat load
window.addEventListener('DOMContentLoaded', function() {
    showKelasField(document.getElementById('role').value === 'siswa');
});
</script>
</body>
</html>
