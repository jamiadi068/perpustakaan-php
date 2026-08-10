<?php

/*
|--------------------------------------------------------------------------
| FUNGSI HITUNG DENDA
|--------------------------------------------------------------------------
|
| 1 - 3 hari  = Rp0
| 4 hari      = Rp500
| 5 hari      = Rp1.000
| 6 hari      = Rp1.500
| dst         = tambah Rp500 per hari
|
*/

function hitungDenda($tanggal_pinjam, $tanggal_kembali = null)
{
    // Ubah tanggal pinjam menjadi objek DateTime
    $tanggalPinjam = new DateTime($tanggal_pinjam);

    // Cek jika $tanggal_kembali null atau kosong (buku belum dikembalikan)
    // Gunakan tanggal hari ini sebagai acuannya
    if (empty($tanggal_kembali)) {
        $tanggalKembali = new DateTime('today');
    } else {
        $tanggalKembali = new DateTime($tanggal_kembali);
    }

    // Hitung selisih hari
    $lamaPinjam = $tanggalPinjam
        ->diff($tanggalKembali)
        ->days;

    // Jika lebih dari 3 hari
    if ($lamaPinjam > 3) {

        // Hitung jumlah hari yang terkena denda
        $hariDenda = $lamaPinjam - 3;

        // Denda Rp500 per hari
        $denda = $hariDenda * 500;

    } else {

        // Tidak ada denda
        $denda = 0;

    }

    return $denda;
}

?>