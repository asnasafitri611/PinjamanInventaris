<?php
// pages/inventaris/index.php
require_once '../../includes/functions.php';
requireLogin();

$tables = getInventarisTables();
$db = getDB();

// Hitung jumlah per kategori
$counts = [];
foreach ($tables as $table => $name) {
    $stmt = $db->query("SELECT COUNT(*) as total, 
        SUM(CASE WHEN Status = 0 THEN 1 ELSE 0 END) as idle,
        SUM(CASE WHEN Status = 1 THEN 1 ELSE 0 END) as rent,
        SUM(CASE WHEN Status = 2 THEN 1 ELSE 0 END) as broken,
        SUM(CASE WHEN Status = 3 THEN 1 ELSE 0 END) as repair
        FROM $table");
    $counts[$table] = $stmt->fetch();
}

$pageTitle = 'Data Entry Inventaris';
$activePage = 'inventaris';
include '../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-boxes-stacked"></i> Pilih Kategori Inventaris</span>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
            <?php foreach ($tables as $table => $name): 
                $c = $counts[$table];
            ?>
            <a href="manage.php?table=<?php echo $table; ?>" class="card" style="text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s;">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                        <div>
                            <h3 style="font-size: 16px; font-weight: 700; color: var(--dark);"><?php echo $name; ?></h3>
                            <p style="font-size: 13px; color: var(--secondary); margin-top: 4px;"><?php echo $table; ?></p>
                        </div>
                        <div style="background: var(--primary-light); color: var(--primary); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-box-open"></i>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; text-align: center;">
                        <div style="background: #f0fdf4; padding: 8px; border-radius: 6px;">
                            <div style="font-size: 18px; font-weight: 700; color: #15803d;"><?php echo (int)$c['idle']; ?></div>
                            <div style="font-size: 11px; color: #166534;">Idle</div>
                        </div>
                        <div style="background: #eff6ff; padding: 8px; border-radius: 6px;">
                            <div style="font-size: 18px; font-weight: 700; color: #1d4ed8;"><?php echo (int)$c['rent']; ?></div>
                            <div style="font-size: 11px; color: #1e40af;">Rent</div>
                        </div>
                        <div style="background: #fef2f2; padding: 8px; border-radius: 6px;">
                            <div style="font-size: 18px; font-weight: 700; color: #b91c1c;"><?php echo (int)$c['broken']; ?></div>
                            <div style="font-size: 11px; color: #991b1b;">Broken</div>
                        </div>
                        <div style="background: #fffbeb; padding: 8px; border-radius: 6px;">
                            <div style="font-size: 18px; font-weight: 700; color: #b45309;"><?php echo (int)$c['repair']; ?></div>
                            <div style="font-size: 11px; color: #92400e;">Repair</div>
                        </div>
                    </div>
                    <div style="margin-top: 12px; text-align: center; font-size: 13px; color: var(--secondary);">
                        Total: <strong><?php echo (int)$c['total']; ?></strong> item
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>