<?php
// 数据库连接配置
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'query_system';

// 获取域名前缀作为表前缀
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
$domain_parts = explode('.', $domain);
$table_prefix = $domain_parts[0];

// 版本号，用于更新浏览器缓存
$version = '1.0.0';

// 上传文件大小限制 (4MB)
$upload_max_size = 4 * 1024 * 1024;

// 菜单配置
$user_menus = [
    'list' => '数据列表',
    'liin' => '数据导入', 
    'tong' => '统计管理',
    'site' => '系统设置',
    'baks' => '数据备份',
    'pass' => '修改密码',
    'help' => '使用帮助'
];

$admin_menus = [
    'user' => '用户列表',
    'list' => '查询列表',
    'logs' => '操作日志',
    'pass' => '修改密码',
    'help' => '使用帮助'
];

// 数据库连接
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    die('数据库连接失败: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');
?>