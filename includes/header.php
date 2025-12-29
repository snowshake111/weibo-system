<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>微博系统 - <?php echo isset($pageTitle) ? $pageTitle : '首页'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <script src="js/utils.js"></script>
</head>
<body>
    <header class="header">
        <div class="container">
            <!-- Logo部分 -->
             <a href="index.php" class="logo">
                <img src="images/logo.png" alt="Logo" />
                <span>微博系统</span>
            </a>
            <!-- 导航菜单 -->
            <?php include 'navigation.php'; ?>
            <!-- 搜素框 -->
            <div class="search-box">
                <input type="text" id="search-input" placeholder="搜索微博、用户..." />
                <button id="search-btn"></button>         
        </div>
            <!-- 用户操作区 -->
            <div class="user-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- 已登录 -->
                    <a href="profile.php" class="avatar">个人资料</a>
                    <a href="logout.php" class="logout-btn">退出登录</a>
                <?php else: ?>
                    <!-- 未登录 -->
                    <a href="login.php" class="login-btn">登录</a>
                    <a href="register.php" class="register-btn">注册</a>
                <?php endif; ?>
            </div>
        </div>
    </header>