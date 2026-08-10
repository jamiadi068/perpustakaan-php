<?php
require_once "config/database.php";

/*
|--------------------------------------------------------------------------
| FITUR DOWNLOAD DATA (CSV / EXCEL)
|--------------------------------------------------------------------------
*/
if (isset($_GET['download'])) {
    // Set Header untuk mendownload file CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Data_Anggota_' . date('Y-m-d_H-i-s') . '.csv');

    // Buka output stream PHP
    $output = fopen('php://output', 'w');

    // Tambahkan UTF-8 BOM agar Karakter Spesial & Accent terbaca rapi di Microsoft Excel
    fputs($output, "\xEF\xBB\xBF");

    // Header Kolom pada Excel
    fputcsv($output, ['ID Anggota', 'Nama Anggota', 'Alamat', 'No Telepon', 'Tanggal Daftar']);

    // Ambil Data dari Database PostgreSQL
    $queryDownload = pg_query($conn, "SELECT id_anggota, nama_anggota, alamat, no_telepon, tanggal_daftar FROM anggota ORDER BY id_anggota DESC");

    while ($row = pg_fetch_assoc($queryDownload)) {
        // Format nomor telepon agar tidak terpotong format angka/scientific Excel
        $row['no_telepon'] = "'" . $row['no_telepon'];
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

/*
|--------------------------------------------------------------------------
| TAMBAH DATA
|--------------------------------------------------------------------------
*/

if (isset($_POST['tambah'])) {

    $nama = $_POST['nama_anggota'];
    $alamat = $_POST['alamat'];
    $no_telepon = $_POST['no_telepon'];

    $query = pg_query_params(
        $conn,
        "INSERT INTO anggota
        (nama_anggota, alamat, no_telepon)
        VALUES ($1, $2, $3)",
        [
            $nama,
            $alamat,
            $no_telepon
        ]
    );

    if ($query) {
        header("Location: anggota.php");
        exit;
    } else {
        echo "Gagal menambahkan data anggota.";
    }
}


/*
|--------------------------------------------------------------------------
| HAPUS DATA
|--------------------------------------------------------------------------
*/

if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    $query = pg_query_params(
        $conn,
        "DELETE FROM anggota WHERE id_anggota = $1",
        [$id]
    );

    if ($query) {
        header("Location: anggota.php");
        exit;
    } else {
        echo "Gagal menghapus data anggota.";
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
        "SELECT * FROM anggota WHERE id_anggota = $1",
        [$id]
    );

    $dataEdit = pg_fetch_assoc($queryEdit);
}


/*
|--------------------------------------------------------------------------
| UPDATE DATA
|--------------------------------------------------------------------------
*/

if (isset($_POST['update'])) {

    $id = $_POST['id_anggota'];
    $nama = $_POST['nama_anggota'];
    $alamat = $_POST['alamat'];
    $no_telepon = $_POST['no_telepon'];

    $query = pg_query_params(
        $conn,
        "UPDATE anggota
        SET
            nama_anggota = $1,
            alamat = $2,
            no_telepon = $3
        WHERE id_anggota = $4",
        [
            $nama,
            $alamat,
            $no_telepon,
            $id
        ]
    );

    if ($query) {
        header("Location: anggota.php");
        exit;
    } else {
        echo "Gagal mengupdate data anggota.";
    }
}


/*
|--------------------------------------------------------------------------
| AMBIL SEMUA DATA ANGGOTA
|--------------------------------------------------------------------------
*/

$query = pg_query(
    $conn,
    "SELECT * FROM anggota
     ORDER BY id_anggota DESC"
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Data Anggota</title>

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

        h1 {
            margin-top: 0;
        }

        h2 {
            margin-top: 0;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;

            border: 1px solid #ddd;
            border-radius: 5px;
        }

        textarea {
            height: 80px;
            resize: vertical;
        }

        button,
        .btn {
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

        th,
        td {
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

        .aksi {
            display: flex;

            gap: 5px;
        }

    </style>

</head>

<body>


<!-- NAVBAR -->

<div class="navbar">

    <h2>📚 Sistem Perpustakaan</h2>

</div>


<div class="container">


<!-- FORM TAMBAH / EDIT -->

<div class="card">

<?php if ($dataEdit): ?>

    <h2>Edit Anggota</h2>

    <form method="POST">

        <input
            type="hidden"
            name="id_anggota"
            value="<?= htmlspecialchars($dataEdit['id_anggota']) ?>"
        >

        <div class="form-group">

            <label>Nama Anggota</label>

            <input
                type="text"
                name="nama_anggota"
                value="<?= htmlspecialchars($dataEdit['nama_anggota']) ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Alamat</label>

            <textarea
                name="alamat"
                required
            ><?= htmlspecialchars($dataEdit['alamat']) ?></textarea>

        </div>


        <div class="form-group">

            <label>No Telepon</label>

            <input
                type="text"
                name="no_telepon"
                value="<?= htmlspecialchars($dataEdit['no_telepon']) ?>"
                required
            >

        </div>


        <button
            type="submit"
            name="update"
        >
            Update Anggota
        </button>


        <a
            href="anggota.php"
            class="btn btn-back"
        >
            Batal
        </a>

    </form>


<?php else: ?>


    <h2>Tambah Anggota</h2>

    <form method="POST">


        <div class="form-group">

            <label>Nama Anggota</label>

            <input
                type="text"
                name="nama_anggota"
                placeholder="Masukkan nama anggota"
                required
            >

        </div>


        <div class="form-group">

            <label>Alamat</label>

            <textarea
                name="alamat"
                placeholder="Masukkan alamat"
                required
            ></textarea>

        </div>


        <div class="form-group">

            <label>No Telepon</label>

            <input
                type="text"
                name="no_telepon"
                placeholder="Masukkan nomor telepon"
                required
            >

        </div>


        <button
            type="submit"
            name="tambah"
        >
            + Tambah Anggota
        </button>

    </form>


<?php endif; ?>

</div>


<!-- DATA ANGGOTA -->

<div class="card">

    <h1>Data Anggota</h1>


    <div class="header-actions">
        <a
            href="dashboard.php"
            class="btn btn-back"
        >
            ← Kembali ke Dashboard
        </a>

        <a
            href="anggota.php?download=csv"
            class="btn btn-download"
        >
            📥 Download Data (.csv)
        </a>
    </div>


    <table>

        <thead>

            <tr>

                <th>ID</th>

                <th>Nama Anggota</th>

                <th>Alamat</th>

                <th>No Telepon</th>

                <th>Tanggal Daftar</th>

                <th>Aksi</th>

            </tr>

        </thead>


        <tbody>


        <?php if (pg_num_rows($query) > 0): ?>


            <?php while ($anggota = pg_fetch_assoc($query)): ?>


                <tr>


                    <td>
                        <?= htmlspecialchars(
                            $anggota['id_anggota']
                        ) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars(
                            $anggota['nama_anggota']
                        ) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars(
                            $anggota['alamat']
                        ) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars(
                            $anggota['no_telepon']
                        ) ?>
                    </td>


                    <td>
                        <?= htmlspecialchars(
                            $anggota['tanggal_daftar']
                        ) ?>
                    </td>


                    <td>

                        <div class="aksi">


                            <a
                                href="anggota.php?edit=<?= $anggota['id_anggota'] ?>"
                                class="btn btn-edit"
                            >
                                Edit
                            </a>


                            <a
                                href="anggota.php?hapus=<?= $anggota['id_anggota'] ?>"
                                class="btn btn-delete"
                                onclick="return confirm('Yakin ingin menghapus anggota ini?')"
                            >
                                Hapus
                            </a>


                        </div>

                    </td>


                </tr>


            <?php endwhile; ?>


        <?php else: ?>


            <tr>

                <td
                    colspan="6"
                    class="empty"
                >
                    Belum ada data anggota
                </td>

            </tr>


        <?php endif; ?>


        </tbody>

    </table>

</div>


</div>

</body>

</html>