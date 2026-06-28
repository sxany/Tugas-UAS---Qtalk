<?php
session_start();

// 1. KEAMANAN & AKSES KONTROL (RBAC) - Pastikan hanya admin yang bisa tambah barang
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../LoginPage/login.php');
    exit;
}

// Hubungkan ke database
require_once __DIR__ . '/../LoginPage/koneksi.php';

// Cek apakah data dikirim melalui metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan data input
    $nama_barang = trim($_POST['nama_barang']);
    $deskripsi_barang = trim($_POST['deskripsi_barang']);
    $harga_barang = floatval($_POST['harga_barang']);
    $status_lelang = 'aktif'; // Otomatis aktif saat pertama ditambahkan

    // Validasi input sederhana
    if (empty($nama_barang) || empty($deskripsi_barang) || $harga_barang <= 0) {
        header('Location: dashboardAdmin.php?error=invalid_input');
        exit;
    }

    try {
        // Query SQL untuk memasukkan barang baru
        $query = "INSERT INTO barang_lelang (nama_barang, deskripsi_barang, harga_barang, status_lelang) 
                  VALUES (:nama_barang, :deskripsi_barang, :harga_barang, :status_lelang)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':nama_barang' => $nama_barang,
            ':deskripsi_barang' => $deskripsi_barang,
            ':harga_barang' => $harga_barang,
            ':status_lelang' => $status_lelang
        ]);

        // Jika sukses, kembalikan ke dashboard admin dengan status sukses
        header('Location: dashboardAdmin.php?status=insert_success');
        exit;

    } catch (PDOException $e) {
        // Jika ada eror database
        die("Gagal menambahkan barang ke database: " . $e->getMessage());
    }
} else {
    // Jika diakses langsung tanpa POST, tendang balik
    header('Location: dashboardAdmin.php');
    exit;
}