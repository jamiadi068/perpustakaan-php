<?php
session_start();

// Proteksi Halaman: Jika belum login, lempar ke login.php
// (Pastikan $_SESSION['login'] di-set saat proses login berhasil)
if (!isset($_SESSION['login']) && !isset($_GET['logout'])) {
    // opsional: aktifkan baris berikut jika ingin proteksi akses
    // header("Location: login.php"); exit;
}

// Proses Logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

require_once "config/database.php";

// Hitung jumlah anggota
$queryAnggota = pg_query($conn, "SELECT COUNT(*) AS total FROM anggota");
$totalAnggota = pg_fetch_assoc($queryAnggota)['total'];

// Hitung jumlah buku
// Hitung total seluruh stok buku (Menggunakan SUM)
$queryBuku = pg_query($conn, "SELECT COALESCE(SUM(stok), 0) AS total FROM buku");
$totalBuku = pg_fetch_assoc($queryBuku)['total'];


// Hitung transaksi yang sedang dipinjam
$queryDipinjam = pg_query(
    $conn,
    "SELECT COUNT(*) AS total FROM transaksi WHERE status = 'Dipinjam'"
);
$totalDipinjam = pg_fetch_assoc($queryDipinjam)['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <title>Dashboard Perpustakaan</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
        }

        .navbar {
            background: #2563eb;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h2 {
            margin: 0;
        }

        .container {
            padding: 30px;
        }

        .cards {
            display: flex;
            gap: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            width: 200px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .card h2 {
            font-size: 35px;
            margin: 10px 0;
        }

        .menu {
            margin-top: 30px;
        }

        .menu a {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 10px;
        }

        /* Styling Tombol Logout di Navbar */
        .btn-logout {
            background: #dc2626;
            color: white;
            padding: 9px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
            transition: background 0.2s;
        }

        .btn-logout:hover {
            background: #b91c1c;
        }
    </style>

</head>

<body>

<div class="navbar">
    <h2>📚 Sistem Perpustakaan</h2>
    <!-- Satu-satunya Tombol Logout di Pojok Kanan Navbar -->
    <a href="dashboard.php?logout=true" class="btn-logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
        Logout 🚪
    </a>
</div>

<div class="container">

    <h1>Dashboard</h1>

    <div class="cards p-2 mb-2 text-center">

        <div class="card">
            <p>Total Anggota</p>
            <h2><?= $totalAnggota ?></h2>
        </div>

        <div class="card">
            <p>Total Buku</p>
            <h2><?= $totalBuku ?></h2>
        </div>

        <div class="card">
            <p>Sedang Dipinjam</p>
            <h2><?= $totalDipinjam ?></h2>
        </div>

    </div>

    <!-- Area Menu Navigasi Utama (Hanya Tautan Halaman) -->
    <div class="menu">

        <a href="anggota.php">
            Data Anggota
        </a>

        <a href="buku.php">
            Data Buku
        </a>

        <a href="transaksi.php">
            Transaksi
        </a>

    </div>

</div>

</body>

</html>