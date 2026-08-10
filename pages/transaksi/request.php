<?php
require_once '../../includes/functions.php';
requireLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $user = getUserName();

    if ($action == 'add') {
        $docDate = $_POST['DocDate'] ?? date('Y-m-d');
        $docNumber = generateDocNumber('REQ', 'tRequestRent');
        $empId = (int)($_POST['EmployeeID'] ?? 0);
        $notes = clean($_POST['DocNotes'] ?? '');

        try {
            $db->beginTransaction();
            $stmt = $db->prepare("INSERT INTO tRequestRent (DocDate, DocNumber, EmployeeID, DocNotes, CreatedBy, PostDate) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$docDate, $docNumber, $empId ?: null, $notes, $user]);
            $docId = $db->lastInsertId();

            $items = $_POST['items'] ?? [];
            foreach ($items as $item) {
                $table = $item['table'] ?? '';
                $itemId = (int)($item['item_id'] ?? 0);
                $desc = clean($item['desc'] ?? '');
                $qty = (int)($item['qty'] ?? 1);
                if ($itemId > 0) {
                    $db->prepare("INSERT INTO tRequestRentTrans (DocID, DocDate, DocNumber, ItemRentID, ItemRentDesc, ItemQty, TableName, CreatedBy, PostDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())")
                       ->execute([$docId, $docDate, $docNumber, $itemId, $desc, $qty, $table, $user]);
                }
            }
            $db->commit();
            setFlash('success', 'Request berhasil dibuat! No: ' . $docNumber);
        } catch (Exception $e) {
            $db->rollBack();
            setFlash('danger', 'Error: ' . $e->getMessage());
        }
        redirect('pages/transaksi/request.php');
    }

    if ($action == 'process') {
        $id = (int)($_POST['ID'] ?? 0);

        try {
            $db->beginTransaction();

            $req = $db->prepare("SELECT * FROM tRequestRent WHERE ID = ? AND Void = 0 AND Rent = 0");
            $req->execute([$id]);
            $requestData = $req->fetch();

            if (!$requestData) {
                throw new Exception("Request tidak ditemukan atau sudah diproses!");
            }

            $docNumber = generateDocNumber('RENT', 'tRent');
            $docDate = $requestData['DocDate'];
            $empId = $requestData['EmployeeID'];
            $notes = $requestData['DocNotes'];

            $stmt = $db->prepare("INSERT INTO tRent (DocDate, DocNumber, EmployeeID, DocNotes, CreatedBy, PostDate) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$docDate, $docNumber, $empId, $notes, $user]);
            $rentId = $db->lastInsertId();

            $reqItems = $db->prepare("SELECT * FROM tRequestRentTrans WHERE DocID = ? AND Void = 0");
            $reqItems->execute([$id]);

            foreach ($reqItems->fetchAll() as $item) {
                $table = $item['TableName'] ?? '';
                $itemId = (int)$item['ItemRentID'];

                $db->prepare("INSERT INTO tRentTrans (DocID, DocDate, DocNumber, ItemRentID, ItemRentDesc, ItemQty, TableName, CreatedBy, PostDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())")
                   ->execute([$rentId, $docDate, $docNumber, $itemId, $item['ItemRentDesc'], $item['ItemQty'], $table, $user]);

                if ($table && isset(getInventarisTables()[$table]) && $itemId > 0) {
                    $db->prepare("UPDATE `$table` SET Status=1, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
                       ->execute([$user, $itemId]);
                }
            }

            $db->prepare("UPDATE tRequestRent SET Rent=1, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
               ->execute([$user, $id]);
            $db->prepare("UPDATE tRequestRentTrans SET Rent=1, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE DocID=?")
               ->execute([$user, $id]);

            $db->commit();
            setFlash('success', 'Request berhasil diproses! No Peminjaman: ' . $docNumber);
        } catch (Exception $e) {
            $db->rollBack();
            setFlash('danger', 'Error: ' . $e->getMessage());
        }
        redirect('pages/transaksi/request.php');
    }

    if ($action == 'void') {
        $id = (int)($_POST['ID'] ?? 0);
        $db->prepare("UPDATE tRequestRent SET Void=1, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
           ->execute([$user, $id]);
        setFlash('success', 'Request dibatalkan!');
        redirect('pages/transaksi/request.php');
    }
}

$requests = $db->query("SELECT r.*, e.Description as EmployeeName FROM tRequestRent r LEFT JOIN mEmployee e ON r.EmployeeID = e.ID ORDER BY r.CreatedDate DESC")
                ->fetchAll();
$employees = $db->query("SELECT * FROM mEmployee WHERE Status=1 ORDER BY Description")
                  ->fetchAll();
$tables = getInventarisTables();

$pageTitle = 'Request Peminjaman';
$activePage = 'request';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-clipboard-list"></i> Request Peminjaman</span>
        <button class="btn btn-primary" data-modal="modalRequest"><i class="fas fa-plus"></i> Buat Request</button>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No Request</th>
                    <th>Tanggal</th>
                    <th>Karyawan</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                <tr>
                    <td><strong><?php echo clean($r['DocNumber']); ?></strong></td>
                    <td><?php echo formatDate($r['DocDate']); ?></td>
                    <td><?php echo clean($r['EmployeeName'] ?? '-'); ?></td>
                    <td><?php echo getVoidLabel($r['Void']); ?> <?php echo getRentLabel($r['Rent']); ?></td>
                    <td><?php echo formatDateTime($r['CreatedDate']); ?></td>
                    <td>
                        <div class="action-btns">
                            <?php if ($r['Void'] == 0 && $r['Rent'] == 0): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="process">
                                <input type="hidden" name="ID" value="<?php echo $r['ID']; ?>">
                                <button type="submit" class="btn-icon btn-edit" title="Proses Jadi Peminjaman" onclick="return confirm('Proses request ini jadi dokumen peminjaman?')">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" class="void-form" style="display:inline;">
                                <input type="hidden" name="action" value="void">
                                <input type="hidden" name="ID" value="<?php echo $r['ID']; ?>">
                                <button type="submit" class="btn-icon btn-delete" title="Batalkan">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            <?php elseif ($r['Rent'] == 1 && $r['Void'] == 0): ?>
                                <span class="badge badge-idle">Sudah Diproses</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="modalRequest">
    <div class="modal" style="max-width: 800px;">
        <div class="modal-header">
            <span class="modal-title">Form Request Peminjaman</span>
            <button type="button" class="modal-close" onclick="document.getElementById('modalRequest').classList.remove('active')">&times;</button>
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
                        <label class="form-label">Karyawan *</label>
                        <select name="EmployeeID" class="form-input" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach ($employees as $e): ?>
                            <option value="<?php echo $e['ID']; ?>"><?php echo clean($e['Description']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan</label>
                    <textarea name="DocNotes" class="form-textarea" rows="2"></textarea>
                </div>
                <div id="reqContainer">
                    <div class="form-grid" style="margin-bottom:10px;padding:12px;background:#f8fafc;border-radius:8px;">
                        <div class="form-group">
                            <select name="items[0][table]" class="form-input" required>
                                <option value="">Kategori *</option>
                                <?php foreach ($tables as $tbl => $tname): ?>
                                <option value="<?php echo $tbl; ?>"><?php echo $tname; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="number" name="items[0][item_id]" class="form-input" placeholder="ID Item *" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="items[0][desc]" class="form-input" placeholder="Deskripsi">
                        </div>
                        <div class="form-group">
                            <input type="number" name="items[0][qty]" class="form-input" value="1" min="1">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addReqRow()">
                    <i class="fas fa-plus"></i> Tambah Item
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalRequest').classList.remove('active')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
let reqIndex = 1;
function addReqRow() {
    const c = document.getElementById('reqContainer');
    const d = document.createElement('div');
    d.className = 'form-grid';
    d.style.cssText = 'margin-bottom:10px;padding:12px;background:#f8fafc;border-radius:8px;';
    d.innerHTML = `
        <div class="form-group">
            <select name="items[${reqIndex}][table]" class="form-input" required>
                <option value="">Kategori *</option>
                <?php foreach ($tables as $tbl => $tname): ?>
                <option value="<?php echo $tbl; ?>"><?php echo $tname; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><input type="number" name="items[${reqIndex}][item_id]" class="form-input" placeholder="ID Item *" required></div>
        <div class="form-group"><input type="text" name="items[${reqIndex}][desc]" class="form-input" placeholder="Deskripsi"></div>
        <div class="form-group"><input type="number" name="items[${reqIndex}][qty]" class="form-input" value="1" min="1"></div>
    `;
    c.appendChild(d);
    reqIndex++;
}
</script>

<?php include '../../includes/footer.php'; ?>