<?php
require_once '../../includes/functions.php';
requireLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'add') {
    $docDate = $_POST['DocDate'] ?? date('Y-m-d');
    $docNumber = generateDocNumber('RET', 'tRentReturn');
    $reffDocId = (int)($_POST['ReffDocID'] ?? 0);
    $notes = clean($_POST['DocNotes'] ?? '');
    $user = getUserName();

    try {
        $db->beginTransaction();

        // FIX: tambah backtick di `Return`
        $rentData = $db->prepare("SELECT EmployeeID FROM tRent WHERE ID = ? AND Void = 0 AND `Return` = 0");
        $rentData->execute([$reffDocId]);
        $rentRow = $rentData->fetch();

        if (!$rentRow) {
            throw new Exception("Peminjaman tidak ditemukan atau sudah dikembalikan!");
        }

        $empId = (int)$rentRow['EmployeeID'];

        $stmt = $db->prepare("INSERT INTO tRentReturn (DocDate, DocNumber, ReffDocID, EmployeeID, DocNotes, CreatedBy, PostDate) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$docDate, $docNumber, $reffDocId, $empId ?: null, $notes, $user]);
        $retId = $db->lastInsertId();

        $rentItems = $db->prepare("SELECT * FROM tRentTrans WHERE DocID = ? AND Void = 0");
        $rentItems->execute([$reffDocId]);
        $items = $rentItems->fetchAll();

        foreach ($items as $item) {
            $table = $item['TableName'] ?? '';
            $itemId = (int)$item['ItemRentID'];

            $db->prepare("INSERT INTO tRentReturnTrans (DocID, DocDate, DocNumber, ReffTransID, ItemRentID, ItemRentDesc, ItemQty, CreatedBy, PostDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())")
               ->execute([$retId, $docDate, $docNumber, $item['ID'], $itemId, $item['ItemRentDesc'], $item['ItemQty'], $user]);

            if ($table && isset(getInventarisTables()[$table]) && $itemId > 0) {
                $db->prepare("UPDATE `$table` SET Status=0, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
                   ->execute([$user, $itemId]);
            }
        }

        // FIX: tambah backtick di `Return`
        $db->prepare("UPDATE tRent SET `Return`=1, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
           ->execute([$user, $reffDocId]);
        // FIX: tambah backtick di `Return`
        $db->prepare("UPDATE tRentTrans SET `Return`=1, ReturnDocDate=?, ReturnDocID=?, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE DocID=?")
           ->execute([$docDate, $retId, $user, $reffDocId]);

        $db->commit();
        setFlash('success', 'Pengembalian berhasil! No: ' . $docNumber);
    } catch (Exception $e) {
        $db->rollBack();
        setFlash('danger', 'Error: ' . $e->getMessage());
    }
    redirect('pages/transaksi/return.php');
}

$returns = $db->query("SELECT r.*, e.Description as EmployeeName, rent.DocNumber as ReffDocNumber 
                       FROM tRentReturn r 
                       LEFT JOIN mEmployee e ON r.EmployeeID = e.ID 
                       LEFT JOIN tRent rent ON r.ReffDocID = rent.ID 
                       ORDER BY r.CreatedDate DESC")
               ->fetchAll();

// FIX: tambah backtick di `Return`
$pendingRents = $db->query("SELECT r.ID, r.DocNumber, r.DocDate, e.Description as EmployeeName 
                            FROM tRent r 
                            LEFT JOIN mEmployee e ON r.EmployeeID = e.ID 
                            WHERE r.Void=0 AND r.`Return`=0 
                            ORDER BY r.DocDate DESC")
                    ->fetchAll();

$pageTitle = 'Data Pengembalian';
$activePage = 'return';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-rotate-left"></i> Data Pengembalian</span>
        <button class="btn btn-primary" data-modal="modalReturn"><i class="fas fa-plus"></i> Proses Pengembalian</button>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No Dokumen</th>
                    <th>Tanggal</th>
                    <th>Ref Peminjaman</th>
                    <th>Karyawan</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($returns as $r): ?>
                <tr>
                    <td><strong><?php echo clean($r['DocNumber']); ?></strong></td>
                    <td><?php echo formatDate($r['DocDate']); ?></td>
                    <td><strong><?php echo clean($r['ReffDocNumber'] ?? '-'); ?></strong></td>
                    <td><?php echo clean($r['EmployeeName'] ?? '-'); ?></td>
                    <td><?php echo getVoidLabel($r['Void']); ?></td>
                    <td><?php echo formatDateTime($r['CreatedDate']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalReturn">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Form Pengembalian</span>
            <button type="button" class="modal-close" onclick="document.getElementById('modalReturn').classList.remove('active')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Tanggal *</label>
                        <input type="date" name="DocDate" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Peminjaman Ref *</label>
                        <select name="ReffDocID" class="form-input" required>
                            <option value="">-- Pilih Peminjaman --</option>
                            <?php foreach ($pendingRents as $pr): ?>
                            <option value="<?php echo $pr['ID']; ?>">
                                <?php echo clean($pr['DocNumber'] . ' - ' . ($pr['EmployeeName'] ?? '-')); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan</label>
                    <textarea name="DocNotes" class="form-textarea" rows="2"></textarea>
                </div>
                <div style="padding: 12px; background: #fef3c7; border-radius: 8px; font-size: 13px; color: #92400e;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Info:</strong> Karyawan akan diambil otomatis dari data peminjaman. 
                    Status inventaris akan kembali ke <strong>Idle</strong> setelah disimpan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalReturn').classList.remove('active')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>