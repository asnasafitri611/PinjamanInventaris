<?php
require_once '../../includes/functions.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $user = getUserName();
    
    if ($action == 'add' || $action == 'edit') {
        $uname = clean($_POST['UserName'] ?? '');
        $pass = $_POST['Password'] ?? '';
        $empId = (int)($_POST['EmployeeID'] ?? 0);
        $access = (int)($_POST['Access'] ?? 1);
        $status = (int)($_POST['Status'] ?? 1);
        $id = (int)($_POST['ID'] ?? 0);
        
        if (empty($uname)) {
            setFlash('danger', 'Username wajib diisi!');
        } else {
            try {
                if ($action == 'add') {
                    $hash = password_hash($pass ?: 'user123', PASSWORD_DEFAULT);
                    $db->prepare("INSERT INTO mUser (UserName, Password, EmployeeID, Access, Status, CreatedBy, PostDate) VALUES (?, ?, ?, ?, ?, ?, NOW())")
                       ->execute([$uname, $hash, $empId ?: null, $access, $status, $user]);
                    setFlash('success', 'User ditambahkan! Default password: user123');
                } else {
                    if (!empty($pass)) {
                        $hash = password_hash($pass, PASSWORD_DEFAULT);
                        $db->prepare("UPDATE mUser SET UserName=?, Password=?, EmployeeID=?, Access=?, Status=?, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
                           ->execute([$uname, $hash, $empId ?: null, $access, $status, $user, $id]);
                    } else {
                        $db->prepare("UPDATE mUser SET UserName=?, EmployeeID=?, Access=?, Status=?, EditBy=?, EditDate=NOW(), PostDate=NOW() WHERE ID=?")
                           ->execute([$uname, $empId ?: null, $access, $status, $user, $id]);
                    }
                    setFlash('success', 'User diperbarui!');
                }
            } catch (PDOException $e) {
                setFlash('danger', 'Username sudah ada atau error database!');
            }
        }
        redirect('user.php');
    } elseif ($action == 'delete') {
        $id = (int)($_POST['ID'] ?? 0);
        $db->prepare("DELETE FROM mUser WHERE ID = ?")->execute([$id]);
        setFlash('success', 'User dihapus!');
        redirect('user.php');
    }
}

$users = $db->query("SELECT u.*, e.Description as EmployeeName FROM mUser u LEFT JOIN mEmployee e ON u.EmployeeID = e.ID ORDER BY u.CreatedDate DESC")->fetchAll();
$employees = $db->query("SELECT * FROM mEmployee WHERE Status=1 ORDER BY Description")->fetchAll();
$editItem = null;
if (isset($_GET['edit'])) {
    $editItem = $db->prepare("SELECT * FROM mUser WHERE ID = ?");
    $editItem->execute([(int)$_GET['edit']]);
    $editItem = $editItem->fetch();
}

$pageTitle = 'Master User';
$activePage = 'user';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-user-shield"></i> Data User</span>
        <button class="btn btn-primary" data-modal="modalUser"><i class="fas fa-plus"></i> Tambah User</button>
    </div>
    <div class="card-body">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Username</th><th>Karyawan</th><th>Akses</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo $u['ID']; ?></td>
                    <td><strong><?php echo clean($u['UserName']); ?></strong></td>
                    <td><?php echo clean($u['EmployeeName'] ?? '-'); ?></td>
                    <td><?php echo $u['Access'] == 0 ? '<span class="badge badge-rent">Admin</span>' : '<span class="badge badge-idle">Karyawan</span>'; ?></td>
                    <td><?php echo $u['Status'] == 1 ? '<span class="badge badge-idle">Aktif</span>' : '<span class="badge badge-broken">Nonaktif</span>'; ?></td>
                    <td>
                        <div class="action-btns">
                            <a href="user.php?edit=<?php echo $u['ID']; ?>" class="btn-icon btn-edit"><i class="fas fa-pen"></i></a>
                            <form method="POST" class="delete-form" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="ID" value="<?php echo $u['ID']; ?>">
                                <button type="submit" class="btn-icon btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay <?php echo $editItem ? 'active' : ''; ?>" id="modalUser">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title"><?php echo $editItem ? 'Edit' : 'Tambah'; ?> User</span>
            <a href="user.php" class="modal-close">&times;</a>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="<?php echo $editItem ? 'edit' : 'add'; ?>">
            <?php if ($editItem): ?><input type="hidden" name="ID" value="<?php echo $editItem['ID']; ?>"><?php endif; ?>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" name="UserName" class="form-input" value="<?php echo $editItem ? clean($editItem['UserName']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password <?php echo $editItem ? '(Kosongkan jika tidak diubah)' : '*'; ?></label>
                        <input type="password" name="Password" class="form-input" <?php echo $editItem ? '' : 'required'; ?>>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Karyawan</label>
                        <select name="EmployeeID" class="form-input">
                            <option value="">-- Pilih --</option>
                            <?php foreach ($employees as $e): ?>
                            <option value="<?php echo $e['ID']; ?>" <?php echo ($editItem && $editItem['EmployeeID'] == $e['ID']) ? 'selected' : ''; ?>><?php echo clean($e['Description']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Akses</label>
                        <select name="Access" class="form-input">
                            <option value="0" <?php echo ($editItem && $editItem['Access']==0) ? 'selected' : ''; ?>>Admin</option>
                            <option value="1" <?php echo ($editItem && $editItem['Access']==1) ? 'selected' : ''; ?>>Karyawan</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="Status" class="form-input">
                        <option value="1" <?php echo ($editItem && $editItem['Status']==1) ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?php echo ($editItem && $editItem['Status']==0) ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <a href="user.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>