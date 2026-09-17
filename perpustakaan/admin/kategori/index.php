<?php
require_once __DIR__ . '/../../includes/auth.php';
cekRole('admin');
require_once __DIR__ . '/../../includes/fungsi.php';

$halaman_aktif = 'kategori';
$error   = '';
$success = '';

// ===== TAMBAH KATEGORI =====
if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama_kategori = bersihkan($_POST['nama_kategori']);
    if (empty($nama_kategori)) {
        $error = 'Nama kategori tidak boleh kosong.';
    } else {
        $cek = mysqli_query($koneksi, "SELECT id_kategori FROM kategori WHERE nama_kategori = '$nama_kategori'");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Kategori <strong>' . htmlspecialchars($nama_kategori) . '</strong> sudah ada.';
        } elseif (mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')")) {
            $success = 'Kategori berhasil ditambahkan.';
        } else {
            $error = 'Gagal menyimpan: ' . mysqli_error($koneksi);
        }
    }
}

// ===== EDIT KATEGORI =====
if (isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id_kategori   = (int)$_POST['id_kategori'];
    $nama_kategori = bersihkan($_POST['nama_kategori']);
    if (empty($nama_kategori) || !$id_kategori) {
        $error = 'Nama kategori tidak boleh kosong.';
    } else {
        $cek = mysqli_query($koneksi, "SELECT id_kategori FROM kategori
                                       WHERE nama_kategori = '$nama_kategori'
                                         AND id_kategori != $id_kategori");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Nama kategori sudah digunakan oleh kategori lain.';
        } elseif (mysqli_query($koneksi, "UPDATE kategori SET nama_kategori = '$nama_kategori'
                                           WHERE id_kategori = $id_kategori")) {
            $success = 'Kategori berhasil diperbarui.';
        } else {
            $error = 'Gagal memperbarui: ' . mysqli_error($koneksi);
        }
    }
}

// ===== HAPUS KATEGORI =====
if (isset($_GET['hapus'])) {
    $id_kategori = (int)$_GET['hapus'];
    // Cek apakah kategori masih dipakai buku
    $cekBuku = mysqli_query($koneksi, "SELECT id_buku FROM buku WHERE id_kategori = $id_kategori LIMIT 1");
    if (mysqli_num_rows($cekBuku) > 0) {
        $error = 'Kategori tidak bisa dihapus karena masih digunakan oleh data buku.';
    } elseif (mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = $id_kategori")) {
        $success = 'Kategori berhasil dihapus.';
    } else {
        $error = 'Gagal menghapus: ' . mysqli_error($koneksi);
    }
}

// Ambil semua kategori beserta jumlah bukunya
$result = mysqli_query($koneksi,
    "SELECT k.*, COUNT(b.id_buku) AS jumlah_buku
     FROM kategori k
     LEFT JOIN buku b ON k.id_kategori = b.id_kategori
     GROUP BY k.id_kategori
     ORDER BY k.nama_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kategori - Admin Perpustakaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/../../includes/sidebar_admin.php'; ?>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 fw-bold">Kategori Buku</h4>
                <small class="text-muted">Kelola kategori / genre buku</small>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle me-1"></i> Tambah Kategori
            </button>
        </div>

        <?php if ($error):   echo tampilkanAlert('danger',  $error);   endif; ?>
        <?php if ($success): echo tampilkanAlert('success', $success); endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Kategori</th>
                            <th width="15%" class="text-center">Jumlah Buku</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    $rows = [];
                    while ($row = mysqli_fetch_assoc($result)) { $rows[] = $row; }

                    if (count($rows) > 0):
                        foreach ($rows as $row):
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $row['jumlah_buku'] ?> buku</span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-warning text-white"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEdit"
                                            data-id="<?= $row['id_kategori'] ?>"
                                            data-nama="<?= htmlspecialchars($row['nama_kategori']) ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?hapus=<?= $row['id_kategori'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Hapus kategori <?= htmlspecialchars($row['nama_kategori']) ?>?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php
                        endforeach;
                    else:
                    ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Belum ada kategori. Silakan tambah kategori baru.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" name="nama_kategori" class="form-control"
                           placeholder="contoh: Fiksi, Teknologi, Sejarah..." required autofocus>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id_kategori" id="editId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" name="nama_kategori" id="editNama" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Isi data ke modal edit saat tombol edit diklik
document.getElementById('modalEdit').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('editId').value   = btn.getAttribute('data-id');
    document.getElementById('editNama').value = btn.getAttribute('data-nama');
});
</script>
</body>
</html>
