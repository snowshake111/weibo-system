<nav class="nav">
    <a href="index.php"<?php echo isset($currentPage) && $currentPage === 'index' ? 'style="color: #e6162d; font-weight: bold;"' : ''; ?>>首页</a>
    <a href="profile.php" <?php echo isset($currentPage) && $currentPage === 'profile' ? 'style="color: #e6162d; font-weight: bold;"' : ''; ?>>我的主页</a>
  <!-- 管理员专属导航 -->
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <a href="admin/index.php" <?php echo isset($currentPage) && $currentPage === 'admin' ? 'style="color: #e6162d; font-weight: bold;"' : ''; ?>>管理后台</a>
    <?php endif; ?>
</nav>