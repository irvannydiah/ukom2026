<?php
require_once __DIR__ . '/../../includes/auth.php';
cekRole('user');
require_once __DIR__ . '/../../includes/fungsi.php';

$halaman_aktif = 'buku';

// Pesan notifikasi dari halaman lain (mis. setelah ajukan pinjam gagal)
$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe']  ?? 'success';

// ===== PENCARIAN & FILTER KATEGORI =====
$cari           = bersihkan($_GET['cari'] ?? '');
$id_kategori_f  = (int)($_GET['kategori'] ?? 0);

$where = [];
if ($cari !== '') {
    $where[] = "(b.judul LIKE '%$cari%' OR b.penulis LIKE '%$cari%' OR k.nama_kategori LIKE '%$cari%')";
}
if ($id_kategori_f > 0) {
    $where[] = "b.id_kategori = $id_kategori_f";
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// ===== AMBIL DATA BUKU =====
$query = "SELECT b.*, k.nama_kategori
          FROM buku b
          JOIN kategori k ON b.id_kategori = k.id_kategori
          $where_sql
          ORDER BY b.judul ASC";
$result = mysqli_query($koneksi, $query);

// Daftar kategori untuk dropdown filter
$kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori");
$kategori_rows = [];
while ($k = mysqli_fetch_assoc($kategori_list)) { $kategori_rows[] = $k; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cari Buku - Perpustakaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/../../includes/sidebar_user.php'; ?>

    <div class="flex-grow-1 p-4" style="overflow-x:auto;">
        <div class="mb-4">
            <h4 class="mb-0 fw-bold">Cari Buku</h4>
            <small class="text-muted">Telusuri koleksi buku perpustakaan &amp; ajukan peminjaman</small>
        </div>

        <?php if ($pesan): ?>
            <?= tampilkanAlert($tipe, $pesan) ?>
        <?php endif; ?>

        <!-- Form Pencarian & Filter -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" action="" class="row g-2">
                    <div class="col-md-7">
                        <input type="text" name="cari" class="form-control"
                               placeholder="Cari judul, penulis, atau kategori..."
                               value="<?= htmlspecialchars($cari) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="kategori" class="form-select">
                            <option value="0">Semua Kategori</option>
                            <?php foreach ($kategori_rows as $kat): ?>
                                <option value="<?= $kat['id_kategori'] ?>"
                                    <?= $id_kategori_f === (int)$kat['id_kategori'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button class="btn btn-outline-primary w-100" type="submit">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <?php if ($cari || $id_kategori_f): ?>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x"></i>
                            </a>
                        <?php endif; ?>
                    </div>
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
                                    <?php if ($row['status'] === 'tersedia' && $row['stok'] > 0): ?>
                                        <span class="badge bg-success">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Dipinjam</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="detail.php?id=<?= $row['id_buku'] ?>"
                                       class="btn btn-sm btn-primary" title="Lihat Detail &amp; Pinjam">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
