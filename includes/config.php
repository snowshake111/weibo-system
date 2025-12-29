<?php
// 数据库配置信息
$db_host = 'localhost';
$db_user = 'root';
$db_pwd = '';
$db_name = 'weibo_system';

// 连接数据库
$conn = mysqli_connect($db_host, $db_user, $db_pwd, $db_name);

if (!$conn) {
    die('数据库连接失败: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8');

// 网站基础配置
define('SITE_URL', 'http://localhost/weibo-system');

// 启动会话
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>