<?php
// pages/laporan/broken.php
require_once '../../includes/functions.php';
requireLogin();

$db = getDB();
$tables = getInventarisTables();

$allItems = [];
foreach ($tables as $table => $name) {
    $items = $db->query("SELECT ID, Code, Description, Notes, Status, RentDocNumber, CreatedDate, '$table' as Tbl, '$name' as Cat FROM $table WHERE Status = 2 ORDER BY CreatedDate DESC")->fetchAll();
    $allItems = array_merge($allItems, $items);
}

usort($allItems, function($a, $b) {
    return strtotime($b['CreatedDate']) - strtotime($a['CreatedDate']);
});

$pageTitle = 'Laporan Inventaris Status Broken';
$activePage = 'laporan_broken';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-triangle-exclamation"></i> Inventaris Status Broken</span>
        <span class="badge badge-broken"><?php echo count($allItems); ?> Item</span>
    </div>
    <div class="card-body">
        <div class="filter-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="tableSearch" placeholder="Cari data...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th>Catatan</th>
                        <th>No. Dokumen Pinjam</th>
                        <th>Tanggal Input</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allItems as $item): ?>
                    <tr>
                        <td><span class="badge" style="background: #e0e7ff; color: #4338ca;"><?php echo $item['Cat']; ?></span></td>
                        <td><strong><?php echo clean($item['Code']); ?></strong></td>
                        <td><?php echo clean($item['Description']); ?></td>
                        <td><?php echo clean($item['Notes'] ?? '-'); ?></td>
                        <td><?php echo clean($item['RentDocNumber'] ?? '-'); ?></td>
                        <td><?php echo formatDateTime($item['CreatedDate']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($allItems)): ?>
                    <tr><td colspan="6" class="empty-state" style="padding: 40px;">
                        <i class="fas fa-inbox"></i><h3>Tidak ada data</h3><p>Belum ada inventaris dengan status Broken</p>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>