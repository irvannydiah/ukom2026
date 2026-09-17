<?php
require_once __DIR__ . '/../../includes/auth.php';
cekRole('admin');
require_once __DIR__ . '/../../includes/fungsi.php';

$id_buku = (int)($_GET['id'] ?? 0);

if (!$id_buku) {
    header('Location: index.php?pesan=' . urlencode('ID tidak valid.') . '&tipe=danger');
    exit;
}

// Cek apakah buku masih aktif dipinjam (status peminjaman = dipinjam atau diproses)
$cekAktif = mysqli_query($koneksi,
    "SELECT id_peminjaman FROM peminjaman
     WHERE id_buku = $id_buku
       AND status IN ('dipinjam', 'diproses', 'diajukan')
     LIMIT 1");

if (mysqli_num_rows($cekAktif) > 0) {
    header('Location: index.php?pesan=' . urlencode('Buku tidak bisa dihapus karena masih ada peminjaman aktif.') . '&tipe=danger');
    exit;
}

// Hapus buku
if (mysqli_query($koneksi, "DELETE FROM buku WHERE id_buku = $id_buku")) {
    header('Location: index.php?pesan=' . urlencode('Buku berhasil dihapus.') . '&tipe=success');
} else {
    header('Location: index.php?pesan=' . urlencode('Gagal menghapus buku.') . '&tipe=danger');
}
exit;
