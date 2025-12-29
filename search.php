<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = '搜索';
$keyword = isset($_GET['q']) ? mysqli_real_escape_string($conn, trim($_GET['q'])) : '';
$search_type = isset($_GET['type']) ? $_GET['type'] : 'posts';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$results = [];
$total_results = 0;

if (!empty($keyword)) {
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    if ($search_type === 'posts') {
        // 搜索微博
        $sql = "SELECT p.*, u.username, u.avatar 
                FROM posts p 
                LEFT JOIN users u ON p.user_id = u.id 
                WHERE p.content LIKE '%$keyword%' 
                ORDER BY p.created_at DESC 
                LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($conn, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $results[] = $row;
            }
        }
        
        // 获取总数
        $sql_count = "SELECT COUNT(*) as count FROM posts WHERE content LIKE '%$keyword%'";
        $result_count = mysqli_query($conn, $sql_count);
        $total_results = mysqli_fetch_assoc($result_count)['count'];
        
    } elseif ($search_type === 'users') {
        // 搜索用户
        $sql = "SELECT id, username, avatar, bio, created_at 
                FROM users 
                WHERE username LIKE '%$keyword%' 
                ORDER BY id DESC 
                LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($conn, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $results[] = $row;
            }
        }
        
        // 获取总数
        $sql_count = "SELECT COUNT(*) as count FROM users WHERE username LIKE '%$keyword%'";
        $result_count = mysqli_query($conn, $sql_count);
        $total_results = mysqli_fetch_assoc($result_count)['count'];
    }
}

require_once 'includes/header.php';
?>

<div class="search-container">
    <!-- 搜索框 -->
    <div class="search-box-large">
        <form method="GET" action="search.php" class="search-form">
            <div class="search-input-group">
                <input type="text" name="q" 
                       value="<?php echo htmlspecialchars($keyword); ?>"
                       placeholder="搜索微博、用户..."
                       class="search-input" 
                       autofocus>
                <button type="submit" class="search-btn">搜索</button>
            </div>
            <div class="search-types">
                <label>
                    <input type="radio" name="type" value="posts" 
                           <?php echo $search_type === 'posts' ? 'checked' : ''; ?>> 微博
                </label>
                <label>
                    <input type="radio" name="type" value="users"
                           <?php echo $search_type === 'users' ? 'checked' : ''; ?>> 用户
                </label>
            </div>
        </form>
    </div>
    
    <!-- 搜索结果 -->
    <div class="search-results">
        <?php if (empty($keyword)): ?>
            <!-- 搜索提示 -->
            <div class="search-tips">
                <h3>搜索提示</h3>
                <div class="tips-grid">
                    <div class="tip-card">
                        <h4>热门搜索</h4>
                        <ul>
                            <li><a href="search.php?q=学习">学习</a></li>
                            <li><a href="search.php?q=生活">生活</a></li>
                            <li><a href="search.php?q=美食">美食</a></li>
                            <li><a href="search.php?q=旅游">旅游</a></li>
                        </ul>
                    </div>
                    
                    <div class="tip-card">
                        <h4>搜索技巧</h4>
                        <ul>
                            <li>输入关键词搜索相关微博和用户</li>
                            <li>可以搜索特定用户的微博</li>
                            <li>关键词越长，结果越精确</li>
                        </ul>
                    </div>
                </div>
            </div>
            
        <?php elseif (empty($results)): ?>
            <!-- 无结果 -->
            <div class="no-results">
                <p>没有找到相关结果</p>
                <p>请尝试其他关键词</p>
            </div>
            
        <?php else: ?>
            <!-- 搜索结果 -->
            <div class="results-header">
                <h3>
                    <?php if ($search_type === 'posts'): ?>
                        找到 <?php echo $total_results; ?> 条微博
                    <?php else: ?>
                        找到 <?php echo $total_results; ?> 个用户
                    <?php endif; ?>
                </h3>
            </div>
            
            <?php if ($search_type === 'posts'): ?>
                <!-- 微博结果 -->
                <div class="posts-results">
                    <?php foreach ($results as $post): ?>
                        <div class="post-item search-result">
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
                                <?php 
                                // 高亮关键词
                                $content = htmlspecialchars($post['content']);
                                $highlighted = str_ireplace($keyword, 
                                    "<span class='highlight'>$keyword</span>", $content);
                                echo nl2br($highlighted);
                                ?>
                            </div>
                            <div class="post-meta">
                                <a href="post.php?id=<?php echo $post['id']; ?>">查看详情</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
            <?php else: ?>
                <!-- 用户结果 -->
                <div class="users-results">
                    <div class="users-grid">
                        <?php foreach ($results as $user): ?>
                            <div class="user-card search">
                                <div class="user-header">
                                    <img src="<?php echo $user['avatar'] ?? 'images/default-avatar.jpg'; ?>" 
                                         class="avatar" alt="头像">
                                    <div class="user-info">
                                        <h4>
                                            <a href="profile.php?id=<?php echo $user['id']; ?>">
                                                <?php echo htmlspecialchars($user['username']); ?>
                                            </a>
                                        </h4>
                                        <?php if ($user['bio']): ?>
                                            <p class="user-bio"><?php 
                                                echo mb_strlen($user['bio']) > 30 ? 
                                                    mb_substr($user['bio'], 0, 30) . '...' : 
                                                    $user['bio']; 
                                            ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-footer">
                                    <span class="join-date">加入时间：<?php echo $user['created_at']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- 分页 -->
            <?php
            $total_pages = ceil($total_results / 10);
            if ($total_pages > 1):
            ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="search.php?q=<?php echo urlencode($keyword); ?>&type=<?php echo $search_type; ?>&page=<?php echo $i; ?>" 
                           class="page-item <?php echo $i == $page ? 'current' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.search-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 0 20px;
}

.search-box-large {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.search-input-group {
    display: flex;
    margin-bottom: 15px;
}

.search-input {
    flex: 1;
    padding: 12px 15px;
    border: 2px solid #e6162d;
    border-radius: 4px 0 0 4px;
    font-size: 16px;
}

.search-btn {
    background: #e6162d;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    font-size: 16px;
}

.search-types {
    display: flex;
    gap: 15px;
}

.search-types label {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.search-tips {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.search-tips h3 {
    margin-bottom: 20px;
    color: #333;
}

.tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.tip-card {
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 8px;
}

.tip-card h4 {
    margin-bottom: 10px;
    color: #666;
}

.tip-card ul {
    list-style: none;
    padding: 0;
}

.tip-card li {
    padding: 5px 0;
    color: #666;
}

.tip-card a {
    color: #e6162d;
}

.no-results {
    background: white;
    padding: 40px;
    text-align: center;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    color: #666;
}

.results-header {
    margin-bottom: 20px;
}

.results-header h3 {
    color: #333;
}

.posts-results {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.search-result {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.highlight {
    background: yellow;
    padding: 0 2px;
}

.post-meta {
    margin-top: 10px;
    border-top: 1px solid #eee;
    padding-top: 10px;
}

.post-meta a {
    color: #e6162d;
}

.users-results {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.user-card.search {
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 8px;
    transition: transform 0.3s;
}

.user-card.search:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.user-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 10px;
}

.user-header .avatar {
    width: 50px;
    height: 50px;
}

.user-header h4 {
    margin: 0;
}

.user-header h4 a {
    color: #333;
}

.user-bio {
    color: #666;
    font-size: 13px;
    margin-top: 5px;
}

.user-footer {
    border-top: 1px solid #f5f5f5;
    padding-top: 10px;
    font-size: 12px;
    color: #999;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 30px;
}

.page-item {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #666;
    text-decoration: none;
}

.page-item.current {
    background: #e6162d;
    color: white;
    border-color: #e6162d;
}

.page-item:hover {
    background: #f5f5f5;
}
</style>

<?php require_once 'includes/footer.php'; ?>