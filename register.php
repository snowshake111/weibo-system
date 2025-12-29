<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// 如果已登录，跳转到首页
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$page_title = '注册';
$error = '';
$success = '';

// 处理注册
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_inputs($_POST['username'] ?? '');
    $password = filter_inputs($_POST['password'] ?? '');
    $email = filter_inputs($_POST['email'] ?? '');
    $confirm_password = filter_inputs($_POST['confirm_password'] ?? '');
    
    // 验证
    if (empty($username) || empty($password) || empty($email)) {
        $error = '所有字段都不能为空';
    } elseif ($password !== $confirm_password) {
        $error = '两次输入的密码不一致';
    } elseif (strlen($password) < 6) {
        $error = '密码长度至少6位';
    } else {
        // 检查用户名是否已存在
        $username_safe = mysqli_real_escape_string($conn, $username);
        $sql = "SELECT id FROM users WHERE username = '$username_safe'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $error = '用户名已存在';
        } else {
            // 检查邮箱是否已存在
            $email_safe = mysqli_real_escape_string($conn, $email);
            $sql = "SELECT id FROM users WHERE email = '$email_safe'";
            $result = mysqli_query($conn, $sql);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $error = '邮箱已被注册';
            } else {
                // 注册用户
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, password, email, created_at) 
                        VALUES ('$username_safe', '$hashed_password', '$email_safe', NOW())";
                
                if (mysqli_query($conn, $sql)) {
                    $success = '注册成功！请<a href="login.php">登录</a>';
                } else {
                    $error = '注册失败，请稍后再试';
                }
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>注册新账号</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <div class="form-group">
                <input type="text" name="username" placeholder="用户名" required
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <input type="email" name="email" placeholder="邮箱" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="密码（至少6位）" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="确认密码" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">注册</button>
        </form>
        
        <div class="auth-links">
            <p>已有账号？ <a href="login.php">立即登录</a></p>
            <p><a href="index.php">返回首页</a></p>
        </div>
    </div>
</div>

<style>
.alert-success {
    background: #e7f7ef;
    color: #0a5;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
    border: 1px solid #a7e6c5;
}

.alert-success a {
    color: #0a5;
    font-weight: bold;
}
</style>

<?php require_once 'includes/footer.php'; ?>