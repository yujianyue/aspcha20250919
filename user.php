<?php
require_once 'inc/conn.php';
require_once 'inc/sqls.php';
require_once 'inc/pubs.php';

session_start();

$do = $_GET['do'] ?? 'login';

// 检查登录状态（除了登录页面）
if ($do !== 'login' && $do !== 'logout') {
    checkLogin();
}

switch ($do) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'list':
        require_once 'lib/list.php';
        break;
    case 'liin':
        require_once 'lib/liin.php';
        break;
    case 'tong':
        require_once 'lib/tong.php';
        break;
    case 'site':
        require_once 'lib/site.php';
        break;
    case 'baks':
        require_once 'lib/baks.php';
        break;
    case 'pass':
        require_once 'lib/pass.php';
        break;
    case 'help':
        require_once 'lib/help.php';
        break;
    default:
        showLoginPage();
        break;
}

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $phone = safeFilter($_POST['phone'] ?? '');
        $password = safeFilter($_POST['password'] ?? '');
        $captcha = safeFilter($_POST['captcha'] ?? '');
        
        // 验证输入
        if (empty($phone) || empty($password)) {
            error('手机号和密码不能为空');
        }
        
        if (!validatePhone($phone)) {
            error('手机号格式不正确');
        }
        
        if (!verifyCaptcha($captcha)) {
            error('验证码错误');
        }
        
        // 验证用户
        global $db, $table_prefix;
        $user = $db->fetchRow("SELECT * FROM `{$table_prefix}_user` WHERE phone = '{$phone}' AND status = 'normal'");
        
        if (!$user || !password_verify($password, $user['password'])) {
            error('用户名或密码错误');
        }
        
        // 登录成功
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_domain'] = $_SERVER['HTTP_HOST'];
        
        // 记录日志
        writeLog($user['phone'], $_SERVER['HTTP_HOST'], getClientIP(), 'login', '用户登录');
        
        success('登录成功', ['redirect' => 'user.php?do=list']);
    } else {
        showLoginPage();
    }
}

function handleLogout() {
    session_destroy();
    success('已退出登录', ['redirect' => 'user.php']);
}

function showLoginPage() {
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>用户登录 - 查询系统</title>
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
                background: #3498db;
                color: white;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
                transition: background 0.3s;
            }
            .login-btn:hover {
                background: #2980b9;
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
                <h1>用户登录</h1>
                <p>请输入您的登录信息</p>
            </div>
            
            <form class="login-form" id="loginForm">
                <div class="form-group">
                    <label>手机号:</label>
                    <input type="tel" name="phone" class="form-control" placeholder="请输入手机号" required>
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
                <p>查询系统 v<?php echo $version; ?></p>
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
                
                ajaxRequest('user.php?do=login', Object.fromEntries(formData), function(response) {
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