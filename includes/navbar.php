<nav class="navbar">
    <div class="logo">
        <div class="logo-icon"><i class="fas fa-map-marked-alt"></i></div>
        <div>
            <h2>WEBGIS SMPN</h2>
            <span>Ternate Tengah & Ternate Selatan</span>
        </div>
    </div>
    <div class="nav-menu">
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-map"></i> Peta
        </a>
        <a href="data_sekolah.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'data_sekolah.php' ? 'active' : ''; ?>">
            <i class="fas fa-school"></i> Data Sekolah
        </a>
        <a href="analisis.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'analisis.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Analisis
        </a>
        <a href="tentang.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tentang.php' ? 'active' : ''; ?>">
            <i class="fas fa-info-circle"></i> Tentang
        </a>
        <a href="bantuan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bantuan.php' ? 'active' : ''; ?>">
            <i class="fas fa-question-circle"></i> Bantuan
        </a>
        <div class="user-info">
            <div class="user-avatar"><i class="fas fa-user"></i></div>
            <span><?php echo $_SESSION['username']; ?></span>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<style>
    .navbar {
        background: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 1000;
        flex-wrap: wrap;
    }
    .logo { display: flex; align-items: center; gap: 10px; }
    .logo-icon { background: linear-gradient(135deg, #1e5631, #2d6a4f); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; }
    .logo h2 { font-size: 20px; color: #1e5631; margin: 0; }
    .logo span { font-size: 12px; color: #666; font-weight: normal; display: block; }
    .nav-menu { display: flex; gap: 25px; align-items: center; flex-wrap: wrap; }
    .nav-menu a { text-decoration: none; color: #333; font-weight: 500; display: flex; align-items: center; gap: 8px; transition: color 0.3s; }
    .nav-menu a:hover, .nav-menu a.active { color: #1e5631; }
    .user-info { display: flex; align-items: center; gap: 15px; padding-left: 20px; border-left: 1px solid #ddd; }
    .user-avatar { width: 35px; height: 35px; background: #1e5631; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; }
    .logout-btn { background: none; border: none; color: #dc2626; cursor: pointer; font-size: 18px; text-decoration: none; }
    @media (max-width: 768px) {
        .navbar { flex-direction: column; gap: 15px; }
        .nav-menu { justify-content: center; }
        .user-info { border-left: none; padding-left: 0; }
    }
</style>