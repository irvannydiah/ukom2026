<?php
require_once __DIR__ . '/../../includes/auth.php';
cekRole('admin');
require_once __DIR__ . '/../../includes/fungsi.php';

$halaman_aktif = 'peminjaman';
$error   = '';
$success = '';

// ===== UBAH STATUS PEMINJAMAN =====
if (isset($_POST['aksi']) && $_POST['aksi'] === 'ubah_status') {
    $id_peminjaman = (int)$_POST['id_peminjaman'];
    $status_baru   = bersihkan($_POST['status']);
    $catatan       = bersihkan($_POST['catatan'] ?? '');

    $status_valid = ['diajukan', 'diproses', 'dipinjam', 'dikembalikan', 'ditolak'];
    if (!in_array($status_baru, $status_valid)) {
        $error = 'Status tidak valid.';
    } else {
        // Ambil data peminjaman saat ini (untuk update stok buku)
        $pinjam = mysqli_fetch_assoc(mysqli_query($koneksi,
            "SELECT p.*, b.stok, b.status AS status_buku
             FROM peminjaman p
             JOIN buku b ON p.id_buku = b.id_buku
             WHERE p.id_peminjaman = $id_peminjaman"));

        if ($pinjam) {
            $status_lama = $pinjam['status'];
            $id_buku     = $pinjam['id_buku'];
            $stok        = (int)$pinjam['stok'];

            // Atur tanggal kembali rencana (7 hari dari sekarang) saat status dipinjam
            $set_tanggal = '';
            if ($status_baru === 'dipinjam' && $status_lama !== 'dipinjam') {
                $tgl_kembali = date('Y-m-d', strtotime('+7 days'));
                $set_tanggal = ", tanggal_kembali = '$tgl_kembali'";
            }

            // Update status peminjaman
            mysqli_query($koneksi,
                "UPDATE peminjaman
                 SET status = '$status_baru', catatan = '$catatan' $set_tanggal
                 WHERE id_peminjaman = $id_peminjaman");

            // Sinkronkan stok & status buku
            // Saat buku berpindah ke 'dipinjam' -> kurangi stok
            if ($status_baru === 'dipinjam' && $status_lama !== 'dipinjam') {
                $stok_baru   = max(0, $stok - 1);
                $status_buku = ($stok_baru > 0) ? 'tersedia' : 'dipinjam';
                mysqli_query($koneksi,
                    "UPDATE buku SET stok = $stok_baru, status = '$status_buku'
                     WHERE id_buku = $id_buku");
            }

            // Saat buku dikembalikan / ditolak -> kembalikan stok
            if (in_array($status_baru, ['dikembalikan', 'ditolak']) &&
                $status_lama === 'dipinjam') {
                $stok_baru = $stok + 1;
                mysqli_query($koneksi,
                    "UPDATE buku SET stok = $stok_baru, status = 'tersedia'
                     WHERE id_buku = $id_buku");
            }

            $success = 'Status peminjaman berhasil diubah menjadi <strong>' . ucfirst($status_baru) . '</strong>.';
        } else {
            $error = 'Data peminjaman tidak ditemukan.';
        }
    }
}

// ===== FILTER STATUS =====
$filter = bersihkan($_GET['filter'] ?? '');
$where  = '';
if ($filter && in_array($filter, ['diajukan','diproses','dipinjam','dikembalikan','ditolak'])) {
    $where = "WHERE p.status = '$filter'";
}

// ===== AMBIL DATA PEMINJAMAN =====
$result = mysqli_query($koneksi,
    "SELECT p.*, u.nama AS nama_user, u.email, b.judul AS judul_buku
     FROM peminjaman p
     JOIN users u ON p.id_user  = u.id_user
     JOIN buku  b ON p.id_buku  = b.id_buku
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
    <title>Peminjaman - Admin Perpustakaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/../../includes/sidebar_admin.php'; ?>

    <div class="flex-grow-1 p-4" style="overflow-x:auto;">
        <div class="mb-4">
            <h4 class="mb-0 fw-bold">Data Peminjaman</h4>
            <small class="text-muted">Kelola dan ubah status peminjaman buku</small>
        </div>

        <?php if ($error):   echo tampilkanAlert('danger',  $error);   endif; ?>
        <?php if ($success): echo tampilkanAlert('success', $success); endif; ?>

        <!-- Filter Status -->
        <div class="mb-3 d-flex flex-wrap gap-2">
            <a href="index.php"
               class="btn btn-sm <?= $filter === '' ? 'btn-dark' : 'btn-outline-dark' ?>">Semua</a>
            <?php
            $label_filter = [
                'diajukan'    => ['label'=>'Diajukan',    'cls'=>'btn-secondary'],
                'diproses'    => ['label'=>'Diproses',    'cls'=>'btn-info text-dark'],
                'dipinjam'    => ['label'=>'Dipinjam',    'cls'=>'btn-primary'],
                'dikembalikan'=> ['label'=>'Dikembalikan','cls'=>'btn-success'],
                'ditolak'     => ['label'=>'Ditolak',     'cls'=>'btn-danger'],
            ];
            foreach ($label_filter as $key => $val):
                $aktif = ($filter === $key) ? '' : 'outline-';
            ?>
                <a href="?filter=<?= $key ?>"
                   class="btn btn-sm btn-<?= $aktif . ltrim($val['cls'], 'btn-') ?>">
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
                                <th>Anggota</th>
                                <th>Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no  = 1;
                        $rows = [];
                        while ($row = mysqli_fetch_assoc($result)) { $rows[] = $row; }

                        if (count($rows) > 0):
                            foreach ($rows as $row):
                                $kelas = $badge[$row['status']] ?? 'bg-secondary';
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($row['nama_user']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['judul_buku']) ?></td>
                                <td><?= formatTanggal($row['tanggal_pinjam']) ?></td>
                                <td><?= formatTanggal($row['tanggal_kembali']) ?></td>
                                <td><span class="badge <?= $kelas ?>"><?= ucfirst($row['status']) ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#modalStatus"
                                            data-id="<?= $row['id_peminjaman'] ?>"
                                            data-user="<?= htmlspecialchars($row['nama_user']) ?>"
                                            data-buku="<?= htmlspecialchars($row['judul_buku']) ?>"
                                            data-status="<?= $row['status'] ?>"
                                            data-catatan="<?= htmlspecialchars($row['catatan'] ?? '') ?>">
                                        <i class="bi bi-pencil-square me-1"></i> Ubah Status
                                    </button>
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

<!-- Modal Ubah Status -->
<div class="modal fade" id="modalStatus" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="aksi" value="ubah_status">
                <input type="hidden" name="id_peminjaman" id="statusId">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Status Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1"><strong>Anggota :</strong> <span id="statusUser"></span></p>
                    <p class="mb-3"><strong>Buku &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</strong> <span id="statusBuku"></span></p>

                    <label class="form-label fw-semibold">Status Baru</label>
                    <select name="status" id="statusPilih" class="form-select mb-3">
                        <option value="diajukan">Diajukan</option>
                        <option value="diproses">Diproses</option>
                        <option value="dipinjam">Dipinjam</option>
                        <option value="dikembalikan">Dikembalikan</option>
                        <option value="ditolak">Ditolak</option>
                    </select>

                    <label class="form-label fw-semibold">Catatan (opsional)</label>
                    <textarea name="catatan" id="statusCatatan" class="form-control" rows="2"
                              placeholder="contoh: alasan ditolak, kondisi buku, dll."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('modalStatus').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('statusId').value          = btn.getAttribute('data-id');
    document.getElementById('statusUser').textContent  = btn.getAttribute('data-user');
    document.getElementById('statusBuku').textContent  = btn.getAttribute('data-buku');
    document.getElementById('statusCatatan').value     = btn.getAttribute('data-catatan');
    // Set opsi select ke status saat ini
    document.getElementById('statusPilih').value       = btn.getAttribute('data-status');
});
</script>
</body>
</html>
