<?php
require_once '../../includes/functions.php';
requireLogin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $user = getUserName();

    if ($action == 'add') {
        $code = clean($_POST['Code'] ?? '');
        $description = clean($_POST['Description'] ?? '');
        $status = (int)($_POST['Status'] ?? 1);

        if (empty($code) || empty($description)) {
            setFlash('danger', 'Kode dan Nama Karyawan wajib diisi!');
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO mEmployee (Code, Description, Status, CreatedBy, PostDate) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$code, $description, $status, $user]);
                setFlash('success', 'Karyawan berhasil ditambahkan!');
            } catch (Exception $e) {
                setFlash('danger', 'Error: ' . $e->getMessage());
            }
        }
        redirect('pages/master/employee.php');
    }

    if ($action == 'edit') {
        $id = (int)($_POST['ID'] ?? 0);
        $code = clean($_POST['Code'] ?? '');
        $description = clean($_POST['Description'] ?? '');
        $status = (int)($_POST['Status'] ?? 1);

        if ($id > 0 && !empty($code) && !empty($description)) {
            $db->prepare("UPDATE mEmployee SET Code=?, Description=?, Status=?, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
               ->execute([$code, $description, $status, $user, $id]);
            setFlash('success', 'Karyawan berhasil diupdate!');
        } else {
            setFlash('danger', 'Data tidak valid!');
        }
        redirect('pages/master/employee.php');
    }

    if ($action == 'delete') {
        $id = (int)($_POST['ID'] ?? 0);
        if ($id > 0) {
            // Cek apakah karyawan sudah pernah dipakai di transaksi
            $cekRent = $db->prepare("SELECT COUNT(*) FROM tRent WHERE EmployeeID = ? AND Void = 0");
            $cekRent->execute([$id]);
            $cekRequest = $db->prepare("SELECT COUNT(*) FROM tRequestRent WHERE EmployeeID = ? AND Void = 0");
            $cekRequest->execute([$id]);

            if ($cekRent->fetchColumn() > 0 || $cekRequest->fetchColumn() > 0) {
                setFlash('warning', 'Karyawan tidak bisa dihapus karena sudah memiliki riwayat transaksi!');
            } else {
                $db->prepare("DELETE FROM mEmployee WHERE ID = ?")->execute([$id]);
                setFlash('success', 'Karyawan berhasil dihapus!');
            }
        }
        redirect('pages/master/employee.php');
    }
}

$employees = $db->query("SELECT * FROM mEmployee ORDER BY Description ASC")->fetchAll();

$pageTitle = 'Data Karyawan';
$activePage = 'employee';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-users"></i> Data Karyawan</span>
        <button class="btn btn-primary" data-modal="modalEmployee"><i class="fas fa-plus"></i> Tambah Karyawan</button>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode</th>
                    <th>Nama Karyawan</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $e): ?>
                <tr>
                    <td><?php echo $e['ID']; ?></td>
                    <td><strong><?php echo clean($e['Code']); ?></strong></td>
                    <td><?php echo clean($e['Description']); ?></td>
                    <td>
                        <?php if ($e['Status'] == 1): ?>
                            <span class="badge badge-idle">Aktif</span>
                        <?php else: ?>
                            <span class="badge badge-broken">Nonaktif</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo formatDateTime($e['PostDate']); ?></td>
                    <td>
                        <div class="action-btns">
                            <button type="button" class="btn-icon btn-edit" onclick="editEmployee(<?php echo $e['ID']; ?>, '<?php echo addslashes($e['Code']); ?>', '<?php echo addslashes($e['Description']); ?>', <?php echo $e['Status']; ?>)" title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus karyawan ini?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="ID" value="<?php echo $e['ID']; ?>">
                                <button type="submit" class="btn-icon btn-delete" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalEmployee">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <span class="modal-title">Tambah Karyawan</span>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" id="formEmployee">
            <input type="hidden" name="action" value="add" id="formAction">
            <input type="hidden" name="ID" id="editID">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Kode Karyawan *</label>
                    <input type="text" name="Code" id="editCode" class="form-input" placeholder="Contoh: K001" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Karyawan *</label>
                    <input type="text" name="Description" id="editDescription" class="form-input" placeholder="Nama lengkap" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="Status" id="editStatus" class="form-input">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function editEmployee(id, code, desc, status) {
    document.getElementById('formAction').value = 'edit';
    document.getElementById('editID').value = id;
    document.getElementById('editCode').value = code;
    document.getElementById('editDescription').value = desc;
    document.getElementById('editStatus').value = status;
    document.querySelector('.modal-title').textContent = 'Edit Karyawan';
    document.getElementById('modalEmployee').classList.add('active');
}

function closeModal() {
    document.getElementById('modalEmployee').classList.remove('active');
    // Reset form
    setTimeout(() => {
        document.getElementById('formEmployee').reset();
        document.getElementById('formAction').value = 'add';
        document.getElementById('editID').value = '';
        document.querySelector('.modal-title').textContent = 'Tambah Karyawan';
    }, 200);
}
</script>

<?php include '../../includes/footer.php'; ?>