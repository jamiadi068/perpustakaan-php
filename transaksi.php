<?php

require_once "config/database.php";
require_once "denda.php";

/*
|--------------------------------------------------------------------------
| PROSES PEMINJAMAN
|--------------------------------------------------------------------------
*/

if (isset($_POST['pinjam'])) {

    $id_anggota     = $_POST['id_anggota'];
    $id_buku        = $_POST['id_buku'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];

    // Cek stok buku
    $cekStok = pg_query_params(
        $conn,
        "SELECT stok FROM buku WHERE id_buku = $1",
        [$id_buku]
    );

    $buku = pg_fetch_assoc($cekStok);

    if (!$buku) {
        echo "<script>
                alert('Buku tidak ditemukan!');
                window.location='transaksi.php';
              </script>";
        exit;
    }

    if ($buku['stok'] <= 0) {
        echo "<script>
                alert('Stok buku sedang habis!');
                window.location='transaksi.php';
              </script>";
        exit;
    }

    // Mulai transaksi database
    pg_query($conn, "BEGIN");

    // Simpan transaksi peminjaman
    $queryPinjam = pg_query_params(
        $conn,
        "INSERT INTO transaksi
        (
            id_anggota,
            id_buku,
            tanggal_pinjam,
            status
        )
        VALUES
        (
            $1,
            $2,
            $3,
            'Dipinjam'
        )",
        [
            $id_anggota,
            $id_buku,
            $tanggal_pinjam
        ]
    );

    // Kurangi stok buku
    $queryStok = pg_query_params(
        $conn,
        "UPDATE buku
         SET stok = stok - 1
         WHERE id_buku = $1",
        [$id_buku]
    );

    // Cek apakah semua berhasil
    if ($queryPinjam && $queryStok) {
        pg_query($conn, "COMMIT");
        echo "<script>
                alert('Peminjaman berhasil disimpan!');
                window.location='transaksi.php';
              </script>";
        exit;
    } else {
        pg_query($conn, "ROLLBACK");
        echo "<script>
                alert('Peminjaman gagal!');
                window.location='transaksi.php';
              </script>";
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| PROSES PENGEMBALIAN
|--------------------------------------------------------------------------
*/

if (isset($_GET['kembali'])) {

    $id_transaksi = $_GET['kembali'];

    // Ambil id buku
    $queryData = pg_query_params(
        $conn,
        "SELECT id_buku
         FROM transaksi
         WHERE id_transaksi = $1
         AND status = 'Dipinjam'",
        [$id_transaksi]
    );

    $data = pg_fetch_assoc($queryData);

    if ($data) {
        $id_buku = $data['id_buku'];

        pg_query($conn, "BEGIN");

        // Update transaksi
        $queryKembali = pg_query_params(
            $conn,
            "UPDATE transaksi
             SET
                status = 'Dikembalikan',
                tanggal_kembali = CURRENT_DATE
             WHERE id_transaksi = $1",
            [$id_transaksi]
        );

        // Tambahkan stok kembali
        $queryStok = pg_query_params(
            $conn,
            "UPDATE buku
             SET stok = stok + 1
             WHERE id_buku = $1",
            [$id_buku]
        );

        if ($queryKembali && $queryStok) {
            pg_query($conn, "COMMIT");
            echo "<script>
                    alert('Buku berhasil dikembalikan!');
                    window.location='transaksi.php';
                  </script>";
            exit;
        } else {
            pg_query($conn, "ROLLBACK");
            echo "<script>
                    alert('Pengembalian gagal!');
                    window.location='transaksi.php';
                  </script>";
            exit;
        }
    }
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA ANGGOTA & BUKU
|--------------------------------------------------------------------------
*/

$queryAnggota = pg_query(
    $conn,
    "SELECT id_anggota, nama_anggota FROM anggota ORDER BY nama_anggota ASC"
);

$queryBuku = pg_query(
    $conn,
    "SELECT id_buku, judul_buku, stok FROM buku WHERE stok > 0 ORDER BY judul_buku ASC"
);

$queryPeminjaman = pg_query(
    $conn,
    "SELECT
        t.id_transaksi,
        a.nama_anggota,
        b.judul_buku,
        t.tanggal_pinjam,
        t.tanggal_kembali,
        t.status
     FROM transaksi t
     JOIN anggota a ON t.id_anggota = a.id_anggota
     JOIN buku b ON t.id_buku = b.id_buku
     ORDER BY t.id_transaksi DESC"
);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Peminjaman Buku</title>

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
            max-width: 1200px;
            margin: auto;
            padding: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }

        h1 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        select, input[type="date"] {
            width: 100%;
            padding: 11px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        button, .btn {
            display: inline-block;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }

        button {
            background: #2563eb;
        }

        .btn-back {
            background: #6b7280;
            margin-bottom: 20px;
        }

        .btn-kembali {
            background: #16a34a;
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

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
        }

        .dipinjam {
            background: #fef3c7;
            color: #92400e;
        }

        .dikembalikan {
            background: #dcfce7;
            color: #166534;
        }

        .empty {
            text-align: center;
            color: #777;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <h2>📚 Sistem Perpustakaan</h2>
</div>

<div class="container">

<!-- FORM PEMINJAMAN -->
<div class="card">
    <h1>Peminjaman Buku</h1>

    <a href="dashboard.php" class="btn btn-back">
        ← Kembali ke Dashboard
    </a>

    <form method="POST">

        <!-- PILIH ANGGOTA -->
        <label>Pilih Anggota</label>
        <select name="id_anggota" required>
            <option value="">-- Pilih Anggota --</option>
            <?php while ($anggota = pg_fetch_assoc($queryAnggota)): ?>
                <option value="<?= $anggota['id_anggota'] ?>">
                    <?= htmlspecialchars($anggota['nama_anggota']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- PILIH BUKU -->
        <label>Pilih Buku</label>
        <select name="id_buku" required>
            <option value="">-- Pilih Buku --</option>
            <?php while ($buku = pg_fetch_assoc($queryBuku)): ?>
                <option value="<?= $buku['id_buku'] ?>">
                    <?= htmlspecialchars($buku['judul_buku']) ?> - Stok: <?= $buku['stok'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- TANGGAL PINJAM -->
        <label>Tanggal Pinjam</label>
        <input 
            type="date" 
            name="tanggal_pinjam" 
            value="<?= date('Y-m-d') ?>" 
            required
        >

        <button type="submit" name="pinjam">
            📚 Pinjam Buku
        </button>

    </form>
</div>

<!-- DAFTAR TRANSAKSI -->
<div class="card">
    <h1>Daftar Peminjaman</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Anggota</th>
                <th>Judul Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Status</th>
                <th>Denda</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
        <?php if (pg_num_rows($queryPeminjaman) > 0): ?>
            <?php while ($data = pg_fetch_assoc($queryPeminjaman)): 
                // Hitung denda menggunakan fungsi di denda.php
                $denda = 0;
                if (function_exists('hitungDenda')) {
                    $denda = hitungDenda($data['tanggal_pinjam'], $data['tanggal_kembali']);
                }
            ?>
                <tr>
                    <td><?= $data['id_transaksi'] ?></td>
                    <td><?= htmlspecialchars($data['nama_anggota']) ?></td>
                    <td><?= htmlspecialchars($data['judul_buku']) ?></td>
                    <td><?= $data['tanggal_pinjam'] ?></td>
                    <td><?= $data['tanggal_kembali'] ?: '-' ?></td>
                    <td>
                        <?php if ($data['status'] === 'Dipinjam'): ?>
                            <span class="status dipinjam">Dipinjam</span>
                        <?php else: ?>
                            <span class="status dikembalikan">Dikembalikan</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($denda > 0): ?>
                            <strong style="color: #dc2626;">
                                Rp <?= number_format($denda, 0, ',', '.') ?>
                            </strong>
                        <?php else: ?>
                            Rp 0
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($data['status'] === 'Dipinjam'): ?>
                            <a 
                                href="transaksi.php?kembali=<?= $data['id_transaksi'] ?>" 
                                class="btn btn-kembali"
                                onclick="return confirm('Apakah buku ini sudah dikembalikan?')"
                            >
                                Kembalikan
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="empty">
                    Belum ada transaksi peminjaman.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div>

</body>
</html>