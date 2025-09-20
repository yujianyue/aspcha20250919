<?php
// 管理员 - 修改密码

$act = $_GET['act'] ?? '';

switch ($act) {
    case 'change':
        changeAdminPassword();
        break;
    default:
        showPasswordPage();
        break;
}

function showPasswordPage() {
    global $version;
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>修改密码 - 查询系统</title>
        <link rel="stylesheet" href="inc/style.css?v=<?php echo $version; ?>">
    </head>
    <body>
        <div class="header">
            <h1>修改密码</h1>
            <button class="btn btn-secondary" onclick="location.href='admin.php?de=logout'">退出</button>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab" onclick="switchTab('user')">用户列表</button>
                <button class="tab" onclick="switchTab('list')">查询列表</button>
                <button class="tab" onclick="switchTab('logs')">操作日志</button>
                <button class="tab active" onclick="switchTab('pass')">修改密码</button>
                <button class="tab" onclick="switchTab('help')">使用帮助</button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">修改管理员密码</h3>
                </div>
                
                <form id="passwordForm">
                    <div class="form-group">
                        <label>当前密码:</label>
                        <input type="password" name="old_password" class="form-control" placeholder="请输入当前密码" required>
                    </div>
                    
                    <div class="form-group">
                        <label>新密码:</label>
                        <input type="password" name="new_password" class="form-control" placeholder="请输入新密码 (6-16位数字字母)" required>
                        <small class="form-text">密码长度6-16位，只能包含数字和字母</small>
                    </div>
                    
                    <div class="form-group">
                        <label>确认新密码:</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="请再次输入新密码" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">修改密码</button>
                        <span id="resultMessage" style="margin-left: 15px;"></span>
                    </div>
                </form>
            </div>
        </div>
        
        <script src="inc/js.js?v=<?php echo $version; ?>"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('passwordForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    changePassword();
                });
            });
            
            function switchTab(tab) {
                window.location.href = 'admin.php?de=' + tab;
            }
            
            function changePassword() {
                const form = document.getElementById('passwordForm');
                const formData = new FormData(form);
                const resultMessage = document.getElementById('resultMessage');
                
                // 验证密码格式
                const newPassword = formData.get('new_password');
                const confirmPassword = formData.get('confirm_password');
                
                if (!/^[a-zA-Z0-9]{6,16}$/.test(newPassword)) {
                    showPasswordResult('密码格式不正确，请输入6-16位数字字母', 'error');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    showPasswordResult('两次输入的密码不一致', 'error');
                    return;
                }
                
                showLoading();
                
                ajaxRequest('admin.php?de=pass&act=change', Object.fromEntries(formData), function(response) {
                    hideLoading();
                    
                    if (response.code === 1) {
                        showPasswordResult('密码修改成功', 'success');
                        form.reset();
                    } else {
                        showPasswordResult(response.msg, 'error');
                    }
                });
            }
            
            function showPasswordResult(message, type) {
                const resultMessage = document.getElementById('resultMessage');
                resultMessage.textContent = message;
                resultMessage.className = 'status-' + type;
                resultMessage.style.color = type === 'success' ? '#27ae60' : '#e74c3c';
                
                setTimeout(() => {
                    resultMessage.textContent = '';
                }, 3000);
            }
        </script>
    </body>
    </html>
    <?php
}

function changeAdminPassword() {
    $old_password = safeFilter($_POST['old_password'] ?? '');
    $new_password = safeFilter($_POST['new_password'] ?? '');
    $confirm_password = safeFilter($_POST['confirm_password'] ?? '');
    
    // 验证输入
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        error('所有字段都不能为空');
    }
    
    if (!validatePassword($new_password)) {
        error('新密码格式不正确，请输入6-16位数字字母');
    }
    
    if ($new_password !== $confirm_password) {
        error('两次输入的密码不一致');
    }
    
    // 验证旧密码（这里简化处理，实际应该从数据库读取）
    if ($old_password !== 'admin123') {
        error('当前密码错误');
    }
    
    // 记录日志
    writeLog('admin', $_SERVER['HTTP_HOST'], getClientIP(), 'change_admin_password', '修改管理员密码');
    
    success('密码修改成功（注意：实际应用中需要更新数据库中的密码）');
}
?>