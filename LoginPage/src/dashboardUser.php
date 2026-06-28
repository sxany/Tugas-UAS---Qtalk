<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: /login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
        <style>

        label {
            display: inline-block;
            width: 70px;
        }

        body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        }

        form {
        text-align: left;
        }

    
       
    </style>
</head>
<body>
    <div>
    <h1>Selamat Datang di Dashboard User!</h1>
    <p>Ini adalah halaman dashboard setelah login berhasil.</p>
    <button><a href="../logout.php">Logout</a></button>
    <div>
</body>
</html>