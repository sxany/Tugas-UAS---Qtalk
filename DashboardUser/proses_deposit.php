<?php
session_start();


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../LoginPage/login.php');
    exit;
}

// 2. KONEKSI DATABASE (Menggunakan PDO bawaan proyekmu)
require_once __DIR__ . '/../LoginPage/koneksi.php';

// 3. PROSES VALIDASI FORM DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Menggunakan bypass ID user = 1 sesuai dengan struktur sistemmu saat ini
    $id_user = 1; 
    $nominal = isset($_POST['nominal']) ? floatval($_POST['nominal']) : 0;

    // Validasi: Nominal tidak boleh kurang dari Rp 10.000
    if ($nominal < 10000) {
        header('Location: dashboardUser.php?tab=deposit&status=nominal_low');
        exit;
    }

    try {
        // Query UPDATE menambah nilai saldo di tabel users yang baru kamu update
        $query = "UPDATE users SET saldo = saldo + :nominal WHERE id_user = :id_user";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':nominal' => $nominal,
            ':id_user' => $id_user
        ]);

        // Jika berhasil, balik ke dashboard membawa parameter sukses dan nominalnya
        header("Location: dashboardUser.php?tab=deposit&status=success&amount=" . $nominal);
        exit;

    } catch (PDOException $e) {
        // Jika terjadi error pada database
        header('Location: dashboardUser.php?tab=deposit&status=error');
        exit;
    }
} else {
    // Jika file ini diakses langsung tanpa form POST
    header('Location: dashboardUser.php');
    exit;
}