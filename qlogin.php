<?php

session_start();

require_once "config/database.php";


// Jika sudah login
if (isset($_SESSION['id_admin'])) {

    header("Location: dashboard.php");

    exit;
}


$error = "";


// Proses login
if (isset($_POST['login'])) {

    $username = trim($_POST['username']);

    $password = $_POST['password'];


    // Cari admin berdasarkan username
    $query = pg_query_params(

        $conn,

        "SELECT
            id_admin,
            nama_admin,
            username,
            password

         FROM admin

         WHERE username = $1",

        [$username]

    );


    $admin = pg_fetch_assoc($query);


    // Cek admin dan password
    if (
        $admin
        &&
        password_verify(
            $password,
            $admin['password']
        )
    ) {


        // Simpan data admin ke session
        $_SESSION['id_admin'] =
            $admin['id_admin'];

        $_SESSION['nama_admin'] =
            $admin['nama_admin'];

        $_SESSION['username'] =
            $admin['username'];


        // Redirect ke dashboard
        header(
            "Location: dashboard.php"
        );

        exit;


    } else {

        $error =
            "Username atau password salah!";

    }

}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Login Administrator
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background: #f4f6f9;

            display: flex;

            justify-content: center;

            align-items: center;

            min-height: 100vh;

        }


        .login-card {

            width: 400px;

            background: white;

            padding: 35px;

            border-radius: 12px;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.1);

        }


        .logo {

            text-align: center;

            font-size: 50px;

        }


        h1 {

            text-align: center;

            margin-bottom: 10px;

        }


        .subtitle {

            text-align: center;

            color: #777;

            margin-bottom: 30px;

        }


        label {

            display: block;

            font-weight: bold;

            margin-bottom: 7px;

        }


        input {

            width: 100%;

            padding: 12px;

            margin-bottom: 20px;

            border:

                1px solid #ddd;

            border-radius: 6px;

            font-size: 15px;

        }


        button {

            width: 100%;

            padding: 12px;

            background: #2563eb;

            color: white;

            border: none;

            border-radius: 6px;

            font-size: 16px;

            cursor: pointer;

        }


        button:hover {

            background: #1d4ed8;

        }


        .error {

            background: #fee2e2;

            color: #b91c1c;

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 20px;

            text-align: center;

        }

    </style>

</head>


<body>


<div class="login-card">


    <div class="logo">
        📚
    </div>


    <h1>
        Login Administrator
    </h1>


    <div class="subtitle">

        Sistem Informasi Perpustakaan

    </div>


    <?php if ($error): ?>


        <div class="error">

            <?= htmlspecialchars(
                $error
            ) ?>

        </div>


    <?php endif; ?>


    <form method="POST">


        <label>
            Username
        </label>


        <input

            type="text"

            name="username"

            placeholder="Masukkan username"

            required

        >


        <label>
            Password
        </label>


        <input

            type="password"

            name="password"

            placeholder="Masukkan password"

            required

        >


        <button

            type="submit"

            name="login"

        >

            🔐 Login

        </button>


    </form>


</div>


</body>

</html>
