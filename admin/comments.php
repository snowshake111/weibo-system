<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// 检查是否管理员登录
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    redirect('../index.php');
}

$page_title = '评论管理';

// 删除评论
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $comment_id = (int)$_GET['delete'];
    $sql = "DELETE FROM comments WHERE id = $comment_id";
    mysqli_query($conn, $sql);
    header('Location: comments.php');
    exit;
}

// 获取所有评论
$sql = "SELECT c.*, u.username as comment_user, p.content as post_content 
        FROM comments c 
        LEFT JOIN users u ON c.user_id = u.id 
        LEFT JOIN posts p ON c.post_id = p.id 
        ORDER BY c.created_at DESC";
$result = mysqli_query($conn, $sql);
$comments = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $comments[] = $row;
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
        <h1>评论管理</h1>
        <a href="index.php">返回后台首页</a>
    </div>
    
    <div class="admin-content">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>评论内容</th>
                    <th>评论者</th>
                    <th>所属微博</th>
                    <th>评论时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><?php echo $comment['id']; ?></td>
                    <td class="comment-content-cell">
                        <?php 
                        $content = htmlspecialchars($comment['content']);
                        echo strlen($content) > 30 ? substr($content, 0, 30) . '...' : $content;
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($comment['comment_user']); ?></td>
                    <td class="post-preview">
                        <?php 
                        $post_content = htmlspecialchars($comment['post_content']);
                        echo strlen($post_content) > 30 ? substr($post_content, 0, 30) . '...' : $post_content;
                        ?>
                    </td>
                    <td><?php echo $comment['created_at']; ?></td>
                    <td>
                        <a href="../post.php?id=<?php echo $comment['post_id']; ?>" 
                           target="_blank" class="btn btn-view">查看微博</a>
                        <a href="comments.php?delete=<?php echo $comment['id']; ?>" 
                           onclick="return confirm('确定要删除这条评论吗？')"
                           class="btn btn-danger">删除</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (empty($comments)): ?>
            <p class="no-data">暂无评论数据</p>
        <?php endif; ?>
    </div>
</body>
</html>