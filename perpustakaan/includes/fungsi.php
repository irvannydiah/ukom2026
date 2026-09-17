<?php
/**
 * Kumpulan fungsi bantu yang dipakai di banyak halaman
 */

// Membersihkan input dari tag HTML & spasi berlebih (mencegah XSS sederhana)
function bersihkan($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Menampilkan pesan alert Bootstrap (untuk notifikasi sukses/gagal)
function tampilkanAlert($tipe, $pesan)
{
    // $tipe: 'success', 'danger', 'warning', 'info'
    return "<div class='alert alert-$tipe alert-dismissible fade show' role='alert'>
                $pesan
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

// Format tanggal Indonesia sederhana (contoh: 2026-09-15 -> 15-09-2026)
function formatTanggal($tanggal)
{
    if (!$tanggal) return '-';
    return date('d-m-Y', strtotime($tanggal));
}
