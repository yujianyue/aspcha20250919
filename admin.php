<?php
require_once 'inc/conn.php';
require_once 'inc/sqls.php';
require_once 'inc/pubs.php';

session_start();

$de = $_GET['de'] ?? 'login';

// 检查管理员登录状态（除了登录页面）
if ($de !== 'login' && $de !== 'logout') {
    checkAdminLogin();
}

switch ($de) {
    case 'login':
        handleAdminLogin();
        break;
    case 'logout':
        handleAdminLogout();
        break;
    case 'user':
        require_once 'lia/user.php';
        break;
    case 'list':
        require_once 'lia/list.php';
        break;
    case 'logs':
        require_once 'lia/logs.php';
        break;
    case 'pass':
        require_once 'lia/pass.php';
        break;
    case 'help':
        require_once 'lia/help.php';
        break;
    default:
        showAdminLoginPage();
        break;
}

function handleAdminLogin() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = safeFilter($_POST['username'] ?? '');
        $password = safeFilter($_POST['password'] ?? '');
        $captcha = safeFilter($_POST['captcha'] ?? '');
        
        // 验证输入
        if (empty($username) || empty($password)) {
            error('用户名和密码不能为空');
        }
        
        if (!verifyCaptcha($captcha)) {
            error('验证码错误');
        }
        
        // 验证管理员账户
        if ($username === 'admin' && $password === 'admin123') {
            // 登录成功
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_username'] = 'admin';
            
            // 记录日志
            writeLog('admin', $_SERVER['HTTP_HOST'], getClientIP(), 'admin_login', '管理员登录');
            
            success('登录成功', ['redirect' => 'admin.php?de=user']);
        } else {
            error('用户名或密码错误');
        }
    } else {
        showAdminLoginPage();
    }
}

function handleAdminLogout() {
    session_destroy();
    success('已退出登录', ['redirect' => 'admin.php']);
}

function showAdminLoginPage() {
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>管理员登录 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
        <style>
            .login-container {
                max-width: 400px;
                margin: 100px auto;
                background: white;
                border-radius: 8px;
                padding: 40px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .login-header {
                text-align: center;
                margin-bottom: 30px;
            }
            .login-header h1 {
                color: #2c3e50;
                margin-bottom: 10px;
            }
            .login-form .form-group {
                margin-bottom: 20px;
            }
            .login-form .form-control {
                width: 100%;
                padding: 12px 15px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            .login-form .form-control:focus {
                outline: none;
                border-color: #3498db;
                box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            }
            .captcha-row {
                display: flex;
                gap: 10px;
                align-items: center;
            }
            .captcha-img {
                width: 100px;
                height: 40px;
                border: 1px solid #ddd;
                border-radius: 4px;
                cursor: pointer;
                background: #f8f9fa;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                font-weight: bold;
                color: #333;
            }
            .login-btn {
                width: 100%;
                padding: 12px;
                background: #e74c3c;
                color: white;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
                transition: background 0.3s;
            }
            .login-btn:hover {
                background: #c0392b;
            }
            .login-btn:disabled {
                background: #bdc3c7;
                cursor: not-allowed;
            }
            .login-footer {
                text-align: center;
                margin-top: 20px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-header">
                <h1>管理员登录</h1>
                <p>请输入管理员账户信息</p>
            </div>
            
            <form class="login-form" id="loginForm">
                <div class="form-group">
                    <label>用户名:</label>
                    <input type="text" name="username" class="form-control" placeholder="请输入用户名" required>
                </div>
                
                <div class="form-group">
                    <label>密码:</label>
                    <input type="password" name="password" class="form-control" placeholder="请输入密码" required>
                </div>
                
                <div class="form-group">
                    <label>验证码:</label>
                    <div class="captcha-row">
                        <input type="text" name="captcha" class="form-control" placeholder="请输入验证码" style="width: 120px;" required>
                        <div class="captcha-img" onclick="refreshCaptcha()" id="captchaImg">点击刷新</div>
                    </div>
                </div>
                
                <button type="submit" class="login-btn" id="loginBtn">登录</button>
            </form>
            
            <div class="login-footer">
                <p>查询系统管理后台 v<?php echo $version; ?></p>
            </div>
        </div>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                refreshCaptcha();
                
                document.getElementById('loginForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    performLogin();
                });
            });
            
            function refreshCaptcha() {
                const captchaImg = document.getElementById('captchaImg');
                captchaImg.textContent = '加载中...';
                captchaImg.style.background = '#f8f9fa';
                setTimeout(() => {
                    captchaImg.textContent = Math.floor(Math.random() * 9000 + 1000);
                    captchaImg.style.background = '#e9ecef';
                }, 500);
            }
            
            function performLogin() {
                const form = document.getElementById('loginForm');
                const formData = new FormData(form);
                
                showLoading();
                
                ajaxRequest('admin.php?de=login', Object.fromEntries(formData), function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        showToast('登录成功', 'success');
                        setTimeout(() => {
                            window.location.href = response.data.redirect;
                        }, 1000);
                    } else {
                        showToast(response.msg, 'error');
                        refreshCaptcha();
                    }
                });
            }
        </script>
    </body>
    </html>
    <?php
}
?>