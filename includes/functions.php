<?php
// 通用工具函数
// 1. 过滤用户输入，防止XSS攻击
function filter_inputs($str) {
    return isset($str) ? htmlspecialchars(trim($str)) : '';
}

// 2. 跳转函数
function redirect($url) {
    header("Location: {$url}");
    exit;
}
?>