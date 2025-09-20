<?php
header('Content-Type: text/plain; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'db':
        testDatabase();
        break;
    case 'info':
        showSystemInfo();
        break;
    default:
        showPHPInfo();
        break;
}

function showPHPInfo() {
    echo "PHP Version: " . phpversion() . "\n";
    echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
    echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
    echo "Current Directory: " . getcwd() . "\n";
    echo "ODBC Support: " . (extension_loaded('odbc') ? 'Yes' : 'No') . "\n";
    echo "Error Reporting: " . error_reporting() . "\n";
    echo "Display Errors: " . ini_get('display_errors') . "\n";
    echo "Log Errors: " . ini_get('log_errors') . "\n";
    echo "Error Log: " . ini_get('error_log') . "\n";
}

function testDatabase() {
    $dbdir = "./shujuku/data.mdb";
    $dbsname = realpath($dbdir);
    
    if (!$dbsname) {
        echo "error: 数据库文件路径不存在: {$dbdir}\n";
        return;
    }
    
    if (!file_exists($dbsname)) {
        echo "error: 数据库文件不存在: {$dbsname}\n";
        return;
    }
    
    $cstr = "DRIVER=Microsoft Access Driver (*.mdb);DBQ={$dbsname}";
    $conn = @odbc_connect($cstr, "", "", SQL_CUR_USE_ODBC);
    
    if (!$conn) {
        echo "error: 无法连接到数据库\n";
        echo "连接字符串: {$cstr}\n";
        echo "ODBC错误: " . odbc_errormsg() . "\n";
        return;
    }
    
    // 测试查询
    $sql = "SELECT COUNT(*) as total FROM kangxi";
    $result = @odbc_exec($conn, $sql);
    
    if (!$result) {
        echo "error: 查询失败\n";
        echo "SQL: {$sql}\n";
        echo "ODBC错误: " . odbc_errormsg() . "\n";
        @odbc_close($conn);
        return;
    }
    
    $row = @odbc_fetch_array($result);
    $total = $row['total'] ?? 0;
    
    echo "success: 数据库连接正常\n";
    echo "数据库文件: {$dbsname}\n";
    echo "记录总数: {$total}\n";
    
    @odbc_close($conn);
}

function showSystemInfo() {
    echo "=== 系统信息 ===\n";
    echo "PHP版本: " . phpversion() . "\n";
    echo "服务器: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
    echo "操作系统: " . php_uname() . "\n";
    echo "当前时间: " . date('Y-m-d H:i:s') . "\n";
    echo "时区: " . date_default_timezone_get() . "\n";
    echo "内存限制: " . ini_get('memory_limit') . "\n";
    echo "最大执行时间: " . ini_get('max_execution_time') . "秒\n";
    echo "文件上传限制: " . ini_get('upload_max_filesize') . "\n";
    echo "POST限制: " . ini_get('post_max_size') . "\n";
    
    echo "\n=== 扩展支持 ===\n";
    echo "ODBC: " . (extension_loaded('odbc') ? '✓' : '✗') . "\n";
    echo "JSON: " . (extension_loaded('json') ? '✓' : '✗') . "\n";
    echo "MBString: " . (extension_loaded('mbstring') ? '✓' : '✗') . "\n";
    echo "PDO: " . (extension_loaded('pdo') ? '✓' : '✗') . "\n";
    
    echo "\n=== 目录权限 ===\n";
    $dirs = ['.', './shujuku'];
    foreach ($dirs as $dir) {
        $writable = is_writable($dir) ? '✓' : '✗';
        echo "{$dir}: {$writable}\n";
    }
    
    echo "\n=== 文件检查 ===\n";
    $files = ['index.php', 'search.php', 'config.php', 'style.css', 'script.js'];
    foreach ($files as $file) {
        $exists = file_exists($file) ? '✓' : '✗';
        $readable = is_readable($file) ? '✓' : '✗';
        echo "{$file}: 存在{$exists} 可读{$readable}\n";
    }
}
?>