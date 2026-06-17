<?php
// components/sidebar.php
// Get pending count
$pending_count = 0;
if (isset($conn)) {
    try {
        $pending_count = $conn->query("SELECT COUNT(*) FROM loans WHERE status_loan = 'pending'")->fetchColumn();
    } catch (Exception $e) {
        $pending_count = 0;
    }
}

// Determine current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Minimal Tanpa CSS -->
<div style="background: #0A192F; color: white; width: 200px; min-height: 100vh; float: left; padding: 20px 0;">
    
    <!-- Logo -->
    <div style="text-align: center; padding: 20px 10px; border-bottom: 1px solid #1a3a6b;">
        <h3 style="margin: 0; color: #64FFDA;">AssetFlow</h3>
        <small style="color: #888;">HRGA Module</small>
    </div>
    
    <!-- Menu -->
    <div style="padding: 20px 0;">
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="margin: 5px 0;">
                <a href="admin_dashboard.php" 
                   style="display: block; padding: 10px 20px; color: <?= $current_page=='admin_dashboard.php'?'#64FFDA':'white' ?>; 
                          text-decoration: none; background: <?= $current_page=='admin_dashboard.php'?'rgba(100,255,218,0.1)':'transparent' ?>;">
                    📊 Dashboard
                </a>
            </li>
            <li style="margin: 5px 0;">
                <a href="kelola_aset.php" 
                   style="display: block; padding: 10px 20px; color: <?= $current_page=='kelola_aset.php'?'#64FFDA':'white' ?>; 
                          text-decoration: none; background: <?= $current_page=='kelola_aset.php'?'rgba(100,255,218,0.1)':'transparent' ?>;">
                    📦 Kelola Aset
                </a>
            </li>
            <li style="margin: 5px 0;">
                <a href="persetujuan.php" 
                   style="display: block; padding: 10px 20px; color: <?= $current_page=='persetujuan.php'?'#64FFDA':'white' ?>; 
                          text-decoration: none; background: <?= $current_page=='persetujuan.php'?'rgba(100,255,218,0.1)':'transparent' ?>;">
                    ✅ Persetujuan
                    <?php if($pending_count > 0): ?>
                        <span style="background: red; color: white; padding: 2px 6px; border-radius: 10px; font-size: 12px; float: right;">
                            <?= $pending_count ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <li style="margin: 5px 0;">
                <a href="laporan.php" 
                   style="display: block; padding: 10px 20px; color: <?= $current_page=='laporan.php'?'#64FFDA':'white' ?>; 
                          text-decoration: none; background: <?= $current_page=='laporan.php'?'rgba(100,255,218,0.1)':'transparent' ?>;">
                    📈 Laporan
                </a>
            </li>
            <li style="margin: 5px 0;">
                <a href="kelola_user.php" 
                   style="display: block; padding: 10px 20px; color: <?= $current_page=='kelola_user.php'?'#64FFDA':'white' ?>; 
                          text-decoration: none; background: <?= $current_page=='kelola_user.php'?'rgba(100,255,218,0.1)':'transparent' ?>;">
                    👥 Kelola User
                </a>
            </li>
        </ul>
    </div>
    
    <!-- User Info -->
    <div style="position: absolute; bottom: 20px; width: 200px; padding: 20px; border-top: 1px solid #1a3a6b;">
        <div style="text-align: center;">
            <div style="color: #64FFDA; margin-bottom: 5px;">👤 <?= htmlspecialchars($_SESSION['nama']) ?></div>
            <div>
                <a href="logout.php" style="color: #ff6b6b; text-decoration: none;">🚪 Logout</a>
            </div>
        </div>
    </div>
</div>