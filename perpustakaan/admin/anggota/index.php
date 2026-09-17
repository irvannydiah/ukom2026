<?php
require_once __DIR__ . '/../../includes/auth.php';
cekRole('admin');
require_once __DIR__ . '/../../includes/fungsi.php';

$halaman_aktif = 'anggota';
$error   = '';
$success = '';

// ===== HAPUS ANGGOTA =====
if (isset($_GET['hapus'])) {
    $id_user = (int)$_GET['hapus'];
    // Jangan hapus diri sendiri
    if ($id_user === (int)$_SESSION['id_user']) {
        $error = 'Tidak bisa menghapus akun sendiri.';
    } else {
        // Cek peminjaman aktif
        $cekPinjam = mysqli_query($koneksi,
            "SELECT id_peminjaman FROM peminjaman
             WHERE id_user = $id_user AND status IN ('diajukan','diproses','dipinjam') LIMIT 1");
        if (mysqli_num_rows($cekPinjam) > 0) {
            $error = 'Anggota tidak bisa dihapus karena masih memiliki peminjaman aktif.';
        } elseif (mysqli_query($koneksi, "DELETE FROM users WHERE id_user = $id_user AND role = 'user'")) {
            $success = 'Anggota berhasil dihapus.';
        } else {
            $error = 'Gagal menghapus: ' . mysqli_error($koneksi);
        }
    }
}

// ===== TAMBAH ANGGOTA =====
if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama     = bersihkan($_POST['nama']);
    $email    = bersihkan($_POST['email']);
    $no_hp    = bersihkan($_POST['no_hp']);
    $alamat   = bersihkan($_POST['alamat']);
    $password = $_POST['password'];

    if (empty($nama) || empty($email) || empty($password)) {
        $error = 'Nama, email, dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $cek = mysqli_query($koneksi, "SELECT id_user FROM users WHERE email = '$email'");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Email sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if (mysqli_query($koneksi,
                "INSERT INTO users (nama, email, password, no_hp, alamat, role)
                 VALUES ('$nama','$email','$hash','$no_hp','$alamat','user')")) {
                $success = 'Anggota berhasil ditambahkan.';
            } else {
                $error = 'Gagal menyimpan: ' . mysqli_error($koneksi);
            }
        }
    }
}

// ===== EDIT ANGGOTA =====
if (isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id_user  = (int)$_POST['id_user'];
    $nama     = bersihkan($_POST['nama']);
    $email    = bersihkan($_POST['email']);
    $no_hp    = bersihkan($_POST['no_hp']);
    $alamat   = bersihkan($_POST['alamat']);
    $password = $_POST['password'];

    if (empty($nama) || empty($email)) {
        $error = 'Nama dan email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $cek = mysqli_query($koneksi,
            "SELECT id_user FROM users WHERE email = '$email' AND id_user != $id_user");
        if (mysqli_num_rows($cek) > 0) {
            $error = 'Email sudah digunakan anggota lain.';
        } else {
            // Kalau password diisi, perbarui juga passwordnya
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $error = 'Password minimal 6 karakter.';
                } else {
                    $hash  = password_hash($password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET nama='$nama', email='$email',
                                               no_hp='$no_hp', alamat='$alamat',
                                               password='$hash'
                              WHERE id_user=$id_user AND role='user'";
                }
            } else {
                $query = "UPDATE users SET nama='$nama', email='$email',
                                           no_hp='$no_hp', alamat='$alamat'
                          WHERE id_user=$id_user AND role='user'";
            }

            if (empty($error)) {
                if (mysqli_query($koneksi, $query)) {
                    $success = 'Data anggota berhasil diperbarui.';
                } else {
                    $error = 'Gagal memperbarui: ' . mysqli_error($koneksi);
                }
            }
        }
    }
}

// Pencarian
$cari  = bersihkan($_GET['cari'] ?? '');
$where = "WHERE role = 'user'";
if ($cari !== '') {
    $where .= " AND (nama LIKE '%$cari%' OR email LIKE '%$cari%' OR no_hp LIKE '%$cari%')";
}

$result = mysqli_query($koneksi, "SELECT * FROM users $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Anggota - Admin Perpustakaan</title>
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
                <h4 class="mb-0 fw-bold">Data Anggota</h4>
                <small class="text-muted">Kelola data anggota perpustakaan</small>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-person-plus me-1"></i> Tambah Anggota
            </button>
        </div>

        <?php if ($error):   echo tampilkanAlert('danger',  $error);   endif; ?>
        <?php if ($success): echo tampilkanAlert('success', $success); endif; ?>

        <!-- Pencarian -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="cari" class="form-control"
                           placeholder="Cari nama, email, atau no. HP..."
                           value="<?= htmlspecialchars($cari) ?>">
                    <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
                    <?php if ($cari): ?>
                        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. HP</th>
                                <th>Terdaftar</th>
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
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($row['nama']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['no_hp'] ?? '-') ?></td>
                                <td><?= formatTanggal($row['created_at']) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-warning text-white"
                                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                                data-id="<?= $row['id_user'] ?>"
                                                data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                                data-email="<?= htmlspecialchars($row['email']) ?>"
                                                data-nohp="<?= htmlspecialchars($row['no_hp'] ?? '') ?>"
                                                data-alamat="<?= htmlspecialchars($row['alamat'] ?? '') ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?hapus=<?= $row['id_user'] ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Hapus anggota <?= htmlspecialchars($row['nama']) ?>?')">
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
                                <td colspan="6" class="text-center text-muted py-4">
                                    <?= $cari ? "Tidak ditemukan anggota dengan kata kunci \"$cari\"." : 'Belum ada data anggota.' ?>
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Anggota Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">No. HP</label>
                            <input type="text" name="no_hp" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                            <small class="text-muted">Minimal 6 karakter.</small>
                        </div>
                    </div>
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
                <input type="hidden" name="id_user" id="editId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Anggota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="editNama" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">No. HP</label>
                            <input type="text" name="no_hp" id="editNohp" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat</label>
                            <textarea name="alamat" id="editAlamat" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password" name="password" class="form-control" minlength="6">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti password.</small>
                        </div>
                    </div>
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
document.getElementById('modalEdit').addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    document.getElementById('editId').value      = btn.getAttribute('data-id');
    document.getElementById('editNama').value    = btn.getAttribute('data-nama');
    document.getElementById('editEmail').value   = btn.getAttribute('data-email');
    document.getElementById('editNohp').value    = btn.getAttribute('data-nohp');
    document.getElementById('editAlamat').value  = btn.getAttribute('data-alamat');
});
</script>
</body>
</html>
