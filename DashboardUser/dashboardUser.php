<?php
require_once '../LoginPage/authUser.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashboardUser</title>
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
    <h1>Selamat Datang di DashboardUser!</h1>
    <p>Ini adalah halaman dashboard setelah login berhasil.</p>
    <button><a href="../LoginPage/logout.php">Logout</a></button>
    <div>
</body>
</html>