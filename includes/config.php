<?php
// 数据库配置信息（后端同学可补充，前端无需修改）
$db_host = 'localhost';
$db_user = 'root';
$db_pwd = '';
$db_name = 'weibo_system';

$conn = mysqli_connect($db_host, $db_user, $db_pwd, $db_name);

if ($conn === false) {
    exit('数据库连接失败：' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8');
//  类型注解，消除警告
/** @var mysqli $conn */
?>