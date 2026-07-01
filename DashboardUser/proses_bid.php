<?php
session_start();

// Hubungkan ke database
require_once __DIR__ . '/../LoginPage/koneksi.php';

// Cek apakah data dikirim via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_user = 1; // Bypass ID user disamakan bernilai 1

    $id_barang = isset($_POST['id_barang']) ? intval($_POST['id_barang']) : 0;
    $harga_tawar = isset($_POST['harga_tawar']) ? floatval($_POST['harga_tawar']) : 0;

    // Cek kelengkapan data form awal
    if ($id_barang <= 0 || $harga_tawar <= 0) {
        header('Location: dashboardUser.php?tab=daftar&status=error');
        exit;
    }

    try {
        // ==========================================================
        // 1. KEKURANGAN PERTAMA: AMBIL DATA SALDO USER SEKARANG
        // ==========================================================
        $query_user = "SELECT saldo FROM users WHERE id_user = :id_user";
        $stmt_user = $pdo->prepare($query_user);
        $stmt_user->execute([':id_user' => $id_user]);
        $user = $stmt_user->fetch();
        
        $saldo_user = $user ? floatval($user['saldo']) : 0;

        // ==========================================================
        // 2. KEKURANGAN KEDUA: VALIDASI APAKAH SALDO MENCUKUPI NILAI BID
        // ==========================================================
        if ($harga_tawar > $saldo_user) {
            // Jika harga tawar lebih besar dari isi dompet saldo, tolak langsung!
            header('Location: dashboardUser.php?tab=daftar&status=saldo_insufficient');
            exit;
        }

        // 3. AMBIL DATA HARGA BERJALAN BARANG LELANG
        $query_cek = "
            SELECT 
                bl.id_barang, 
                IFNULL(MAX(b.harga_tawar), bl.harga_barang) AS harga_berjalan
            FROM barang_lelang bl
            LEFT JOIN bid b ON bl.id_barang = b.barang_id
            WHERE bl.id_barang = :id_barang AND bl.status_lelang = 'aktif'
            GROUP BY bl.id_barang
        ";
        
        $stmt_cek = $pdo->prepare($query_cek);
        $stmt_cek->execute([':id_barang' => $id_barang]);
        $barang = $stmt_cek->fetch();

        if (!$barang) {
            die("Error: Barang tidak ditemukan atau sudah tidak aktif!");
        }

        // 4. VALIDASI PERSAINGAN HARGA: Nominal harus di atas harga berjalan saat ini
        if ($harga_tawar <= $barang['harga_berjalan']) {
            header('Location: dashboardUser.php?tab=daftar&status=bid_too_low');
            exit;
        }

        // 5. JIKA SEMUA VALIDASI AMAN, BARU INSERT DATA BID BARU
        $query_insert = "INSERT INTO bid (barang_id, user_id, harga_tawar) VALUES (:barang_id, :user_id, :harga_tawar)";
        $stmt_insert = $pdo->prepare($query_insert);
        $stmt_insert->execute([
            ':barang_id' => $id_barang,
            ':user_id' => $id_user,
            ':harga_tawar' => $harga_tawar
        ]);

        header('Location: dashboardUser.php?tab=daftar&status=success');
        exit;

    } catch (PDOException $e) {
        die("Gagal memproses penawaran lelang: " . $e->getMessage());
    }
} else {
    header('Location: dashboardUser.php');
    exit;
}