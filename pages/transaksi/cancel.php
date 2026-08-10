<?php
require_once '../../includes/functions.php';
requireLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $type = $_POST['type'] ?? '';
    $id = (int)($_POST['ID'] ?? 0);
    $user = getUserName();
    
    try {
        if ($type == 'rent' && $action == 'void') {
            $db->beginTransaction();
            // Void rent
            $db->prepare("UPDATE tRent SET Void=1, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")->execute([$user, $id]);
            $db->prepare("UPDATE tRentTrans SET Void=1, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE DocID=?")->execute([$user, $id]);
            
            // Kembalikan status item ke Idle (0)
            $items = $db->prepare("SELECT ItemRentID FROM tRentTrans WHERE DocID = ?");
            $items->execute([$id]);
            // Note: perlu tahu tabel mana, untuk sederhana update semua tabel yang mungkin
            $tables = getInventarisTables();
            foreach ($tables as $tbl => $nm) {
                $db->prepare("UPDATE $tbl SET Status=0, RentDocID=NULL, RentDocNumber=NULL, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE RentDocID=?")
                   ->execute([$user, $id]);
            }
            $db->commit();
            setFlash('success', 'Peminjaman berhasil dibatalkan!');
        }
        
        if ($type == 'request' && $action == 'void') {
            $db->prepare("UPDATE tRequestRent SET Void=1, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")->execute([$user, $id]);
            setFlash('success', 'Request berhasil dibatalkan!');
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        setFlash('danger', 'Error: ' . $e->getMessage());
    }
    redirect('cancel.php');
}

$rents = $db->query("SELECT r.*, e.Description as EmployeeName FROM tRent r LEFT JOIN mEmployee e ON r.EmployeeID = e.ID WHERE r.Void=0 ORDER BY r.CreatedDate DESC")->fetchAll();
$requests = $db->query("SELECT r.*, e.Description as EmployeeName FROM tRequestRent r LEFT JOIN mEmployee e ON r.EmployeeID = e.ID WHERE r.Void=0 ORDER BY r.CreatedDate DESC")->fetchAll();

$pageTitle = 'Pembatalan Transaksi';
$activePage = 'cancel';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-ban"></i> Pembatalan Peminjaman</span>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr><th>No Dokumen</th><th>Tanggal</th><th>Karyawan</th><th>Keterangan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($rents as $r): ?>
                <tr>
                    <td><strong><?php echo clean($r['DocNumber']); ?></strong></td>
                    <td><?php echo formatDate($r['DocDate']); ?></td>
                    <td><?php echo clean($r['EmployeeName'] ?? '-'); ?></td>
                    <td><?php echo clean($r['DocNotes'] ?? '-'); ?></td>
                    <td>
                        <form method="POST" class="void-form" style="display:inline;">
                            <input type="hidden" name="action" value="void">
                            <input type="hidden" name="type" value="rent">
                            <input type="hidden" name="ID" value="<?php echo $r['ID']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-ban"></i> Batalkan</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-ban"></i> Pembatalan Request</span>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr><th>No Request</th><th>Tanggal</th><th>Karyawan</th><th>Keterangan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                <tr>
                    <td><strong><?php echo clean($r['DocNumber']); ?></strong></td>
                    <td><?php echo formatDate($r['DocDate']); ?></td>
                    <td><?php echo clean($r['EmployeeName'] ?? '-'); ?></td>
                    <td><?php echo clean($r['DocNotes'] ?? '-'); ?></td>
                    <td>
                        <form method="POST" class="void-form" style="display:inline;">
                            <input type="hidden" name="action" value="void">
                            <input type="hidden" name="type" value="request">
                            <input type="hidden" name="ID" value="<?php echo $r['ID']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-ban"></i> Batalkan</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>