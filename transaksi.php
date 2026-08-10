<?php
require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| FITUR DOWNLOAD LAPORAN TRANSAKSI (CSV / EXCEL)
|--------------------------------------------------------------------------
*/
if (isset($_GET['download'])) {
    // Set Header untuk mendownload file CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laporan_Transaksi_' . date('Y-m-d_H-i-s') . '.csv');

    // Buka output stream PHP
    $output = fopen('php://output', 'w');

    // Tambahkan UTF-8 BOM agar terbaca rapi di Microsoft Excel
    fputs($output, "\xEF\xBB\xBF");

    // Header Kolom Laporan pada CSV
    fputcsv($output, ['ID Transaksi', 'Nama Anggota', 'Judul Buku', 'Tanggal Pinjam', 'Tanggal Kembali', 'Status']);

    // Query JOIN untuk mengambil detail transaksi lengkap
    $queryDownload = pg_query(
        $conn,
        "SELECT 
            t.id_transaksi,
            COALESCE(a.nama_anggota, '-') AS nama_anggota,
            COALESCE(b.judul_buku, '-') AS judul_buku,
            t.tanggal_pinjam,
            t.tanggal_kembali,
            t.status
         FROM transaksi t
         LEFT JOIN anggota a ON t.id_anggota = a.id_anggota
         LEFT JOIN buku b ON t.id_buku = b.id_buku
         ORDER BY t.id_transaksi DESC"
    );

    while ($row = pg_fetch_assoc($queryDownload)) {
        fputcsv($output, [
            $row['id_transaksi'] ?? '',
            $row['nama_anggota'] ?? '-',
            $row['judul_buku'] ?? '-',
            $row['tanggal_pinjam'] ?? '-',
            $row['tanggal_kembali'] ?? '-',
            $row['status'] ?? 'Dipinjam'
        ]);
    }

    fclose($output);
    exit;
}

/*
|--------------------------------------------------------------------------
| TAMBAH TRANSAKSI PEMINJAMAN
|--------------------------------------------------------------------------
*/
if (isset($_POST['tambah'])) {
    $id_anggota     = $_POST['id_anggota'];
    $id_buku        = $_POST['id_buku'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];

    $query = pg_query_params(
        $conn,
        "INSERT INTO transaksi (id_anggota, id_buku, tanggal_pinjam, status)
         VALUES ($1, $2, $3, 'Dipinjam')",
        [$id_anggota, $id_buku, $tanggal_pinjam]
    );

    if ($query) {
        header("Location: transaksi.php");
        exit;
    } else {
        echo "Gagal menambahkan transaksi.";
    }
}

/*
|--------------------------------------------------------------------------
| PROSES PENGEMBALIAN BUKU
|--------------------------------------------------------------------------
*/
if (isset($_GET['kembali'])) {
    $id_transaksi    = $_GET['kembali'];
    $tanggal_kembali = date('Y-m-d');

    $query = pg_query_params(
        $conn,
        "UPDATE transaksi 
         SET tanggal_kembali = $1, status = 'Dikembalikan' 
         WHERE id_transaksi = $2",
        [$tanggal_kembali, $id_transaksi]
    );

    if ($query) {
        header("Location: transaksi.php");
        exit;
    } else {
        echo "Gagal mengupdate pengembalian buku.";
    }
}

/*
|--------------------------------------------------------------------------
| HAPUS TRANSAKSI
|--------------------------------------------------------------------------
*/
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $query = pg_query_params(
        $conn,
        "DELETE FROM transaksi WHERE id_transaksi = $1",
        [$id]
    );

    if ($query) {
        header("Location: transaksi.php");
        exit;
    } else {
        echo "Gagal menghapus data transaksi.";
    }
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA ANGGOTA & BUKU UNTUK DROPDOWN FORM
|--------------------------------------------------------------------------
*/
$queryAnggota = pg_query($conn, "SELECT id_anggota, nama_anggota FROM anggota ORDER BY nama_anggota ASC");
$queryBuku    = pg_query($conn, "SELECT id_buku, judul_buku FROM buku ORDER BY id_buku DESC");

/*
|--------------------------------------------------------------------------
| AMBIL SEMUA DATA TRANSAKSI
|--------------------------------------------------------------------------
*/
$queryTransaksi = pg_query(
    $conn,
    "SELECT 
        t.id_transaksi,
        t.id_anggota,
        t.id_buku,
        COALESCE(a.nama_anggota, '-') AS nama_anggota,
        COALESCE(b.judul_buku, '-') AS judul_buku,
        t.tanggal_pinjam,
        t.tanggal_kembali,
        t.status
     FROM transaksi t
     LEFT JOIN anggota a ON t.id_anggota = a.id_anggota
     LEFT JOIN buku b ON t.id_buku = b.id_buku
     ORDER BY t.id_transaksi DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Transaksi Peminjaman</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .navbar {
            background: #2563eb;
            color: white;
            padding: 20px;
        }

        .container {
            padding: 30px;
            max-width: 1200px;
            margin: auto;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }

        h1, h2 {
            margin-top: 0;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button, .btn {
            display: inline-block;
            border: none;
            background: #2563eb;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-back {
            background: #6b7280;
        }

        .btn-success {
            background: #10b981;
        }

        .btn-delete {
            background: #dc2626;
        }

        .btn-download {
            background: #059669;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            display: inline-block;
        }

        .badge-warning {
            background: #f59e0b;
        }

        .badge-success {
            background: #10b981;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #2563eb;
            color: white;
        }

        .empty {
            text-align: center;
            color: #777;
        }

        .form-group {
            margin-bottom: 5px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .aksi {
            display: flex;
            gap: 5px;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <h2>📚 Sistem Perpustakaan - Transaksi Peminjaman</h2>
</div>

<div class="container">

<!-- FORM TAMBAH TRANSAKSI -->
<div class="card">
    <h2>Tambah Peminjaman Baru</h2>

    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Pilih Anggota</label>
                <select name="id_anggota" required>
                    <option value="">-- Pilih Anggota --</option>
                    <?php while ($a = pg_fetch_assoc($queryAnggota)): ?>
                        <option value="<?= htmlspecialchars($a['id_anggota']) ?>">
                            <?= htmlspecialchars($a['nama_anggota']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Pilih Buku</label>
                <select name="id_buku" required>
                    <option value="">-- Pilih Buku --</option>
                    <?php while ($b = pg_fetch_assoc($queryBuku)): ?>
                        <option value="<?= htmlspecialchars($b['id_buku']) ?>">
                            <?= htmlspecialchars($b['judul_buku']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Tanggal Pinjam</label>
                <input type="date" name="tanggal_pinjam" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <button type="submit" name="tambah">+ Tambah Transaksi</button>
    </form>
</div>

<!-- TABEL DATA TRANSAKSI & LAPORAN -->
<div class="card">
    <h1>Daftar Transaksi Peminjaman</h1>

    <div class="header-actions">
        <a href="dashboard.php" class="btn btn-back">
            ← Kembali ke Dashboard
        </a>

        <!-- TOMBOL DOWNLOAD LAPORAN CSV -->
        <a href="transaksi.php?download=csv" class="btn btn-download">
            📊 Download Laporan Transaksi (.csv)
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Anggota</th>
                <th>Judul Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if (pg_num_rows($queryTransaksi) > 0): ?>
            <?php while ($trx = pg_fetch_assoc($queryTransaksi)): ?>
                <tr>
                    <td><?= htmlspecialchars($trx['id_transaksi'] ?? '') ?></td>
                    <td><?= htmlspecialchars($trx['nama_anggota'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($trx['judul_buku'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($trx['tanggal_pinjam'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($trx['tanggal_kembali'] ?? '-') ?></td>
                    <td>
                        <?php if (($trx['status'] ?? '') === 'Dikembalikan'): ?>
                            <span class="badge badge-success">Dikembalikan</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Dipinjam</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="aksi">
                            <?php if (($trx['status'] ?? '') !== 'Dikembalikan'): ?>
                                <a href="transaksi.php?kembali=<?= $trx['id_transaksi'] ?>" class="btn btn-success" onclick="return confirm('Proses pengembalian buku ini?')">Kembalikan</a>
                            <?php endif; ?>
                            <a href="transaksi.php?hapus=<?= $trx['id_transaksi'] ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin menghapus data transaksi ini?')">Hapus</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="empty">Belum ada data transaksi peminjaman</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div>

</body>
</html>