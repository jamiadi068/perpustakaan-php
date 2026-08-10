<?php
// 1. Wajib jalankan session di paling atas
session_start();

// 2. Sertakan koneksi database (sesuaikan path jika berbeda)
require_once "config/database.php"; 

// 3. Cek apakah user sudah login
if (!isset($_SESSION['id_anggota'])) {
    header("Location: index.php");
    exit();
}

$id_anggota   = $_SESSION['id_anggota'];
$nama_anggota = $_SESSION['nama_anggota'] ?? 'Anggota';

// 4. Query Statistik Peminjaman berdasarkan struktur kolom asli
$query_stat = pg_query_params($conn, "
    SELECT 
        COUNT(*) AS total_pinjam,
        COUNT(CASE WHEN status = 'Dipinjam' THEN 1 END) AS sedang_dipinjam,
        COUNT(CASE WHEN status = 'Dipinjam' AND tanggal_kembali < CURRENT_DATE THEN 1 END) AS terlambat,
        COALESCE(SUM(denda), 0) AS total_denda
    FROM transaksi 
    WHERE id_anggota = $1
", [$id_anggota]);

if (!$query_stat) {
    die("Query Error (Statistik): " . pg_last_error($conn));
}

$stat = pg_fetch_assoc($query_stat);

// 5. Query Riwayat Transaksi Anggota (JOIN ke tabel buku)
$query_transaksi = pg_query_params($conn, "
    SELECT 
        t.id_transaksi,
        b.judul_buku,
        t.tanggal_pinjam,
        t.tanggal_kembali,
        t.status,
        t.denda
    FROM transaksi t
    JOIN buku b ON t.id_buku = b.id_buku
    WHERE t.id_anggota = $1
    ORDER BY t.tanggal_pinjam DESC
", [$id_anggota]);

if (!$query_transaksi) {
    die("Query Error (Riwayat): " . pg_last_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Anggota - Perpustakaan</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; background: #2563eb; color: white; padding: 15px 25px; border-radius: 8px; }
        .header a { color: white; text-decoration: none; background: #dc2626; padding: 8px 15px; border-radius: 5px; }
        .grid-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; }
        .card h3 { margin: 0; font-size: 28px; color: #2563eb; }
        .card p { margin: 5px 0 0; color: #6b7280; font-size: 14px; }
        .table-section { background: white; padding: 20px; border-radius: 8px; margin-top: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: left; }
        th { background: #f8fafc; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-dipinjam { background: #fef3c7; color: #d97706; }
        .badge-kembali { background: #d1fae5; color: #059669; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Selamat Datang, <?= htmlspecialchars($nama_anggota); ?>! 👋</h2>
        <a href="logout.php">Logout</a>
    </div>

    <!-- STATISTIK RINGKASAN -->
    <div class="grid-cards">
        <div class="card">
            <h3><?= $stat['total_pinjam']; ?></h3>
            <p>Total Peminjaman</p>
        </div>
        <div class="card">
            <h3><?= $stat['sedang_dipinjam']; ?></h3>
            <p>Sedang Dipinjam</p>
        </div>
        <div class="card">
            <h3><?= $stat['terlambat']; ?></h3>
            <p>Buku Terlambat</p>
        </div>
        <div class="card">
            <h3>Rp <?= number_format($stat['total_denda'], 0, ',', '.'); ?></h3>
            <p>Total Denda</p>
        </div>
    </div>

    <!-- TABEL RIWAYAT TRANSAKSI -->
    <div class="table-section">
        <h3>📚 Riwayat Peminjaman Buku</h3>
        <table>
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Jatuh Tempo / Tanggal Kembali</th>
                    <th>Status</th>
                    <th>Denda</th>
                </tr>
            </thead>
            <tbody>
                <?php if (pg_num_rows($query_transaksi) > 0): ?>
                    <?php while ($row = pg_fetch_assoc($query_transaksi)): ?>
                        <tr>
                            <td>#<?= $row['id_transaksi']; ?></td>
                            <td><?= htmlspecialchars($row['judul_buku']); ?></td>
                            <td><?= date('d-m-Y', strtotime($row['tanggal_pinjam'])); ?></td>
                            <td><?= $row['tanggal_kembali'] ? date('d-m-Y', strtotime($row['tanggal_kembali'])) : '-'; ?></td>
                            <td>
                                <span class="badge <?= $row['status'] == 'Dipinjam' ? 'badge-dipinjam' : 'badge-kembali'; ?>">
                                    <?= htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>Rp <?= number_format($row['denda'] ?? 0, 0, ',', '.'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #6b7280;">Belum ada riwayat transaksi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>