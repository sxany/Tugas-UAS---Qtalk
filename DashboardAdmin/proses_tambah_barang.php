<?php
session_start();

// Validasi RBAC
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../LoginPage/login.php');
    exit;
}

require_once __DIR__ . '/../LoginPage/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_barang = trim($_POST['nama_barang']);
    $deskripsi_barang = trim($_POST['deskripsi_barang']);
    $harga_barang = floatval($_POST['harga_barang']);
    $waktu = trim($_POST['waktu']);

    if (empty($nama_barang) || empty($deskripsi_barang) || $harga_barang <= 0 || empty($waktu) ) {
        header('Location: dashboardAdmin.php?error=invalid_input');
        exit;
    }

    // --- PROSES MENANGKAP GAMBAR DARI GALERI ---
    $nama_gambar_db = null; // Default kosong kalau upload gagal

    if (isset($_FILES['foto_barang']) && $_FILES['foto_barang']['error'] === 0) {
        $nama_file_asli = $_FILES['foto_barang']['name'];
        $tmp_name = $_FILES['foto_barang']['tmp_name'];
        
        // Dapatkan ekstensi file (misal: png atau jpg)
        $ekstensi = strtolower(pathinfo($nama_file_asli, PATHINFO_EXTENSION));
        
        // Ekstensi yang diizinkan masuk sistem lelang
        $ekstensi_valid = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ekstensi, $ekstensi_valid)) {
            // RENAME FOTO: Biar namanya unik di folder dan gak ketimpa sama file lain (Contoh hasil: 171956423_lelang.png)
            $nama_gambar_db = time() . '_' . uniqid() . '.' . $ekstensi;
            
            // Pindahkan file dari memori sementara windows ke folder img di DashboardUser
            $folder_tujuan = '../DashboardUser/img/' . $nama_gambar_db;
            
            if (!move_uploaded_file($tmp_name, $folder_tujuan)) {
                // Jika gagal pindah folder, batalkan proses insert demi keamanan
                die("Gagal memindahkan file foto dari galeri ke folder server project!");
            }
        } else {
            die("Format file salah! Sistem hanya menerima ekstensi JPG, JPEG, PNG, atau WEBP.");
        }
    }

    try {
        // Simpan semua data teks sekaligus string nama gambar baru ke database
        $query = "
            INSERT INTO barang_lelang (nama_barang, deskripsi_barang, harga_barang, gambar, status_lelang, waktu) 
            VALUES (:nama, :deskripsi, :harga, :gambar, 'aktif', :waktu)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':nama' => $nama_barang,
            ':deskripsi' => $deskripsi_barang,
            ':harga' => $harga_barang,
            ':gambar' => $nama_gambar_db,
            ':waktu' => $waktu
        ]);

        header('Location: dashboardAdmin.php?status=insert_success');
        exit;
    } catch (PDOException $e) {
        die("Gagal menyimpan komoditas lelang baru: " . $e->getMessage());
    }
} else {
    header('Location: dashboardAdmin.php');
    exit;
}