<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// 获取微博ID
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id <= 0) {
    header('Location: index.php');
    exit;
}

// 获取微博详情
$sql_post = "SELECT p.*, u.username, u.avatar 
             FROM posts p 
             LEFT JOIN users u ON p.user_id = u.id 
             WHERE p.id = $post_id";
$result_post = mysqli_query($conn, $sql_post);
$post = mysqli_fetch_assoc($result_post);

if (!$post) {
    header('Location: index.php');
    exit;
}

$page_title = '微博详情';

// 获取评论
$sql_comments = "SELECT c.*, u.username, u.avatar 
                 FROM comments c 
                 LEFT JOIN users u ON c.user_id = u.id 
                 WHERE c.post_id = $post_id 
                 ORDER BY c.created_at ASC";
$result_comments = mysqli_query($conn, $sql_comments);
$comments = [];
if ($result_comments) {
    while ($row = mysqli_fetch_assoc($result_comments)) {
        $comments[] = $row;
    }
}

// 处理添加评论
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $content = filter_inputs($_POST['content'] ?? '');
    
    if (!empty($content)) {
        $user_id = $_SESSION['user_id'];
        $content_safe = mysqli_real_escape_string($conn, $content);
        
        // 插入评论
        $sql = "INSERT INTO comments (post_id, user_id, content, created_at) 
                VALUES ($post_id, $user_id, '$content_safe', NOW())";
        
        if (mysqli_query($conn, $sql)) {
            // 更新微博评论数
            $sql_update = "UPDATE posts SET comments_count = comments_count + 1 WHERE id = $post_id";
            mysqli_query($conn, $sql_update);
            
            header("Location: post.php?id=$post_id");
            exit;
        }
    }
}

// 处理删除评论
if (isset($_GET['delete_comment'])) {
    $comment_id = (int)$_GET['delete_comment'];
    
    // 检查权限：评论所有者或管理员可以删除
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $sql_check = "SELECT user_id FROM comments WHERE id = $comment_id";
        $result_check = mysqli_query($conn, $sql_check);
        $comment = mysqli_fetch_assoc($result_check);
        
        if ($comment && ($comment['user_id'] == $user_id || 
            (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'))) {
            
            $sql_delete = "DELETE FROM comments WHERE id = $comment_id";
            mysqli_query($conn, $sql_delete);
            
            // 更新微博评论数
            $sql_update = "UPDATE posts SET comments_count = comments_count - 1 WHERE id = $post_id";
            mysqli_query($conn, $sql_update);
            
            header("Location: post.php?id=$post_id");
            exit;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="post-detail-container">
    <!-- 微博内容 -->
    <div class="post-detail">
        <div class="post-header">
            <img src="<?php echo $post['avatar'] ?? 'images/default-avatar.jpg'; ?>" 
                 class="avatar" alt="头像">
            <div class="user-info">
                <div class="username">
                    <a href="profile.php?id=<?php echo $post['user_id']; ?>">
                        <?php echo htmlspecialchars($post['username']); ?>
                    </a>
                </div>
                <div class="post-time"><?php echo $post['created_at']; ?></div>
            </div>
        </div>
        
        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
        
        <div class="post-stats">
            <span class="stat-item">❤️ <?php echo $post['likes_count']; ?> 赞</span>
            <span class="stat-item">💬 <?php echo $post['comments_count']; ?> 评论</span>
        </div>
        
        <div class="post-actions">
            <button class="action-btn like-btn" data-post-id="<?php echo $post_id; ?>">
                <?php
                // 检查是否已点赞（简单实现）
                $is_liked = false;
                if (isset($_SESSION['user_id'])) {
                    $user_id = $_SESSION['user_id'];
                    $sql_check = "SELECT id FROM likes WHERE post_id = $post_id AND user_id = $user_id";
                    $result_check = mysqli_query($conn, $sql_check);
                    $is_liked = mysqli_num_rows($result_check) > 0;
                }
                ?>
                <span class="like-icon"><?php echo $is_liked ? '❤️' : '🤍'; ?></span>
                <span class="like-text"><?php echo $is_liked ? '取消赞' : '点赞'; ?></span>
            </button>
        </div>
    </div>
    
    <!-- 评论表单 -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="comment-form-section">
            <form method="POST" class="comment-form">
                <textarea name="content" placeholder="写下你的评论..." required></textarea>
                <button type="submit" class="submit-btn">发表评论</button>
            </form>
        </div>
    <?php else: ?>
        <div class="login-prompt">
            <p>请<a href="login.php">登录</a>后发表评论</p>
        </div>
    <?php endif; ?>
    
    <!-- 评论列表 -->
    <div class="comments-section">
        <h3>评论 (<?php echo count($comments); ?>)</h3>
        
        <?php if (empty($comments)): ?>
            <div class="no-comments">
                <p>还没有评论，快来第一个评论吧！</p>
            </div>
        <?php else: ?>
            <div class="comment-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <img src="<?php echo $comment['avatar'] ?? 'images/default-avatar.jpg'; ?>" 
                             class="avatar" alt="头像">
                        <div class="comment-content">
                            <div class="comment-header">
                                <a href="profile.php?id=<?php echo $comment['user_id']; ?>" class="username">
                                    <?php echo htmlspecialchars($comment['username']); ?>
                                </a>
                                <span class="comment-time"><?php echo $comment['created_at']; ?></span>
                                
                                <?php if (isset($_SESSION['user_id']) && 
                                         ($_SESSION['user_id'] == $comment['user_id'] || 
                                          (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'))): ?>
                                    <a href="post.php?id=<?php echo $post_id; ?>&delete_comment=<?php echo $comment['id']; ?>" 
                                       onclick="return confirm('确定要删除这条评论吗？')"
                                       class="delete-comment">删除</a>
                                <?php endif; ?>
                            </div>
                            <div class="comment-text">
                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.post-detail-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 0 20px;
}

.post-detail {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.post-detail .post-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.post-detail .post-header .user-info {
    flex: 1;
}

.post-detail .post-content {
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 20px;
    color: #333;
}

.post-stats {
    display: flex;
    gap: 20px;
    margin: 20px 0;
    color: #666;
    font-size: 14px;
}

.post-actions {
    border-top: 1px solid #eee;
    padding-top: 15px;
}

.like-btn {
    background: none;
    border: 1px solid #e6162d;
    color: #e6162d;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
}

.like-btn:hover {
    background: #e6162d;
    color: white;
}

.comment-form-section {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.comment-form textarea {
    width: 100%;
    height: 80px;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    resize: vertical;
    margin-bottom: 15px;
    font-size: 14px;
}

.comment-form .submit-btn {
    background: #e6162d;
    color: white;
    padding: 10px 20px;
    border-radius: 20px;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.login-prompt {
    background: white;
    padding: 20px;
    text-align: center;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    color: #666;
}

.login-prompt a {
    color: #e6162d;
}

.comments-section {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.comments-section h3 {
    margin-bottom: 20px;
    color: #333;
}

.comment-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.comment-item {
    display: flex;
    gap: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f5f5f5;
}

.comment-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.comment-content {
    flex: 1;
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.comment-header .username {
    font-weight: 500;
    color: #333;
}

.comment-time {
    color: #999;
    font-size: 12px;
}

.delete-comment {
    color: #ff4444;
    font-size: 12px;
    margin-left: auto;
}

.comment-text {
    color: #333;
    line-height: 1.5;
}

.no-comments {
    text-align: center;
    padding: 30px;
    color: #999;
}
</style>

<script>
// 点赞功能
const likeBtn = document.querySelector('.like-btn');
if (likeBtn) {
    likeBtn.addEventListener('click', function() {
        const postId = this.getAttribute('data-post-id');
        const likeIcon = this.querySelector('.like-icon');
        const likeText = this.querySelector('.like-text');
        
        // 简单切换样式
        if (likeIcon.textContent === '🤍') {
            likeIcon.textContent = '❤️';
            likeText.textContent = '取消赞';
            alert('已点赞微博 #' + postId);
        } else {
            likeIcon.textContent = '🤍';
            likeText.textContent = '点赞';
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>