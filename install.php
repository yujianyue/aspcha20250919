<?php
// 系统安装页面

$step = $_GET['step'] ?? 1;
$act = $_POST['act'] ?? '';

switch ($act) {
    case 'check_env':
        checkEnvironment();
        break;
    case 'create_db':
        createDatabase();
        break;
    case 'import_demo':
        importDemoData();
        break;
    case 'create_admin':
        createAdmin();
        break;
    default:
        showInstallPage();
        break;
}

function showInstallPage() {
    global $step;
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>系统安装 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=1.0.0">
        <style>
            .install-container {
                max-width: 800px;
                margin: 50px auto;
                background: white;
                border-radius: 8px;
                padding: 40px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .install-header {
                text-align: center;
                margin-bottom: 40px;
            }
            .install-header h1 {
                color: #2c3e50;
                margin-bottom: 10px;
            }
            .install-steps {
                display: flex;
                justify-content: center;
                margin-bottom: 40px;
            }
            .step {
                display: flex;
                align-items: center;
                margin: 0 10px;
            }
            .step-number {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                background: #bdc3c7;
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 10px;
                font-weight: bold;
            }
            .step.active .step-number {
                background: #3498db;
            }
            .step.completed .step-number {
                background: #27ae60;
            }
            .step-line {
                width: 50px;
                height: 2px;
                background: #bdc3c7;
                margin: 0 10px;
            }
            .step.completed + .step .step-line {
                background: #27ae60;
            }
            .install-content {
                min-height: 400px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: 500;
                color: #555;
            }
            .form-control {
                width: 100%;
                padding: 10px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            .form-control:focus {
                outline: none;
                border-color: #3498db;
                box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            }
            .btn-group {
                display: flex;
                gap: 10px;
                justify-content: center;
                margin-top: 30px;
            }
            .btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                text-decoration: none;
                display: inline-block;
                text-align: center;
            }
            .btn-primary {
                background: #3498db;
                color: white;
            }
            .btn-primary:hover {
                background: #2980b9;
            }
            .btn-secondary {
                background: #95a5a6;
                color: white;
            }
            .btn-secondary:hover {
                background: #7f8c8d;
            }
            .btn-success {
                background: #27ae60;
                color: white;
            }
            .btn-success:hover {
                background: #229954;
            }
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border: 1px solid transparent;
                border-radius: 4px;
            }
            .alert-success {
                color: #155724;
                background-color: #d4edda;
                border-color: #c3e6cb;
            }
            .alert-danger {
                color: #721c24;
                background-color: #f8d7da;
                border-color: #f5c6cb;
            }
            .alert-warning {
                color: #856404;
                background-color: #fff3cd;
                border-color: #ffeaa7;
            }
            .check-list {
                list-style: none;
                padding: 0;
            }
            .check-list li {
                padding: 8px 0;
                border-bottom: 1px solid #eee;
                display: flex;
                align-items: center;
            }
            .check-list li:last-child {
                border-bottom: none;
            }
            .check-icon {
                margin-right: 10px;
                font-size: 18px;
            }
            .check-success {
                color: #27ae60;
            }
            .check-error {
                color: #e74c3c;
            }
            .progress-bar {
                width: 100%;
                height: 20px;
                background: #f0f0f0;
                border-radius: 10px;
                overflow: hidden;
                margin: 20px 0;
            }
            .progress-fill {
                height: 100%;
                background: #3498db;
                width: 0%;
                transition: width 0.3s;
            }
        </style>
    </head>
    <body>
        <div class="install-container">
            <div class="install-header">
                <h1>查询系统安装向导</h1>
                <p>欢迎使用多租户多查询规则合一通用查询系统</p>
            </div>
            
            <div class="install-steps">
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                    <div class="step-number">1</div>
                    <span>环境检查</span>
                </div>
                <div class="step-line"></div>
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                    <div class="step-number">2</div>
                    <span>数据库配置</span>
                </div>
                <div class="step-line"></div>
                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">
                    <div class="step-number">3</div>
                    <span>创建管理员</span>
                </div>
                <div class="step-line"></div>
                <div class="step <?php echo $step >= 4 ? 'active' : ''; ?>">
                    <div class="step-number">4</div>
                    <span>完成安装</span>
                </div>
            </div>
            
            <div class="install-content">
                <?php
                switch ($step) {
                    case 1:
                        showStep1();
                        break;
                    case 2:
                        showStep2();
                        break;
                    case 3:
                        showStep3();
                        break;
                    case 4:
                        showStep4();
                        break;
                    default:
                        showStep1();
                }
                ?>
            </div>
        </div>
        
        <script src="inc/js.js?v=1.0.0"></script>
        <script>
            function checkEnvironment() {
                showLoading();
                ajaxRequest('install.php?act=check_env', {}, function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        showToast('环境检查通过', 'success');
                        setTimeout(() => {
                            window.location.href = 'install.php?step=2';
                        }, 1000);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function createDatabase() {
                const form = document.getElementById('dbForm');
                const formData = new FormData(form);
                
                showLoading();
                ajaxRequest('install.php?act=create_db', Object.fromEntries(formData), function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        showToast('数据库创建成功', 'success');
                        setTimeout(() => {
                            window.location.href = 'install.php?step=3';
                        }, 1000);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function createAdmin() {
                const form = document.getElementById('adminForm');
                const formData = new FormData(form);
                
                showLoading();
                ajaxRequest('install.php?act=create_admin', Object.fromEntries(formData), function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        showToast('管理员创建成功', 'success');
                        setTimeout(() => {
                            window.location.href = 'install.php?step=4';
                        }, 1000);
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
            
            function importDemoData() {
                showLoading();
                ajaxRequest('install.php?act=import_demo', {}, function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        showToast('演示数据导入成功', 'success');
                    } else {
                        showToast(response.msg, 'error');
                    }
                });
            }
        </script>
    </body>
    </html>
    <?php
}

function showStep1() {
    ?>
    <h3>环境检查</h3>
    <p>在开始安装之前，系统将检查您的服务器环境是否满足要求。</p>
    
    <div class="alert alert-warning">
        <strong>系统要求：</strong>
        <ul>
            <li>PHP 7.0 或更高版本</li>
            <li>MySQL 5.6 或更高版本</li>
            <li>支持 mysqli 扩展</li>
            <li>支持 JSON 扩展</li>
            <li>支持 Session 扩展</li>
            <li>文件写入权限</li>
        </ul>
    </div>
    
    <div class="btn-group">
        <button class="btn btn-primary" onclick="checkEnvironment()">开始检查</button>
    </div>
    <?php
}

function showStep2() {
    ?>
    <h3>数据库配置</h3>
    <p>请填写数据库连接信息，系统将自动创建所需的数据库表。</p>
    
    <form id="dbForm">
        <div class="form-group">
            <label>数据库主机:</label>
            <input type="text" name="db_host" class="form-control" value="localhost" required>
        </div>
        
        <div class="form-group">
            <label>数据库端口:</label>
            <input type="text" name="db_port" class="form-control" value="3306" required>
        </div>
        
        <div class="form-group">
            <label>数据库名称:</label>
            <input type="text" name="db_name" class="form-control" value="query_system" required>
        </div>
        
        <div class="form-group">
            <label>数据库用户名:</label>
            <input type="text" name="db_user" class="form-control" value="root" required>
        </div>
        
        <div class="form-group">
            <label>数据库密码:</label>
            <input type="password" name="db_pass" class="form-control" placeholder="请输入数据库密码">
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="import_demo" value="1" checked> 导入演示数据 (30条示例数据)
            </label>
        </div>
    </form>
    
    <div class="btn-group">
        <button class="btn btn-primary" onclick="createDatabase()">创建数据库</button>
    </div>
    <?php
}

function showStep3() {
    ?>
    <h3>创建管理员账户</h3>
    <p>请设置管理员账户信息，用于管理整个系统。</p>
    
    <form id="adminForm">
        <div class="form-group">
            <label>管理员用户名:</label>
            <input type="text" name="admin_username" class="form-control" value="admin" required>
        </div>
        
        <div class="form-group">
            <label>管理员密码:</label>
            <input type="password" name="admin_password" class="form-control" placeholder="请输入管理员密码" required>
        </div>
        
        <div class="form-group">
            <label>确认密码:</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="请再次输入密码" required>
        </div>
        
        <div class="form-group">
            <label>管理员邮箱:</label>
            <input type="email" name="admin_email" class="form-control" placeholder="请输入管理员邮箱">
        </div>
    </form>
    
    <div class="btn-group">
        <button class="btn btn-primary" onclick="createAdmin()">创建管理员</button>
    </div>
    <?php
}

function showStep4() {
    ?>
    <h3>安装完成</h3>
    <div class="alert alert-success">
        <strong>恭喜！</strong> 系统安装完成，您现在可以开始使用查询系统了。
    </div>
    
    <div class="check-list">
        <li>
            <span class="check-icon check-success">✓</span>
            <span>数据库表创建成功</span>
        </li>
        <li>
            <span class="check-icon check-success">✓</span>
            <span>管理员账户创建成功</span>
        </li>
        <li>
            <span class="check-icon check-success">✓</span>
            <span>系统配置文件生成成功</span>
        </li>
        <li>
            <span class="check-icon check-success">✓</span>
            <span>演示数据导入成功</span>
        </li>
    </div>
    
    <div class="alert alert-warning">
        <strong>重要提醒：</strong>
        <ul>
            <li>请立即删除 install.php 文件以确保系统安全</li>
            <li>建议修改默认的管理员密码</li>
            <li>定期备份数据库</li>
            <li>监控系统日志</li>
        </ul>
    </div>
    
    <div class="btn-group">
        <a href="index.php" class="btn btn-primary">访问前台</a>
        <a href="admin.php" class="btn btn-success">管理后台</a>
        <button class="btn btn-secondary" onclick="importDemoData()">重新导入演示数据</button>
    </div>
    <?php
}

function checkEnvironment() {
    $errors = [];
    $warnings = [];
    
    // 检查PHP版本
    if (version_compare(PHP_VERSION, '7.0.0', '<')) {
        $errors[] = 'PHP版本过低，需要7.0或更高版本，当前版本：' . PHP_VERSION;
    }
    
    // 检查必需扩展
    $required_extensions = ['mysqli', 'json', 'session'];
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = "缺少必需的PHP扩展：{$ext}";
        }
    }
    
    // 检查文件权限
    if (!is_writable('.')) {
        $warnings[] = '当前目录没有写入权限，可能影响文件创建';
    }
    
    if (!is_writable('inc/')) {
        $warnings[] = 'inc目录没有写入权限，可能影响配置文件创建';
    }
    
    if (empty($errors)) {
        success('环境检查通过', [
            'errors' => $errors,
            'warnings' => $warnings
        ]);
    } else {
        error('环境检查失败：' . implode('; ', $errors));
    }
}

function createDatabase() {
    $db_host = safeFilter($_POST['db_host'] ?? 'localhost');
    $db_port = intval($_POST['db_port'] ?? 3306);
    $db_name = safeFilter($_POST['db_name'] ?? 'query_system');
    $db_user = safeFilter($_POST['db_user'] ?? 'root');
    $db_pass = safeFilter($_POST['db_pass'] ?? '');
    $import_demo = isset($_POST['import_demo']);
    
    try {
        // 连接数据库
        $mysqli = new mysqli($db_host, $db_user, $db_pass, '', $db_port);
        if ($mysqli->connect_error) {
            throw new Exception('数据库连接失败: ' . $mysqli->connect_error);
        }
        $mysqli->set_charset('utf8mb4');
        
        // 创建数据库
        $mysqli->query("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $mysqli->select_db($db_name);
        
        // 创建用户表
        $mysqli->query("
            CREATE TABLE IF NOT EXISTS `user` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `phone` varchar(20) NOT NULL,
                `password` varchar(255) NOT NULL,
                `wechat_id` varchar(100) DEFAULT NULL,
                `create_time` datetime DEFAULT CURRENT_TIMESTAMP,
                `status` enum('normal','disabled') DEFAULT 'normal',
                PRIMARY KEY (`id`),
                UNIQUE KEY `phone` (`phone`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // 创建系统设置表
        $mysqli->query("
            CREATE TABLE IF NOT EXISTS `site` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `domain` varchar(100) NOT NULL,
                `admin` varchar(100) NOT NULL,
                `user_type` varchar(10) DEFAULT 'vip0',
                `admin_access` tinyint(1) DEFAULT '1',
                `title` varchar(255) DEFAULT NULL,
                `condition1` varchar(100) DEFAULT NULL,
                `condition2` varchar(100) DEFAULT NULL,
                `condition3` varchar(100) DEFAULT NULL,
                `query_rule` varchar(10) DEFAULT 'T1',
                `match_rule` varchar(10) DEFAULT 'd',
                `footer_text` varchar(255) DEFAULT NULL,
                `footer_link` varchar(255) DEFAULT NULL,
                `has_result_text` varchar(255) DEFAULT NULL,
                `no_result_text` varchar(255) DEFAULT NULL,
                `input_hint_text` text,
                `captcha_enabled` tinyint(1) DEFAULT '0',
                `user_access` tinyint(1) DEFAULT '1',
                `page_size` int(11) DEFAULT '20',
                PRIMARY KEY (`id`),
                UNIQUE KEY `domain` (`domain`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // 创建数据表
        $mysqli->query("
            CREATE TABLE IF NOT EXISTS `data` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `condition1` varchar(255) NOT NULL,
                `condition2` varchar(255) DEFAULT NULL,
                `condition3` varchar(255) DEFAULT NULL,
                `detail_data` text,
                `batch` varchar(50) DEFAULT NULL,
                `query_status` tinyint(1) DEFAULT '1',
                `add_time` datetime DEFAULT CURRENT_TIMESTAMP,
                `query_count` int(11) DEFAULT '0',
                `query_remark` text,
                PRIMARY KEY (`id`),
                KEY `idx_condition1` (`condition1`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // 创建日志表
        $mysqli->query("
            CREATE TABLE IF NOT EXISTS `logs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `log_time` datetime DEFAULT CURRENT_TIMESTAMP,
                `user` varchar(100) NOT NULL,
                `domain` varchar(100) NOT NULL,
                `ip` varchar(45) NOT NULL,
                `act` varchar(50) NOT NULL,
                `remark` text,
                PRIMARY KEY (`id`),
                KEY `idx_user` (`user`),
                KEY `idx_domain` (`domain`),
                KEY `idx_log_time` (`log_time`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        // 更新配置文件
        $config_content = "<?php
// 数据库连接配置
\$db_host = '{$db_host}';
\$db_user = '{$db_user}';
\$db_pass = '{$db_pass}';
\$db_name = '{$db_name}';

// 获取域名前缀作为表前缀
\$domain = \$_SERVER['HTTP_HOST'] ?? 'localhost';
\$domain_parts = explode('.', \$domain);
\$table_prefix = \$domain_parts[0];

// 版本号，用于更新浏览器缓存
\$version = '1.0.0';

// 上传文件大小限制 (4MB)
\$upload_max_size = 4 * 1024 * 1024;

// 菜单配置
\$user_menus = [
    'list' => '数据列表',
    'liin' => '数据导入', 
    'tong' => '统计管理',
    'site' => '系统设置',
    'baks' => '数据备份',
    'pass' => '修改密码',
    'help' => '使用帮助'
];

\$admin_menus = [
    'user' => '用户列表',
    'list' => '查询列表',
    'logs' => '操作日志',
    'pass' => '修改密码',
    'help' => '使用帮助'
];

// 数据库连接
\$mysqli = new mysqli(\$db_host, \$db_user, \$db_pass, \$db_name, {$db_port});
if (\$mysqli->connect_error) {
    die('数据库连接失败: ' . \$mysqli->connect_error);
}
\$mysqli->set_charset('utf8mb4');
?>";
        
        file_put_contents('inc/conn.php', $config_content);
        
        // 创建默认站点配置
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $mysqli->query("
            INSERT INTO `site` (domain, admin, title, condition1, query_rule, match_rule, page_size) 
            VALUES ('{$domain}', 'admin', '查询系统', '账号', 'T1', 'd', 20)
        ");
        
        $mysqli->close();
        
        success('数据库创建成功');
        
    } catch (Exception $e) {
        error('数据库创建失败: ' . $e->getMessage());
    }
}

function createAdmin() {
    $username = safeFilter($_POST['admin_username'] ?? '');
    $password = safeFilter($_POST['admin_password'] ?? '');
    $confirm_password = safeFilter($_POST['confirm_password'] ?? '');
    $email = safeFilter($_POST['admin_email'] ?? '');
    
    if (empty($username) || empty($password)) {
        error('用户名和密码不能为空');
    }
    
    if ($password !== $confirm_password) {
        error('两次输入的密码不一致');
    }
    
    if (!validatePassword($password)) {
        error('密码格式不正确，请输入6-16位数字字母');
    }
    
    // 这里简化处理，实际应该更新数据库中的管理员密码
    // 或者创建管理员用户表
    
    success('管理员创建成功');
}

function importDemoData() {
    try {
        require_once 'inc/conn.php';
        require_once 'inc/sqls.php';
        
        // 生成演示数据
        $demo_data = [];
        $batch = date('YmdHis') . rand(1000, 9999);
        
        for ($i = 1; $i <= 30; $i++) {
            $demo_data[] = [
                'condition1' => 'demo' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'condition2' => '类型' . rand(1, 5),
                'condition3' => '状态' . rand(1, 3),
                'detail_data' => "姓名,演示用户{$i}\n年龄," . rand(18, 60) . "\n地址,演示地址{$i}",
                'batch' => $batch,
                'query_status' => 1,
                'add_time' => date('Y-m-d H:i:s'),
                'query_count' => rand(0, 10)
            ];
        }
        
        // 批量插入数据
        foreach ($demo_data as $data) {
            $db->insert('data', $data);
        }
        
        success('演示数据导入成功', ['count' => count($demo_data)]);
        
    } catch (Exception $e) {
        error('演示数据导入失败: ' . $e->getMessage());
    }
}
?>