<?php
require_once '../../includes/functions.php';
requireLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';
    $itemId = (int)($_POST['ItemID'] ?? 0);
    $notes = clean($_POST['Notes'] ?? '');
    $user = getUserName();

    if ($action == 'service') {
        if (isset(getInventarisTables()[$table]) && $itemId > 0) {
            $db->prepare("UPDATE $table SET Status=3, Notes=CONCAT(IFNULL(Notes,''), ' | Service: ', ?), EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
               ->execute([$notes, $user, $itemId]);
            setFlash('success', 'Item berhasil diupdate status ke Repair!');
        } else {
            setFlash('danger', 'Data tidak valid!');
        }
        // FIX: Redirect ke path lengkap dari root
        redirect('pages/transaksi/service.php');
    }

    if ($action == 'finish') {
        if (isset(getInventarisTables()[$table]) && $itemId > 0) {
            $db->prepare("UPDATE $table SET Status=0, Notes=CONCAT(IFNULL(Notes,''), ' | Selesai Service: ', ?), EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
               ->execute([$notes, $user, $itemId]);
            setFlash('success', 'Item berhasil dikembalikan ke Idle setelah service!');
        } else {
            setFlash('danger', 'Data tidak valid!');
        }
        // FIX: Redirect ke path lengkap dari root
        redirect('pages/transaksi/service.php');
    }
}

$tables = getInventarisTables();
$selectedTable = $_GET['table'] ?? 'mNotebook';
if (!isset($tables[$selectedTable])) $selectedTable = 'mNotebook';

$items = $db->query("SELECT * FROM $selectedTable ORDER BY Status, CreatedDate DESC")
             ->fetchAll();

$pageTitle = 'Pengeluaran untuk Service';
$activePage = 'service';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-screwdriver-wrench"></i> Proses Service Inventaris</span>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-bar" style="margin-bottom: 20px;">
            <select name="table" class="form-input" style="width: 250px;" onchange="this.form.submit()">
                <?php foreach ($tables as $tbl => $tname): ?>
                <option value="<?php echo $tbl; ?>" <?php echo $tbl == $selectedTable ? 'selected' : ''; ?>><?php echo $tname; ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['ID']; ?></td>
                    <td><strong><?php echo clean($item['Code']); ?></strong></td>
                    <td><?php echo clean($item['Description']); ?></td>
                    <td><?php echo getStatusLabel($item['Status']); ?></td>
                    <td><?php echo clean($item['Notes'] ?? '-'); ?></td>
                    <td>
                        <?php if ($item['Status'] != 3): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="service">
                            <input type="hidden" name="table" value="<?php echo $selectedTable; ?>">
                            <input type="hidden" name="ItemID" value="<?php echo $item['ID']; ?>">
                            <input type="text" name="Notes" placeholder="Keterangan service" class="form-input" style="width: 150px; display: inline-block; padding: 6px 10px; font-size: 12px;" required>
                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Yakin masukkan item ini ke service?')">
                                <i class="fas fa-wrench"></i> Service
                            </button>
                        </form>
                        <?php else: ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="finish">
                            <input type="hidden" name="table" value="<?php echo $selectedTable; ?>">
                            <input type="hidden" name="ItemID" value="<?php echo $item['ID']; ?>">
                            <input type="text" name="Notes" placeholder="Keterangan selesai" class="form-input" style="width: 150px; display: inline-block; padding: 6px 10px; font-size: 12px;" required>
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Yakin item ini sudah selesai service?')">
                                <i class="fas fa-check"></i> Selesai
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>