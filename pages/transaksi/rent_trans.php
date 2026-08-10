<?php
require_once '../../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$db = getDB();

$rent = $db->prepare("SELECT r.*, e.Description as EmployeeName FROM tRent r LEFT JOIN mEmployee e ON r.EmployeeID = e.ID WHERE r.ID = ?");
$rent->execute([$id]);
$rent = $rent->fetch();

if (!$rent) {
    setFlash('danger', 'Data tidak ditemukan!');
    redirect('rent.php');
}

$items = $db->prepare("SELECT * FROM tRentTrans WHERE DocID = ?");
$items->execute([$id]);
$items = $items->fetchAll();

$pageTitle = 'Detail Peminjaman ' . $rent['DocNumber'];
$activePage = 'rent';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Detail Peminjaman #<?php echo clean($rent['DocNumber']); ?></span>
        <a href="rent.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <div class="form-grid" style="margin-bottom: 20px;">
            <div><label class="form-label">No Dokumen</label><p><strong><?php echo clean($rent['DocNumber']); ?></strong></p></div>
            <div><label class="form-label">Tanggal</label><p><?php echo formatDate($rent['DocDate']); ?></p></div>
            <div><label class="form-label">Karyawan</label><p><?php echo clean($rent['EmployeeName'] ?? '-'); ?></p></div>
            <div><label class="form-label">Status</label><p><?php echo getVoidLabel($rent['Void']); ?> <?php echo getReturnLabel($rent['Return']); ?></p></div>
        </div>
        <h4 style="margin-bottom: 12px;">Item yang Dipinjam</h4>
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Item ID</th><th>Deskripsi</th><th>Qty</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($items as $i): ?>
                <tr>
                    <td><?php echo $i['ID']; ?></td>
                    <td><?php echo $i['ItemRentID']; ?></td>
                    <td><?php echo clean($i['ItemRentDesc']); ?></td>
                    <td><?php echo $i['ItemQty']; ?></td>
                    <td><?php echo getVoidLabel($i['Void']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
