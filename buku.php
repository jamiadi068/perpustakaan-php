<?php

require_once "config/database.php";


// ======================================================
// TAMBAH DATA BUKU
// ======================================================

if (isset($_POST['tambah'])) {

    $judul = $_POST['judul_buku'];
    $penulis = $_POST['penulis'];
    $penerbit = $_POST['penerbit'];
    $tahun = $_POST['tahun_terbit'];
    $stok = $_POST['stok'];

    $query = pg_query_params(
        $conn,

        "INSERT INTO buku
        (
            judul_buku,
            penulis,
            penerbit,
            tahun_terbit,
            stok
        )
        VALUES
        ($1, $2, $3, $4, $5)",

        [
            $judul,
            $penulis,
            $penerbit,
            $tahun,
            $stok
        ]
    );


    if ($query) {

        header("Location: buku.php");

        exit;

    } else {

        echo "Gagal menambahkan buku.";

    }
}


// ======================================================
// HAPUS DATA BUKU
// ======================================================

if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];


    $query = pg_query_params(
        $conn,

        "DELETE FROM buku
         WHERE id_buku = $1",

        [$id]
    );


    if ($query) {

        header("Location: buku.php");

        exit;

    } else {

        echo "Gagal menghapus buku.";

    }
}


// ======================================================
// AMBIL DATA BUKU UNTUK EDIT
// ======================================================

$dataEdit = null;


if (isset($_GET['edit'])) {

    $id = $_GET['edit'];


    $queryEdit = pg_query_params(
        $conn,

        "SELECT *
         FROM buku
         WHERE id_buku = $1",

        [$id]
    );


    $dataEdit = pg_fetch_assoc(
        $queryEdit
    );

}


// ======================================================
// UPDATE DATA BUKU
// ======================================================

if (isset($_POST['update'])) {

    $id = $_POST['id_buku'];

    $judul = $_POST['judul_buku'];

    $penulis = $_POST['penulis'];

    $penerbit = $_POST['penerbit'];

    $tahun = $_POST['tahun_terbit'];

    $stok = $_POST['stok'];


    $query = pg_query_params(
        $conn,

        "UPDATE buku

         SET
            judul_buku = $1,
            penulis = $2,
            penerbit = $3,
            tahun_terbit = $4,
            stok = $5

         WHERE id_buku = $6",

        [
            $judul,
            $penulis,
            $penerbit,
            $tahun,
            $stok,
            $id
        ]
    );


    if ($query) {

        header("Location: buku.php");

        exit;

    } else {

        echo "Gagal mengupdate buku.";

    }

}


// ======================================================
// AMBIL SEMUA DATA BUKU
// ======================================================

$query = pg_query(

    $conn,

    "SELECT *

     FROM buku

     ORDER BY id_buku DESC"

);

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>Data Buku</title>


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

            box-shadow:
                0 3px 10px
                rgba(0,0,0,0.1);

            margin-bottom: 25px;

        }


        h1 {

            margin-top: 0;

        }


        label {

            display: block;

            margin-bottom: 5px;

            font-weight: bold;

        }


        input {

            width: 100%;

            padding: 10px;

            margin-bottom: 15px;

            border:

                1px solid #ddd;

            border-radius: 5px;

        }


        button,
        .btn {

            display: inline-block;

            padding: 10px 15px;

            border: none;

            border-radius: 5px;

            text-decoration: none;

            cursor: pointer;

            color: white;

        }


        button {

            background: #2563eb;

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


        table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 20px;

        }


        th,
        td {

            padding: 12px;

            border-bottom:
                1px solid #ddd;

            text-align: left;

        }


        th {

            background: #2563eb;

            color: white;

        }


        .aksi {

            display: flex;

            gap: 5px;

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

    <h2>
        📚 Sistem Perpustakaan
    </h2>

</div>


<div class="container">


<!-- FORM TAMBAH / EDIT -->

<div class="card">


<?php if ($dataEdit): ?>


    <h1>
        Edit Buku
    </h1>


    <form method="POST">


        <input
            type="hidden"

            name="id_buku"

            value="<?= htmlspecialchars(
                $dataEdit['id_buku']
            ) ?>"
        >


        <label>
            Judul Buku
        </label>


        <input

            type="text"

            name="judul_buku"

            value="<?= htmlspecialchars(
                $dataEdit['judul_buku']
            ) ?>"

            required

        >


        <label>
            Penulis
        </label>


        <input

            type="text"

            name="penulis"

            value="<?= htmlspecialchars(
                $dataEdit['penulis']
            ) ?>"

        >


        <label>
            Penerbit
        </label>


        <input

            type="text"

            name="penerbit"

            value="<?= htmlspecialchars(
                $dataEdit['penerbit']
            ) ?>"

        >


        <label>
            Tahun Terbit
        </label>


        <input

            type="number"

            name="tahun_terbit"

            value="<?= htmlspecialchars(
                $dataEdit['tahun_terbit']
            ) ?>"

        >


        <label>
            Stok
        </label>


        <input

            type="number"

            name="stok"

            min="0"

            value="<?= htmlspecialchars(
                $dataEdit['stok']
            ) ?>"

            required

        >


        <button

            type="submit"

            name="update"

        >

            Update Buku

        </button>


        <a

            href="buku.php"

            class="btn btn-back"

        >

            Batal

        </a>


    </form>


<?php else: ?>


    <h1>
        Tambah Buku
    </h1>


    <form method="POST">


        <label>
            Judul Buku
        </label>


        <input

            type="text"

            name="judul_buku"

            placeholder="Masukkan judul buku"

            required

        >


        <label>
            Penulis
        </label>


        <input

            type="text"

            name="penulis"

            placeholder="Masukkan nama penulis"

        >


        <label>
            Penerbit
        </label>


        <input

            type="text"

            name="penerbit"

            placeholder="Masukkan nama penerbit"

        >


        <label>
            Tahun Terbit
        </label>


        <input

            type="number"

            name="tahun_terbit"

            placeholder="Contoh: 2024"

        >


        <label>
            Stok
        </label>


        <input

            type="number"

            name="stok"

            min="0"

            placeholder="Masukkan jumlah stok"

            required

        >


        <button

            type="submit"

            name="tambah"

        >

            + Tambah Buku

        </button>


    </form>


<?php endif; ?>


</div>


<!-- DATA BUKU -->

<div class="card">


    <h1>
        Data Buku
    </h1>


    <a

        href="dashboard.php"

        class="btn btn-back"

    >

        ← Kembali ke Dashboard

    </a>


    <table>


        <thead>

            <tr>

                <th>
                    ID
                </th>

                <th>
                    Judul Buku
                </th>

                <th>
                    Penulis
                </th>

                <th>
                    Penerbit
                </th>

                <th>
                    Tahun
                </th>

                <th>
                    Stok
                </th>

                <th>
                    Aksi
                </th>

            </tr>

        </thead>


        <tbody>


        <?php if (
            pg_num_rows($query) > 0
        ): ?>


            <?php while (
                $buku =
                pg_fetch_assoc($query)
            ): ?>


                <tr>


                    <td>

                        <?= htmlspecialchars(
                            $buku['id_buku']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $buku['judul_buku']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $buku['penulis']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $buku['penerbit']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $buku['tahun_terbit']
                        ) ?>

                    </td>


                    <td>

                        <?= htmlspecialchars(
                            $buku['stok']
                        ) ?>

                    </td>


                    <td>


                        <div class="aksi">


                            <a

                                href="buku.php?edit=<?= $buku['id_buku'] ?>"

                                class="btn btn-edit"

                            >

                                Edit

                            </a>


                            <a

                                href="buku.php?hapus=<?= $buku['id_buku'] ?>"

                                class="btn btn-delete"

                                onclick="
                                    return confirm(
                                        'Yakin ingin menghapus buku ini?'
                                    )
                                "

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

                    colspan="7"

                    class="empty"

                >

                    Belum ada data buku.

                </td>


            </tr>


        <?php endif; ?>


        </tbody>


    </table>


</div>


</div>


</body>

</html>
