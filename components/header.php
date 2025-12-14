<?php
// components/header.php
?>
<!-- Header -->
<header class="navbar navbar-dark sticky-top flex-md-nowrap p-0 shadow" style="background-color: var(--primary-navy);">
    <div class="container-fluid">
        <button class="navbar-toggler d-md-none collapsed me-3" type="button" 
                data-bs-toggle="collapse" data-bs-target="#sidebar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="admin_dashboard.php">
            <i class="fas fa-boxes me-2"></i>
            <span class="d-none d-md-inline">AssetFlow - HRGA Admin</span>
        </a>
        
        <div class="navbar-nav ms-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white" href="#" role="button" 
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-1"></i>
                    <?php echo htmlspecialchars($_SESSION['nama']); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text">
                        <small class="text-muted">Role: <?php echo htmlspecialchars($_SESSION['role']); ?></small>
                    </span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="profile.php">
                        <i class="fas fa-user me-2"></i>Profil
                    </a></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</header>