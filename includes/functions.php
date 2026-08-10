<?php
// includes/functions.php

session_start();
require_once __DIR__ . '/../config/database.php';

// === BASE URL ===
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim(str_replace('\\', '/', $scriptDir), '/');

// Jika di subfolder (pages/xxx), naik ke root
if (strpos($basePath, '/pages/') !== false || strpos($basePath, '/includes/') !== false || strpos($basePath, '/config/') !== false || strpos($basePath, '/assets/') !== false) {
    $parts = explode('/', $basePath);
    $newParts = [];
    foreach ($parts as $p) {
        if ($p === 'pages' || $p === 'includes' || $p === 'config' || $p === 'assets') break;
        $newParts[] = $p;
    }
    $basePath = implode('/', $newParts);
}
if (empty($basePath)) $basePath = '';
define('BASE_URL', $protocol . '://' . $host . $basePath . '/');

function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['user_access']) && $_SESSION['user_access'] == 0;
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: " . BASE_URL . "dashboard.php?error=unauthorized");
        exit;
    }
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

function formatDate($date) {
    if (!$date) return '-';
    return date('d M Y', strtotime($date));
}

function formatDateTime($datetime) {
    if (!$datetime) return '-';
    return date('d M Y H:i', strtotime($datetime));
}

function getStatusLabel($status) {
    $labels = [
        0 => '<span class="badge badge-idle">Idle</span>',
        1 => '<span class="badge badge-rent">Rent</span>',
        2 => '<span class="badge badge-broken">Broken</span>',
        3 => '<span class="badge badge-repair">Repair</span>'
    ];
    return $labels[$status] ?? '<span class="badge">Unknown</span>';
}

function getStatusText($status) {
    $texts = [0 => 'Idle', 1 => 'Rent', 2 => 'Broken', 3 => 'Repair'];
    return $texts[$status] ?? 'Unknown';
}

function getVoidLabel($void) {
    return $void == 0 
        ? '<span class="badge badge-idle">Aktif</span>' 
        : '<span class="badge badge-broken">Dibatalkan</span>';
}

function getReturnLabel($return) {
    return $return == 0 
        ? '<span class="badge badge-rent">Belum Kembali</span>' 
        : '<span class="badge badge-idle">Sudah Kembali</span>';
}

function getRentLabel($rent) {
    return $rent == 0 
        ? '<span class="badge badge-repair">Belum Diproses</span>' 
        : '<span class="badge badge-idle">Sudah Diproses</span>';
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function showFlash() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $msg = $_SESSION['flash']['message'];
        unset($_SESSION['flash']);
        return '<div class="alert alert-' . $type . '">' . htmlspecialchars($msg) . '</div>';
    }
    return '';
}

function generateDocNumber($prefix, $table, $column = 'DocNumber') {
    $db = getDB();
    $year = date('Y');
    $month = date('m');
    $stmt = $db->query("SELECT MAX(`$column`) as max_num FROM `$table` WHERE `$column` LIKE '$prefix/$year/$month/%'");
    $row = $stmt->fetch();
    $last = $row['max_num'] ?? '';
    $num = 1;
    if ($last) {
        $parts = explode('/', $last);
        $num = (int)end($parts) + 1;
    }
    return sprintf("%s/%s/%s/%04d", $prefix, $year, $month, $num);
}

function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    if (strpos($url, '://') === false && strpos($url, '/') !== 0) {
        $url = BASE_URL . $url;
    }
    header("Location: $url");
    exit;
}

function getInventarisTables() {
    return [
        'mNotebook' => 'Notebook',
        'mProcessor' => 'Processor',
        'mMotherboard' => 'Motherboard',
        'mMemory' => 'Memory',
        'mBaterry' => 'Baterai Notebook',
        'mHardisknb' => 'Hard Disk Notebook',
        'mLcdnb' => 'LCD Notebook',
        'mNetwork' => 'Network Adapter / Wifi',
        'mCam' => 'Web Cam',
        'mAdaptor' => 'Adaptor',
        'mHardiskext' => 'HardDisk External',
        'mFlashdisk' => 'Flashdisk',
        'mBook' => 'Buku',
        'mOther' => 'Lainnya'
    ];
}
?>