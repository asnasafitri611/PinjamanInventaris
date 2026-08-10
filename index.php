<?php
// index.php - Halaman Login
require_once 'includes/functions.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM mUser WHERE UserName = ? AND Status = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['user_name'] = $user['UserName'];
            $_SESSION['user_access'] = $user['Access'];
            $_SESSION['employee_id'] = $user['EmployeeID'];
            
            // Update PostDate
            $db->prepare("UPDATE mUser SET PostDate = NOW() WHERE ID = ?")->execute([$user['ID']]);
            
            setFlash('success', 'Selamat datang, ' . $user['UserName'] . '!');
            redirect('dashboard.php');
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Peminjaman Inventaris</title>
    <link rel="stylesheet" href="assets/style.css">
   <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-logo">
            <i class="fas fa-boxes-stacked"></i>
            <h2>Sistem Inventaris</h2>
            <p>Manajemen Peminjaman & Service</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" placeholder="Masukkan username" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 20px; font-size: 13px; color: var(--secondary);">
           
        </div>
    </div>
</body>
</html>