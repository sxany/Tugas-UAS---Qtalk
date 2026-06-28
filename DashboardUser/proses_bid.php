<?php
session_start();

// Hubungkan ke database
require_once __DIR__ . '/../LoginPage/koneksi.php';

// Cek apakah data dikirim via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_user = 1; 

    $id_barang = isset($_POST['id_barang']) ? intval($_POST['id_barang']) : 0;
    $harga_tawar = isset($_POST['harga_tawar']) ? floatval($_POST['harga_tawar']) : 0;

    // DEBUGGING DI LAYAR: Kalau gagal, kita cetak nilainya langsung di browser biar ketahuan mana yang kosong
    if ($id_barang <= 0 || $harga_tawar <= 0) {
        echo "<h3>🚨 DEBUGGING ERROR ALFA AUCTION 🚨</h3>";
        echo "Waduh Bro, ada data form yang gagal kekirim ke server!<br><br>";
        echo "<b>Isi POST yang diterima server:</b><br>";
        echo "<pre>"; print_r($_POST); echo "</pre>";
        echo "<br><b>Variabel Terbaca:</b><br>";
        echo "- ID User: " . $id_user . "<br>";
        echo "- ID Barang: " . $id_barang . " (Gagal kalau nilainya 0)<br>";
        echo "- Harga Tawar: " . $harga_tawar . " (Gagal kalau nilainya 0)<br><br>";
        echo "<a href='dashboardUser.php'>⬅️ Kembali ke Dashboard</a>";
        exit;
    }

    try {
        // 2. CEK HARGA BERJALAN SAAT INI DI DATABASE
        $query_cek = "
            SELECT 
                bl.harga_barang AS harga_awal,
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
            die("Eror: Barang dengan ID $id_barang tidak ditemukan atau statusnya sudah tidak aktif di database!");
        }

        // 3. VALIDASI NOMINAL
        if ($harga_tawar <= $barang['harga_berjalan']) {
            header('Location: dashboardUser.php?tab=daftar&status=bid_too_low');
            exit;
        }

        // 4. INSERT DATA BID BARU
        $query_insert = "INSERT INTO bid (barang_id, user_id, harga_tawar) VALUES (:barang_id, :user_id, :harga_tawar)";
        $stmt_insert = $pdo->prepare($query_insert);
        $stmt_insert->execute([
            ':barang_id' => $id_barang,
            ':user_id' => $id_user,
            ':harga_tawar' => $harga_tawar
        ]);

        // Sukses!
        header('Location: dashboardUser.php?tab=daftar&status=bid_success');
        exit;

    } catch (PDOException $e) {
        die("Eror Database: " . $e->getMessage());
    }
} else {
    header('Location: dashboardUser.php');
    exit;
}