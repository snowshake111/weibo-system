<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// 如果已登录，跳转到首页
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$page_title = '登录';
$error = '';

// 处理登录
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_inputs($_POST['username'] ?? '');
    $password = filter_inputs($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = '用户名和密码不能为空';
    } else {
        $username_safe = mysqli_real_escape_string($conn, $username);
        $sql = "SELECT * FROM users WHERE username = '$username_safe'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && $user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['avatar'] = $user['avatar'];
                
                header('Location: index.php');
                exit;
            } else {
                $error = '密码错误';
            }
        } else {
            $error = '用户不存在';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>登录微博系统</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <input type="text" name="username" placeholder="用户名" required
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="密码" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">登录</button>
        </form>
        
        <div class="auth-links">
            <p>还没有账号？ <a href="register.php">立即注册</a></p>
            <p><a href="index.php">返回首页</a></p>
        </div>
    </div>
</div>

<style>
.auth-container {
    max-width: 400px;
    margin: 50px auto;
    padding: 0 20px;
}

.auth-box {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.auth-box h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.btn-block {
    width: 100%;
    padding: 12px;
    font-size: 16px;
}

.auth-links {
    margin-top: 20px;
    text-align: center;
    color: #666;
}

.auth-links a {
    color: #e6162d;
}

.alert-error {
    background: #fee;
    color: #c00;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
    border: 1px solid #fcc;
}
</style>

<?php require_once 'includes/footer.php'; ?>