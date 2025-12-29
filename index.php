<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = '首页';

// 获取当前页码
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 获取微博总数
$sql_count = "SELECT COUNT(*) as total FROM posts";
$result_count = mysqli_query($conn, $sql_count);
$total_posts = mysqli_fetch_assoc($result_count)['total'];
$total_pages = ceil($total_posts / $limit);

// 获取微博列表
$sql = "SELECT p.*, u.username, u.avatar 
        FROM posts p 
        LEFT JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$posts = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
}

// 处理发布微博
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $content = filter_inputs($_POST['content'] ?? '');
    
    if (!empty($content)) {
        $user_id = $_SESSION['user_id'];
        $content_safe = mysqli_real_escape_string($conn, $content);
        
        $sql = "INSERT INTO posts (user_id, content, created_at) 
                VALUES ($user_id, '$content_safe', NOW())";
        
        if (mysqli_query($conn, $sql)) {
            header('Location: index.php');
            exit;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="main">
    <!-- 左侧边栏 -->
    <div class="sidebar-left">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="user-card">
                <img src="<?php echo $_SESSION['avatar'] ?? 'images/default-avatar.jpg'; ?>" 
                     alt="头像" class="avatar">
                <h3><?php echo htmlspecialchars($_SESSION['username'] ?? '用户'); ?></h3>
                <p class="bio">欢迎使用微博系统</p>
                <a href="profile.php?id=<?php echo $_SESSION['user_id']; ?>" 
                   class="btn btn-outline">我的主页</a>
            </div>
        <?php else: ?>
            <div class="user-card">
                <h3>欢迎来到微博系统</h3>
                <p>登录后可以发布微博、评论和点赞</p>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-primary">登录</a>
                    <a href="register.php" class="btn btn-outline">注册</a>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- 热门话题 -->
        <div class="hot-topics">
            <h4>热门话题</h4>
            <ul>
                <li><a href="search.php?q=学习">#学习#</a></li>
                <li><a href="search.php?q=生活">#生活#</a></li>
                <li><a href="search.php?q=美食">#美食#</a></li>
                <li><a href="search.php?q=旅游">#旅游#</a></li>
                <li><a href="search.php?q=科技">#科技#</a></li>
            </ul>
        </div>
    </div>
    
    <!-- 主要内容 -->
    <div class="content">
        <!-- 发布微博表单 -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="post-form">
                <form method="POST" id="postForm">
                    <textarea name="content" placeholder="分享新鲜事..." required></textarea>
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">发布</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- 微博列表 -->
        <div class="post-list">
            <?php if (empty($posts)): ?>
                <div class="empty-state">
                    <p>暂时还没有微博，赶快发布第一条吧！</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-item">
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
                        <div class="post-actions">
                            <button class="action-btn like-btn" data-post-id="<?php echo $post['id']; ?>">
                                <span class="like-icon">❤️</span> 
                                赞 <span class="like-count"><?php echo $post['likes_count']; ?></span>
                            </button>
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="action-btn comment-btn">
                                💬 评论 <span><?php echo $post['comments_count']; ?></span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- 分页 -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="index.php?page=<?php echo $i; ?>" 
                       class="page-item <?php echo $i == $page ? 'current' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="js/main.js"></script>
<script>
// 点赞功能
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const postId = this.getAttribute('data-post-id');
        const likeCount = this.querySelector('.like-count');
        
        // 简单切换样式
        this.classList.toggle('liked');
        
        // 更新点赞数
        let count = parseInt(likeCount.textContent);
        if (this.classList.contains('liked')) {
            count++;
            likeCount.textContent = count;
            alert('已点赞微博 #' + postId);
        } else {
            count--;
            likeCount.textContent = count;
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>