<?php
require 'koneksi.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: index.php'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tambah'])) {
        $nama = trim($_POST['nama_kelas'] ?? '');
        if ($nama==='') $err='Nama kelas harus diisi';
        else {
            $stmt = $koneksi->prepare('INSERT INTO kelas (nama_kelas) VALUES (:n)');
            $stmt->execute([':n'=>$nama]);
            header('Location: admin_kelas.php'); exit;
        }
    } elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $nama = trim($_POST['nama_kelas'] ?? '');
        if ($nama==='') $err='Nama kelas harus diisi';
        else {
            $stmt = $koneksi->prepare('UPDATE kelas SET nama_kelas=:n WHERE id=:id');
            $stmt->execute([':n'=>$nama, ':id'=>$id]);
            header('Location: admin_kelas.php'); exit;
        }
    }
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $koneksi->prepare('DELETE FROM kelas WHERE id=:id');
    $stmt->execute([':id'=>$id]);
    header('Location: admin_kelas.php'); exit;
}

$kelas = $koneksi->query('SELECT * FROM kelas ORDER BY id')->fetchAll();
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Kelas</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="container">
    <h2>Kelola Kelas</h2>
    <?php if($err):?><p class="error"><?=htmlspecialchars($err)?></p><?php endif;?>
    <form method="POST">
        <input type="hidden" name="id" id="kelas_id">
        <label>Nama Kelas<input type="text" name="nama_kelas" id="nama_kelas" required></label>
        <button type="submit" name="tambah">Tambah</button>
        <button type="submit" name="edit">Simpan Perubahan</button>
    </form>
    <hr>
    <table class="table">
        <thead><tr><th>No</th><th>Nama Kelas</th><th>Aksi</th></tr></thead>
        <tbody>
            <?php foreach($kelas as $i=>$k): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?=htmlspecialchars($k['nama_kelas'])?></td>
                    <td>
                        <a href="#" class="edit-btn" data-id="<?=$k['id']?>" data-nama="<?=htmlspecialchars($k['nama_kelas'], ENT_QUOTES)?>">Edit</a> |
                        <a href="admin_kelas.php?hapus=<?=$k['id']?>" onclick="return confirm('Hapus kelas ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
    <br><a href="admin.php">Kembali</a>
</div>
<script>
document.querySelectorAll('.edit-btn').forEach(b=>{
    b.addEventListener('click', e=>{
        e.preventDefault();
        document.getElementById('kelas_id').value = b.getAttribute('data-id');
        document.getElementById('nama_kelas').value = b.getAttribute('data-nama');
    });
});
</script>
</body>
</html>
