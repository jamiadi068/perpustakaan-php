<?php

$host = "localhost";
$port = "5432";
$dbname = "db_perpus";
$user = "postgres";
$password = "admin123";

// Membuat koneksi ke PostgreSQL
$conn = pg_connect(
    "host=$host port=$port dbname=$dbname user=$user password=$password"
);

// Cek koneksi
if (!$conn) {
    die("❌ Koneksi ke PostgreSQL gagal: " . pg_last_error());
}

