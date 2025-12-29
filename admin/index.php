<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// 检查是否管理员登录
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    redirect('../index.php');
}

$page_title = '后台管理';

// 获取统计数据
$stats = [
    'total_users' => 0,
    'total_posts' => 0,
    'total_comments' => 0,
    'today_posts' => 0
];

// 总用户数
$sql = "SELECT COUNT(*) as count FROM users";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_users'] = $row['count'];
}

// 总微博数
$sql = "SELECT COUNT(*) as count FROM posts";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_posts'] = $row['count'];
}

// 总评论数
$sql = "SELECT COUNT(*) as count FROM comments";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['total_comments'] = $row['count'];
}

// 今日微博数
$today = date('Y-m-d');
$sql = "SELECT COUNT(*) as count FROM posts WHERE DATE(created_at) = '$today'";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['today_posts'] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - 微博系统后台</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #e6162d;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .menu-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .menu-icon {
            font-size: 40px;
            color: #e6162d;
            margin-bottom: 15px;
        }
        
        .menu-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .menu-desc {
            color: #666;
            font-size: 14px;
        }
        
        .welcome-message {
            background: linear-gradient(135deg, #e6162d, #ff6b6b);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .welcome-message h2 {
            margin-bottom: 10px;
        }
        
        .current-time {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>微博系统后台管理</h1>
        <div>
            <a href="../index.php" target="_blank">访问前台</a>
            <a href="../logout.php" style="margin-left: 10px;">退出登录</a>
        </div>
    </div>
    
    <div class="admin-content">
        <!-- 欢迎信息 -->
        <div class="welcome-message">
            <h2>欢迎回来，管理员 <?php echo $_SESSION['username'] ?? ''; ?>！</h2>
            <p class="current-time">当前时间：<?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <!-- 统计数据 -->
        <h3>系统概览</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">注册用户</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_posts']; ?></div>
                <div class="stat-label">微博总数</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_comments']; ?></div>
                <div class="stat-label">评论总数</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['today_posts']; ?></div>
                <div class="stat-label">今日微博</div>
            </div>
        </div>
        
        <!-- 管理菜单 -->
        <h3>管理功能</h3>
        <div class="admin-menu">
            <a href="users.php" class="menu-card">
                <div class="menu-icon">👥</div>
                <div class="menu-title">用户管理</div>
                <div class="menu-desc">管理用户账号、查看用户信息、删除用户</div>
            </a>
            
            <a href="posts.php" class="menu-card">
                <div class="menu-icon">📝</div>
                <div class="menu-title">微博管理</div>
                <div class="menu-desc">查看所有微博、删除不当内容</div>
            </a>
            
            <a href="comments.php" class="menu-card">
                <div class="menu-icon">💬</div>
                <div class="menu-title">评论管理</div>
                <div class="menu-desc">管理评论内容、删除不良评论</div>
            </a>
        </div>
        
        <!-- 快速操作 -->
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
            <h3>快速操作</h3>
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <a href="users.php" class="btn" style="background: #007bff; color: white;">查看最新用户</a>
                <a href="posts.php" class="btn" style="background: #28a745; color: white;">查看最新微博</a>
                <a href="../index.php" target="_blank" class="btn" style="background: #6c757d; color: white;">浏览前台</a>
            </div>
        </div>
    </div>
</body>
</html>