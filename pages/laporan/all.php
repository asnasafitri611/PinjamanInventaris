<?php
// pages/laporan/all.php
require_once '../../includes/functions.php';
requireLogin();

$db = getDB();
$tables = getInventarisTables();

// Ambil semua data dari semua tabel
$allItems = [];
foreach ($tables as $table => $name) {
    $items = $db->query("SELECT ID, Code, Description, Notes, Status, CreatedDate, EditDate, '$table' as Tbl, '$name' as Cat FROM $table ORDER BY CreatedDate DESC LIMIT 100")->fetchAll();
    $allItems = array_merge($allItems, $items);
}

// Sort by date
usort($allItems, function($a, $b) {
    return strtotime($b['CreatedDate']) - strtotime($a['CreatedDate']);
});

$pageTitle = 'Laporan Semua Inventaris';
$activePage = 'laporan_all';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-list"></i> Semua Data Inventaris</span>
        <button class="btn btn-secondary btn-print"><i class="fas fa-print"></i> Cetak</button>
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
                        <th>Status</th>
                        <th>Input</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allItems as $item): ?>
                    <tr>
                        <td><span class="badge" style="background: #e0e7ff; color: #4338ca;"><?php echo $item['Cat']; ?></span></td>
                        <td><strong><?php echo clean($item['Code']); ?></strong></td>
                        <td><?php echo clean($item['Description']); ?></td>
                        <td><?php echo clean($item['Notes'] ?? '-'); ?></td>
                        <td><?php echo getStatusLabel($item['Status']); ?></td>
                        <td><?php echo formatDateTime($item['CreatedDate']); ?></td>
                        <td><?php echo $item['EditDate'] ? formatDateTime($item['EditDate']) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>