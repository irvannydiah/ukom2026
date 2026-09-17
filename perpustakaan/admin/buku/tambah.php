<?php
require_once __DIR__ . '/../../includes/auth.php';
cekRole('admin');
require_once __DIR__ . '/../../includes/fungsi.php';

$halaman_aktif = 'buku';
$error   = '';
$success = '';

// Ambil semua kategori untuk pilihan dropdown
$kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & bersihkan input
    $id_kategori  = (int)$_POST['id_kategori'];
    $judul        = bersihkan($_POST['judul']);
    $penulis      = bersihkan($_POST['penulis']);
    $penerbit     = bersihkan($_POST['penerbit']);
    $tahun_terbit = (int)$_POST['tahun_terbit'];
    $isbn         = bersihkan($_POST['isbn']);
    $stok         = (int)$_POST['stok'];
    $deskripsi    = bersihkan($_POST['deskripsi']);

    // ===== VALIDASI =====
    if (!$id_kategori || empty($judul) || empty($penulis) || $stok < 0) {
        $error = 'Kategori, judul, penulis, dan stok wajib diisi. Stok minimal 0.';
    } else {
        // Status buku: tersedia jika stok > 0, dipinjam jika stok = 0
        $status = ($stok > 0) ? 'tersedia' : 'dipinjam';

        $query = "INSERT INTO buku
                    (id_kategori, judul, penulis, penerbit, tahun_terbit, isbn, stok, status, deskripsi)
                  VALUES
                    ('$id_kategori','$judul','$penulis','$penerbit','$tahun_terbit',
                     '$isbn','$stok','$status','$deskripsi')";

        if (mysqli_query($koneksi, $query)) {
            header('Location: index.php?pesan=' . urlencode('Buku berhasil ditambahkan!') . '&tipe=success');
            exit;
        } else {
            $error = 'Gagal menyimpan: ' . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Buku - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/../../includes/sidebar_admin.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.php">Data Buku</a>
                    </li>
                    <li class="breadcrumb-item active">Tambah Buku</li>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">Tambah Buku Baru</h4>
        </div>

        <?php if ($error): ?>
            <?= tampilkanAlert('danger', $error) ?>
        <?php endif; ?>

        <div class="card border-0 shadow-sm" style="max-width:700px;">
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Judul Buku <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required
                                   value="<?= isset($judul) ? htmlspecialchars($judul) : '' ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Penulis <span class="text-danger">*</span></label>
                            <input type="text" name="penulis" class="form-control" required
                                   value="<?= isset($penulis) ? htmlspecialchars($penulis) : '' ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Penerbit</label>
                            <input type="text" name="penerbit" class="form-control"
                                   value="<?= isset($penerbit) ? htmlspecialchars($penerbit) : '' ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select name="id_kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                                    <option value="<?= $kat['id_kategori'] ?>"
                                        <?= (isset($id_kategori) && $id_kategori == $kat['id_kategori']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tahun Terbit</label>
                            <input type="number" name="tahun_terbit" class="form-control"
                                   min="1900" max="<?= date('Y') ?>"
                                   value="<?= isset($tahun_terbit) && $tahun_terbit ? $tahun_terbit : date('Y') ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stok" class="form-control" min="0" required
                                   value="<?= isset($stok) ? $stok : 1 ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">ISBN</label>
                            <input type="text" name="isbn" class="form-control"
                                   placeholder="contoh: 9786020123456"
                                   value="<?= isset($isbn) ? htmlspecialchars($isbn) : '' ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Deskripsi / Sinopsis</label>
                            <textarea name="deskripsi" class="form-control" rows="4"
                                      placeholder="Tulis sinopsis atau deskripsi buku..."
                            ><?= isset($deskripsi) ? htmlspecialchars($deskripsi) : '' ?></textarea>
                        </div>

                        <div class="col-12 d-flex gap-2 mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan Buku
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
