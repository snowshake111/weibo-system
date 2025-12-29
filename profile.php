<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// 获取用户ID
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0 && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

if ($user_id <= 0) {
    header('Location: login.php');
    exit;
}

// 获取用户信息
$sql = "SELECT id, username, email, avatar, bio, created_at 
        FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header('Location: index.php');
    exit;
}

$page_title = $user['username'] . '的主页';

// 获取用户的微博
$sql_posts = "SELECT * FROM posts WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 20";
$result_posts = mysqli_query($conn, $sql_posts);
$posts = [];
if ($result_posts) {
    while ($row = mysqli_fetch_assoc($result_posts)) {
        $posts[] = $row;
    }
}

// 获取微博数量
$sql_count = "SELECT COUNT(*) as count FROM posts WHERE user_id = $user_id";
$result_count = mysqli_query($conn, $sql_count);
$post_count = mysqli_fetch_assoc($result_count)['count'];

// 处理更新个人资料（只能更新自己的资料）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
    $bio = filter_inputs($_POST['bio'] ?? '');
    
    // 处理头像上传
    $avatar = $user['avatar'];
    if (!empty($_FILES['avatar']['name'])) {
        $upload_dir = 'uploads/avatars/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = uniqid() . '_' . basename($_FILES['avatar']['name']);
        $destination = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
            $avatar = $destination;
        }
    }
    
    // 更新数据库
    $bio_safe = mysqli_real_escape_string($conn, $bio);
    $avatar_safe = mysqli_real_escape_string($conn, $avatar);
    
    $sql = "UPDATE users SET bio = '$bio_safe', avatar = '$avatar_safe' WHERE id = $user_id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['avatar'] = $avatar;
        header("Location: profile.php?id=$user_id");
        exit;
    }
}

require_once 'includes/header.php';
?>

<div class="profile-container">
    <!-- 用户信息卡片 -->
    <div class="profile-header">
        <div class="avatar-container">
            <img src="<?php echo $user['avatar'] ?: 'images/default-avatar.jpg'; ?>" 
                 alt="头像" class="profile-avatar">
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id): ?>
                <form method="POST" enctype="multipart/form-data" class="avatar-form">
                    <input type="file" name="avatar" id="avatar-input" accept="image/*" style="display: none;">
                    <label for="avatar-input" class="avatar-edit-btn">更换头像</label>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['username']); ?></h1>
            <div class="profile-stats">
                <span class="stat-item">
                    <strong><?php echo $post_count; ?></strong> 微博
                </span>
                <span class="stat-item">
                    <strong>0</strong> 关注
                </span>
                <span class="stat-item">
                    <strong>0</strong> 粉丝
                </span>
            </div>
            
            <?php if ($user['bio']): ?>
                <p class="profile-bio"><?php echo htmlspecialchars($user['bio']); ?></p>
            <?php endif; ?>
            
            <p class="join-date">加入时间：<?php echo $user['created_at']; ?></p>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id): ?>
                <button id="edit-bio-btn" class="btn btn-outline">编辑资料</button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 编辑资料表单 -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id): ?>
        <div class="edit-form" id="editForm" style="display: none;">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>个人简介</label>
                    <textarea name="bio" rows="3" placeholder="介绍一下自己..."><?php 
                        echo htmlspecialchars($user['bio'] ?? ''); 
                    ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存</button>
                    <button type="button" id="cancel-edit-btn" class="btn btn-outline">取消</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
    
    <!-- 用户的微博 -->
    <div class="profile-posts">
        <h3>我的微博 (<?php echo $post_count; ?>)</h3>
        
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <p>还没有发布过微博</p>
            </div>
        <?php else: ?>
            <div class="post-list">
                <?php foreach ($posts as $post): ?>
                    <div class="post-item">
                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>
                        <div class="post-meta">
                            <span class="post-time"><?php echo $post['created_at']; ?></span>
                            <span class="post-stats">
                                <span class="likes">❤️ <?php echo $post['likes_count']; ?></span>
                                <span class="comments">💬 <?php echo $post['comments_count']; ?></span>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.profile-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 0 20px;
}

.profile-header {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    gap: 30px;
    margin-bottom: 30px;
}

.avatar-container {
    position: relative;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f5f5f5;
}

.avatar-edit-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    background: #e6162d;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}

.profile-info h1 {
    margin-bottom: 10px;
    color: #333;
}

.profile-stats {
    display: flex;
    gap: 20px;
    margin: 15px 0;
}

.stat-item {
    font-size: 14px;
    color: #666;
}

.stat-item strong {
    color: #333;
    font-size: 18px;
}

.profile-bio {
    margin: 15px 0;
    color: #555;
    line-height: 1.6;
}

.join-date {
    color: #999;
    font-size: 13px;
    margin-top: 10px;
}

.edit-form {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.edit-form textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    resize: vertical;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.profile-posts {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.profile-posts h3 {
    margin-bottom: 20px;
    color: #333;
}

.post-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.profile-posts .post-item {
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 8px;
}

.post-meta {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    font-size: 13px;
    color: #999;
}

.post-stats {
    display: flex;
    gap: 15px;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #999;
}
</style>

<script>
// 编辑资料功能
const editBtn = document.getElementById('edit-bio-btn');
const editForm = document.getElementById('editForm');
const cancelBtn = document.getElementById('cancel-edit-btn');
const avatarInput = document.getElementById('avatar-input');

if (editBtn && editForm) {
    editBtn.addEventListener('click', function() {
        editForm.style.display = 'block';
        editBtn.style.display = 'none';
    });
}

if (cancelBtn && editForm) {
    cancelBtn.addEventListener('click', function() {
        editForm.style.display = 'none';
        editBtn.style.display = 'block';
    });
}

// 头像上传预览
if (avatarInput) {
    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('.profile-avatar').src = e.target.result;
                // 自动提交表单
                document.querySelector('.avatar-form').submit();
            }
            reader.readAsDataURL(file);
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>