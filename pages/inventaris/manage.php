<?php
require_once '../../includes/functions.php';
requireLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'void') {
    $id = (int)($_POST['ID'] ?? 0);
    $user = getUserName();

    if ($id > 0) {
        // Cek apakah sudah dikembalikan
        $cek = $db->prepare("SELECT `Return` FROM tRent WHERE ID = ?");
        $cek->execute([$id]);
        $row = $cek->fetch();

        if ($row && $row['Return'] == 1) {
            setFlash('warning', 'Peminjaman sudah dikembalikan, tidak bisa dibatalkan!');
        } else {
            // Void peminjaman dan kembalikan status inventaris ke Idle
            $db->beginTransaction();

            // Update header
            $db->prepare("UPDATE tRent SET Void=1, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
               ->execute([$user, $id]);
            // Update transaksi
            $db->prepare("UPDATE tRentTrans SET Void=1, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE DocID=?")
               ->execute([$user, $id]);

            // Kembalikan status inventaris ke Idle (0)
            $items = $db->prepare("SELECT ItemRentID, TableName FROM tRentTrans WHERE DocID = ? AND Void = 0");
            $items->execute([$id]);
            foreach ($items->fetchAll() as $item) {
                $table = $item['TableName'] ?? '';
                $itemId = (int)$item['ItemRentID'];
                if ($table && isset(getInventarisTables()[$table]) && $itemId > 0) {
                    $db->prepare("UPDATE `$table` SET Status=0, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
                       ->execute([$user, $itemId]);
                }
            }

            $db->commit();
            setFlash('success', 'Peminjaman berhasil dibatalkan!');
        }
    }
    redirect('pages/transaksi/rent.php');
}

// Ambil daftar peminjaman
$rents = $db->query("SELECT r.*, e.Description as EmployeeName 
                     FROM tRent r 
                     LEFT JOIN mEmployee e ON r.EmployeeID = e.ID 
                     WHERE r.Void = 0 
                     ORDER BY r.CreatedDate DESC")
             ->fetchAll();

$pageTitle = 'Data Peminjaman';
$activePage = 'rent';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-file-signature"></i> Data Peminjaman</span>
        <span style="color: var(--secondary); font-size: 13px;">
            <i class="fas fa-info-circle"></i> Peminjaman dibuat otomatis saat proses Request
        </span>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No Dokumen</th>
                    <th>Tanggal</th>
                    <th>Karyawan</th>
                    <th>Status Kembali</th>
                    <th>Keterangan</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rents as $r): ?>
                <tr>
                    <td><strong><?php echo clean($r['DocNumber']); ?></strong></td>
                    <td><?php echo formatDate($r['DocDate']); ?></td>
                    <td><?php echo clean($r['EmployeeName'] ?? '-'); ?></td>
                    <td><?php echo getReturnLabel($r['Return']); ?></td>
                    <td><?php echo clean($r['DocNotes'] ?? '-'); ?></td>
                    <td><?php echo formatDateTime($r['CreatedDate']); ?></td>
                    <td>
                        <?php if ($r['Return'] == 0): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin batalkan peminjaman ini? Status inventaris akan kembali ke Idle.')">
                            <input type="hidden" name="action" value="void">
                            <input type="hidden" name="ID" value="<?php echo $r['ID']; ?>">
                            <button type="submit" class="btn-icon btn-delete" title="Batalkan Peminjaman">
                                <i class="fas fa-ban"></i>
                            </button>
                        </form>
                        <?php else: ?>
                            <span class="badge badge-idle">Selesai</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>