<?php
// dashboard.php
require_once 'includes/functions.php';
requireLogin();

$db = getDB();

// Hitung total per kategori inventaris
$tables = getInventarisTables();
$totalItems = 0;
$totalIdle = 0;
$totalRent = 0;
$totalRepair = 0;
$totalBroken = 0;

foreach ($tables as $table => $name) {
    $totalItems += (int)$db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    $totalIdle   += (int)$db->query("SELECT COUNT(*) FROM `$table` WHERE `Status` = 0")->fetchColumn();
    $totalRent   += (int)$db->query("SELECT COUNT(*) FROM `$table` WHERE `Status` = 1")->fetchColumn();
    $totalRepair += (int)$db->query("SELECT COUNT(*) FROM `$table` WHERE `Status` = 3")->fetchColumn();
    $totalBroken += (int)$db->query("SELECT COUNT(*) FROM `$table` WHERE `Status` = 2")->fetchColumn();
}

// Hitung transaksi
$totalRentDoc = (int)$db->query("SELECT COUNT(*) FROM `tRent` WHERE `Void` = 0")->fetchColumn();
$totalReturnDoc = (int)$db->query("SELECT COUNT(*) FROM `tRentReturn` WHERE `Void` = 0")->fetchColumn();
$totalRequest = (int)$db->query("SELECT COUNT(*) FROM `tRequestRent` WHERE `Void` = 0 AND `Rent` = 0")->fetchColumn();
// FIX: tambah backtick di `Return`
$totalPendingReturn = (int)$db->query("SELECT COUNT(*) FROM `tRent` WHERE `Void` = 0 AND `Return` = 0")->fetchColumn();

// Recent transactions
$recentRents = $db->query("SELECT r.*, e.Description as EmployeeName FROM `tRent` r LEFT JOIN `mEmployee` e ON r.EmployeeID = e.ID WHERE r.Void = 0 ORDER BY r.CreatedDate DESC LIMIT 5")
                   ->fetchAll();

$recentReturns = $db->query("SELECT r.*, e.Description as EmployeeName, rent.DocNumber as ReffDocNumber 
                             FROM `tRentReturn` r 
                             LEFT JOIN `mEmployee` e ON r.EmployeeID = e.ID 
                             LEFT JOIN `tRent` rent ON r.ReffDocID = rent.ID 
                             WHERE r.Void = 0 
                             ORDER BY r.CreatedDate DESC LIMIT 5")
                     ->fetchAll();

// Recent items added
$recentItems = [];
foreach (array_slice(array_keys($tables), 0, 5) as $table) {
    $item = $db->query("SELECT `Code`, `Description`, `Status`, `CreatedDate`, '$table' as TableName FROM `$table` ORDER BY `CreatedDate` DESC LIMIT 1")
               ->fetch();
    if ($item) $recentItems[] = $item;
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
include 'includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-boxes-stacked"></i></div>
        <div class="stat-info">
            <h3><?php echo number_format($totalItems); ?></h3>
            <p>Total Inventaris</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <h3><?php echo number_format($totalIdle); ?></h3>
            <p>Status Idle</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-hand-holding"></i></div>
        <div class="stat-info">
            <h3><?php echo number_format($totalRent); ?></h3>
            <p>Sedang Dipinjam</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-wrench"></i></div>
        <div class="stat-info">
            <h3><?php echo number_format($totalRepair); ?></h3>
            <p>Dalam Service</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="stat-info">
            <h3><?php echo number_format($totalBroken); ?></h3>
            <p>Rusak</p>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-clock-rotate-left"></i> Transaksi Peminjaman Terbaru</span>
            <a href="pages/transaksi/rent.php" class="btn btn-sm btn-primary">Lihat Semua</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Dokumen</th>
                            <th>Tanggal</th>
                            <th>Karyawan</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recentRents) > 0): ?>
                            <?php foreach ($recentRents as $r): ?>
                            <tr>
                                <td><strong><?php echo clean($r['DocNumber']); ?></strong></td>
                                <td><?php echo formatDate($r['DocDate']); ?></td>
                                <td><?php echo clean($r['EmployeeName'] ?? '-'); ?></td>
                                <td><?php echo getReturnLabel($r['Return']); ?></td>
                                <td><?php echo formatDateTime($r['CreatedDate']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center;">Belum ada transaksi</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-chart-simple"></i> Ringkasan Transaksi</span>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 8px;">
                    <span><i class="fas fa-file-signature" style="color: var(--primary);"></i> Total Peminjaman</span>
                    <strong><?php echo number_format($totalRentDoc); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 8px;">
                    <span><i class="fas fa-rotate-left" style="color: var(--success);"></i> Total Pengembalian</span>
                    <strong><?php echo number_format($totalReturnDoc); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 8px;">
                    <span><i class="fas fa-clipboard-list" style="color: var(--warning);"></i> Request Pending</span>
                    <strong><?php echo number_format($totalRequest); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 8px;">
                    <span><i class="fas fa-hourglass-half" style="color: var(--danger);"></i> Belum Kembali</span>
                    <strong><?php echo number_format($totalPendingReturn); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-rotate-left"></i> Pengembalian Terbaru</span>
        <a href="pages/transaksi/return.php" class="btn btn-sm btn-success">Lihat Semua</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Dokumen</th>
                        <th>Tanggal</th>
                        <th>Ref Peminjaman</th>
                        <th>Karyawan</th>
                        <th>Dibuat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recentReturns) > 0): ?>
                        <?php foreach ($recentReturns as $r): ?>
                        <tr>
                            <td><strong><?php echo clean($r['DocNumber']); ?></strong></td>
                            <td><?php echo formatDate($r['DocDate']); ?></td>
                            <td><strong><?php echo clean($r['ReffDocNumber'] ?? '-'); ?></strong></td>
                            <td><?php echo clean($r['EmployeeName'] ?? '-'); ?></td>
                            <td><?php echo formatDateTime($r['CreatedDate']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">Belum ada pengembalian</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-box-open"></i> Inventaris Terbaru Ditambahkan</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Tanggal Input</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentItems as $item): ?>
                    <tr>
                        <td><?php echo $tables[$item['TableName']]; ?></td>
                        <td><strong><?php echo clean($item['Code']); ?></strong></td>
                        <td><?php echo clean($item['Description']); ?></td>
                        <td><?php echo getStatusLabel($item['Status']); ?></td>
                        <td><?php echo formatDateTime($item['CreatedDate']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>