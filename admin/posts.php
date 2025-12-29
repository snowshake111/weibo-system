<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// 检查是否管理员登录
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    redirect('../index.php');
}

$page_title = '微博管理';

// 删除微博
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $post_id = (int)$_GET['delete'];
    $sql = "DELETE FROM posts WHERE id = $post_id";
    mysqli_query($conn, $sql);
    header('Location: posts.php');
    exit;
}

// 获取所有微博
$sql = "SELECT p.*, u.username 
        FROM posts p 
        LEFT JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $sql);
$posts = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
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
        <h1>微博管理</h1>
        <a href="index.php">返回后台首页</a>
    </div>
    
    <div class="admin-content">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>内容</th>
                    <th>发布者</th>
                    <th>点赞数</th>
                    <th>评论数</th>
                    <th>发布时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?php echo $post['id']; ?></td>
                    <td class="post-content-cell">
                        <?php 
                        $content = htmlspecialchars($post['content']);
                        echo strlen($content) > 50 ? substr($content, 0, 50) . '...' : $content;
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($post['username']); ?></td>
                    <td><?php echo $post['likes_count']; ?></td>
                    <td><?php echo $post['comments_count']; ?></td>
                    <td><?php echo $post['created_at']; ?></td>
                    <td>
                        <a href="../post.php?id=<?php echo $post['id']; ?>" 
                           target="_blank" class="btn btn-view">查看</a>
                        <a href="posts.php?delete=<?php echo $post['id']; ?>" 
                           onclick="return confirm('确定要删除这条微博吗？')"
                           class="btn btn-danger">删除</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (empty($posts)): ?>
            <p class="no-data">暂无微博数据</p>
        <?php endif; ?>
    </div>
</body>
</html>