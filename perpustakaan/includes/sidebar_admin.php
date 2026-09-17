<?php
// includes/sidebar_admin.php
// Di-include di setiap halaman admin
// $halaman_aktif diset di halaman yang meng-include file ini
// Contoh: $halaman_aktif = 'buku';
$halaman_aktif = $halaman_aktif ?? '';
?>
<div class="sidebar d-flex flex-column" style="width:240px; min-width:240px;">
    <div class="brand">
        <i class="bi bi-book-half me-2"></i> Perpustakaan
        <div style="font-size:0.75rem; color:#94a3b8; font-weight:400;">Panel Admin</div>
    </div>

    <nav class="mt-2 flex-grow-1">
        <a href="<?= BASE_URL ?>/admin/dashboard.php"
           class="<?= $halaman_aktif === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a href="<?= BASE_URL ?>/admin/buku/index.php"
           class="<?= $halaman_aktif === 'buku' ? 'active' : '' ?>">
            <i class="bi bi-journal-bookmark me-2"></i> Data Buku
        </a>
        <a href="<?= BASE_URL ?>/admin/kategori/index.php"
           class="<?= $halaman_aktif === 'kategori' ? 'active' : '' ?>">
            <i class="bi bi-tags me-2"></i> Kategori
        </a>
        <a href="<?= BASE_URL ?>/admin/anggota/index.php"
           class="<?= $halaman_aktif === 'anggota' ? 'active' : '' ?>">
            <i class="bi bi-people me-2"></i> Anggota
        </a>
        <a href="<?= BASE_URL ?>/admin/peminjaman/index.php"
           class="<?= $halaman_aktif === 'peminjaman' ? 'active' : '' ?>">
            <i class="bi bi-arrow-left-right me-2"></i> Peminjaman
        </a>
    </nav>

    <div class="p-3" style="border-top:1px solid #374151;">
        <div style="font-size:0.85rem; color:#94a3b8;">
            <i class="bi bi-person-circle me-1"></i>
            <?= htmlspecialchars($_SESSION['nama']) ?>
        </div>
        <a href="<?= BASE_URL ?>/auth/logout.php"
           class="btn btn-sm btn-outline-danger mt-2 w-100">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
</div>
