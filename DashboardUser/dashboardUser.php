<?php
session_start();

// 1. KEAMANAN & AKSES KONTROL (RBAC)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../LoginPage/login.php');
    exit;
}

// Hubungkan ke database
require_once __DIR__ . '/../LoginPage/koneksi.php';

// Tentukan tab mana yang sedang aktif (default: daftar)
$tab_aktif = isset($_GET['tab']) ? $_GET['tab'] : 'daftar';

// =======================================================
// BYPASS ID USER LANGSUNG KE ANGKA 1 (Disamakan dengan proses_bid.php)
// =======================================================
$id_user = 1; 

try {
    if ($tab_aktif === 'daftar') {
        // QUERY TAB DAFTAR LELANG AKTIF
        $query = "
            SELECT 
                bl.id_barang, 
                bl.nama_barang, 
                bl.deskripsi_barang, 
                bl.harga_barang AS harga_awal,
                IFNULL(MAX(b.harga_tawar), bl.harga_barang) AS harga_berjalan,
                COUNT(b.id_bid) AS total_penawaran
            FROM barang_lelang bl
            LEFT JOIN bid b ON bl.id_barang = b.barang_id
            WHERE bl.status_lelang = 'aktif'
            GROUP BY bl.id_barang
            ORDER BY bl.id_barang DESC
        ";
        $stmt = $pdo->query($query);
        $daftar_lelang = $stmt->fetchAll();
    } else if ($tab_aktif === 'penawaran') {
        // --- QUERY BARU UNTUK TAB PENAWARAN SAYA (LEBIH RINGAN & PASTI SINKRON) ---
        $query = "
            SELECT 
                bl.id_barang,
                bl.nama_barang,
                bl.status_lelang,
                MAX(b.harga_tawar) AS bid_kamu,
                (SELECT IFNULL(MAX(harga_tawar), bl.harga_barang) FROM bid WHERE barang_id = bl.id_barang) AS harga_berjalan
            FROM bid b
            INNER JOIN barang_lelang bl ON b.barang_id = bl.id_barang
            WHERE b.user_id = :id_user
            GROUP BY bl.id_barang
            ORDER BY bl.id_barang DESC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id_user' => $id_user]);
        $penawaran_saya = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    die("Eror mengambil data lelang: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alfa Auction - Midnight Elegant</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans">

    <!-- NAVBAR ATAS (MIDNIGHT INDIGO) -->
    <nav class="bg-slate-900 border-b border-indigo-950/50 text-white p-4 shadow-xl flex justify-between items-center z-10">
        <h1 class="text-xl font-bold tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">✨ Alfa Auction</h1>
        <div class="flex items-center gap-4">
            <span class="font-medium text-slate-400 text-sm">Welcome, <strong class="text-indigo-300"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></strong></span>
            <a href="../LoginPage/logout.php" class="bg-rose-950/40 hover:bg-rose-900/60 text-rose-400 border border-rose-900/50 text-xs px-3 py-1.5 rounded-xl transition duration-200">Logout</a>
        </div>
    </nav>

    <!-- LAYOUT UTAMA (SIDEBAR + MAIN CONTENT) -->
    <div class="flex flex-1 flex-col md:flex-row">
        
        <!-- SIDEBAR DARK UNGU (Sebelah Kiri) -->
        <aside class="w-full md:w-64 bg-slate-900/50 border-r border-indigo-950/40 p-4 space-y-2">
            <div class="text-[10px] font-semibold text-indigo-400/70 uppercase tracking-widest px-3 mb-3">Menu Utama</div>
            
            <!-- Tab Daftar Lelang -->
            <a href="dashboardUser.php?tab=daftar" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200 <?php echo $tab_aktif === 'daftar' ? 'bg-gradient-to-r from-indigo-950/60 to-purple-950/40 text-indigo-300 font-bold border-l-4 border-indigo-500 shadow-inner' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-200'; ?>">
                📦 Daftar Lelang
            </a>
            
            <!-- Tab Penawaran Saya -->
            <a href="dashboardUser.php?tab=penawaran" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition duration-200 <?php echo $tab_aktif === 'penawaran' ? 'bg-gradient-to-r from-indigo-950/60 to-purple-950/40 text-indigo-300 font-bold border-l-4 border-indigo-500 shadow-inner' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-200'; ?>">
                💰 Penawaran Saya
            </a>
        </aside>

        <!-- KONTEN UTAMA (Sebelah Kanan) -->
        <main class="flex-1 p-6 bg-gradient-to-br from-slate-950 via-slate-950 to-slate-900">

            <!-- NOTIFIKASI BANNER NEO-DARK -->
            <?php if (isset($_GET['status']) && $_GET['status'] === 'bid_success'): ?>
                <div class="bg-emerald-950/30 border border-emerald-900/50 text-emerald-400 px-4 py-3 rounded-xl mb-6 shadow-lg backdrop-blur-sm">
                    🎉 <strong>Sukses!</strong> Penawaran (bid) kamu berhasil dipasang di server!
                </div>
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'bid_too_low'): ?>
                <div class="bg-rose-950/30 border border-rose-900/50 text-rose-400 px-4 py-3 rounded-xl mb-6 shadow-lg backdrop-blur-sm">
                    ❌ <strong>Gagal!</strong> Angka bid kamu kalah tinggi dari penawaran live saat ini!
                </div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] === 'invalid_input'): ?>
                <div class="bg-amber-950/30 border border-amber-900/50 text-amber-400 px-4 py-3 rounded-xl mb-6 shadow-lg backdrop-blur-sm">
                    ⚠️ Input salah. Mohon ketik nominal angka taruhan dengan benar.
                </div>
            <?php endif; ?>


            <!-- ===================== KONTEN 1: DAFTAR LELANG ===================== -->
            <?php if ($tab_aktif === 'daftar'): ?>
                <h2 class="text-xl font-bold mb-6 text-slate-300 tracking-wide flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></span> Daftar Lelang Aktif
                </h2>
                
                <?php if (empty($daftar_lelang)): ?>
                    <p class="text-slate-500 text-center py-10 bg-slate-900/40 border border-indigo-950/30 rounded-2xl shadow-inner">Belum ada komoditas lelang malam ini.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($daftar_lelang as $barang): ?>
                            <div class="bg-slate-900/80 rounded-2xl shadow-xl hover:shadow-indigo-950/20 overflow-hidden border border-indigo-950/60 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1">
                                
                                <div class="bg-gradient-to-br from-slate-800 to-indigo-950/50 h-44 w-full flex items-center justify-center text-indigo-300/70 font-bold border-b border-indigo-950/40">
                                    📦 <?php echo htmlspecialchars($barang['nama_barang']); ?>
                                </div>

                                <div class="p-5 flex-1 flex flex-col justify-between">
                                    <div>
                                        <h3 class="text-base font-bold text-slate-100 mb-1 tracking-wide"><?php echo htmlspecialchars($barang['nama_barang']); ?></h3>
                                        <p class="text-slate-400 text-xs mb-4 line-clamp-2 leading-relaxed"><?php echo htmlspecialchars($barang['deskripsi_barang']); ?></p>
                                        
                                        <div class="grid grid-cols-2 gap-2 mb-4 bg-slate-950/60 p-3 rounded-xl text-xs border border-indigo-950/40">
                                            <div>
                                                <span class="text-slate-500 block mb-0.5">Harga Buka:</span>
                                                <span class="font-semibold text-slate-300">Rp <?php echo number_format($barang['harga_awal'], 0, ',', '.'); ?></span>
                                            </div>
                                            <div>
                                                <span class="text-indigo-400 block mb-0.5 font-medium">Live Bid:</span>
                                                <span class="font-bold text-purple-400 text-sm tracking-wide">Rp <?php echo number_format($barang['harga_berjalan'], 0, ',', '.'); ?></span>
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-center text-[10px] text-slate-500 mb-4 border-b border-indigo-950/30 pb-3">
                                            <span class="flex items-center gap-1">⏳ <strong class="text-indigo-400/80">2 Jam Lagi (Gimik)</strong></span>
                                            <span class="bg-slate-950 px-2 py-0.5 rounded border border-indigo-950/30">💬 <?php echo $barang['total_penawaran']; ?> Taruhan</span>
                                        </div>
                                    </div>

                                    <form action="proses_bid.php" method="POST" class="mt-auto">
                                        <input type="hidden" name="id_barang" value="<?php echo $barang['id_barang']; ?>">
                                        <div class="flex gap-2">
                                            <input type="number" 
                                                   name="harga_tawar" 
                                                   placeholder="Input nominal" 
                                                   min="<?php echo $barang['harga_berjalan'] + 1000; ?>" 
                                                   step="1000" 
                                                   class="w-full px-3 py-2 bg-slate-950 border border-indigo-950/80 rounded-xl text-xs text-indigo-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 placeholder-slate-600" 
                                                   required>
                                            <button type="submit" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold text-xs px-4 py-2 rounded-xl transition shadow-md shadow-indigo-950/50">Bid</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>


            <!-- ===================== KONTEN 2: PENAWARAN SAYA ===================== -->
            <?php elseif ($tab_aktif === 'penawaran'): ?>
                <h2 class="text-xl font-bold mb-6 text-slate-300 tracking-wide flex items-center gap-2">
                    📊 Penawaran Saya
                </h2>
                
                <div class="bg-slate-900/60 rounded-2xl border border-indigo-950/60 overflow-hidden shadow-xl backdrop-blur-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-950 text-indigo-400 text-xs uppercase font-semibold tracking-wider border-b border-indigo-950/60">
                                    <th class="py-3 px-4">Nama Barang</th>
                                    <th class="py-3 px-4">Taruhan Terakhirmu</th>
                                    <th class="py-3 px-4">Harga Live Sekarang</th>
                                    <th class="py-3 px-4">Posisi Kamu</th>
                                    <th class="py-3 px-4">Status Event</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-indigo-950/30">
                                <?php if (empty($penawaran_saya)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-8 text-slate-500 bg-slate-900/20">Kamu belum pernah menaruh bid malam ini.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($penawaran_saya as $posisi): ?>
                                        <tr class="hover:bg-slate-900/30 transition duration-150">
                                            <td class="py-3 px-4 font-medium text-slate-200"><?php echo htmlspecialchars($posisi['nama_barang']); ?></td>
                                            <td class="py-3 px-4 text-slate-400 font-semibold">Rp <?php echo number_format($posisi['bid_kamu'], 0, ',', '.'); ?></td>
                                            <td class="py-3 px-4 font-bold text-purple-400">Rp <?php echo number_format($posisi['harga_berjalan'], 0, ',', '.'); ?></td>
                                            <td class="py-3 px-4">
                                                <?php if ($posisi['bid_kamu'] >= $posisi['harga_berjalan']): ?>
                                                    <span class="bg-emerald-950/40 text-emerald-400 text-xs font-medium px-2.5 py-1 rounded-full border border-emerald-900/40 shadow-sm shadow-emerald-950/50">🏆 Tertinggi (Memimpin)</span>
                                                <?php else: ?>
                                                    <span class="bg-rose-950/40 text-rose-400 text-xs font-medium px-2.5 py-1 rounded-full border border-rose-900/40 shadow-sm shadow-rose-950/50">📉 Tersalip (Kalah)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3 px-4">
                                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full bg-indigo-950/50 text-indigo-400 border border-indigo-900/30">
                                                    <?php echo strtoupper($posisi['status_lelang']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>

</body>
</html>