<?php
require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| FITUR DOWNLOAD LAPORAN DATA BUKU (CSV / EXCEL)
|--------------------------------------------------------------------------
*/
if (isset($_GET['download'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laporan_Data_Buku_' . date('Y-m-d_H-i-s') . '.csv');

    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM untuk Excel

    fputcsv($output, ['ID Buku', 'Judul Buku', 'Pengarang', 'Penerbit', 'Tahun Terbit', 'Stok', 'Kategori']);

    $queryDownload = pg_query(
        $conn, 
        "SELECT * FROM buku ORDER BY id_buku DESC"
    );

    while ($row = pg_fetch_assoc($queryDownload)) {
        // Menggunakan ?? '' agar tidak crash/warning jika kolom tidak ada di DB
        fputcsv($output, [
            $row['id_buku'] ?? '',
            $row['judul_buku'] ?? $row['judul'] ?? '',
            $row['pengarang'] ?? $row['penulis'] ?? $row['nama_pengarang'] ?? '',
            $row['penerbit'] ?? '',
            $row['tahun_terbit'] ?? $row['tahun'] ?? '',
            $row['stok'] ?? $row['jumlah'] ?? '',
            $row['kategori'] ?? $row['jenis'] ?? ''
        ]);
    }

    fclose($output);
    exit;
}

/*
|--------------------------------------------------------------------------
| TAMBAH DATA BUKU
|--------------------------------------------------------------------------
*/
if (isset($_POST['tambah'])) {

    $judul      = $_POST['judul_buku'];
    $pengarang  = $_POST['pengarang'];
    $penerbit   = $_POST['penerbit'];
    $tahun      = $_POST['tahun_terbit'];
    $stok       = $_POST['stok'];
    $kategori   = $_POST['kategori'];

    $query = pg_query_params(
        $conn,
        "INSERT INTO buku (judul_buku, pengarang, penerbit, tahun_terbit, stok, kategori)
         VALUES ($1, $2, $3, $4, $5, $6)",
        [$judul, $pengarang, $penerbit, $tahun, $stok, $kategori]
    );

    if ($query) {
        header("Location: buku.php");
        exit;
    } else {
        echo "Gagal menambahkan data buku.";
    }
}

/*
|--------------------------------------------------------------------------
| HAPUS DATA BUKU
|--------------------------------------------------------------------------
*/
if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    $query = pg_query_params(
        $conn,
        "DELETE FROM buku WHERE id_buku = $1",
        [$id]
    );

    if ($query) {
        header("Location: buku.php");
        exit;
    } else {
        echo "Gagal menghapus data buku.";
    }
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA UNTUK EDIT
|--------------------------------------------------------------------------
*/
$dataEdit = null;

if (isset($_GET['edit'])) {

    $id = $_GET['edit'];

    $queryEdit = pg_query_params(
        $conn,
        "SELECT * FROM buku WHERE id_buku = $1",
        [$id]
    );

    $dataEdit = pg_fetch_assoc($queryEdit);
}

/*
|--------------------------------------------------------------------------
| UPDATE DATA BUKU
|--------------------------------------------------------------------------
*/
if (isset($_POST['update'])) {

    $id         = $_POST['id_buku'];
    $judul      = $_POST['judul_buku'];
    $pengarang  = $_POST['pengarang'];
    $penerbit   = $_POST['penerbit'];
    $tahun      = $_POST['tahun_terbit'];
    $stok       = $_POST['stok'];
    $kategori   = $_POST['kategori'];

    $query = pg_query_params(
        $conn,
        "UPDATE buku
         SET judul_buku = $1,
             pengarang  = $2,
             penerbit   = $3,
             tahun_terbit = $4,
             stok       = $5,
             kategori   = $6
         WHERE id_buku = $7",
        [$judul, $pengarang, $penerbit, $tahun, $stok, $kategori, $id]
    );

    if ($query) {
        header("Location: buku.php");
        exit;
    } else {
        echo "Gagal mengupdate data buku.";
    }
}

/*
|--------------------------------------------------------------------------
| AMBIL SEMUA DATA BUKU
|--------------------------------------------------------------------------
*/
$query = pg_query(
    $conn,
    "SELECT * FROM buku ORDER BY id_buku DESC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Buku</title>

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

        .btn-edit {
            background: #f59e0b;
        }

        .btn-delete {
            background: #dc2626;
        }

        .btn-download {
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
    <h2>📚 Sistem Perpustakaan - Kelola Buku</h2>
</div>

<div class="container">

<!-- FORM TAMBAH / EDIT BUKU -->
<div class="card">
<?php if ($dataEdit): ?>

    <h2>Edit Data Buku</h2>

    <form method="POST">
        <input type="hidden" name="id_buku" value="<?= htmlspecialchars($dataEdit['id_buku'] ?? '') ?>">

        <div class="form-group">
            <label>Judul Buku</label>
            <input type="text" name="judul_buku" value="<?= htmlspecialchars($dataEdit['judul_buku'] ?? $dataEdit['judul'] ?? '') ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Pengarang</label>
                <input type="text" name="pengarang" value="<?= htmlspecialchars($dataEdit['pengarang'] ?? $dataEdit['penulis'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Penerbit</label>
                <input type="text" name="penerbit" value="<?= htmlspecialchars($dataEdit['penerbit'] ?? '') ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Tahun Terbit</label>
                <input type="number" name="tahun_terbit" value="<?= htmlspecialchars($dataEdit['tahun_terbit'] ?? $dataEdit['tahun'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stok" value="<?= htmlspecialchars($dataEdit['stok'] ?? $dataEdit['jumlah'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <input type="text" name="kategori" value="<?= htmlspecialchars($dataEdit['kategori'] ?? '') ?>" required>
            </div>
        </div>

        <button type="submit" name="update">Update Buku</button>
        <a href="buku.php" class="btn btn-back">Batal</a>
    </form>

<?php else: ?>

    <h2>Tambah Buku Baru</h2>

    <form method="POST">
        <div class="form-group">
            <label>Judul Buku</label>
            <input type="text" name="judul_buku" placeholder="Masukkan judul buku" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Pengarang</label>
                <input type="text" name="pengarang" placeholder="Nama pengarang" required>
            </div>

            <div class="form-group">
                <label>Penerbit</label>
                <input type="text" name="penerbit" placeholder="Nama penerbit" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Tahun Terbit</label>
                <input type="number" name="tahun_terbit" placeholder="Contoh: 2024" required>
            </div>

            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stok" placeholder="Jumlah stok" required>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <input type="text" name="kategori" placeholder="Contoh: Pemrograman / Novel" required>
            </div>
        </div>

        <button type="submit" name="tambah">+ Tambah Buku</button>
    </form>

<?php endif; ?>
</div>

<!-- TABEL DATA BUKU & LAPORAN -->
<div class="card">
    <h1>Daftar Buku</h1>

    <div class="header-actions">
        <a href="dashboard.php" class="btn btn-back">
            ← Kembali ke Dashboard
        </a>

        <!-- TOMBOL DOWNLOAD LAPORAN CSV -->
        <a href="buku.php?download=csv" class="btn btn-download">
            📊 Download Laporan Buku (.csv)
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Judul Buku</th>
                <th>Pengarang</th>
                <th>Penerbit</th>
                <th>Tahun</th>
                <th>Stok</th>
                <th>Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if (pg_num_rows($query) > 0): ?>
            <?php while ($buku = pg_fetch_assoc($query)): ?>
                <tr>
                    <td><?= htmlspecialchars($buku['id_buku'] ?? '') ?></td>
                    <td><?= htmlspecialchars($buku['judul_buku'] ?? $buku['judul'] ?? '') ?></td>
                    <td><?= htmlspecialchars($buku['pengarang'] ?? $buku['penulis'] ?? $buku['nama_pengarang'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($buku['penerbit'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($buku['tahun_terbit'] ?? $buku['tahun'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($buku['stok'] ?? $buku['jumlah'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($buku['kategori'] ?? $buku['jenis'] ?? '-') ?></td>
                    <td>
                        <div class="aksi">
                            <a href="buku.php?edit=<?= $buku['id_buku'] ?? '' ?>" class="btn btn-edit">Edit</a>
                            <a href="buku.php?hapus=<?= $buku['id_buku'] ?? '' ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin menghapus buku ini?')">Hapus</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="empty">Belum ada data buku</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div>

</body>
</html>