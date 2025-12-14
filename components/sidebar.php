<?php
// components/sidebar.php
// Get pending count
$pending_count = 0;
if (isset($conn)) {
    try {
        $pending_count = $conn->query("SELECT COUNT(*) FROM loans WHERE status_loan = 'pending'")->fetchColumn();
    } catch (PDOException $e) {
        $pending_count = 0;
    }
}
?>
<!-- Sidebar -->
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar collapse" style="background-color: var(--primary-navy);">
    <div class="position-sticky pt-3">
        <div class="sidebar-logo text-center py-4">
            <h4 class="text-white">
                <i class="fas fa-boxes"></i> AssetFlow
            </h4>
            <small class="text-secondary">HRGA Module</small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>" 
                   href="admin_dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kelola_aset.php' ? 'active' : ''; ?>" 
                   href="kelola_aset.php">
                    <i class="fas fa-boxes"></i>
                    Kelola Aset
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'persetujuan.php' ? 'active' : ''; ?>" 
                   href="persetujuan.php">
                    <i class="fas fa-check-circle"></i>
                    Persetujuan
                    <?php if ($pending_count > 0): ?>
                        <span class="badge bg-danger rounded-pill float-end"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>" 
                   href="laporan.php">
                    <i class="fas fa-chart-bar"></i>
                    Laporan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kelola_user.php' ? 'active' : ''; ?>" 
                   href="kelola_user.php">
                    <i class="fas fa-users"></i>
                    Kelola User
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer mt-4 pt-3 border-top border-secondary">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['nama']); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>