<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// 检查是否管理员登录
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    redirect('../index.php');
}

$page_title = '用户管理';

// 删除用户
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // 不能删除自己
    if ($user_id != $_SESSION['user_id']) {
        $sql = "DELETE FROM users WHERE id = $user_id";
        mysqli_query($conn, $sql);
    }
    
    header('Location: users.php');
    exit;
}

// 获取所有用户
$sql = "SELECT * FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
$users = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - 微博系统后台</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-header">
        <h1>用户管理</h1>
        <a href="index.php">返回后台首页</a>
    </div>
    
    <div class="admin-content">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>邮箱</th>
                    <th>角色</th>
                    <th>注册时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td><?php echo $user['created_at']; ?></td>
                    <td>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="users.php?delete=<?php echo $user['id']; ?>" 
                               onclick="return confirm('确定要删除用户 <?php echo htmlspecialchars($user['username']); ?> 吗？')"
                               class="btn btn-danger">删除</a>
                        <?php else: ?>
                            <span class="text-muted">当前用户</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (empty($users)): ?>
            <p class="no-data">暂无用户数据</p>
        <?php endif; ?>
    </div>
</body>
</html>