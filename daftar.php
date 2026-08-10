<?php
session_start(); // 1. Wajib diaktifkan untuk mencatat session login pendaftar baru
require_once "config/database.php";

$status = null; // Variabel penampung status untuk memicu modal pop-up
$error_message = "";

// ======================================================
// PROSES PENDAFTARAN
// ======================================================

if (isset($_POST['daftar'])) {

    $nama              = trim($_POST['nama_anggota']);
    $alamat            = trim($_POST['alamat']);
    $no_telepon        = trim($_POST['no_telepon']);
    $password          = trim($_POST['password']);
    $confirm_password  = trim($_POST['confirm_password']);

    // Validasi backend: Pastikan password & konfirmasi cocok
    if ($password !== $confirm_password) {
        $status = 'failed';
        $error_message = "Password dan Konfirmasi Password tidak cocok!";
    } else {
        // Enkripsi password demi keamanan database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 2. Simpan data ke database & Ambil ID Anggota yang baru dibuat (RETURNING id_anggota)
        $query = pg_query_params(
            $conn,
            "INSERT INTO anggota
            (
                nama_anggota,
                alamat,
                no_telepon,
                password,
                tanggal_daftar
            )
            VALUES
            (
                $1,
                $2,
                $3,
                $4,
                CURRENT_DATE
            )
            RETURNING id_anggota",
            [
                $nama,
                $alamat,
                $no_telepon,
                $hashed_password
            ]
        );

        if ($query && pg_num_rows($query) > 0) {
            $new_user = pg_fetch_assoc($query);
            
            // 3. Set SESSION login untuk anggota baru
            $_SESSION['id_anggota']   = $new_user['id_anggota'];
            $_SESSION['nama_anggota'] = $nama;
            $_SESSION['role']         = 'anggota'; // Pembeda peran dengan admin jika ada

            $status = 'success';
        } else {
            $status = 'failed';
            $error_message = "Terjadi kesalahan saat menyimpan data ke database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota Perpustakaan</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }

        .navbar {
            background: #2563eb;
            color: white;
            padding: 20px 40px;
        }

        .navbar h2 {
            margin: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-top: 0;
            color: #1f2937;
        }

        .description {
            color: #6b7280;
            margin-bottom: 30px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
            color: #374151;
        }

        input,
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 15px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        /* ======================================================
           STYLING INPUT PASSWORD & TOMBOL MATA
           ====================================================== */
        .password-group {
            position: relative;
            margin-bottom: 20px;
        }

        .password-group input {
            margin-bottom: 0;
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            width: auto;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            background: none;
            color: #2563eb;
        }

        .toggle-password svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .error-text {
            color: #dc2626;
            font-size: 13px;
            margin-top: 5px;
            margin-bottom: 15px;
            display: none;
        }

        button[type="submit"] {
            width: 100%;
            padding: 13px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background: #1d4ed8;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #2563eb;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }

        /* ======================================================
           STYLING POP-UP MODAL CUSTOM
           ====================================================== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transform: scale(0.8);
            transition: all 0.3s ease;
        }

        .modal-overlay.active .modal-box {
            transform: scale(1);
        }

        .modal-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .modal-title {
            margin: 0 0 10px;
            font-size: 20px;
            color: #1f2937;
        }

        .modal-message {
            color: #6b7280;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .btn-modal {
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
        }

        .btn-modal:hover {
            background: #1d4ed8;
        }

        .btn-modal.error {
            background: #dc2626;
        }

        .btn-modal.error:hover {
            background: #b91c1c;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <h2>📚 Sistem Perpustakaan</h2>
</div>

<div class="container">
    <div class="card">
        <h1>📝 Pendaftaran Anggota</h1>
        <p class="description">
            Silakan isi formulir berikut untuk mendaftar sebagai anggota perpustakaan.
        </p>

        <form method="POST" id="registerForm" onsubmit="return validateForm()">
            <!-- NAMA -->
            <label for="nama_anggota">Nama Lengkap</label>
            <input type="text" id="nama_anggota" name="nama_anggota" placeholder="Masukkan nama lengkap" required>

            <!-- ALAMAT -->
            <label for="alamat">Alamat</label>
            <textarea id="alamat" name="alamat" placeholder="Masukkan alamat lengkap" required></textarea>

            <!-- NOMOR TELEPON -->
            <label for="no_telepon">Nomor Telepon</label>
            <input type="text" id="no_telepon" name="no_telepon" placeholder="Contoh: 081234567890" required>

            <!-- PASSWORD -->
            <label for="password">Password</label>
            <div class="password-group">
                <input type="password" id="password" name="password" placeholder="Masukkan password akun" required>
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)">
                    <svg class="eye-icon" viewBox="0 0 24 24">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                    </svg>
                </button>
            </div>

            <!-- KONFIRMASI PASSWORD -->
            <label for="confirm_password">Konfirmasi Password</label>
            <div class="password-group">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password Anda" required>
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm_password', this)">
                    <svg class="eye-icon" viewBox="0 0 24 24">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                    </svg>
                </button>
            </div>
            <div id="passwordError" class="error-text">⚠️ Password dan Konfirmasi Password tidak cocok!</div>

            <!-- TOMBOL -->
            <button type="submit" name="daftar">📝 Daftar Sekarang</button>
        </form>

        <a href="index.php" class="back">← Kembali ke Login</a>
    </div>
</div>

<!-- ======================================================
     POP-UP MODAL STRUCTURE
     ====================================================== -->
<?php if ($status === 'success'): ?>
<div class="modal-overlay active" id="customModal">
    <div class="modal-box">
        <div class="modal-icon">🎉</div>
        <h3 class="modal-title">Pendaftaran Berhasil!</h3>
        <p class="modal-message">Akun anggota baru berhasil dibuat.</p>
        <!-- 4. Diarahkan ke dashboard_anggota.php -->
        <button class="btn-modal" onclick="redirectPage('dashboard_anggota.php')">Lanjutkan ke Dashboard Anggota</button>
    </div>
</div>
<?php elseif ($status === 'failed'): ?>
<div class="modal-overlay active" id="customModal">
    <div class="modal-box">
        <div class="modal-icon">⚠️</div>
        <h3 class="modal-title">Pendaftaran Gagal!</h3>
        <p class="modal-message"><?= htmlspecialchars($error_message); ?></p>
        <button class="btn-modal error" onclick="closeModal()">Coba Lagi</button>
    </div>
</div>
<?php endif; ?>

<script>
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const isPassword = input.type === 'password';
        
        input.type = isPassword ? 'text' : 'password';

        if (isPassword) {
            btn.innerHTML = `
                <svg viewBox="0 0 24 24">
                    <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.44-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.17c0-1.66-1.34-3-3-3l-.17.02z"/>
                </svg>`;
        } else {
            btn.innerHTML = `
                <svg viewBox="0 0 24 24">
                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                </svg>`;
        }
    }

    function validateForm() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const errorText = document.getElementById('passwordError');

        if (password !== confirmPassword) {
            errorText.style.display = 'block';
            return false;
        }

        errorText.style.display = 'none';
        return true;
    }

    function redirectPage(url) {
        window.location.href = url;
    }

    function closeModal() {
        const modal = document.getElementById('customModal');
        if (modal) {
            modal.classList.remove('active');
        }
    }
</script>

</body>
</html>