<?php
session_start();

// 1. KONTROL AKSES - Wajib Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../LoginPage/login.php');
    exit;
}

require_once __DIR__ . '/../LoginPage/koneksi.php';

// Validasi parameter URL mendasar
if (!isset($_GET['aksi']) || !isset($_GET['id'])) {
    header('Location: dashboardAdmin.php');
    exit;
}

$aksi = $_GET['aksi'];
$id_barang = intval($_GET['id']);

// --- AKSI 1: UPDATE STATUS LELANG (TOGGLE) ---
if ($aksi === 'toggle_status' && isset($_GET['status_sekarang'])) {
    $status_baru = ($_GET['status_sekarang'] === 'aktif') ? 'selesai' : 'aktif';
    
    try {
        $query = "UPDATE barang_lelang SET status_lelang = :status_baru WHERE id_barang = :id_barang";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':status_baru' => $status_baru,
            ':id_barang' => $id_barang
        ]);
        
        header('Location: dashboardAdmin.php?status=update_success');
        exit;
    } catch (PDOException $e) {
        die("Gagal memperbarui status lelang: " . $e->getMessage());
    }
}

// --- AKSI 2: DELETE BARANG ---
if ($aksi === 'hapus') {
    try {
        // Mulai database transaction demi keamanan data relasi
        $pdo->beginTransaction();
        
        // 1. Hapus dulu histori taruhan (bid) yang terikat dengan barang ini agar tidak error foreign key constraint
        $query_delete_bid = "DELETE FROM bid WHERE barang_id = :id_barang";
        $stmt_bid = $pdo->prepare($query_delete_bid);
        $stmt_bid->execute([':id_barang' => $id_barang]);
        
        // 2. Baru hapus data barang utamanya
        $query_delete_barang = "DELETE FROM barang_lelang WHERE id_barang = :id_barang";
        $stmt_barang = $pdo->prepare($query_delete_barang);
        $stmt_barang->execute([':id_barang' => $id_barang]);
        
        // Commit perubahan jika semua query sukses dijalankan
        $pdo->commit();
        
        header('Location: dashboardAdmin.php?status=delete_success');
        exit;
    } catch (PDOException $e) {
        // Rollback data jika ada error di tengah jalan
        $pdo->rollBack();
        die("Gagal menghapus data lelang: " . $e->getMessage());
    }
}

// Jika ada parameter aneh yang masuk, kembalikan ke dashboard
header('Location: dashboardAdmin.php');
exit;