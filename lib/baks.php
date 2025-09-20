<?php
// 数据备份管理

$act = $_GET['act'] ?? '';

switch ($act) {
    case 'backup':
        backupData();
        break;
    default:
        showBackupPage();
        break;
}

function showBackupPage() {
    global $version;
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>数据备份 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
    </head>
    <body>
        <div class="header">
            <h1>数据备份</h1>
            <button class="btn btn-secondary" onclick="location.href='user.php?do=logout'">退出</button>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab" onclick="switchTab('list')">数据列表</button>
                <button class="tab" onclick="switchTab('liin')">数据导入</button>
                <button class="tab" onclick="switchTab('tong')">统计管理</button>
                <button class="tab" onclick="switchTab('site')">系统设置</button>
                <button class="tab active" onclick="switchTab('baks')">数据备份</button>
                <button class="tab" onclick="switchTab('pass')">修改密码</button>
                <button class="tab" onclick="switchTab('help')">使用帮助</button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">数据备份</h3>
                </div>
                
                <div class="backup-options">
                    <div class="backup-option">
                        <h4>完整备份</h4>
                        <p>备份所有数据表，包括用户数据、系统设置、操作日志等</p>
                        <button class="btn btn-primary" onclick="backupData('full')">开始完整备份</button>
                    </div>
                    
                    <div class="backup-option">
                        <h4>数据备份</h4>
                        <p>仅备份查询数据，不包含用户和系统设置</p>
                        <button class="btn btn-success" onclick="backupData('data')">开始数据备份</button>
                    </div>
                    
                    <div class="backup-option">
                        <h4>设置备份</h4>
                        <p>仅备份系统设置和用户信息</p>
                        <button class="btn btn-info" onclick="backupData('config')">开始设置备份</button>
                    </div>
                </div>
                
                <div id="backupProgress" style="display: none; margin-top: 20px;">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div class="progress-text" id="progressText">准备备份...</div>
                </div>
                
                <div id="backupResult" style="display: none; margin-top: 20px;"></div>
            </div>
        </div>
        
        <style>
            .backup-options {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-bottom: 20px;
            }
            
            .backup-option {
                padding: 20px;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                text-align: center;
            }
            
            .backup-option h4 {
                color: #2c3e50;
                margin-bottom: 10px;
            }
            
            .backup-option p {
                color: #666;
                margin-bottom: 15px;
                line-height: 1.5;
            }
            
            .progress-bar {
                width: 100%;
                height: 20px;
                background: #f0f0f0;
                border-radius: 10px;
                overflow: hidden;
                margin-bottom: 10px;
            }
            
            .progress-fill {
                height: 100%;
                background: #3498db;
                width: 0%;
                transition: width 0.3s;
            }
            
            .progress-text {
                text-align: center;
                color: #666;
            }
        </style>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 页面加载完成
            });
            
            function switchTab(tab) {
                window.location.href = 'user.php?do=' + tab;
            }
            
            function backupData(type) {
                showBackupProgress();
                
                ajaxRequest('user.php?do=baks&act=backup', {type: type}, function(response) {
                    hideBackupProgress();
                    
                    if (response.code === 1) {
                        showBackupResult(response.data);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function showBackupProgress() {
                document.getElementById('backupProgress').style.display = 'block';
                document.getElementById('backupResult').style.display = 'none';
                updateProgress(0, '准备备份...');
            }
            
            function hideBackupProgress() {
                document.getElementById('backupProgress').style.display = 'none';
            }
            
            function updateProgress(percent, text) {
                document.getElementById('progressFill').style.width = percent + '%';
                document.getElementById('progressText').textContent = text;
            }
            
            function showBackupResult(data) {
                let html = '<div class="backup-result">';
                html += '<h4>备份完成</h4>';
                html += '<div class="result-info">';
                html += '<p><strong>文件名:</strong> ' + data.filename + '</p>';
                html += '<p><strong>文件大小:</strong> ' + data.size + '</p>';
                html += '<p><strong>备份时间:</strong> ' + data.time + '</p>';
                html += '<p><strong>记录数:</strong> ' + data.records + '</p>';
                html += '</div>';
                html += '<div class="result-actions">';
                html += '<button class="btn btn-primary" onclick="downloadBackup(\'' + data.filename + '\')">下载备份文件</button>';
                html += '</div>';
                html += '</div>';
                
                document.getElementById('backupResult').innerHTML = html;
                document.getElementById('backupResult').style.display = 'block';
            }
            
            function downloadBackup(filename) {
                window.open('user.php?do=baks&act=backup&download=' + encodeURIComponent(filename));
            }
        </script>
    </body>
    </html>
    <?php
}

function backupData() {
    global $db, $table_prefix;
    
    $type = safeFilter($_GET['type'] ?? 'full');
    $download = safeFilter($_GET['download'] ?? '');
    
    if ($download) {
        // 下载备份文件
        $backup_dir = 'backups/';
        $file_path = $backup_dir . $download;
        
        if (!file_exists($file_path)) {
            error('备份文件不存在');
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $download . '"');
        header('Content-Length: ' . filesize($file_path));
        
        readfile($file_path);
        exit;
    }
    
    // 创建备份目录
    $backup_dir = 'backups/';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $filename = 'backup_' . $type . '_' . date('YmdHis') . '.sql';
    $file_path = $backup_dir . $filename;
    
    $sql_content = "-- 查询系统数据备份\n";
    $sql_content .= "-- 备份时间: " . date('Y-m-d H:i:s') . "\n";
    $sql_content .= "-- 备份类型: " . $type . "\n\n";
    
    $records = 0;
    
    if ($type === 'full' || $type === 'data') {
        // 备份数据表
        $data = $db->fetchAll("SELECT * FROM `{$table_prefix}_data` ORDER BY id");
        $records += count($data);
        
        $sql_content .= "-- 数据表备份\n";
        $sql_content .= "DROP TABLE IF EXISTS `{$table_prefix}_data`;\n";
        $sql_content .= "CREATE TABLE `{$table_prefix}_data` (\n";
        $sql_content .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
        $sql_content .= "  `condition1` varchar(255) NOT NULL,\n";
        $sql_content .= "  `condition2` varchar(255) DEFAULT NULL,\n";
        $sql_content .= "  `condition3` varchar(255) DEFAULT NULL,\n";
        $sql_content .= "  `detail_data` text,\n";
        $sql_content .= "  `batch` varchar(50) DEFAULT NULL,\n";
        $sql_content .= "  `query_status` tinyint(1) DEFAULT '1',\n";
        $sql_content .= "  `add_time` datetime DEFAULT CURRENT_TIMESTAMP,\n";
        $sql_content .= "  `query_count` int(11) DEFAULT '0',\n";
        $sql_content .= "  `query_remark` text,\n";
        $sql_content .= "  PRIMARY KEY (`id`),\n";
        $sql_content .= "  KEY `idx_condition1` (`condition1`)\n";
        $sql_content .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";
        
        if (!empty($data)) {
            $sql_content .= "INSERT INTO `{$table_prefix}_data` VALUES\n";
            $values = [];
            foreach ($data as $row) {
                $values[] = "(" . $row['id'] . ", '" . addslashes($row['condition1']) . "', '" . 
                           addslashes($row['condition2']) . "', '" . addslashes($row['condition3']) . "', '" . 
                           addslashes($row['detail_data']) . "', '" . addslashes($row['batch']) . "', " . 
                           $row['query_status'] . ", '" . $row['add_time'] . "', " . 
                           $row['query_count'] . ", '" . addslashes($row['query_remark']) . "')";
            }
            $sql_content .= implode(",\n", $values) . ";\n\n";
        }
    }
    
    if ($type === 'full' || $type === 'config') {
        // 备份用户表
        $users = $db->fetchAll("SELECT * FROM `{$table_prefix}_user` ORDER BY id");
        $records += count($users);
        
        $sql_content .= "-- 用户表备份\n";
        $sql_content .= "DROP TABLE IF EXISTS `{$table_prefix}_user`;\n";
        $sql_content .= "CREATE TABLE `{$table_prefix}_user` (\n";
        $sql_content .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
        $sql_content .= "  `phone` varchar(20) NOT NULL,\n";
        $sql_content .= "  `password` varchar(255) NOT NULL,\n";
        $sql_content .= "  `wechat_id` varchar(100) DEFAULT NULL,\n";
        $sql_content .= "  `create_time` datetime DEFAULT CURRENT_TIMESTAMP,\n";
        $sql_content .= "  `status` enum('normal','disabled') DEFAULT 'normal',\n";
        $sql_content .= "  PRIMARY KEY (`id`),\n";
        $sql_content .= "  UNIQUE KEY `phone` (`phone`)\n";
        $sql_content .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";
        
        if (!empty($users)) {
            $sql_content .= "INSERT INTO `{$table_prefix}_user` VALUES\n";
            $values = [];
            foreach ($users as $row) {
                $values[] = "(" . $row['id'] . ", '" . addslashes($row['phone']) . "', '" . 
                           addslashes($row['password']) . "', '" . addslashes($row['wechat_id']) . "', '" . 
                           $row['create_time'] . "', '" . $row['status'] . "')";
            }
            $sql_content .= implode(",\n", $values) . ";\n\n";
        }
        
        // 备份系统设置表
        $sites = $db->fetchAll("SELECT * FROM `{$table_prefix}_site` ORDER BY id");
        $records += count($sites);
        
        $sql_content .= "-- 系统设置表备份\n";
        $sql_content .= "DROP TABLE IF EXISTS `{$table_prefix}_site`;\n";
        $sql_content .= "CREATE TABLE `{$table_prefix}_site` (\n";
        $sql_content .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
        $sql_content .= "  `domain` varchar(100) NOT NULL,\n";
        $sql_content .= "  `admin` varchar(100) NOT NULL,\n";
        $sql_content .= "  `user_type` varchar(10) DEFAULT 'vip0',\n";
        $sql_content .= "  `admin_access` tinyint(1) DEFAULT '1',\n";
        $sql_content .= "  `title` varchar(255) DEFAULT NULL,\n";
        $sql_content .= "  `condition1` varchar(100) DEFAULT NULL,\n";
        $sql_content .= "  `condition2` varchar(100) DEFAULT NULL,\n";
        $sql_content .= "  `condition3` varchar(100) DEFAULT NULL,\n";
        $sql_content .= "  `query_rule` varchar(10) DEFAULT 'T1',\n";
        $sql_content .= "  `match_rule` varchar(10) DEFAULT 'd',\n";
        $sql_content .= "  `footer_text` varchar(255) DEFAULT NULL,\n";
        $sql_content .= "  `footer_link` varchar(255) DEFAULT NULL,\n";
        $sql_content .= "  `has_result_text` varchar(255) DEFAULT NULL,\n";
        $sql_content .= "  `no_result_text` varchar(255) DEFAULT NULL,\n";
        $sql_content .= "  `input_hint_text` text,\n";
        $sql_content .= "  `captcha_enabled` tinyint(1) DEFAULT '0',\n";
        $sql_content .= "  `user_access` tinyint(1) DEFAULT '1',\n";
        $sql_content .= "  `page_size` int(11) DEFAULT '20',\n";
        $sql_content .= "  PRIMARY KEY (`id`),\n";
        $sql_content .= "  UNIQUE KEY `domain` (`domain`)\n";
        $sql_content .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";
        
        if (!empty($sites)) {
            $sql_content .= "INSERT INTO `{$table_prefix}_site` VALUES\n";
            $values = [];
            foreach ($sites as $row) {
                $values[] = "(" . $row['id'] . ", '" . addslashes($row['domain']) . "', '" . 
                           addslashes($row['admin']) . "', '" . addslashes($row['user_type']) . "', " . 
                           $row['admin_access'] . ", '" . addslashes($row['title']) . "', '" . 
                           addslashes($row['condition1']) . "', '" . addslashes($row['condition2']) . "', '" . 
                           addslashes($row['condition3']) . "', '" . addslashes($row['query_rule']) . "', '" . 
                           addslashes($row['match_rule']) . "', '" . addslashes($row['footer_text']) . "', '" . 
                           addslashes($row['footer_link']) . "', '" . addslashes($row['has_result_text']) . "', '" . 
                           addslashes($row['no_result_text']) . "', '" . addslashes($row['input_hint_text']) . "', " . 
                           $row['captcha_enabled'] . ", " . $row['user_access'] . ", " . $row['page_size'] . ")";
            }
            $sql_content .= implode(",\n", $values) . ";\n\n";
        }
    }
    
    // 写入文件
    file_put_contents($file_path, $sql_content);
    
    // 记录日志
    writeLog($_SESSION['user_phone'], $_SESSION['user_domain'], getClientIP(), 'backup_data', "备份类型: {$type}");
    
    success('备份完成', [
        'filename' => $filename,
        'size' => formatFileSize(filesize($file_path)),
        'time' => date('Y-m-d H:i:s'),
        'records' => $records
    ]);
}

function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>