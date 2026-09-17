<?php
require_once __DIR__ . '/../../includes/auth.php';
cekRole('user');
require_once __DIR__ . '/../../includes/fungsi.php';

$halaman_aktif = 'peminjaman';
$id_user = (int)$_SESSION['id_user'];

$pesan = $_GET['pesan'] ?? '';
$tipe  = $_GET['tipe']  ?? 'success';

// ===== BATALKAN PENGAJUAN (hanya boleh selama status masih 'diajukan') =====
if (isset($_GET['batal'])) {
    $id_peminjaman = (int)$_GET['batal'];

    $cek = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT * FROM peminjaman WHERE id_peminjaman = $id_peminjaman AND id_user = $id_user"));

    if (!$cek) {
        $pesan = 'Data peminjaman tidak ditemukan.';
        $tipe  = 'danger';
    } elseif ($cek['status'] !== 'diajukan') {
        $pesan = 'Pengajuan yang sudah diproses admin tidak bisa dibatalkan sendiri.';
        $tipe  = 'danger';
    } elseif (mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_peminjaman = $id_peminjaman")) {
        $pesan = 'Pengajuan peminjaman berhasil dibatalkan.';
        $tipe  = 'success';
    } else {
        $pesan = 'Gagal membatalkan: ' . mysqli_error($koneksi);
        $tipe  = 'danger';
    }
}

// ===== FILTER STATUS =====
$filter = bersihkan($_GET['filter'] ?? '');
$where  = "WHERE p.id_user = $id_user";
if ($filter && in_array($filter, ['diajukan','diproses','dipinjam','dikembalikan','ditolak'])) {
    $where .= " AND p.status = '$filter'";
}

// ===== AMBIL DATA PEMINJAMAN MILIK USER =====
$result = mysqli_query($koneksi,
    "SELECT p.*, b.judul AS judul_buku, b.penulis
     FROM peminjaman p
     JOIN buku b ON p.id_buku = b.id_buku
     $where
     ORDER BY
         FIELD(p.status,'diajukan','diproses','dipinjam','dikembalikan','ditolak'),
         p.created_at DESC");

$badge = [
    'diajukan'    => 'bg-secondary',
    'diproses'    => 'bg-info text-dark',
    'dipinjam'    => 'bg-primary',
    'dikembalikan'=> 'bg-success',
    'ditolak'     => 'bg-danger',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Peminjaman Saya - Perpustakaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/../../includes/sidebar_user.php'; ?>

    <div class="flex-grow-1 p-4" style="overflow-x:auto;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 fw-bold">Peminjaman Saya</h4>
                <small class="text-muted">Riwayat &amp; status pengajuan peminjaman buku Anda</small>
            </div>
            <a href="<?= BASE_URL ?>/user/buku/index.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Ajukan Pinjam Buku
            </a>
        </div>

        <?php if ($pesan): ?>
            <?= tampilkanAlert($tipe, $pesan) ?>
        <?php endif; ?>

        <!-- Filter Status -->
        <div class="mb-3 d-flex flex-wrap gap-2">
            <a href="index.php"
               class="btn btn-sm <?= $filter === '' ? 'btn-dark' : 'btn-outline-dark' ?>">Semua</a>
            <?php
            $label_filter = [
                'diajukan'    => ['label'=>'Diajukan',    'cls'=>'secondary'],
                'diproses'    => ['label'=>'Diproses',    'cls'=>'info'],
                'dipinjam'    => ['label'=>'Dipinjam',    'cls'=>'primary'],
                'dikembalikan'=> ['label'=>'Dikembalikan','cls'=>'success'],
                'ditolak'     => ['label'=>'Ditolak',     'cls'=>'danger'],
            ];
            foreach ($label_filter as $key => $val):
                $aktif = ($filter === $key) ? '' : 'outline-';
            ?>
                <a href="?filter=<?= $key ?>"
                   class="btn btn-sm btn-<?= $aktif . $val['cls'] ?>">
                   <?= $val['label'] ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th>Catatan Admin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no   = 1;
                        $rows = [];
                        while ($row = mysqli_fetch_assoc($result)) { $rows[] = $row; }

                        if (count($rows) > 0):
                            foreach ($rows as $row):
                                $kelas = $badge[$row['status']] ?? 'bg-secondary';
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($row['judul_buku']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($row['penulis']) ?></small>
                                </td>
                                <td><?= formatTanggal($row['tanggal_pinjam']) ?></td>
                                <td><?= formatTanggal($row['tanggal_kembali']) ?></td>
                                <td><span class="badge <?= $kelas ?>"><?= ucfirst($row['status']) ?></span></td>
                                <td><?= $row['catatan'] ? htmlspecialchars($row['catatan']) : '-' ?></td>
                                <td>
                                    <?php if ($row['status'] === 'diajukan'): ?>
                                        <a href="?batal=<?= $row['id_peminjaman'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Batalkan pengajuan peminjaman buku ini?')">
                                            <i class="bi bi-x-circle me-1"></i> Batalkan
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Tidak ada data peminjaman<?= $filter ? " dengan status \"$filter\"" : '' ?>.
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
