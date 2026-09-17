<?php
require_once __DIR__ . '/../../includes/auth.php';
cekRole('admin');
require_once __DIR__ . '/../../includes/fungsi.php';

$halaman_aktif = 'buku';

// Pesan notifikasi dari halaman lain (tambah/edit/hapus)
$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe']  ?? 'success';

// ===== PENCARIAN =====
$cari  = bersihkan($_GET['cari'] ?? '');
$where = '';
if ($cari !== '') {
    $where = "WHERE b.judul LIKE '%$cari%'
               OR b.penulis LIKE '%$cari%'
               OR k.nama_kategori LIKE '%$cari%'";
}

// ===== AMBIL DATA BUKU =====
$query = "SELECT b.*, k.nama_kategori
          FROM buku b
          JOIN kategori k ON b.id_kategori = k.id_kategori
          $where
          ORDER BY b.created_at DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Buku - Admin Perpustakaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/../../includes/sidebar_admin.php'; ?>

    <div class="flex-grow-1 p-4" style="overflow-x:auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 fw-bold">Data Buku</h4>
                <small class="text-muted">Kelola semua data buku perpustakaan</small>
            </div>
            <a href="tambah.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Tambah Buku
            </a>
        </div>

        <?php if ($pesan): ?>
            <?= tampilkanAlert($tipe, $pesan) ?>
        <?php endif; ?>

        <!-- Form Pencarian -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" action="" class="d-flex gap-2">
                    <input type="text" name="cari" class="form-control"
                           placeholder="Cari judul, penulis, atau kategori..."
                           value="<?= htmlspecialchars($cari) ?>">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if ($cari): ?>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Tabel Buku -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th width="30%">Judul Buku</th>
                                <th width="20%">Penulis</th>
                                <th width="15%">Kategori</th>
                                <th width="8%">Stok</th>
                                <th width="12%">Status</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($row['judul']) ?></div>
                                    <?php if ($row['tahun_terbit']): ?>
                                        <small class="text-muted"><?= $row['tahun_terbit'] ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['penulis']) ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($row['nama_kategori']) ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= $row['stok'] ?></td>
                                <td>
                                    <?php if ($row['status'] === 'tersedia'): ?>
                                        <span class="badge bg-success">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Dipinjam</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="detail.php?id=<?= $row['id_buku'] ?>"
                                           class="btn btn-sm btn-info text-white" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $row['id_buku'] ?>"
                                           class="btn btn-sm btn-warning text-white" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= $row['id_buku'] ?>"
                                           class="btn btn-sm btn-danger" title="Hapus"
                                           onclick="return konfirmasiHapus('<?= htmlspecialchars($row['judul']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <?= $cari ? "Tidak ada buku dengan kata kunci \"$cari\"." : 'Belum ada data buku.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function konfirmasiHapus(judul) {
    return confirm('Yakin ingin menghapus buku "' + judul + '"?\nData yang sudah dihapus tidak bisa dikembalikan.');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
