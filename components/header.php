<?php
// components/header.php
?>
<!-- Header Minimal Tanpa CSS -->
<div style="background: #0A192F; color: white; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <strong>AssetFlow - HRGA Admin</strong>
    </div>
    <div>
        User: <?php echo htmlspecialchars($_SESSION['nama']); ?> 
        (<?php echo htmlspecialchars($_SESSION['role']); ?>)
        | 
        <a href="profile.php" style="color: white; margin-right: 10px;">Profil</a>
        <a href="logout.php" style="color: #ff6b6b;">Logout</a>
    </div>
</div>